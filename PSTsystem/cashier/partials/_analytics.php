<?php
//1. Customers
$query = "SELECT COUNT(*) FROM `rpos_customers` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($customers);
$stmt->fetch();
$stmt->close();

//2. Orders (distinct groups by order_code)
$query = "SELECT COUNT(DISTINCT order_code) FROM `rpos_orders` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($orders);
$stmt->fetch();
$stmt->close();

//3. Products
$query = "SELECT COUNT(*) FROM `rpos_products` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($products);
$stmt->fetch();
$stmt->close();

//4.Sales
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($sales);
$stmt->fetch();
$stmt->close();
$sales = $sales ?: 0;

//5. Pending Orders (by order_code)
$query = "SELECT COUNT(DISTINCT order_code)
          FROM rpos_orders
          WHERE order_status = 'Pending' OR order_status = ''";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($pendingOrders);
$stmt->fetch();
$stmt->close();
