<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

// Fix database schema
$fixes = [
    "ALTER TABLE rpos_orders ADD COLUMN IF NOT EXISTS order_type VARCHAR(20) DEFAULT 'dine-in'",
    "ALTER TABLE rpos_orders ADD COLUMN IF NOT EXISTS additional_charge DECIMAL(10,2) DEFAULT 0.00",
    "ALTER TABLE rpos_payments ADD COLUMN IF NOT EXISTS order_type VARCHAR(20) DEFAULT 'dine-in'"
];

echo "<h1>Database Fix</h1>";
echo "<style>body { background: #1a1a2e; color: #f8f5f2; font-family: Arial; padding: 20px; }</style>";

foreach ($fixes as $fix) {
    if ($mysqli->query($fix)) {
        echo "✅ " . $fix . "<br>";
    } else {
        echo "❌ " . $fix . " - Error: " . $mysqli->error . "<br>";
    }
}

echo "<br><a href='debug_checkout.php'>Test Order Processing</a>";
?>
