<?php

session_start();

include('config/config.php');

include('config/checklogin.php');

check_login();



// This script adds sample product-ingredient relationships

// Run this after the migration to add some example relationships



echo "<h2>Add Sample Product-Ingredient Relationships</h2>";



try {

    // Sample relationships based on typical pastil ingredients

    $sample_relationships = [

        // B1 - Regular Beef Pastil

        ['39443d8638', 'fd840c315a', 100], // Rice: 100g

        ['39443d8638', '23e423af90', 50],  // Beef: 50g

        ['39443d8638', '5044e66a3e', 1],   // Banana Leaf: 1 piece

        

        // C1 - Regular Chicken Pastil

        ['52b31af7f6', 'fd840c315a', 100], // Rice: 100g

        ['52b31af7f6', 'ca04304db4', 50],  // Chicken: 50g

        ['52b31af7f6', '5044e66a3e', 1],   // Banana Leaf: 1 piece

        

        // C2 - Spicy Chicken Pastil

        ['3e82f9082a', 'fd840c315a', 100], // Rice: 100g

        ['3e82f9082a', 'ca04304db4', 50],  // Chicken: 50g

        ['3e82f9082a', '5044e66a3e', 1],   // Banana Leaf: 1 piece

        ['3e82f9082a', '133e6541d2', 1],   // Egg: 1 piece (for spicy version)

    ];

    

    $added_count = 0;

    $error_count = 0;

    

    foreach ($sample_relationships as $relationship) {

        // Check if relationship already exists

        $check = "SELECT id FROM rpos_product_ingredients WHERE product_id = ? AND ingredient_id = ?";

        $stmt = $mysqli->prepare($check);

        $stmt->bind_param('ss', $relationship[0], $relationship[1]);

        $stmt->execute();

        $result = $stmt->get_result();

        

        if ($result->num_rows == 0) {

            // Add the relationship

            $insert = "INSERT INTO rpos_product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";

            $stmt = $mysqli->prepare($insert);

            $stmt->bind_param('ssd', $relationship[0], $relationship[1], $relationship[2]);

            

            if ($stmt->execute()) {

                echo "<p style='color: green;'>✓ Added: Product {$relationship[0]} -> Ingredient {$relationship[1]} (Qty: {$relationship[2]})</p>";

                $added_count++;

            } else {

                echo "<p style='color: red;'>✗ Error adding relationship: " . $stmt->error . "</p>";

                $error_count++;

            }

        } else {

            echo "<p style='color: blue;'>ℹ Relationship already exists: Product {$relationship[0]} -> Ingredient {$relationship[1]}</p>";

        }

    }

    

    echo "<h3>Summary:</h3>";

    echo "<p style='color: green;'>✓ Added: {$added_count} relationships</p>";

    if ($error_count > 0) {

        echo "<p style='color: red;'>✗ Errors: {$error_count}</p>";

    }

    

    echo "<p><a href='products.php'>Go to Products Management</a></p>";

    

} catch (Exception $e) {

    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";

}

?>


        $stmt = $mysqli->prepare($check);

        $stmt->bind_param('ss', $relationship[0], $relationship[1]);

        $stmt->execute();

        $result = $stmt->get_result();

        

        if ($result->num_rows == 0) {

            // Add the relationship

            $insert = "INSERT INTO rpos_product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";

            $stmt = $mysqli->prepare($insert);

            $stmt->bind_param('ssd', $relationship[0], $relationship[1], $relationship[2]);

            

            if ($stmt->execute()) {

                echo "<p style='color: green;'>✓ Added: Product {$relationship[0]} -> Ingredient {$relationship[1]} (Qty: {$relationship[2]})</p>";

                $added_count++;

            } else {

                echo "<p style='color: red;'>✗ Error adding relationship: " . $stmt->error . "</p>";

                $error_count++;

            }

        } else {

            echo "<p style='color: blue;'>ℹ Relationship already exists: Product {$relationship[0]} -> Ingredient {$relationship[1]}</p>";

        }

    }

    

    echo "<h3>Summary:</h3>";

    echo "<p style='color: green;'>✓ Added: {$added_count} relationships</p>";

    if ($error_count > 0) {

        echo "<p style='color: red;'>✗ Errors: {$error_count}</p>";

    }

    

    echo "<p><a href='products.php'>Go to Products Management</a></p>";

    

} catch (Exception $e) {

    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";

}

?>


