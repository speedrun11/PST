<?php
class SalesForecasting {
    private $mysqli;
    private $external_factors = [];
    
    public function __construct($mysqli) {
        if (!$mysqli || $mysqli->connect_error) {
            throw new Exception('Database connection failed: ' . ($mysqli ? $mysqli->connect_error : 'No connection'));
        }
        $this->mysqli = $mysqli;
        $this->loadExternalFactors();
    }
    
    /**
     * Load external factors that affect sales
     */
    private function loadExternalFactors() {
        // Weather factor (simulated - in real implementation, use weather API)
        $this->external_factors['weather'] = $this->getWeatherFactor();
        
        // Holiday factor
        $this->external_factors['holiday'] = $this->getHolidayFactor();
        
        // Day of week factor
        $this->external_factors['day_of_week'] = $this->getDayOfWeekFactor();
        
        // Seasonal factor
        $this->external_factors['seasonal'] = $this->getSeasonalFactor();
    }
    
    /**
     * Get historical sales data for a product (including linked products)
     */
    public function getHistoricalSales($product_id, $days = 90) {
        // Input validation
        if (empty($product_id) || !is_string($product_id)) {
            throw new InvalidArgumentException('Product ID must be a non-empty string');
        }
        
        if (!is_int($days) || $days < 1 || $days > 365) {
            throw new InvalidArgumentException('Days must be an integer between 1 and 365');
        }
        
        // Get product links to understand relationships
        $product_links = $this->getProductLinks($product_id);
        
        // Build query to include sales from linked products
        $linked_products = [$product_id]; // Include the product itself
        
        // Add linked products based on relationships
        foreach ($product_links as $link) {
            if ($link['relation'] === 'mirror') {
                // For mirror products, we need to get sales of the base product
                // and convert them to mirror product equivalent (divide by 2)
                $linked_products[] = $link['base_product_id'];
            } elseif ($link['relation'] === 'combo') {
                // For combo products, we need to get sales of the combo product itself
                // and also consider the base products
                $linked_products[] = $link['base_product_id'];
            }
        }
        
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($linked_products) - 1) . '?';
        
        $query = "SELECT 
                    DATE(created_at) as sale_date,
                    SUM(CAST(prod_qty AS UNSIGNED)) as daily_quantity,
                    COUNT(*) as daily_orders,
                    SUM(CAST(prod_price AS DECIMAL(10,2)) * CAST(prod_qty AS UNSIGNED)) as daily_revenue
                  FROM rpos_orders 
                  WHERE prod_id IN ($placeholders)
                    AND order_status IN ('Paid','Preparing','Ready','Completed')
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY sale_date ASC";
        
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare query: ' . $this->mysqli->error);
        }
        
        // Bind parameters: linked products + days
        $params = array_merge($linked_products, [$days]);
        $types = str_repeat('s', count($linked_products)) . 'i';
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception('Failed to get result: ' . $stmt->error);
        }
        
        $sales_data = [];
        while ($row = $result->fetch_assoc()) {
            // Adjust quantities based on product relationships
            $adjusted_quantity = $this->adjustQuantityForProductRelations($product_id, $row['daily_quantity'], $product_links);
            $row['daily_quantity'] = $adjusted_quantity;
            $sales_data[] = $row;
        }
        
        $stmt->close();
        return $sales_data;
    }
    
    /**
     * Calculate moving average for sales data
     */
    public function calculateMovingAverage($sales_data, $period = 7) {
        $moving_averages = [];
        $count = count($sales_data);
        
        for ($i = $period - 1; $i < $count; $i++) {
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += $sales_data[$j]['daily_quantity'];
            }
            $moving_averages[] = [
                'date' => $sales_data[$i]['sale_date'],
                'moving_average' => round($sum / $period, 2)
            ];
        }
        
        return $moving_averages;
    }
    
    /**
     * Calculate exponential smoothing forecast
     */
    public function calculateExponentialSmoothing($sales_data, $alpha = 0.3) {
        if (empty($sales_data)) return [];
        
        $forecasts = [];
        $forecasts[0] = $sales_data[0]['daily_quantity']; // Initial forecast
        
        for ($i = 1; $i < count($sales_data); $i++) {
            $forecasts[$i] = $alpha * $sales_data[$i]['daily_quantity'] + 
                           (1 - $alpha) * $forecasts[$i - 1];
        }
        
        return $forecasts;
    }
    
    /**
     * Predict future demand using multiple methods
     */
    public function predictFutureDemand($product_id, $forecast_days = 30) {
        $historical_sales = $this->getHistoricalSales($product_id, 90);
        
        if (empty($historical_sales)) {
            return $this->getFallbackForecast($product_id, $forecast_days);
        }
        
        // Calculate different forecasting methods
        $moving_avg = $this->calculateMovingAverage($historical_sales, 7);
        $exponential_smooth = $this->calculateExponentialSmoothing($historical_sales);
        
        // Get trend analysis
        $trend = $this->calculateTrend($historical_sales);
        
        // Get seasonal patterns
        $seasonal_pattern = $this->getSeasonalPattern($historical_sales);
        
        // Combine forecasts with external factors
        $forecast = [];
        $last_moving_avg = end($moving_avg)['moving_average'] ?? 0;
        $last_exponential = end($exponential_smooth) ?? 0;
        
        for ($i = 1; $i <= $forecast_days; $i++) {
            $forecast_date = date('Y-m-d', strtotime("+$i days"));
            
            // Base forecast (average of methods)
            $base_forecast = ($last_moving_avg + $last_exponential) / 2;
            
            // Apply trend
            $trend_adjusted = $base_forecast + ($trend * $i);
            
            // Apply seasonal factor
            $seasonal_factor = $this->getSeasonalFactorForDate($forecast_date);
            $seasonal_adjusted = $trend_adjusted * $seasonal_factor;
            
            // Apply external factors
            $external_factor = $this->getExternalFactorForDate($forecast_date);
            $final_forecast = $seasonal_adjusted * $external_factor;
            
            $forecast[] = [
                'date' => $forecast_date,
                'predicted_demand' => max(0, round($final_forecast, 2)),
                'confidence' => $this->calculateConfidence($historical_sales, $i),
                'factors' => [
                    'trend' => $trend,
                    'seasonal' => $seasonal_factor,
                    'external' => $external_factor
                ]
            ];
        }
        
        return $forecast;
    }

    /**
     * Public helpers to expose real external factors to the UI
     */
    public function getRealWeatherDataPublic() {
        $data = [];
        $sql = "SELECT weather_date, location, `condition`, temperature, humidity, impact_factor
                FROM rpos_weather_data
                ORDER BY weather_date DESC
                LIMIT 20";
        if ($res = $this->mysqli->query($sql)) {
            while ($row = $res->fetch_assoc()) { $data[] = $row; }
            $res->close();
        }
        return $data;
    }

    public function getRealHolidayDataPublic() {
        $data = [];
        $sql = "SELECT holiday_date, holiday_name, is_holiday, impact_factor
                FROM rpos_holiday_data
                ORDER BY holiday_date DESC
                LIMIT 50";
        if ($res = $this->mysqli->query($sql)) {
            while ($row = $res->fetch_assoc()) { $data[] = $row; }
            $res->close();
        }
        return $data;
    }

    public function getRealEconomicDataPublic() {
        $data = [];
        $sql = "SELECT data_date, inflation_rate, unemployment_rate, consumer_confidence, impact_factor
                FROM rpos_economic_data
                ORDER BY data_date DESC
                LIMIT 24";
        if ($res = $this->mysqli->query($sql)) {
            while ($row = $res->fetch_assoc()) { $data[] = $row; }
            $res->close();
        }
        return $data;
    }

    public function getRealLocalEventsDataPublic() {
        $data = [];
        $sql = "SELECT event_date, location, event_type, event_name, impact_factor
                FROM rpos_local_events
                ORDER BY event_date DESC
                LIMIT 30";
        if ($res = $this->mysqli->query($sql)) {
            while ($row = $res->fetch_assoc()) { $data[] = $row; }
            $res->close();
        }
        return $data;
    }
    
    /**
     * Calculate trend from historical data
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
     * Get seasonal pattern from historical data
     */
    private function getSeasonalPattern($sales_data) {
        $day_of_week_avg = [];
        $month_avg = [];
        
        foreach ($sales_data as $sale) {
            $day_of_week = date('w', strtotime($sale['sale_date']));
            $month = date('n', strtotime($sale['sale_date']));
            
            if (!isset($day_of_week_avg[$day_of_week])) {
                $day_of_week_avg[$day_of_week] = [];
            }
            if (!isset($month_avg[$month])) {
                $month_avg[$month] = [];
            }
            
            $day_of_week_avg[$day_of_week][] = $sale['daily_quantity'];
            $month_avg[$month][] = $sale['daily_quantity'];
        }
        
        return [
            'day_of_week' => $day_of_week_avg,
            'month' => $month_avg
        ];
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
    
    /**
     * Get external factor for specific date
     */
    private function getExternalFactorForDate($date) {
        $day_of_week = date('w', strtotime($date));
        $is_holiday = $this->isHoliday($date);
        $weather_factor = $this->getWeatherFactorForDate($date);
        
        $factor = 1.0;
        
        // Holiday factor
        if ($is_holiday) {
            $factor *= 1.3; // 30% increase during holidays
        }
        
        // Weather factor
        $factor *= $weather_factor;
        
        return $factor;
    }
    
    /**
     * Calculate confidence level for forecast
     */
    private function calculateConfidence($sales_data, $forecast_days_ahead) {
        if (empty($sales_data)) return 0.5;
        
        $data_points = count($sales_data);
        $variance = $this->calculateVariance($sales_data);
        
        // Confidence decreases with forecast horizon
        $horizon_factor = max(0.3, 1 - ($forecast_days_ahead * 0.02));
        
        // Confidence increases with more data points
        $data_factor = min(1.0, $data_points / 30);
        
        // Confidence decreases with higher variance
        $variance_factor = max(0.5, 1 - ($variance / 100));
        
        return round($horizon_factor * $data_factor * $variance_factor, 2);
    }
    
    /**
     * Calculate variance of sales data
     */
    private function calculateVariance($sales_data) {
        if (count($sales_data) < 2) return 0;
        
        $mean = array_sum(array_column($sales_data, 'daily_quantity')) / count($sales_data);
        $variance = 0;
        
        foreach ($sales_data as $sale) {
            $variance += pow($sale['daily_quantity'] - $mean, 2);
        }
        
        return $variance / count($sales_data);
    }
    
    /**
     * Get fallback forecast when no historical data
     */
    private function getFallbackForecast($product_id, $forecast_days) {
        // Get product threshold as fallback
        $query = "SELECT prod_threshold FROM rpos_products WHERE prod_id = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        $daily_usage = $product['prod_threshold'] / 7; // Fallback calculation
        
        $forecast = [];
        for ($i = 1; $i <= $forecast_days; $i++) {
            $forecast[] = [
                'date' => date('Y-m-d', strtotime("+$i days")),
                'predicted_demand' => round($daily_usage, 2),
                'confidence' => 0.3, // Low confidence for fallback
                'factors' => [
                    'trend' => 0,
                    'seasonal' => 1.0,
                    'external' => 1.0
                ]
            ];
        }
        
        return $forecast;
    }

    /**
     * Validate forecast accuracy over the last N days using a rolling 7-day moving average as forecast
     * Returns: ['mae','mse','rmse','mape','bias','accuracy','overall_rating','data_points'] or null if insufficient data
     */
    public function validateForecastAccuracy($product_id, $days = 7) {
        // Fetch at least days + 7 to build moving averages
        $lookbackDays = max(14, (int)$days + 7);
        $sales = $this->getHistoricalSales($product_id, $lookbackDays);
        if (count($sales) < 8) {
            return null;
        }
        $totalError = 0.0;
        $totalSqError = 0.0;
        $totalPctError = 0.0;
        $totalBias = 0.0;
        $count = 0;
        // Use last $days data points for evaluation when available
        $startIndex = max(7, count($sales) - (int)$days);
        for ($i = $startIndex; $i < count($sales); $i++) {
            // previous 7 days average as forecast for today
            $window = array_slice($sales, $i - 7, 7);
            $avg = 0.0;
            foreach ($window as $w) { $avg += (float)$w['daily_quantity']; }
            $avg = $avg / 7.0;
            $actual = (float)$sales[$i]['daily_quantity'];
            if ($actual <= 0) { continue; }
            $err = abs($avg - $actual);
            $totalError += $err;
            $totalSqError += $err * $err;
            $totalPctError += ($err / max(1e-9, $actual)) * 100.0;
            $totalBias += ($avg - $actual);
            $count++;
        }
        if ($count === 0) { return null; }
        $mae = round($totalError / $count, 2);
        $mse = round($totalSqError / $count, 2);
        $rmse = round(sqrt($totalSqError / $count), 2);
        $mape = round($totalPctError / $count, 2);
        $bias = round($totalBias / $count, 2);
        $accuracy = round(max(0, 100 - $mape), 2);
        // Map accuracy to rating
        $overall = 'very_poor';
        if ($accuracy >= 90) $overall = 'excellent';
        elseif ($accuracy >= 80) $overall = 'good';
        elseif ($accuracy >= 70) $overall = 'fair';
        elseif ($accuracy >= 60) $overall = 'poor';
        return [
            'mae' => $mae,
            'mse' => $mse,
            'rmse' => $rmse,
            'mape' => $mape,
            'bias' => $bias,
            'accuracy' => $accuracy,
            'overall_rating' => $overall,
            'data_points' => $count,
        ];
    }
    
    /**
     * Check if date is a holiday
     */
    private function isHoliday($date) {
        $holidays = [
            '01-01', // New Year
            '12-25', // Christmas
            '12-31', // New Year's Eve
            // Add more holidays as needed
        ];
        
        $month_day = date('m-d', strtotime($date));
        return in_array($month_day, $holidays);
    }
    
    /**
     * Get weather factor (simulated)
     */
    private function getWeatherFactor() {
        // In real implementation, integrate with weather API
        return 1.0; // Neutral weather
    }
    
    private function getWeatherFactorForDate($date) {
        // Simulate weather impact on food sales
        $day_of_week = date('w', strtotime($date));
        
        // Weekend weather has more impact
        if ($day_of_week == 0 || $day_of_week == 6) {
            return 1.1; // 10% increase on weekends
        }
        
        return 1.0;
    }
    
    private function getHolidayFactor() {
        return 1.0;
    }
    
    private function getDayOfWeekFactor() {
        return 1.0;
    }
    
    private function getSeasonalFactor() {
        return 1.0;
    }
    
    /**
     * Generate restocking recommendations
     */
    public function generateRestockingRecommendations($product_id) {
        $forecast = $this->predictFutureDemand($product_id, 30);
        $product_info = $this->getProductInfo($product_id);
        
        if (!$product_info) return null;
        
        // Use effective stock that considers linked products
        $current_stock = $this->getEffectiveStock($product_id);
        $threshold = $product_info['prod_threshold'];
        
        // Calculate average daily demand from forecast
        $total_demand = array_sum(array_column($forecast, 'predicted_demand'));
        $avg_daily_demand = $total_demand / count($forecast);
        
        // Calculate days until stockout
        $days_until_stockout = $current_stock / max($avg_daily_demand, 1);
        
        // Calculate optimal reorder point
        $lead_time_days = 7; // Assume 7 days lead time
        $safety_stock = $avg_daily_demand * 3; // 3 days safety stock
        $optimal_reorder_point = ($avg_daily_demand * $lead_time_days) + $safety_stock;
        
        // Calculate economic order quantity
        $annual_demand = $avg_daily_demand * 365;
        $ordering_cost = 50; // Assume $50 ordering cost
        $holding_cost = $product_info['prod_price'] * 0.2; // 20% of price as holding cost
        $eoq = sqrt((2 * $annual_demand * $ordering_cost) / $holding_cost);
        
        // Get product type information
        $product_links = $this->getProductLinks($product_id);
        $product_type = $this->getProductType($product_links);
        
        $urgency = $this->calculateUrgency($days_until_stockout, $current_stock, $threshold);
        $urgency_details = $this->getUrgencyDetails($urgency, $current_stock, $threshold, $days_until_stockout);
        
        $recommendation = [
            'product_id' => $product_id,
            'product_name' => $product_info['prod_name'],
            'current_stock' => $current_stock,
            'threshold' => $threshold,
            'avg_daily_demand' => round($avg_daily_demand, 2),
            'days_until_stockout' => round($days_until_stockout, 1),
            'optimal_reorder_point' => round($optimal_reorder_point, 0),
            'economic_order_quantity' => round($eoq, 0),
            'recommended_order_quantity' => max(round($eoq, 0), round($optimal_reorder_point - $current_stock, 0)),
            'urgency' => $urgency,
            'urgency_details' => $urgency_details,
            'confidence' => round(array_sum(array_column($forecast, 'confidence')) / count($forecast), 2),
            'product_type' => $product_type,
            'forecast_summary' => [
                'next_7_days' => array_sum(array_slice(array_column($forecast, 'predicted_demand'), 0, 7)),
                'next_14_days' => array_sum(array_slice(array_column($forecast, 'predicted_demand'), 0, 14)),
                'next_30_days' => $total_demand
            ]
        ];
        
        return $recommendation;
    }
    
    /**
     * Get product information
     */
    private function getProductInfo($product_id) {
        $query = "SELECT * FROM rpos_products WHERE prod_id = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Calculate urgency level based on real data
     */
    private function calculateUrgency($days_until_stockout, $current_stock, $threshold) {
        // Critical: Stock is at or below 25% of threshold OR will run out in 3 days or less
        if ($current_stock <= $threshold * 0.25 || $days_until_stockout <= 3) {
            return 'critical';
        } 
        // High: Stock is at or below 50% of threshold OR will run out in 7 days or less
        elseif ($current_stock <= $threshold * 0.5 || $days_until_stockout <= 7) {
            return 'high';
        } 
        // Medium: Stock is at or below 75% of threshold OR will run out in 14 days or less
        elseif ($current_stock <= $threshold * 0.75 || $days_until_stockout <= 14) {
            return 'medium';
        } 
        // Low: Stock is at or below threshold OR will run out in 21 days or less
        elseif ($current_stock <= $threshold || $days_until_stockout <= 21) {
            return 'low';
        } 
        // Normal: Stock is above threshold and will last more than 21 days
        else {
            return 'normal';
        }
    }
    
    /**
     * Get all products with forecasting data
     */
    public function getAllProductForecasts($limit = 50) {
        $query = "SELECT prod_id, prod_name, prod_quantity, prod_threshold, prod_price 
                  FROM rpos_products 
                  ORDER BY prod_name ASC 
                  LIMIT ?";
        
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $forecasts = [];
        while ($product = $result->fetch_assoc()) {
            $forecast = $this->generateRestockingRecommendations($product['prod_id']);
            if ($forecast) {
                $forecasts[] = $forecast;
            }
        }
        
        // Sort by urgency
        usort($forecasts, function($a, $b) {
            $urgency_order = ['critical' => 5, 'high' => 4, 'medium' => 3, 'low' => 2, 'normal' => 1];
            return $urgency_order[$b['urgency']] - $urgency_order[$a['urgency']];
        });
        
        return $forecasts;
    }
    
    /**
     * Get product links for a given product
     */
    private function getProductLinks($product_id) {
        $query = "SELECT relation, base_product_id FROM rpos_product_links WHERE linked_product_id = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        
        $stmt->close();
        return $links;
    }
    
    /**
     * Adjust quantity based on product relationships
     */
    private function adjustQuantityForProductRelations($product_id, $quantity, $product_links) {
        $adjusted_quantity = $quantity;
        
        foreach ($product_links as $link) {
            if ($link['relation'] === 'mirror') {
                // For mirror products, sales of base product should be divided by 2
                // to get the equivalent mirror product sales
                $adjusted_quantity = $quantity / 2;
            } elseif ($link['relation'] === 'combo') {
                // For combo products, the quantity represents the combo sales
                // which consume 1 unit from each base product
                $adjusted_quantity = $quantity; // No adjustment needed for combo
            }
        }
        
        return round($adjusted_quantity, 2);
    }
    
    /**
     * Get effective stock for a product considering linked products
     */
    private function getEffectiveStock($product_id) {
        $product_links = $this->getProductLinks($product_id);
        $product_info = $this->getProductInfo($product_id);
        
        if (!$product_info) {
            return 0;
        }
        
        $base_stock = (int)$product_info['prod_quantity'];
        
        // Check if this is a mirror product
        foreach ($product_links as $link) {
            if ($link['relation'] === 'mirror') {
                // For mirror products, effective stock is half of base product stock
                $base_product_info = $this->getProductInfo($link['base_product_id']);
                if ($base_product_info) {
                    return intdiv((int)$base_product_info['prod_quantity'], 2);
                }
            } elseif ($link['relation'] === 'combo') {
                // For combo products, effective stock is the minimum of all base products
                $combo_stocks = [];
                foreach ($product_links as $combo_link) {
                    if ($combo_link['relation'] === 'combo') {
                        $combo_base_info = $this->getProductInfo($combo_link['base_product_id']);
                        if ($combo_base_info) {
                            $combo_stocks[] = (int)$combo_base_info['prod_quantity'];
                        }
                    }
                }
                if (!empty($combo_stocks)) {
                    return min($combo_stocks);
                }
            }
        }
        
        // Regular product - return its own stock
        return $base_stock;
    }
    
    /**
     * Get product type based on links
     */
    private function getProductType($product_links) {
        if (empty($product_links)) {
            return 'regular';
        }
        
        foreach ($product_links as $link) {
            if ($link['relation'] === 'mirror') {
                return 'double';
            } elseif ($link['relation'] === 'combo') {
                return 'combo';
            }
        }
        
        return 'regular';
    }
    
    /**
     * Get detailed urgency information
     */
    private function getUrgencyDetails($urgency, $current_stock, $threshold, $days_until_stockout) {
        $stock_percentage = $threshold > 0 ? round(($current_stock / $threshold) * 100, 1) : 0;
        
        $details = [
            'urgency' => $urgency,
            'stock_percentage' => $stock_percentage,
            'days_until_stockout' => round($days_until_stockout, 1),
            'reason' => '',
            'action_required' => ''
        ];
        
        switch ($urgency) {
            case 'critical':
                $details['reason'] = $current_stock <= $threshold * 0.25 ? 
                    "Stock is at {$stock_percentage}% of threshold" : 
                    "Will run out in {$days_until_stockout} days";
                $details['action_required'] = 'Immediate restocking required';
                break;
                
            case 'high':
                $details['reason'] = $current_stock <= $threshold * 0.5 ? 
                    "Stock is at {$stock_percentage}% of threshold" : 
                    "Will run out in {$days_until_stockout} days";
                $details['action_required'] = 'Urgent restocking needed within 2-3 days';
                break;
                
            case 'medium':
                $details['reason'] = $current_stock <= $threshold * 0.75 ? 
                    "Stock is at {$stock_percentage}% of threshold" : 
                    "Will run out in {$days_until_stockout} days";
                $details['action_required'] = 'Plan restocking within 1 week';
                break;
                
            case 'low':
                $details['reason'] = $current_stock <= $threshold ? 
                    "Stock is at {$stock_percentage}% of threshold" : 
                    "Will run out in {$days_until_stockout} days";
                $details['action_required'] = 'Monitor closely, consider restocking';
                break;
                
            case 'normal':
            default:
                $details['reason'] = "Stock is healthy at {$stock_percentage}% of threshold";
                $details['action_required'] = 'No immediate action needed';
                break;
        }
        
        return $details;
    }
}
?>
