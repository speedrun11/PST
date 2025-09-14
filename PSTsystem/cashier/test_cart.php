<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart Test</title>
    <style>
        body { background: #1a1a2e; color: #f8f5f2; font-family: Arial; padding: 20px; }
        .test-section { background: rgba(26, 26, 46, 0.8); padding: 20px; margin: 10px 0; border-radius: 10px; }
        button { background: #c0a062; color: #1a1a2e; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #9e2b2b; color: #f8f5f2; }
    </style>
</head>
<body>
    <h1>Cart Test Page</h1>
    
    <div class="test-section">
        <h2>Session Cart Data</h2>
        <pre><?php print_r($_SESSION['cart'] ?? 'No cart data'); ?></pre>
    </div>
    
    <div class="test-section">
        <h2>Test Actions</h2>
        <button onclick="testAddToCart()">Add Test Item to Cart</button>
        <button onclick="testSessionStorage()">Check SessionStorage</button>
        <button onclick="testCheckout()">Test Checkout</button>
        <button onclick="clearCart()">Clear Cart</button>
    </div>
    
    <div class="test-section">
        <h2>Debug Info</h2>
        <div id="debugInfo"></div>
    </div>

    <script>
        function testAddToCart() {
            const testItem = {
                id: 'test-001',
                name: 'C3 - Double Regular',
                price: 25.00,
                quantity: 2,
                img: 'default.jpg'
            };
            
            // Add to sessionStorage
            let cart = JSON.parse(sessionStorage.getItem('currentOrder') || '{}');
            if (!cart.items) cart.items = [];
            cart.items.push(testItem);
            sessionStorage.setItem('currentOrder', JSON.stringify(cart));
            
            // Add to PHP session
            fetch('store_cart_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cart)
            }).then(response => response.json())
              .then(data => {
                  document.getElementById('debugInfo').innerHTML = 'Test item added: ' + JSON.stringify(data);
                  location.reload();
              });
        }
        
        function testSessionStorage() {
            const data = sessionStorage.getItem('currentOrder');
            document.getElementById('debugInfo').innerHTML = 'SessionStorage: ' + (data || 'Empty');
        }
        
        function testCheckout() {
            window.location.href = 'enhanced_checkout.php';
        }
        
        function clearCart() {
            sessionStorage.removeItem('currentOrder');
            fetch('clear_cart_session.php', { method: 'POST' })
                .then(() => location.reload());
        }
    </script>
</body>
</html>
