<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

header('Content-Type: application/json');

try {
    $labels = [];
    $stock_in = [];
    $stock_out = [];
    
    // Get the last 12 months for the chart
    for ($i = 11; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime("-$i months"));
        
        // Get stock in for products
        $stock_in_query = "SELECT SUM(quantity_change) as total_in 
                          FROM rpos_inventory_logs 
                          WHERE activity_type = 'Restock' 
                          AND DATE_FORMAT(activity_date, '%Y-%m') = ?";
        $stmt_in = $mysqli->prepare($stock_in_query);
        $stmt_in->bind_param('s', $date);
        $stmt_in->execute();
        $result_in = $stmt_in->get_result();
        $row_in = $result_in->fetch_assoc();
        $product_stock_in = (int)$row_in['total_in'];
        
        // Get stock in for ingredients
        $ingredient_stock_in_query = "SELECT SUM(quantity_change) as total_in 
                                     FROM rpos_ingredient_logs 
                                     WHERE activity_type = 'Restock' 
                                     AND DATE_FORMAT(activity_date, '%Y-%m') = ?";
        $stmt_ingredient_in = $mysqli->prepare($ingredient_stock_in_query);
        $stmt_ingredient_in->bind_param('s', $date);
        $stmt_ingredient_in->execute();
        $result_ingredient_in = $stmt_ingredient_in->get_result();
        $row_ingredient_in = $result_ingredient_in->fetch_assoc();
        $ingredient_stock_in = (int)$row_ingredient_in['total_in'];
        
        $stock_in[] = $product_stock_in + $ingredient_stock_in;
        
        // Get stock out for products (Sales, Waste, etc.)
        $stock_out_query = "SELECT SUM(ABS(quantity_change)) as total_out 
                           FROM rpos_inventory_logs 
                           WHERE activity_type IN ('Sale', 'Waste', 'Adjustment') 
                           AND quantity_change < 0
                           AND DATE_FORMAT(activity_date, '%Y-%m') = ?";
        $stmt_out = $mysqli->prepare($stock_out_query);
        $stmt_out->bind_param('s', $date);
        $stmt_out->execute();
        $result_out = $stmt_out->get_result();
        $row_out = $result_out->fetch_assoc();
        $product_stock_out = (int)$row_out['total_out'];
        
        // Get stock out for ingredients (Usage, Waste, etc.)
        $ingredient_stock_out_query = "SELECT SUM(ABS(quantity_change)) as total_out 
                                      FROM rpos_ingredient_logs 
                                      WHERE activity_type IN ('Usage', 'Waste', 'Adjustment') 
                                      AND quantity_change < 0
                                      AND DATE_FORMAT(activity_date, '%Y-%m') = ?";
        $stmt_ingredient_out = $mysqli->prepare($ingredient_stock_out_query);
        $stmt_ingredient_out->bind_param('s', $date);
        $stmt_ingredient_out->execute();
        $result_ingredient_out = $stmt_ingredient_out->get_result();
        $row_ingredient_out = $result_ingredient_out->fetch_assoc();
        $ingredient_stock_out = (int)$row_ingredient_out['total_out'];
        
        $stock_out[] = $product_stock_out + $ingredient_stock_out;
    }
    
    $data = [
        'labels' => $labels,
        'stock_in' => $stock_in,
        'stock_out' => $stock_out
    ];
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch movement data: ' . $e->getMessage()]);
}
?>
