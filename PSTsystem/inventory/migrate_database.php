<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// This script helps migrate the existing database to the new structure
// Run this once to update your existing database

echo "<h2>Database Migration Script</h2>";

try {
    // 1. Create the product_ingredients table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS `rpos_product_ingredients` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` varchar(200) NOT NULL,
        `ingredient_id` varchar(200) NOT NULL,
        `quantity_required` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT 'Quantity of ingredient needed per product unit',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_product_ingredient` (`product_id`,`ingredient_id`),
        KEY `idx_product` (`product_id`),
        KEY `idx_ingredient` (`ingredient_id`),
        CONSTRAINT `fk_prod_ing_product` FOREIGN KEY (`product_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_prod_ing_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `rpos_ingredients` (`ingredient_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($mysqli->query($create_table)) {
        echo "<p style='color: green;'>✓ Product ingredients table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating product ingredients table: " . $mysqli->error . "</p>";
    }
    
    // 2. Remove supplier_id column from products table if it exists
    $check_column = "SHOW COLUMNS FROM rpos_products LIKE 'supplier_id'";
    $result = $mysqli->query($check_column);
    
    if ($result && $result->num_rows > 0) {
        $remove_column = "ALTER TABLE rpos_products DROP COLUMN supplier_id";
        if ($mysqli->query($remove_column)) {
            echo "<p style='color: green;'>✓ Removed supplier_id column from products table</p>";
        } else {
            echo "<p style='color: red;'>✗ Error removing supplier_id column: " . $mysqli->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ supplier_id column not found in products table (already removed or never existed)</p>";
    }
    
    // 3. Add some sample product-ingredient relationships for existing products
    // This is just an example - you should customize this based on your actual products
    
    // Check if there are any existing product-ingredient relationships
    $check_existing = "SELECT COUNT(*) as count FROM rpos_product_ingredients";
    $result = $mysqli->query($check_existing);
    $count = $result->fetch_assoc()['count'];
    
    if ($count == 0) {
        echo "<p style='color: blue;'>ℹ No existing product-ingredient relationships found. You can add them manually through the product management interface.</p>";
        
        // Example: Add some sample relationships (uncomment and modify as needed)
        /*
        $sample_relationships = [
            ['39443d8638', 'fd840c315a', 100], // B1 - Regular Beef needs 100g rice
            ['39443d8638', '23e423af90', 50],  // B1 - Regular Beef needs 50g beef
            ['39443d8638', '5044e66a3e', 1],   // B1 - Regular Beef needs 1 banana leaf
            ['52b31af7f6', 'fd840c315a', 100], // C1 - Regular Chicken needs 100g rice
            ['52b31af7f6', 'ca04304db4', 50],  // C1 - Regular Chicken needs 50g chicken
            ['52b31af7f6', '5044e66a3e', 1],   // C1 - Regular Chicken needs 1 banana leaf
        ];
        
        foreach ($sample_relationships as $relationship) {
            $insert = "INSERT INTO rpos_product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($insert);
            $stmt->bind_param('ssd', $relationship[0], $relationship[1], $relationship[2]);
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ Added sample relationship: Product {$relationship[0]} -> Ingredient {$relationship[1]}</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding relationship: " . $stmt->error . "</p>";
            }
        }
        */
    } else {
        echo "<p style='color: blue;'>ℹ Found {$count} existing product-ingredient relationships</p>";
    }
    
    echo "<h3>Migration completed!</h3>";
    echo "<p><a href='products.php'>Go to Products Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error during migration: " . $e->getMessage() . "</p>";
}
?>
