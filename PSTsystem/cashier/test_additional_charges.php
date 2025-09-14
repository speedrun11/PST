<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Additional Charges</h1>";
echo "<style>body { background: #1a1a2e; color: #f8f5f2; font-family: Arial; padding: 20px; }</style>";

// Get products from database
$productQuery = "SELECT prod_id, prod_name, prod_price FROM rpos_products ORDER BY prod_name";
$productResult = $mysqli->query($productQuery);
$products = [];

if ($productResult && $productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

echo "<h2>Available Products:</h2>";
foreach ($products as $product) {
    echo "ID: {$product['prod_id']}, Name: {$product['prod_name']}, Price: ₱{$product['prod_price']}<br>";
}

echo "<h2>Test Additional Charges Logic:</h2>";

// Test the additional charges logic
function testAdditionalCharges($itemName, $orderType) {
    $additionalCharge = 0;
    if ($orderType === 'takeout') {
        $itemName = strtolower($itemName);
        if (strpos($itemName, 'double') !== false || strpos($itemName, 'regular + spicy') !== false) {
            $additionalCharge = 1; // ₱1 per item
        }
    }
    return $additionalCharge;
}

// Test with sample product names
$testItems = [
    'C1 - Regular Chicken',
    'C2 - Spicy Chicken', 
    'C3 - Double Regular',
    'C4 - Double Spicy',
    'C5 - Regular + Spicy',
    'B1 - Regular Beef',
    'B2 - Spicy Beef',
    'B3 - Double Regular',
    'B4 - Double Spicy',
    'B5 - Regular + Spicy'
];

echo "<h3>Dine-in Orders (No Additional Charges):</h3>";
foreach ($testItems as $item) {
    $charge = testAdditionalCharges($item, 'dine-in');
    echo "$item: +₱$charge<br>";
}

echo "<h3>Takeout Orders (Additional Charges for Double and Regular + Spicy):</h3>";
foreach ($testItems as $item) {
    $charge = testAdditionalCharges($item, 'takeout');
    $status = $charge > 0 ? "✅ +₱$charge" : "❌ No charge";
    echo "$item: $status<br>";
}

// Test with actual products if available
if (!empty($products)) {
    echo "<h2>Test with Real Products:</h2>";
    
    $testOrder = [
        'customer_name' => 'Test Customer',
        'customer_id' => 'CUST-' . time() . rand(1000, 9999),
        'order_type' => 'takeout',
        'items' => []
    ];
    
    // Add first few products to test
    for ($i = 0; $i < min(3, count($products)); $i++) {
        $product = $products[$i];
        $additionalCharge = testAdditionalCharges($product['prod_name'], 'takeout');
        
        $testOrder['items'][] = [
            'id' => $product['prod_id'],
            'name' => $product['prod_name'],
            'price' => floatval($product['prod_price']),
            'quantity' => 2,
            'additional_charge' => $additionalCharge * 2, // per quantity
            'img' => 'default.jpg'
        ];
        
        echo "Product: {$product['prod_name']}, Base Price: ₱{$product['prod_price']}, Additional Charge: ₱$additionalCharge per item<br>";
    }
    
    echo "<h3>Test Order Data:</h3>";
    echo "<pre>";
    print_r($testOrder);
    echo "</pre>";
    
    // Test order processing
    if (isset($_POST['process_test_order'])) {
        echo "<h2>Processing Test Order...</h2>";
        
        $order_code = 'TEST-' . time() . rand(1000, 9999);
        $order_id = 'test-' . time() . rand(1000, 9999);
        $success_count = 0;
        $error_count = 0;
        
        foreach ($testOrder['items'] as $item) {
            $prod_id = $item['id'];
            $prod_name = $item['name'];
            $prod_price = $item['price'];
            $prod_qty = $item['quantity'];
            $additional_charge = $item['additional_charge'];
            
            $final_price = $prod_price + ($additional_charge / $prod_qty);
            
            echo "Processing: $prod_name, Qty: $prod_qty, Base: ₱$prod_price, Additional: ₱$additional_charge, Final: ₱$final_price<br>";
            
            $postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, order_type, additional_charge) VALUES(?,?,?,?,?,?,?,?,?,?)";
            $postStmt = $mysqli->prepare($postQuery);
            $rc = $postStmt->bind_param('ssssssssss', $prod_qty, $order_id, $order_code, $testOrder['customer_id'], $testOrder['customer_name'], $prod_id, $prod_name, $final_price, $testOrder['order_type'], $additional_charge);
            
            if ($postStmt->execute()) {
                $success_count++;
                echo "✅ Success<br>";
            } else {
                $error_count++;
                echo "❌ Failed: " . $postStmt->error . "<br>";
            }
        }
        
        echo "<br><strong>Result: $success_count successful, $error_count failed</strong><br>";
    }
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='process_test_order'>Process Test Order</button>";
    echo "</form>";
}

// Add cleanup option
if (isset($_POST['cleanup_test_data'])) {
    $cleanupQuery = "DELETE FROM rpos_orders WHERE order_id LIKE 'test-%'";
    if ($mysqli->query($cleanupQuery)) {
        echo "<div style='color: #51cf66; margin: 10px 0;'>✅ Test data cleaned up successfully</div>";
    } else {
        echo "<div style='color: #ff6b6b; margin: 10px 0;'>❌ Cleanup failed: " . $mysqli->error . "</div>";
    }
}

echo "<br><a href='debug_checkout.php'>Back to Debug Checkout</a>";
echo "<br><a href='check_products.php'>Check Products</a>";

echo "<form method='POST' style='margin-top: 20px;'>";
echo "<button type='submit' name='cleanup_test_data' style='background: #9e2b2b; color: #f8f5f2; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Clean Up Test Data</button>";
echo "</form>";
?>
