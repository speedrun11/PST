<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

echo "<h1>Available Products</h1>";
echo "<style>body { background: #1a1a2e; color: #f8f5f2; font-family: Arial; padding: 20px; }</style>";

$productQuery = "SELECT prod_id, prod_name, prod_price, prod_code FROM rpos_products ORDER BY prod_name";
$productResult = $mysqli->query($productQuery);

if ($productResult && $productResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #c0a062; color: #1a1a2e;'>";
    echo "<th>Product ID</th><th>Product Code</th><th>Product Name</th><th>Price</th><th>Additional Charge Test</th>";
    echo "</tr>";
    
    while ($row = $productResult->fetch_assoc()) {
        $itemName = strtolower($row['prod_name']);
        $hasAdditionalCharge = (strpos($itemName, 'double') !== false || strpos($itemName, 'regular + spicy') !== false);
        $chargeStatus = $hasAdditionalCharge ? "✅ +₱1.00" : "❌ No charge";
        
        echo "<tr>";
        echo "<td>{$row['prod_id']}</td>";
        echo "<td>{$row['prod_code']}</td>";
        echo "<td>{$row['prod_name']}</td>";
        echo "<td>₱{$row['prod_price']}</td>";
        echo "<td>$chargeStatus</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No products found in database.";
}

echo "<br><a href='debug_checkout.php'>Back to Debug Checkout</a>";
echo "<br><a href='test_additional_charges.php'>Test Additional Charges</a>";
?>
