<?php
require_once __DIR__ . '/../config/config.php';

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

//1. Customers
$query = "SELECT COUNT(*) FROM `rpos_customers` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($customers);
$stmt->fetch();
$stmt->close();

//2. Orders (count unique order codes)
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

//4. Sales
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($sales);
$stmt->fetch();
$stmt->close();

//5. Today's Sales
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` WHERE DATE(created_at) = CURDATE()";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($today_sales);
$stmt->fetch();
$stmt->close();

//6. This Month's Sales
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($month_sales);
$stmt->fetch();
$stmt->close();

//7. Pending Orders (count unique order codes)
$query = "SELECT COUNT(DISTINCT order_code) FROM `rpos_orders` WHERE order_status = '' OR order_status IS NULL";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($pending_orders);
$stmt->fetch();
$stmt->close();

//8. Low Stock Products
$query = "SELECT COUNT(*) FROM `rpos_products` WHERE prod_quantity <= prod_threshold";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($low_stock);
$stmt->fetch();
$stmt->close();

//9. Top Selling Products (Last 30 days)
$query = "SELECT p.prod_name, SUM(CAST(o.prod_qty AS UNSIGNED)) as total_qty, SUM(CAST(o.prod_price AS DECIMAL(10,2)) * CAST(o.prod_qty AS UNSIGNED)) as total_revenue
          FROM rpos_orders o 
          JOIN rpos_products p ON o.prod_id = p.prod_id 
          WHERE o.order_status IN ('Paid', 'Completed') AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          GROUP BY o.prod_id, p.prod_name 
          HAVING total_qty > 0
          ORDER BY total_qty DESC 
          LIMIT 5";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    error_log("Failed to prepare top products query: " . $mysqli->error);
    $top_products = [];
} else {
    if (!$stmt->execute()) {
        error_log("Failed to execute top products query: " . $stmt->error);
        $top_products = [];
    } else {
        $result = $stmt->get_result();
        $top_products = [];
        while ($row = $result->fetch_assoc()) {
            $top_products[] = $row;
        }
    }
    $stmt->close();
}

//10. Sales Trend (Last 7 days)
$query = "SELECT DATE(created_at) as sale_date, SUM(pay_amt) as daily_sales, COUNT(DISTINCT order_code) as daily_orders
          FROM rpos_payments 
          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          GROUP BY DATE(created_at)
          ORDER BY sale_date ASC";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    error_log("Failed to prepare sales trend query: " . $mysqli->error);
    $sales_trend = [];
} else {
    if (!$stmt->execute()) {
        error_log("Failed to execute sales trend query: " . $stmt->error);
        $sales_trend = [];
    } else {
        $result = $stmt->get_result();
        $sales_trend = [];
        while ($row = $result->fetch_assoc()) {
            $sales_trend[] = $row;
        }
    }
    $stmt->close();
}

//11. Order Status Distribution
$query = "SELECT 
            CASE 
                WHEN order_status = 'Paid' THEN 'Paid'
                WHEN order_status = 'Completed' THEN 'Completed'
                WHEN order_status = 'Cancelled' THEN 'Cancelled'
                WHEN order_status = '' OR order_status IS NULL THEN 'Pending'
                ELSE order_status
            END as status,
            COUNT(DISTINCT order_code) as count
          FROM rpos_orders 
          GROUP BY status";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    error_log("Failed to prepare order status query: " . $mysqli->error);
    $order_status_dist = [];
} else {
    if (!$stmt->execute()) {
        error_log("Failed to execute order status query: " . $stmt->error);
        $order_status_dist = [];
    } else {
        $result = $stmt->get_result();
        $order_status_dist = [];
        while ($row = $result->fetch_assoc()) {
            $order_status_dist[] = $row;
        }
    }
    $stmt->close();
}

//12. Recent Activity (Last 5 activities)
$query = "SELECT 'order' as type, order_code as code, customer_name as name, created_at, 'Order placed' as description
          FROM rpos_orders 
          GROUP BY order_code, customer_name, created_at
          UNION ALL
          SELECT 'payment' as type, pay_code as code, '' as name, created_at, CONCAT('Payment of ₱', CAST(pay_amt AS CHAR)) as description
          FROM rpos_payments
          ORDER BY created_at DESC 
          LIMIT 5";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    error_log("Failed to prepare recent activity query: " . $mysqli->error);
    $recent_activity = [];
} else {
    if (!$stmt->execute()) {
        error_log("Failed to execute recent activity query: " . $stmt->error);
        $recent_activity = [];
    } else {
        $result = $stmt->get_result();
        $recent_activity = [];
        while ($row = $result->fetch_assoc()) {
            $recent_activity[] = $row;
        }
    }
    $stmt->close();
}

//13. Calculate growth percentages
// Today vs Yesterday
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($yesterday_sales);
$stmt->fetch();
$stmt->close();

$today_growth = 0;
if ($yesterday_sales > 0) {
    $today_growth = (($today_sales - $yesterday_sales) / $yesterday_sales) * 100;
}

// This Month vs Last Month
$query = "SELECT SUM(pay_amt) FROM `rpos_payments` WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($last_month_sales);
$stmt->fetch();
$stmt->close();

$month_growth = 0;
if ($last_month_sales > 0) {
    $month_growth = (($month_sales - $last_month_sales) / $last_month_sales) * 100;
}

//14. Average Order Value
$query = "SELECT AVG(pay_amt) FROM `rpos_payments` WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($avg_order_value);
$stmt->fetch();
$stmt->close();

//15. Customer Growth (New customers this month)
$query = "SELECT COUNT(*) FROM `rpos_customers` WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($new_customers_month);
$stmt->fetch();
$stmt->close();

// Set default values to prevent undefined variable errors
$today_sales = $today_sales ?? 0;
$month_sales = $month_sales ?? 0;
$pending_orders = $pending_orders ?? 0;
$low_stock = $low_stock ?? 0;
$yesterday_sales = $yesterday_sales ?? 0;
$last_month_sales = $last_month_sales ?? 0;
$avg_order_value = $avg_order_value ?? 0;
$new_customers_month = $new_customers_month ?? 0;
$top_products = $top_products ?? [];
$sales_trend = $sales_trend ?? [];
$order_status_dist = $order_status_dist ?? [];
$recent_activity = $recent_activity ?? [];
?>