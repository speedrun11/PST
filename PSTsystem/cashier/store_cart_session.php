<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $orderData = json_decode($input, true);
    
    if ($orderData && isset($orderData['items'])) {
        $_SESSION['cart'] = $orderData['items'];
        $_SESSION['customer_name'] = $orderData['customer_name'] ?? '';
        $_SESSION['customer_id'] = $orderData['customer_id'] ?? '';
        
        echo json_encode(['success' => true, 'message' => 'Cart stored in session']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
