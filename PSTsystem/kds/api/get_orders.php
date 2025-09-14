<?php
session_start();
include('../../cashier/config/config.php');
include('../../cashier/config/checklogin.php');
check_login();
header('Content-Type: application/json');

try {
	// Enhanced query to fetch active orders with better status logic
	$q = "SELECT order_code, customer_name,
				MIN(order_type) AS order_type,
				CASE 
					WHEN SUM(CASE WHEN order_status = 'Ready' THEN 1 ELSE 0 END) > 0 THEN 'Ready'
					WHEN SUM(CASE WHEN order_status = 'Preparing' THEN 1 ELSE 0 END) > 0 THEN 'Preparing'
					WHEN MIN(order_status) = 'Paid' AND MAX(order_status) = 'Paid' THEN 'Paid'
					ELSE 'Pending'
				END AS status,
				MIN(created_at) AS created_at,
				COUNT(*) as item_count
			FROM rpos_orders
			WHERE created_at >= (NOW() - INTERVAL 2 DAY)
			  AND order_status <> 'Cancelled'
			  AND order_status <> 'Completed'
			GROUP BY order_code, customer_name
			ORDER BY 
				CASE 
					WHEN SUM(CASE WHEN order_status = 'Ready' THEN 1 ELSE 0 END) > 0 THEN 1
					WHEN SUM(CASE WHEN order_status = 'Preparing' THEN 1 ELSE 0 END) > 0 THEN 2
					ELSE 3
				END,
				MIN(created_at) ASC";
	$stmt = $mysqli->prepare($q);
	$stmt->execute();
	$res = $stmt->get_result();
	$orders = [];
	while ($g = $res->fetch_object()) {
		// Items for this group with enhanced details
		$iq = "SELECT prod_name, prod_qty, prod_price, additional_charge 
			   FROM rpos_orders 
			   WHERE order_code = ? 
			   ORDER BY created_at ASC";
		$is = $mysqli->prepare($iq);
		$is->bind_param('s', $g->order_code);
		$is->execute();
		$ir = $is->get_result();
		$items = [];
		$totalAmount = 0;
		while ($row = $ir->fetch_object()) { 
			$items[] = $row;
			$totalAmount += ($row->prod_price * $row->prod_qty) + ($row->additional_charge ?? 0);
		}
		
		// Calculate time since order
		$orderTime = new DateTime($g->created_at);
		$now = new DateTime();
		$timeDiff = $now->diff($orderTime);
		$minutesSince = ($timeDiff->days * 24 * 60) + ($timeDiff->h * 60) + $timeDiff->i;
		
		$orders[] = [
			'order_code' => $g->order_code,
			'customer_name' => $g->customer_name,
			'order_type' => $g->order_type,
			'created_at' => $g->created_at,
			'status' => $g->status,
			'items' => $items,
			'item_count' => $g->item_count,
			'total_amount' => $totalAmount,
			'minutes_since' => $minutesSince,
			'is_priority' => ($g->status === 'Pending' && $minutesSince > 10)
		];
	}
	
	// Add summary statistics
	$stats = [
		'total_orders' => count($orders),
		'pending_orders' => count(array_filter($orders, fn($o) => in_array($o['status'], ['Pending', 'Paid']))),
		'preparing_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'Preparing')),
		'ready_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'Ready')),
		'priority_orders' => count(array_filter($orders, fn($o) => $o['is_priority']))
	];
	
	echo json_encode([
		'success' => true,
		'orders' => $orders,
		'stats' => $stats,
		'timestamp' => date('Y-m-d H:i:s')
	]);
} catch (Exception $e) {
	echo json_encode([
		'success' => false,
		'error' => 'Server error: ' . $e->getMessage(),
		'timestamp' => date('Y-m-d H:i:s')
	]);
}
