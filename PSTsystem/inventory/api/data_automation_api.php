<?php
/**
 * Data Automation API
 * RESTful API for triggering data automation processes
 */

// Security headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include necessary files
require_once('../config/config.php');
require_once('../classes/DataAutomation.php');

// API Key for security (you should change this)
$API_KEY = 'pst_data_automation_2024';

// Input validation function
function validateApiKey($key) {
    global $API_KEY;
    return $key === $API_KEY;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$request = json_decode(file_get_contents('php://input'), true);

try {
    // Check API key
    $api_key = $_GET['api_key'] ?? $request['api_key'] ?? '';
    if (!validateApiKey($api_key)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
    
    // Initialize data automation
    $automation = new DataAutomation($mysqli);
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'status';
            
            switch ($action) {
                case 'status':
                    $status = $automation->getAutomationStatus();
                    echo json_encode([
                        'success' => true,
                        'data' => $status,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'weather':
                    $days = (int)($_GET['days'] ?? 7);
                    $results = $automation->insertWeatherData($days);
                    echo json_encode([
                        'success' => true,
                        'action' => 'weather',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'holidays':
                    $year = (int)($_GET['year'] ?? date('Y'));
                    $results = $automation->insertHolidayData($year);
                    echo json_encode([
                        'success' => true,
                        'action' => 'holidays',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'economic':
                    $months = (int)($_GET['months'] ?? 12);
                    $results = $automation->insertEconomicData($months);
                    echo json_encode([
                        'success' => true,
                        'action' => 'economic',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'events':
                    $days = (int)($_GET['days'] ?? 30);
                    $results = $automation->insertLocalEventsData($days);
                    echo json_encode([
                        'success' => true,
                        'action' => 'events',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'all':
                    $results = $automation->runAllAutomation();
                    $total_inserted = array_sum(array_column($results, 'inserted'));
                    echo json_encode([
                        'success' => true,
                        'action' => 'all',
                        'results' => $results,
                        'total_inserted' => $total_inserted,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        case 'POST':
            if (!$request) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            $action = $request['action'] ?? '';
            
            switch ($action) {
                case 'weather':
                    $days = (int)($request['days'] ?? 7);
                    $results = $automation->insertWeatherData($days);
                    echo json_encode([
                        'success' => true,
                        'action' => 'weather',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'holidays':
                    $year = (int)($request['year'] ?? date('Y'));
                    $results = $automation->insertHolidayData($year);
                    echo json_encode([
                        'success' => true,
                        'action' => 'holidays',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'economic':
                    $months = (int)($request['months'] ?? 12);
                    $results = $automation->insertEconomicData($months);
                    echo json_encode([
                        'success' => true,
                        'action' => 'economic',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'events':
                    $days = (int)($request['days'] ?? 30);
                    $results = $automation->insertLocalEventsData($days);
                    echo json_encode([
                        'success' => true,
                        'action' => 'events',
                        'results' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                case 'all':
                    $results = $automation->runAllAutomation();
                    $total_inserted = array_sum(array_column($results, 'inserted'));
                    echo json_encode([
                        'success' => true,
                        'action' => 'all',
                        'results' => $results,
                        'total_inserted' => $total_inserted,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An unexpected error occurred',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
