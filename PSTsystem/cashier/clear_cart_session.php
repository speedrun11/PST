<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['cart']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_id']);
    
    echo json_encode(['success' => true, 'message' => 'Cart cleared']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
