<?php
require_once('SalesForecasting.php');

class ForecastValidation {
    private $mysqli;
    private $salesForecasting;
    
    public function __construct($mysqli) {
        if (!$mysqli || $mysqli->connect_error) {
            throw new Exception('Database connection failed: ' . ($mysqli ? $mysqli->connect_error : 'No connection'));
        }
        $this->mysqli = $mysqli;
        $this->salesForecasting = new SalesForecasting($mysqli);
    }
    
    /**
     * Calculate forecast accuracy metrics
     */
    public function calculateAccuracy($product_id, $forecast_days = 30) {
        // Input validation
        if (empty($product_id) || !is_string($product_id)) {
            throw new InvalidArgumentException('Product ID must be a non-empty string');
        }
        
        if (!is_int($forecast_days) || $forecast_days < 1 || $forecast_days > 365) {
            throw new InvalidArgumentException('Forecast days must be an integer between 1 and 365');
        }
        
        // Get historical forecasts and actual sales
        $historical_forecasts = $this->getHistoricalForecasts($product_id, $forecast_days);
        $actual_sales = $this->getActualSales($product_id, $forecast_days);
        
        if (empty($historical_forecasts) || empty($actual_sales)) {
            return null;
        }
        
        $metrics = [
            'mae' => 0,      // Mean Absolute Error
            'mse' => 0,      // Mean Squared Error
            'rmse' => 0,     // Root Mean Squared Error
            'mape' => 0,     // Mean Absolute Percentage Error
            'accuracy' => 0, // Overall accuracy percentage
            'bias' => 0      // Forecast bias
        ];
        
        $total_error = 0;
        $total_squared_error = 0;
        $total_percentage_error = 0;
        $total_bias = 0;
        $count = 0;
        
        foreach ($historical_forecasts as $forecast) {
            $date = $forecast['forecast_date'];
            $predicted = $forecast['predicted_demand'];
            
            // Find actual sales for this date
            $actual = 0;
            foreach ($actual_sales as $sale) {
                if ($sale['sale_date'] == $date) {
                    $actual = $sale['daily_quantity'];
                    break;
                }
            }
            
            if ($actual > 0) {
                $error = abs($predicted - $actual);
                $squared_error = pow($error, 2);
                $percentage_error = ($error / $actual) * 100;
                $bias = $predicted - $actual;
                
                $total_error += $error;
                $total_squared_error += $squared_error;
                $total_percentage_error += $percentage_error;
                $total_bias += $bias;
                $count++;
            }
        }
        
        if ($count > 0) {
            $metrics['mae'] = round($total_error / $count, 2);
            $metrics['mse'] = round($total_squared_error / $count, 2);
            $metrics['rmse'] = round(sqrt($total_squared_error / $count), 2);
            $metrics['mape'] = round($total_percentage_error / $count, 2);
            $metrics['accuracy'] = round(max(0, 100 - ($total_percentage_error / $count)), 2);
            $metrics['bias'] = round($total_bias / $count, 2);
        }
        
        return $metrics;
    }
    
    /**
     * Get historical forecasts for validation using real forecasting data
     */
    private function getHistoricalForecasts($product_id, $days) {
        try {
            // Get historical sales data to create past forecasts
            $historical_sales = $this->salesForecasting->getHistoricalSales($product_id, $days + 30); // Get extra data for forecasting
            
            if (empty($historical_sales)) {
                return [];
            }
        
        $forecasts = [];
            $sales_count = count($historical_sales);
            
            // Create forecasts for each day by using previous data to predict the next day
            for ($i = 7; $i < $sales_count; $i++) {
                $forecast_date = $historical_sales[$i]['sale_date'];
                
                // Use previous 7 days to predict this day
                $previous_days = array_slice($historical_sales, $i - 7, 7);
                $avg_demand = array_sum(array_column($previous_days, 'daily_quantity')) / 7;
                
                // Apply some forecasting logic similar to SalesForecasting class
                $trend = $this->calculateTrend($previous_days);
                $seasonal_factor = $this->getSeasonalFactorForDate($forecast_date);
                
                $predicted_demand = ($avg_demand + $trend) * $seasonal_factor;
                
            $forecasts[] = [
                'forecast_date' => $forecast_date,
                    'predicted_demand' => max(0, round($predicted_demand, 2))
            ];
        }
        
        return $forecasts;
        } catch (Exception $e) {
            error_log("Error getting historical forecasts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get actual sales data using SalesForecasting class
     */
    private function getActualSales($product_id, $days) {
        try {
            return $this->salesForecasting->getHistoricalSales($product_id, $days);
        } catch (Exception $e) {
            error_log("Error getting actual sales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate forecast model performance
     */
    public function validateModel($product_id) {
        $accuracy_7 = $this->calculateAccuracy($product_id, 7);
        $accuracy_14 = $this->calculateAccuracy($product_id, 14);
        $accuracy_30 = $this->calculateAccuracy($product_id, 30);
        
        $validation = [
            'product_id' => $product_id,
            'validation_periods' => [
                '7_days' => $accuracy_7,
                '14_days' => $accuracy_14,
                '30_days' => $accuracy_30
            ],
            'overall_rating' => $this->calculateOverallRating($accuracy_7, $accuracy_14, $accuracy_30),
            'recommendations' => $this->generateRecommendations($accuracy_7, $accuracy_14, $accuracy_30)
        ];
        
        return $validation;
    }
    
    /**
     * Calculate overall model rating
     */
    private function calculateOverallRating($acc_7, $acc_14, $acc_30) {
        if (!$acc_7 || !$acc_14 || !$acc_30) {
            return 'insufficient_data';
        }
        
        $avg_accuracy = ($acc_7['accuracy'] + $acc_14['accuracy'] + $acc_30['accuracy']) / 3;
        
        if ($avg_accuracy >= 90) {
            return 'excellent';
        } elseif ($avg_accuracy >= 80) {
            return 'good';
        } elseif ($avg_accuracy >= 70) {
            return 'fair';
        } elseif ($avg_accuracy >= 60) {
            return 'poor';
        } else {
            return 'very_poor';
        }
    }
    
    /**
     * Generate model improvement recommendations
     */
    private function generateRecommendations($acc_7, $acc_14, $acc_30) {
        $recommendations = [];
        
        if (!$acc_7 || !$acc_14 || !$acc_30) {
            return ['Collect more historical data for better forecasting accuracy'];
        }
        
        // Check for bias
        if (abs($acc_7['bias']) > 2) {
            $recommendations[] = 'High forecast bias detected. Consider adjusting trend calculations.';
        }
        
        // Check for accuracy degradation over time
        if ($acc_7['accuracy'] - $acc_30['accuracy'] > 20) {
            $recommendations[] = 'Accuracy degrades significantly over longer periods. Consider shorter forecast horizons.';
        }
        
        // Check for high MAPE
        if ($acc_7['mape'] > 30) {
            $recommendations[] = 'High percentage error. Consider including more external factors.';
        }
        
        // Check for high RMSE
        if ($acc_7['rmse'] > 10) {
            $recommendations[] = 'High forecast variance. Consider smoothing techniques.';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Model performance is satisfactory. Continue monitoring.';
        }
        
        return $recommendations;
    }
    
    /**
     * Get performance dashboard data
     */
    public function getPerformanceDashboard() {
        $query = "SELECT prod_id, prod_name FROM rpos_products ORDER BY prod_name ASC";
        $stmt = $this->mysqli->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dashboard_data = [];
        while ($product = $result->fetch_assoc()) {
            $validation = $this->validateModel($product['prod_id']);
            $dashboard_data[] = [
                'product_id' => $product['prod_id'],
                'product_name' => $product['prod_name'],
                'overall_rating' => $validation['overall_rating'],
                'accuracy_7_days' => $validation['validation_periods']['7_days']['accuracy'] ?? 0,
                'accuracy_14_days' => $validation['validation_periods']['14_days']['accuracy'] ?? 0,
                'accuracy_30_days' => $validation['validation_periods']['30_days']['accuracy'] ?? 0,
                'recommendations_count' => count($validation['recommendations'])
            ];
        }
        
        return $dashboard_data;
    }
    
    /**
     * Get detailed performance metrics for a specific product
     */
    public function getDetailedPerformance($product_id) {
        $validation = $this->validateModel($product_id);
        
        if (!$validation || $validation['overall_rating'] === 'insufficient_data') {
            return null;
        }
        
        // Get additional metrics
        $acc_7 = $validation['validation_periods']['7_days'];
        $acc_14 = $validation['validation_periods']['14_days'];
        $acc_30 = $validation['validation_periods']['30_days'];
        
        return [
            'product_id' => $product_id,
            'overall_rating' => $validation['overall_rating'],
            'accuracy_metrics' => [
                '7_days' => $acc_7,
                '14_days' => $acc_14,
                '30_days' => $acc_30
            ],
            'trend_analysis' => [
                'accuracy_trend' => $this->calculateAccuracyTrend($acc_7, $acc_14, $acc_30),
                'bias_trend' => $this->calculateBiasTrend($acc_7, $acc_14, $acc_30),
                'variance_trend' => $this->calculateVarianceTrend($acc_7, $acc_14, $acc_30)
            ],
            'recommendations' => $validation['recommendations'],
            'performance_summary' => $this->generatePerformanceSummary($validation)
        ];
    }
    
    /**
     * Calculate accuracy trend over time
     */
    private function calculateAccuracyTrend($acc_7, $acc_14, $acc_30) {
        $trend = 'stable';
        $diff_7_14 = $acc_7['accuracy'] - $acc_14['accuracy'];
        $diff_14_30 = $acc_14['accuracy'] - $acc_30['accuracy'];
        
        if ($diff_7_14 > 10 && $diff_14_30 > 10) {
            $trend = 'improving';
        } elseif ($diff_7_14 < -10 && $diff_14_30 < -10) {
            $trend = 'declining';
        }
        
        return $trend;
    }
    
    /**
     * Calculate bias trend over time
     */
    private function calculateBiasTrend($acc_7, $acc_14, $acc_30) {
        $bias_7 = abs($acc_7['bias']);
        $bias_14 = abs($acc_14['bias']);
        $bias_30 = abs($acc_30['bias']);
        
        if ($bias_7 < $bias_14 && $bias_14 < $bias_30) {
            return 'improving';
        } elseif ($bias_7 > $bias_14 && $bias_14 > $bias_30) {
            return 'declining';
        }
        
        return 'stable';
    }
    
    /**
     * Calculate variance trend over time
     */
    private function calculateVarianceTrend($acc_7, $acc_14, $acc_30) {
        $rmse_7 = $acc_7['rmse'];
        $rmse_14 = $acc_14['rmse'];
        $rmse_30 = $acc_30['rmse'];
        
        if ($rmse_7 < $rmse_14 && $rmse_14 < $rmse_30) {
            return 'improving';
        } elseif ($rmse_7 > $rmse_14 && $rmse_14 > $rmse_30) {
            return 'declining';
        }
        
        return 'stable';
    }
    
    /**
     * Generate performance summary
     */
    private function generatePerformanceSummary($validation) {
        $avg_accuracy = ($validation['validation_periods']['7_days']['accuracy'] + 
                        $validation['validation_periods']['14_days']['accuracy'] + 
                        $validation['validation_periods']['30_days']['accuracy']) / 3;
        
        $summary = [
            'overall_score' => round($avg_accuracy, 1),
            'grade' => $this->getPerformanceGrade($avg_accuracy),
            'strengths' => [],
            'weaknesses' => []
        ];
        
        // Identify strengths
        if ($validation['validation_periods']['7_days']['accuracy'] > 85) {
            $summary['strengths'][] = 'Excellent short-term forecasting';
        }
        if (abs($validation['validation_periods']['7_days']['bias']) < 2) {
            $summary['strengths'][] = 'Low forecast bias';
        }
        if ($validation['validation_periods']['7_days']['mape'] < 20) {
            $summary['strengths'][] = 'Low percentage error';
        }
        
        // Identify weaknesses
        if ($validation['validation_periods']['30_days']['accuracy'] < 70) {
            $summary['weaknesses'][] = 'Poor long-term forecasting';
        }
        if (abs($validation['validation_periods']['7_days']['bias']) > 5) {
            $summary['weaknesses'][] = 'High forecast bias';
        }
        if ($validation['validation_periods']['7_days']['mape'] > 30) {
            $summary['weaknesses'][] = 'High percentage error';
        }
        
        return $summary;
    }
    
    /**
     * Get performance grade based on accuracy
     */
    private function getPerformanceGrade($accuracy) {
        if ($accuracy >= 90) return 'A';
        if ($accuracy >= 80) return 'B';
        if ($accuracy >= 70) return 'C';
        if ($accuracy >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Store forecast validation results
     */
    public function storeValidationResults($product_id, $validation_results) {
        $query = "INSERT INTO rpos_forecast_validations 
                  (product_id, validation_date, accuracy_7_days, accuracy_14_days, accuracy_30_days, 
                   overall_rating, mae, mse, rmse, mape, bias, recommendations) 
                  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->mysqli->prepare($query);
        $recommendations_json = json_encode($validation_results['recommendations']);
        
        $acc_7 = $validation_results['validation_periods']['7_days']['accuracy'] ?? 0;
        $acc_14 = $validation_results['validation_periods']['14_days']['accuracy'] ?? 0;
        $acc_30 = $validation_results['validation_periods']['30_days']['accuracy'] ?? 0;
        $mae = $validation_results['validation_periods']['7_days']['mae'] ?? 0;
        $mse = $validation_results['validation_periods']['7_days']['mse'] ?? 0;
        $rmse = $validation_results['validation_periods']['7_days']['rmse'] ?? 0;
        $mape = $validation_results['validation_periods']['7_days']['mape'] ?? 0;
        $bias = $validation_results['validation_periods']['7_days']['bias'] ?? 0;
        
        $stmt->bind_param('sddddddddds', $product_id, $acc_7, $acc_14, $acc_30, 
                         $validation_results['overall_rating'], $mae, $mse, $rmse, $mape, $bias, $recommendations_json);
        
        return $stmt->execute();
    }
    
    /**
     * Calculate trend from sales data
     */
    private function calculateTrend($sales_data) {
        if (count($sales_data) < 2) return 0;
        
        $n = count($sales_data);
        $sum_x = 0;
        $sum_y = 0;
        $sum_xy = 0;
        $sum_x2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $sales_data[$i]['daily_quantity'];
            
            $sum_x += $x;
            $sum_y += $y;
            $sum_xy += $x * $y;
            $sum_x2 += $x * $x;
        }
        
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        return round($slope, 4);
    }
    
    /**
     * Get seasonal factor for specific date
     */
    private function getSeasonalFactorForDate($date) {
        $day_of_week = date('w', strtotime($date));
        $month = date('n', strtotime($date));
        
        // Default seasonal factors (can be adjusted based on business patterns)
        $day_factors = [
            0 => 0.8, // Sunday
            1 => 1.1, // Monday
            2 => 1.2, // Tuesday
            3 => 1.3, // Wednesday
            4 => 1.4, // Thursday
            5 => 1.5, // Friday
            6 => 1.2  // Saturday
        ];
        
        $month_factors = [
            1 => 1.1, 2 => 1.0, 3 => 1.0, 4 => 1.0,
            5 => 1.0, 6 => 1.0, 7 => 1.0, 8 => 1.0,
            9 => 1.0, 10 => 1.0, 11 => 1.1, 12 => 1.2
        ];
        
        return ($day_factors[$day_of_week] ?? 1.0) * ($month_factors[$month] ?? 1.0);
    }
}
?>
