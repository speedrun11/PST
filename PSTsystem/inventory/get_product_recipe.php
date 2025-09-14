<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
// Optional: restrict to logged-in roles
header('Content-Type: application/json');

if (!isset($_GET['product_id']) || $_GET['product_id'] === '') {
    http_response_code(400);
    echo json_encode(['error' => 'product_id is required']);
    exit;
}

$product_id = $_GET['product_id'];

$sql = "SELECT pi.ingredient_id, pi.quantity_required, i.ingredient_name, i.ingredient_unit
        FROM rpos_product_ingredients pi
        JOIN rpos_ingredients i ON i.ingredient_id = pi.ingredient_id
        WHERE pi.product_id = ?";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error]);
    exit;
}
$stmt->bind_param('s', $product_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['product_id' => $product_id, 'ingredients' => $data]);
?>


