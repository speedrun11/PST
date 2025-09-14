<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

header('Content-Type: application/json');

$search_term = $_GET['q'] ?? '';
$category = $_GET['category'] ?? 'all';

$query = "SELECT * FROM rpos_products WHERE 1=1";
$params = [];
$types = '';

if (!empty($search_term)) {
    $query .= " AND (prod_name LIKE ? OR prod_code LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= 'ss';
}

if ($category !== 'all') {
    $query .= " AND prod_category = ?";
    $params[] = $category;
    $types .= 's';
}

$query .= " ORDER BY prod_name ASC";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['prod_id'],
        'name' => $row['prod_name'],
        'code' => $row['prod_code'],
        'price' => $row['prod_price'],
        'image' => $row['prod_img'] ?: 'default.jpg',
        'category' => $row['prod_category'] ?: 'uncategorized'
    ];
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'count' => count($products)
]);
?>
