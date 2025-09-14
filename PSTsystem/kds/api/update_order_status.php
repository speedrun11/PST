<?php
session_start();
include('../../cashier/config/config.php');
include('../../cashier/config/checklogin.php');
check_login();
header('Content-Type: application/json');

try {
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	$order_code = $payload['order_code'] ?? '';
	$status = $payload['status'] ?? '';
	
	if ($order_code === '' || !in_array($status, ['Preparing','Ready','Completed'])) {
		echo json_encode([
			'success' => false,
			'error' => 'Invalid input parameters',
			'timestamp' => date('Y-m-d H:i:s')
		]);
		exit;
	}
	
	// Enhanced update with better validation and logging
	$mysqli->begin_transaction();
	
	// First, verify the order exists and get current status
	$checkQuery = "SELECT order_status, customer_name, order_type 
				   FROM rpos_orders 
				   WHERE order_code = ? 
				   AND order_status <> 'Cancelled' 
				   LIMIT 1";
	$checkStmt = $mysqli->prepare($checkQuery);
	$checkStmt->bind_param('s', $order_code);
	$checkStmt->execute();
	$checkResult = $checkStmt->get_result();
	
	if ($checkResult->num_rows === 0) {
		$mysqli->rollback();
		echo json_encode([
			'success' => false,
			'error' => 'Order not found or already cancelled',
			'timestamp' => date('Y-m-d H:i:s')
		]);
		exit;
	}
	
	$orderInfo = $checkResult->fetch_assoc();
	$currentStatus = $orderInfo['order_status'];
	
	// Validate status transition
	$validTransitions = [
		'Paid' => ['Preparing'],
		'Pending' => ['Preparing'],
		'Preparing' => ['Ready'],
		'Ready' => ['Completed']
	];
	
	if (!isset($validTransitions[$currentStatus]) || !in_array($status, $validTransitions[$currentStatus])) {
		$mysqli->rollback();
		echo json_encode([
			'success' => false,
			'error' => "Invalid status transition from {$currentStatus} to {$status}",
			'timestamp' => date('Y-m-d H:i:s')
		]);
		exit;
	}
	
	// Update all items in the order
	$updateQuery = "UPDATE rpos_orders 
					SET order_status = ? 
					WHERE order_code = ? 
					AND order_status <> 'Cancelled'";
	$updateStmt = $mysqli->prepare($updateQuery);
	$updateStmt->bind_param('ss', $status, $order_code);
	$updateResult = $updateStmt->execute();
	$affectedRows = $updateStmt->affected_rows;
	
	if (!$updateResult || $affectedRows === 0) {
		$mysqli->rollback();
		echo json_encode([
			'success' => false,
			'error' => 'Failed to update order status',
			'timestamp' => date('Y-m-d H:i:s')
		]);
		exit;
	}
	
	// Note: Order logging removed as rpos_order_logs table doesn't exist
	
	$mysqli->commit();
	
	echo json_encode([
		'success' => true,
		'message' => "Order {$order_code} status updated from {$currentStatus} to {$status}",
		'order_code' => $order_code,
		'old_status' => $currentStatus,
		'new_status' => $status,
		'affected_rows' => $affectedRows,
		'timestamp' => date('Y-m-d H:i:s')
	]);
	
} catch (Exception $e) {
	if ($mysqli && $mysqli->errno === 0) {
		$mysqli->rollback();
	}
	echo json_encode([
		'success' => false,
		'error' => 'Server error: ' . $e->getMessage(),
		'timestamp' => date('Y-m-d H:i:s')
	]);
}
