<?php
session_start();

// Clear the single item cart from session
if (isset($_SESSION['single_item_cart'])) {
    unset($_SESSION['single_item_cart']);
}

// Also clear other cart-related session variables if needed
if (isset($_SESSION['customer_name'])) {
    unset($_SESSION['customer_name']);
}

if (isset($_SESSION['customer_id'])) {
    unset($_SESSION['customer_id']);
}

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Single item cart cleared']);
?>
