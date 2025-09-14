<?php
// Security headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

class ExternalFactorsAPI {
    private $api_key;
    
    public function __construct() {
        // In production, store API keys securely
        $this->api_key = 'your_api_key_here';
    }
    
    /**
     * Get weather data for a specific date
     */
    public function getWeatherData($date, $location = 'Manila, Philippines') {
        // Simulated weather data - in production, integrate with weather API
        $weather_conditions = [
            'sunny' => 1.1,      // 10% increase in food sales
            'rainy' => 0.9,      // 10% decrease in food sales
            'cloudy' => 1.0,     // neutral
            'stormy' => 0.8      // 20% decrease in food sales
        ];
        
        // Simulate weather based on date
        $day_of_year = date('z', strtotime($date));
        $weather_type = $this->simulateWeather($day_of_year);
        
        return [
            'date' => $date,
            'location' => $location,
            'condition' => $weather_type,
            'impact_factor' => $weather_conditions[$weather_type],
            'temperature' => $this->simulateTemperature($day_of_year),
            'humidity' => $this->simulateHumidity($day_of_year)
        ];
    }
    
    /**
     * Get holiday information for a specific date
     */
    public function getHolidayData($date) {
        $holidays = [
            '01-01' => ['name' => 'New Year\'s Day', 'impact' => 1.3],
            '02-14' => ['name' => 'Valentine\'s Day', 'impact' => 1.2],
            '03-15' => ['name' => 'Araw ng Kagitingan', 'impact' => 1.1],
            '04-09' => ['name' => 'Araw ng Kagitingan', 'impact' => 1.1],
            '05-01' => ['name' => 'Labor Day', 'impact' => 1.1],
            '06-12' => ['name' => 'Independence Day', 'impact' => 1.2],
            '08-21' => ['name' => 'Ninoy Aquino Day', 'impact' => 1.0],
            '08-26' => ['name' => 'National Heroes Day', 'impact' => 1.1],
            '11-30' => ['name' => 'Bonifacio Day', 'impact' => 1.1],
            '12-25' => ['name' => 'Christmas Day', 'impact' => 1.4],
            '12-30' => ['name' => 'Rizal Day', 'impact' => 1.1],
            '12-31' => ['name' => 'New Year\'s Eve', 'impact' => 1.3]
        ];
        
        $month_day = date('m-d', strtotime($date));
        
        if (isset($holidays[$month_day])) {
            return [
                'date' => $date,
                'is_holiday' => true,
                'holiday_name' => $holidays[$month_day]['name'],
                'impact_factor' => $holidays[$month_day]['impact']
            ];
        }
        
        return [
            'date' => $date,
            'is_holiday' => false,
            'holiday_name' => null,
            'impact_factor' => 1.0
        ];
    }
    
    /**
     * Get economic indicators
     */
    public function getEconomicData($date) {
        // Simulated economic data - in production, integrate with economic APIs
        return [
            'date' => $date,
            'inflation_rate' => 3.2,
            'unemployment_rate' => 5.8,
            'consumer_confidence' => 85.5,
            'impact_factor' => 1.0 // Neutral for now
        ];
    }
    
    /**
     * Get local events data
     */
    public function getLocalEvents($date, $location = 'Manila') {
        // Simulated local events - in production, integrate with events APIs
        $events = [
            'conferences' => 1.1,
            'festivals' => 1.3,
            'sports_events' => 1.2,
            'concerts' => 1.15,
            'none' => 1.0
        ];
        
        // Simulate events based on day of week and month
        $day_of_week = date('w', strtotime($date));
        $month = date('n', strtotime($date));
        
        $event_type = 'none';
        if ($day_of_week == 5 || $day_of_week == 6) { // Weekend
            $event_type = 'festivals';
        } elseif ($month == 12) { // December
            $event_type = 'concerts';
        }
        
        return [
            'date' => $date,
            'location' => $location,
            'event_type' => $event_type,
            'impact_factor' => $events[$event_type]
        ];
    }
    
    /**
     * Get comprehensive external factors for a date
     */
    public function getAllFactors($date, $location = 'Manila, Philippines') {
        $weather = $this->getWeatherData($date, $location);
        $holiday = $this->getHolidayData($date);
        $economic = $this->getEconomicData($date);
        $events = $this->getLocalEvents($date, $location);
        
        // Calculate combined impact factor
        $combined_factor = $weather['impact_factor'] * 
                          $holiday['impact_factor'] * 
                          $economic['impact_factor'] * 
                          $events['impact_factor'];
        
        return [
            'date' => $date,
            'location' => $location,
            'weather' => $weather,
            'holiday' => $holiday,
            'economic' => $economic,
            'events' => $events,
            'combined_impact_factor' => round($combined_factor, 3)
        ];
    }
    
    /**
     * Simulate weather based on day of year
     */
    private function simulateWeather($day_of_year) {
        // Simple simulation based on day of year
        if ($day_of_year >= 150 && $day_of_year <= 270) { // Rainy season
            $conditions = ['rainy', 'cloudy', 'stormy'];
        } else {
            $conditions = ['sunny', 'cloudy'];
        }
        
        return $conditions[array_rand($conditions)];
    }
    
    /**
     * Simulate temperature based on day of year
     */
    private function simulateTemperature($day_of_year) {
        // Simulate temperature between 25-35°C
        $base_temp = 30;
        $variation = sin(($day_of_year / 365) * 2 * pi()) * 5;
        return round($base_temp + $variation, 1);
    }
    
    /**
     * Simulate humidity based on day of year
     */
    private function simulateHumidity($day_of_year) {
        // Simulate humidity between 60-90%
        $base_humidity = 75;
        $variation = sin(($day_of_year / 365) * 2 * pi()) * 15;
        return round($base_humidity + $variation, 1);
    }
}

// Input validation function
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Handle API requests
$api = new ExternalFactorsAPI();
$method = $_SERVER['REQUEST_METHOD'];
$request = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            $date = $_GET['date'] ?? date('Y-m-d');
            $location = $_GET['location'] ?? 'Manila, Philippines';
            
            // Validate date
            if (!validateDate($date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
                exit;
            }
            
            // Sanitize location
            $location = sanitizeInput($location);
            
            if (isset($_GET['type'])) {
                $type = sanitizeInput($_GET['type']);
                switch ($type) {
                    case 'weather':
                        echo json_encode($api->getWeatherData($date, $location));
                        break;
                    case 'holiday':
                        echo json_encode($api->getHolidayData($date));
                        break;
                    case 'economic':
                        echo json_encode($api->getEconomicData($date));
                        break;
                    case 'events':
                        echo json_encode($api->getLocalEvents($date, $location));
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid type parameter']);
                }
            } else {
                echo json_encode($api->getAllFactors($date, $location));
            }
            break;
            
        case 'POST':
            if (!$request) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            $date = $request['date'] ?? date('Y-m-d');
            $location = $request['location'] ?? 'Manila, Philippines';
            
            // Validate date
            if (!validateDate($date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
                exit;
            }
            
            // Sanitize location
            $location = sanitizeInput($location);
            
            echo json_encode($api->getAllFactors($date, $location));
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => 'An unexpected error occurred']);
}
?>
