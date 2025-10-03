<?php
class DataAutomation {
    private $mysqli;
    private $weather_api_key;
    private $location;
    
    public function __construct($mysqli) {
        if (!$mysqli || $mysqli->connect_error) {
            throw new Exception('Database connection failed: ' . ($mysqli ? $mysqli->connect_error : 'No connection'));
        }
        $this->mysqli = $mysqli;
        $this->loadConfiguration();
    }
    
    /**
     * Load configuration from database
     */
    private function loadConfiguration() {
        $query = "SELECT config_key, config_value FROM rpos_system_config WHERE config_key IN ('weather_api_key', 'weather_location')";
        $result = $this->mysqli->query($query);
        
        $config = [];
        while ($row = $result->fetch_assoc()) {
            $config[$row['config_key']] = $row['config_value'];
        }
        
        $this->weather_api_key = $config['weather_api_key'] ?? 'free_weather_api';
        $this->location = $config['weather_location'] ?? 'Pasig,PH';
    }
    
    /**
     * Automatically insert weather data
     */
    public function insertWeatherData($days = 7) {
        $inserted = 0;
        $errors = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            
            // Check if data already exists
            if ($this->weatherDataExists($date)) {
                continue;
            }
            
            try {
                $weather_data = $this->getWeatherFromAPI($date);
                if ($weather_data) {
                    $this->insertWeatherRecord($weather_data);
                    $inserted++;
                }
            } catch (Exception $e) {
                $errors[] = "Weather data for $date: " . $e->getMessage();
            }
        }
        
        return [
            'inserted' => $inserted,
            'errors' => $errors
        ];
    }
    
    /**
     * Get weather data from API (simulated for now, can be replaced with real API)
     */
    private function getWeatherFromAPI($date) {
        // Simulate weather data based on date and season
        $day_of_year = date('z', strtotime($date));
        $month = date('n', strtotime($date));
        
        // Simulate weather conditions based on season
        $conditions = $this->simulateWeatherConditions($day_of_year, $month);
        $temperature = $this->simulateTemperature($day_of_year);
        $humidity = $this->simulateHumidity($day_of_year);
        $impact_factor = $this->calculateWeatherImpact($conditions, $temperature, $humidity);
        
        return [
            'weather_date' => $date,
            'location' => $this->location,
            'condition' => $conditions,
            'temperature' => $temperature,
            'humidity' => $humidity,
            'impact_factor' => $impact_factor
        ];
    }
    
    /**
     * Simulate weather conditions based on season
     */
    private function simulateWeatherConditions($day_of_year, $month) {
        // Rainy season in Philippines: June to November
        if ($month >= 6 && $month <= 11) {
            $conditions = ['rainy', 'cloudy', 'stormy'];
        } else {
            $conditions = ['sunny', 'cloudy'];
        }
        
        // Add some randomness
        $random = rand(1, 100);
        if ($random <= 60) {
            return $conditions[0]; // Most common condition
        } elseif ($random <= 85) {
            return $conditions[1] ?? 'cloudy';
        } else {
            return $conditions[2] ?? 'sunny';
        }
    }
    
    /**
     * Simulate temperature based on season
     */
    private function simulateTemperature($day_of_year) {
        $base_temp = 30; // Base temperature in Celsius
        $variation = sin(($day_of_year / 365) * 2 * pi()) * 5;
        $random_variation = (rand(-200, 200) / 100); // ±2°C random variation
        return round($base_temp + $variation + $random_variation, 1);
    }
    
    /**
     * Simulate humidity based on season
     */
    private function simulateHumidity($day_of_year) {
        $base_humidity = 75; // Base humidity percentage
        $variation = sin(($day_of_year / 365) * 2 * pi()) * 15;
        $random_variation = (rand(-100, 100) / 10); // ±10% random variation
        return round($base_humidity + $variation + $random_variation, 1);
    }
    
    /**
     * Calculate weather impact on food sales
     */
    private function calculateWeatherImpact($condition, $temperature, $humidity) {
        $impact = 1.0;
        
        // Condition impact
        switch ($condition) {
            case 'sunny':
                $impact *= 1.1; // 10% increase
                break;
            case 'cloudy':
                $impact *= 1.0; // neutral
                break;
            case 'rainy':
                $impact *= 0.9; // 10% decrease
                break;
            case 'stormy':
                $impact *= 0.8; // 20% decrease
                break;
        }
        
        // Temperature impact (optimal around 25-30°C)
        if ($temperature < 20 || $temperature > 35) {
            $impact *= 0.95; // 5% decrease for extreme temperatures
        }
        
        // Humidity impact (high humidity can affect comfort)
        if ($humidity > 85) {
            $impact *= 0.98; // 2% decrease for very high humidity
        }
        
        return round($impact, 3);
    }
    
    /**
     * Check if weather data already exists for a date
     */
    private function weatherDataExists($date) {
        $query = "SELECT COUNT(*) as count FROM rpos_weather_data WHERE weather_date = ? AND location = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $date, $this->location);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Insert weather record into database
     */
    private function insertWeatherRecord($data) {
        $query = "INSERT INTO rpos_weather_data (weather_date, location, `condition`, temperature, humidity, impact_factor) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('sssddd', 
            $data['weather_date'],
            $data['location'],
            $data['condition'],
            $data['temperature'],
            $data['humidity'],
            $data['impact_factor']
        );
        $stmt->execute();
    }
    
    /**
     * Automatically insert holiday data for the year
     */
    public function insertHolidayData($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $holidays = $this->getPhilippineHolidays($year);
        $inserted = 0;
        $errors = [];
        
        foreach ($holidays as $holiday) {
            try {
                if (!$this->holidayDataExists($holiday['date'])) {
                    $this->insertHolidayRecord($holiday);
                    $inserted++;
                }
            } catch (Exception $e) {
                $errors[] = "Holiday data for {$holiday['date']}: " . $e->getMessage();
            }
        }
        
        return [
            'inserted' => $inserted,
            'errors' => $errors
        ];
    }
    
    /**
     * Get Philippine holidays for a given year
     */
    private function getPhilippineHolidays($year) {
        return [
            ['date' => "$year-01-01", 'name' => 'New Year\'s Day', 'impact' => 1.3],
            ['date' => "$year-02-14", 'name' => 'Valentine\'s Day', 'impact' => 1.2],
            ['date' => "$year-03-15", 'name' => 'Araw ng Kagitingan', 'impact' => 1.1],
            ['date' => "$year-04-09", 'name' => 'Araw ng Kagitingan', 'impact' => 1.1],
            ['date' => "$year-05-01", 'name' => 'Labor Day', 'impact' => 1.1],
            ['date' => "$year-06-12", 'name' => 'Independence Day', 'impact' => 1.2],
            ['date' => "$year-08-21", 'name' => 'Ninoy Aquino Day', 'impact' => 1.0],
            ['date' => "$year-08-26", 'name' => 'National Heroes Day', 'impact' => 1.1],
            ['date' => "$year-11-30", 'name' => 'Bonifacio Day', 'impact' => 1.1],
            ['date' => "$year-12-25", 'name' => 'Christmas Day', 'impact' => 1.4],
            ['date' => "$year-12-30", 'name' => 'Rizal Day', 'impact' => 1.1],
            ['date' => "$year-12-31", 'name' => 'New Year\'s Eve', 'impact' => 1.3],
        ];
    }
    
    /**
     * Check if holiday data already exists for a date
     */
    private function holidayDataExists($date) {
        $query = "SELECT COUNT(*) as count FROM rpos_holiday_data WHERE holiday_date = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Insert holiday record into database
     */
    private function insertHolidayRecord($data) {
        $query = "INSERT INTO rpos_holiday_data (holiday_date, holiday_name, is_holiday, impact_factor) VALUES (?, ?, 1, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ssd', 
            $data['date'],
            $data['name'],
            $data['impact']
        );
        $stmt->execute();
    }
    
    /**
     * Automatically insert economic data
     */
    public function insertEconomicData($months = 12) {
        $inserted = 0;
        $errors = [];
        
        for ($i = 0; $i < $months; $i++) {
            $date = date('Y-m-01', strtotime("-$i months"));
            
            // Check if data already exists
            if ($this->economicDataExists($date)) {
                continue;
            }
            
            try {
                $economic_data = $this->getEconomicData($date);
                if ($economic_data) {
                    $this->insertEconomicRecord($economic_data);
                    $inserted++;
                }
            } catch (Exception $e) {
                $errors[] = "Economic data for $date: " . $e->getMessage();
            }
        }
        
        return [
            'inserted' => $inserted,
            'errors' => $errors
        ];
    }
    
    /**
     * Get simulated economic data
     */
    private function getEconomicData($date) {
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        
        // Simulate economic indicators with some realistic variation
        $inflation_rate = 3.0 + (sin($month / 12 * 2 * pi()) * 0.5) + (rand(-20, 20) / 100);
        $unemployment_rate = 5.5 + (cos($month / 12 * 2 * pi()) * 0.3) + (rand(-15, 15) / 100);
        $consumer_confidence = 85 + (sin($month / 12 * 2 * pi()) * 5) + (rand(-20, 20) / 10);
        
        // Calculate impact factor based on economic indicators
        $impact_factor = $this->calculateEconomicImpact($inflation_rate, $unemployment_rate, $consumer_confidence);
        
        return [
            'data_date' => $date,
            'inflation_rate' => round($inflation_rate, 2),
            'unemployment_rate' => round($unemployment_rate, 2),
            'consumer_confidence' => round($consumer_confidence, 2),
            'impact_factor' => $impact_factor
        ];
    }
    
    /**
     * Calculate economic impact on food sales
     */
    private function calculateEconomicImpact($inflation, $unemployment, $confidence) {
        $impact = 1.0;
        
        // Inflation impact (moderate inflation is good, high inflation is bad)
        if ($inflation < 2.0) {
            $impact *= 0.98; // Slightly negative for deflation
        } elseif ($inflation > 6.0) {
            $impact *= 0.95; // Negative for high inflation
        } else {
            $impact *= 1.02; // Positive for moderate inflation
        }
        
        // Unemployment impact
        if ($unemployment > 7.0) {
            $impact *= 0.90; // Negative for high unemployment
        } elseif ($unemployment < 4.0) {
            $impact *= 1.05; // Positive for low unemployment
        }
        
        // Consumer confidence impact
        if ($confidence < 70) {
            $impact *= 0.95; // Negative for low confidence
        } elseif ($confidence > 90) {
            $impact *= 1.03; // Positive for high confidence
        }
        
        return round($impact, 3);
    }
    
    /**
     * Check if economic data already exists for a date
     */
    private function economicDataExists($date) {
        $query = "SELECT COUNT(*) as count FROM rpos_economic_data WHERE data_date = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Insert economic record into database
     */
    private function insertEconomicRecord($data) {
        $query = "INSERT INTO rpos_economic_data (data_date, inflation_rate, unemployment_rate, consumer_confidence, impact_factor) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('sdddd', 
            $data['data_date'],
            $data['inflation_rate'],
            $data['unemployment_rate'],
            $data['consumer_confidence'],
            $data['impact_factor']
        );
        $stmt->execute();
    }
    
    /**
     * Automatically insert local events data
     */
    public function insertLocalEventsData($days = 30) {
        $inserted = 0;
        $errors = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            
            // Check if data already exists
            if ($this->eventDataExists($date)) {
                continue;
            }
            
            try {
                $event_data = $this->getLocalEventData($date);
                if ($event_data) {
                    $this->insertEventRecord($event_data);
                    $inserted++;
                }
            } catch (Exception $e) {
                $errors[] = "Event data for $date: " . $e->getMessage();
            }
        }
        
        return [
            'inserted' => $inserted,
            'errors' => $errors
        ];
    }
    
    /**
     * Get simulated local event data
     */
    private function getLocalEventData($date) {
        $day_of_week = date('w', strtotime($date));
        $month = date('n', strtotime($date));
        
        // Simulate events based on day of week and month
        $event_type = 'none';
        $event_name = null;
        $impact = 1.0;
        
        // Weekend events are more common
        if ($day_of_week == 5 || $day_of_week == 6) {
            $random = rand(1, 100);
            if ($random <= 30) { // 30% chance of weekend event
                $event_type = 'festivals';
                $event_name = 'Weekend Festival';
                $impact = 1.3;
            }
        }
        
        // Monthly events
        if ($month == 12) { // December events
            $random = rand(1, 100);
            if ($random <= 40) { // 40% chance of December event
                $event_type = 'concerts';
                $event_name = 'Christmas Concert';
                $impact = 1.15;
            }
        }
        
        // Random events throughout the year
        $random = rand(1, 100);
        if ($random <= 10) { // 10% chance of random event
            $event_types = ['conferences', 'sports_events', 'concerts'];
            $event_type = $event_types[array_rand($event_types)];
            $event_name = ucfirst(str_replace('_', ' ', $event_type));
            $impact = 1.1;
        }
        
        return [
            'event_date' => $date,
            'location' => 'Pasig City',
            'event_type' => $event_type,
            'event_name' => $event_name,
            'impact_factor' => $impact
        ];
    }
    
    /**
     * Check if event data already exists for a date
     */
    private function eventDataExists($date) {
        $query = "SELECT COUNT(*) as count FROM rpos_local_events WHERE event_date = ? AND location = 'Pasig City'";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Insert event record into database
     */
    private function insertEventRecord($data) {
        $query = "INSERT INTO rpos_local_events (event_date, location, event_type, event_name, impact_factor) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ssssd', 
            $data['event_date'],
            $data['location'],
            $data['event_type'],
            $data['event_name'],
            $data['impact_factor']
        );
        $stmt->execute();
    }
    
    /**
     * Run all data automation processes
     */
    public function runAllAutomation() {
        $results = [];
        
        // Weather data (next 7 days)
        $results['weather'] = $this->insertWeatherData(7);
        
        // Holiday data (current year)
        $results['holidays'] = $this->insertHolidayData();
        
        // Economic data (last 12 months)
        $results['economic'] = $this->insertEconomicData(12);
        
        // Local events (next 30 days)
        $results['events'] = $this->insertLocalEventsData(30);
        
        return $results;
    }
    
    /**
     * Get automation status and statistics
     */
    public function getAutomationStatus() {
        $status = [];
        
        // Weather data status
        $query = "SELECT COUNT(*) as count, MAX(weather_date) as latest FROM rpos_weather_data";
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        $status['weather'] = [
            'total_records' => $row['count'],
            'latest_date' => $row['latest']
        ];
        
        // Holiday data status
        $query = "SELECT COUNT(*) as count, MAX(holiday_date) as latest FROM rpos_holiday_data";
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        $status['holidays'] = [
            'total_records' => $row['count'],
            'latest_date' => $row['latest']
        ];
        
        // Economic data status
        $query = "SELECT COUNT(*) as count, MAX(data_date) as latest FROM rpos_economic_data";
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        $status['economic'] = [
            'total_records' => $row['count'],
            'latest_date' => $row['latest']
        ];
        
        // Events data status
        $query = "SELECT COUNT(*) as count, MAX(event_date) as latest FROM rpos_local_events";
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        $status['events'] = [
            'total_records' => $row['count'],
            'latest_date' => $row['latest']
        ];
        
        return $status;
    }
}
?>
