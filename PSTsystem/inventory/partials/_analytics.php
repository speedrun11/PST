<?php
//1. Products
$query = "SELECT COUNT(*) FROM `rpos_products` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($products);
$stmt->fetch();
$stmt->close();

//2. Low stock items
$query = "SELECT COUNT(*) FROM `rpos_products` WHERE prod_quantity <= prod_threshold";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($low_stock);
$stmt->fetch();
$stmt->close();

//3. Critical stock items
$query = "SELECT COUNT(*) FROM `rpos_products` WHERE prod_quantity <= (prod_threshold * 0.5)";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($critical_stock);
$stmt->fetch();
$stmt->close();

//4. Inventory value
$query = "SELECT SUM(prod_price * prod_quantity) FROM `rpos_products`";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($inventory_value);
$stmt->fetch();
$stmt->close();

//5. Recent restocks
$query = "SELECT COUNT(*) FROM `rpos_inventory_logs` WHERE activity_type = 'Restock' AND activity_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($recent_restocks);
$stmt->fetch();
$stmt->close();
?>