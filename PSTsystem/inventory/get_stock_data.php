<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

header('Content-Type: application/json');

try {
    $labels = [];
    $current_stock = [];
    $thresholds = [];
    
    // Fetch all products
    $ret = "SELECT * FROM rpos_products ORDER BY prod_name ASC";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        $labels[] = $row['prod_name'] . ' (Product)';
        $current_stock[] = (int)$row['prod_quantity'];
        $thresholds[] = (int)$row['prod_threshold'];
    }
    
    // Fetch all ingredients
    $ret_ingredients = "SELECT * FROM rpos_ingredients ORDER BY ingredient_name ASC";
    $stmt_ingredients = $mysqli->prepare($ret_ingredients);
    $stmt_ingredients->execute();
    $res_ingredients = $stmt_ingredients->get_result();
    
    while ($row_ingredients = $res_ingredients->fetch_assoc()) {
        $labels[] = $row_ingredients['ingredient_name'] . ' (Ingredient)';
        $current_stock[] = (int)$row_ingredients['ingredient_quantity'];
        $thresholds[] = (int)$row_ingredients['ingredient_threshold'];
    }
    
    $data = [
        'labels' => $labels,
        'current_stock' => $current_stock,
        'threshold' => $thresholds
    ];
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch stock data: ' . $e->getMessage()]);
}
?>
