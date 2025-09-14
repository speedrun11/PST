<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

header('Content-Type: application/json');

try {
    // Get orders grouped by order_group_id
    $query = "
        SELECT 
            order_group_id,
            customer_name,
            customer_id,
            order_code,
            order_type,
            order_status,
            created_at,
            GROUP_CONCAT(
                CONCAT(
                    prod_id, '|', 
                    prod_name, '|', 
                    prod_price, '|', 
                    prod_qty, '|',
                    COALESCE(additional_charge, 0)
                ) SEPARATOR '||'
            ) as items_data
        FROM rpos_orders 
        WHERE order_group_id IS NOT NULL
        GROUP BY order_group_id, customer_name, customer_id, order_code, order_type, order_status, created_at
        ORDER BY created_at DESC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    $stats = [
        'totalOrders' => 0,
        'pendingOrders' => 0,
        'paidOrders' => 0,
        'totalRevenue' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        // Parse items data
        $items = [];
        if (!empty($row['items_data'])) {
            $itemsArray = explode('||', $row['items_data']);
            foreach ($itemsArray as $itemData) {
                $itemParts = explode('|', $itemData);
                if (count($itemParts) >= 4) {
                    $items[] = [
                        'prod_id' => $itemParts[0],
                        'prod_name' => $itemParts[1],
                        'prod_price' => $itemParts[2],
                        'prod_qty' => $itemParts[3],
                        'additional_charge' => isset($itemParts[4]) ? $itemParts[4] : 0,
                        'prod_img' => 'default.jpg' // Default image, can be enhanced later
                    ];
                }
            }
        }
        
        // Calculate order total
        $orderTotal = 0;
        foreach ($items as $item) {
            $itemTotal = floatval($item['prod_price']) * intval($item['prod_qty']) + floatval($item['additional_charge']);
            $orderTotal += $itemTotal;
        }
        
        $orders[] = [
            'order_group_id' => $row['order_group_id'],
            'customer_name' => $row['customer_name'],
            'customer_id' => $row['customer_id'],
            'order_code' => $row['order_code'],
            'order_type' => $row['order_type'],
            'status' => $row['order_status'],
            'created_at' => $row['created_at'],
            'total' => $orderTotal,
            'items' => $items
        ];
        
        // Update stats
        $stats['totalOrders']++;
        if ($row['order_status'] === 'Paid' || $row['order_status'] === 'Completed') {
            $stats['paidOrders']++;
            $stats['totalRevenue'] += $orderTotal;
        } else {
            $stats['pendingOrders']++;
        }
    }
    
    // Also get orders without order_group_id (legacy orders)
    $legacyQuery = "
        SELECT 
            CONCAT(customer_id, '_', order_code, '_legacy') as order_group_id,
            customer_name,
            customer_id,
            order_code,
            order_type,
            order_status,
            created_at,
            prod_id,
            prod_name,
            prod_price,
            prod_qty,
            COALESCE(additional_charge, 0) as additional_charge
        FROM rpos_orders 
        WHERE order_group_id IS NULL
        ORDER BY created_at DESC
    ";
    
    $legacyStmt = $mysqli->prepare($legacyQuery);
    $legacyStmt->execute();
    $legacyResult = $legacyStmt->get_result();
    
    $legacyOrders = [];
    while ($row = $legacyResult->fetch_assoc()) {
        $itemTotal = floatval($row['prod_price']) * intval($row['prod_qty']) + floatval($row['additional_charge']);
        
        $legacyOrders[] = [
            'order_group_id' => $row['order_group_id'],
            'customer_name' => $row['customer_name'],
            'customer_id' => $row['customer_id'],
            'order_code' => $row['order_code'],
            'order_type' => $row['order_type'],
            'status' => $row['order_status'],
            'created_at' => $row['created_at'],
            'total' => $itemTotal,
            'items' => [[
                'prod_id' => $row['prod_id'],
                'prod_name' => $row['prod_name'],
                'prod_price' => $row['prod_price'],
                'prod_qty' => $row['prod_qty'],
                'additional_charge' => $row['additional_charge'],
                'prod_img' => 'default.jpg'
            ]]
        ];
        
        // Update stats for legacy orders
        $stats['totalOrders']++;
        if ($row['order_status'] === 'Paid' || $row['order_status'] === 'Completed') {
            $stats['paidOrders']++;
            $stats['totalRevenue'] += $itemTotal;
        } else {
            $stats['pendingOrders']++;
        }
    }
    
    // Merge orders (new grouped orders first, then legacy)
    $allOrders = array_merge($orders, $legacyOrders);
    
    echo json_encode([
        'success' => true,
        'orders' => $allOrders,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
