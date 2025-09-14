<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

// Handle order submission (robust JSON-based payload)
if (isset($_POST['process_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_id = trim($_POST['customer_id'] ?? '');
    $order_type = $_POST['order_type'] ?? 'dine-in';
    $order_code = $alpha . '-' . $beta;
    
    if ($customer_name === '' || $customer_id === '') {
        $err = "Missing customer information. Please enter a name and ensure a customer ID is generated.";
    } else {
        $items_json = $_POST['items_json'] ?? '';
        $items = json_decode($items_json, true);
        if (!is_array($items) || count($items) === 0) {
            $err = "No items to process. Please go back and add products to cart.";
        } else {
            // Create a unique order group ID for this customer session
            $order_group_id = $customer_id . '_' . $order_code . '_' . time();
    
    $success_count = 0;
    $error_count = 0;
    
            foreach ($items as $item) {
                $prod_id = $item['prod_id'] ?? '';
                $prod_name = $item['prod_name'] ?? '';
                $prod_price = (float)($item['prod_price'] ?? 0);
                $prod_qty = (int)($item['prod_qty'] ?? 0);
                $additional_charge = (float)($item['additional_charge'] ?? 0);
                $order_status = 'Pending';
                
                if ($prod_id === '' || $prod_name === '' || $prod_qty < 1 || $prod_price < 0) {
                    $error_count++;
                    continue;
                }
                
                // Generate a unique order_id per row to satisfy PRIMARY KEY constraint
                try {
                    $item_order_id = bin2hex(random_bytes(5));
                } catch (Exception $e) {
                    $item_order_id = substr(md5(uniqid((string)mt_rand(), true)), 0, 10);
                }
                
                // Calculate final price including additional charges (spread per unit if provided per group)
                $final_price = $prod_price + ($prod_qty > 0 ? ($additional_charge / $prod_qty) : 0);
        
        // Verify product exists before inserting
        $verifyQuery = "SELECT prod_id FROM rpos_products WHERE prod_id = ?";
        $verifyStmt = $mysqli->prepare($verifyQuery);
        $verifyStmt->bind_param('s', $prod_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        if ($verifyResult->num_rows === 0) {
                    error_log("enhanced_checkout: Product ID $prod_id does not exist in rpos_products table");
            $error_count++;
            continue;
        }
                
                $postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, order_status, order_type, additional_charge, order_group_id) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
                $postStmt = $mysqli->prepare($postQuery);
                $postStmt->bind_param(
                    'ssssssssssss',
                    $prod_qty,
                    $item_order_id,
                    $order_code,
                    $customer_id,
                    $customer_name,
                    $prod_id,
                    $prod_name,
                    $final_price,
                    $order_status,
                    $order_type,
                    $additional_charge,
                    $order_group_id
                );
        
        if ($postStmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
                    error_log("enhanced_checkout: Order insert error: " . $postStmt->error);
        }
    }
    
    if ($success_count > 0) {
        $_SESSION['order_success'] = "Order processed successfully! $success_count items added. Order Type: " . ucfirst($order_type);
        $_SESSION['order_code'] = $order_code;
        $_SESSION['customer_id'] = $customer_id;
        $_SESSION['order_type'] = $order_type;
        header("Location: payments.php");
        exit();
    } else {
                $err = "Failed to process order. Please try again. (No items were inserted)";
            }
        }
    }
}

require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Enhanced Checkout</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-light: #f8f5f2;
            --accent-gold: #c0a062;
            --accent-red: #9e2b2b;
            --accent-green: #4a6b57;
            --accent-blue: #3a5673;
            --text-light: #f8f5f2;
            --text-dark: #1a1a2e;
            --transition-speed: 0.4s;
        }
        
        body {
            background-color: var(--primary-dark);
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
        }
        
        .header {
            background: url(../admin/assets/img/theme/pastil.jpg) no-repeat center center;
            background-size: cover;
        }
        
        .mask {
            background-color: rgba(26, 26, 46, 0.5) !important;
        }
        
        .card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            backdrop-filter: blur(8px);
            transition: all var(--transition-speed) ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .checkout-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .order-summary {
            background: rgba(26, 26, 46, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(192, 160, 98, 0.1);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-details h5 {
            color: var(--accent-gold);
            margin: 0;
            font-weight: 600;
        }
        
        .item-details p {
            margin: 5px 0 0 0;
            color: var(--text-light);
            font-size: 0.9em;
        }
        
        .item-quantity {
            background: var(--accent-gold);
            color: var(--text-dark);
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        
        .item-price {
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.1em;
        }
        
        .order-total {
            background: rgba(192, 160, 98, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        
        .total-label {
            font-size: 1.2em;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }
        
        .total-amount {
            font-size: 2em;
            font-weight: 700;
            color: var(--accent-gold);
        }
        
        .customer-info {
            background: rgba(26, 26, 46, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            color: var(--accent-gold);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 5px;
            padding: 12px;
            color: var(--text-light);
            font-size: 1em;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 2px rgba(192, 160, 98, 0.2);
        }
        
        .form-control:read-only {
            background: rgba(26, 26, 46, 0.6);
            color: rgba(192, 160, 98, 0.8);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1em;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-green), var(--accent-blue));
            color: var(--text-light);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 107, 87, 0.4);
        }
        
        .btn-secondary {
            background: rgba(192, 160, 98, 0.2);
            color: var(--accent-gold);
            border: 1px solid rgba(192, 160, 98, 0.4);
        }
        
        .btn-secondary:hover {
            background: rgba(192, 160, 98, 0.3);
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-danger {
            background: rgba(158, 43, 43, 0.2);
            border: 1px solid rgba(158, 43, 43, 0.4);
            color: #ff6b6b;
        }
        
        .alert-success {
            background: rgba(74, 107, 87, 0.2);
            border: 1px solid rgba(74, 107, 87, 0.4);
            color: #51cf66;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 3px solid rgba(192, 160, 98, 0.3);
            border-top: 3px solid var(--accent-gold);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                padding: 0 15px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .item-info {
                width: 100%;
            }
        }
    </style>
</head>
<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    ?>
    <!-- Header -->
    <div style="background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <div class="checkout-container">
        
        <?php if (isset($err)): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $err; ?>
          </div>
        <?php endif; ?>
        
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">Order Checkout</h3>
          </div>
          <div class="card-body">
            
            <!-- Order Summary -->
            <div class="order-summary">
              <h4 style="color: var(--accent-gold); margin-bottom: 20px;">
                <i class="fas fa-shopping-cart"></i> Order Summary
              </h4>
              <div id="orderItems">
                <!-- Items will be populated by JavaScript -->
              </div>
              <div class="order-total">
                <div class="total-label">Total Amount</div>
                <div class="total-amount" id="totalAmount">₱ 0.00</div>
              </div>
            </div>
            
            <!-- Customer Information -->
            <div class="customer-info">
              <h4 style="color: var(--accent-gold); margin-bottom: 20px;">
                <i class="fas fa-user"></i> Customer Information
              </h4>
              
              <form method="POST" id="checkoutForm">
                <div class="form-group">
                  <label class="form-label">Customer Name</label>
                  <input type="text" name="customer_name" id="customerName" class="form-control" required>
                </div>
                
                <div class="form-group">
                  <label class="form-label">Customer ID</label>
                  <input type="text" name="customer_id" id="customerId" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                  <label class="form-label">Order Type</label>
                  <div class="order-type-selection" style="display: flex; gap: 15px; margin-top: 10px;">
                    <label class="order-type-option" style="flex: 1; cursor: pointer;">
                      <input type="radio" name="order_type" value="dine-in" checked style="margin-right: 8px;">
                      <div class="order-type-card" style="background: rgba(26, 26, 46, 0.8); border: 2px solid var(--accent-gold); border-radius: 8px; padding: 15px; text-align: center; transition: all 0.3s ease;">
                        <i class="fas fa-utensils" style="font-size: 1.5em; color: var(--accent-gold); margin-bottom: 8px;"></i>
                        <div style="font-weight: 600; color: var(--accent-gold);">Dine-In</div>
                        <div style="font-size: 0.9em; color: var(--text-light);">No additional charges</div>
                      </div>
                    </label>
                    <label class="order-type-option" style="flex: 1; cursor: pointer;">
                      <input type="radio" name="order_type" value="takeout" style="margin-right: 8px;">
                      <div class="order-type-card" style="background: rgba(26, 26, 46, 0.8); border: 2px solid rgba(192, 160, 98, 0.3); border-radius: 8px; padding: 15px; text-align: center; transition: all 0.3s ease;">
                        <i class="fas fa-shopping-bag" style="font-size: 1.5em; color: var(--accent-gold); margin-bottom: 8px;"></i>
                        <div style="font-weight: 600; color: var(--accent-gold);">Takeout</div>
                        <div style="font-size: 0.9em; color: var(--text-light);">+₱1 for double variants and regular + spicy</div>
                      </div>
                    </label>
                  </div>
                </div>
                
                <!-- Hidden fields for order items -->
                <div id="hiddenItems"></div>
                <input type="hidden" name="items_json" id="items_json" value="">
                
                <div class="btn-group">
                  <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                  </a>
                  <button type="submit" name="process_order" class="btn btn-primary" id="processOrderBtn">
                    <i class="fas fa-credit-card"></i> Process Order
                  </button>
                </div>
              </form>
            </div>
            
            <!-- Loading indicator -->
            <div class="loading" id="loading">
              <div class="spinner"></div>
              <div>Processing order...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  
  <script>
    // Get order data from sessionStorage
    let orderData = JSON.parse(sessionStorage.getItem('currentOrder') || '{}');
    
    // Debug: Log the order data
    console.log('Order data from sessionStorage:', orderData);
    console.log('SessionStorage currentOrder:', sessionStorage.getItem('currentOrder'));
    
    if (!orderData.items || orderData.items.length === 0) {
      // Try to get from PHP session as fallback
      <?php
      if (isset($_SESSION['single_item_cart']) && !empty($_SESSION['single_item_cart'])) {
        echo "const singleItemCart = " . json_encode($_SESSION['single_item_cart']) . ";";
        echo "if (singleItemCart && singleItemCart.length > 0) {";
        echo "  orderData.items = singleItemCart;";
        echo "  orderData.customer_name = '" . ($_SESSION['customer_name'] ?? '') . "';";
        echo "  orderData.customer_id = '" . ($_SESSION['customer_id'] ?? '') . "';";
        echo "  // Clear the single item cart from session";
        echo "  fetch('clear_single_item_cart.php', { method: 'POST' });";
        echo "} else if (typeof fallbackCart !== 'undefined' && fallbackCart && fallbackCart.length > 0) {";
        echo "  orderData.items = fallbackCart;";
        echo "  orderData.customer_name = '" . ($_SESSION['customer_name'] ?? '') . "';";
        echo "  orderData.customer_id = '" . ($_SESSION['customer_id'] ?? '') . "';";
        echo "} else {";
        echo "  alert('No items in cart. Redirecting to products page.');";
        echo "  window.location.href = 'orders.php';";
        echo "}";
      } else if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        echo "const fallbackCart = " . json_encode($_SESSION['cart']) . ";";
        echo "if (fallbackCart && fallbackCart.length > 0) {";
        echo "  orderData.items = fallbackCart;";
        echo "  orderData.customer_name = '" . ($_SESSION['customer_name'] ?? '') . "';";
        echo "  orderData.customer_id = '" . ($_SESSION['customer_id'] ?? '') . "';";
        echo "} else {";
        echo "  alert('No items in cart. Redirecting to products page.');";
        echo "  window.location.href = 'orders.php';";
        echo "}";
      } else {
        echo "alert('No items in cart. Redirecting to products page.');";
        echo "window.location.href = 'orders.php';";
      }
      ?>
    }
    
    // Populate form with order data
    document.getElementById('customerName').value = orderData.customer_name || '';
    document.getElementById('customerId').value = orderData.customer_id || '';
    
    // Display order items
    function displayOrderItems() {
      const orderItemsDiv = document.getElementById('orderItems');
      const hiddenItemsDiv = document.getElementById('hiddenItems');
      const totalAmountDiv = document.getElementById('totalAmount');
      const itemsJsonInput = document.getElementById('items_json');
      
      let total = 0;
      let itemsHTML = '';
      let hiddenHTML = '';
      let additionalCharges = 0;
      
      // Get selected order type
      const orderType = document.querySelector('input[name="order_type"]:checked').value;
      
      orderData.items.forEach((item, index) => {
        let itemPrice = item.price;
        let itemTotal = itemPrice * item.quantity;
        let additionalCharge = 0;
        
        // Check if takeout and item qualifies for additional charge
        if (orderType === 'takeout') {
          const itemName = item.name.toLowerCase();
          // Charge for Double variants and Combo items (Regular + Spicy)
          if (itemName.includes('double') || itemName.includes('combo') || itemName.includes('regular + spicy')) {
            additionalCharge = 1 * item.quantity; // ₱1 per item
            additionalCharges += additionalCharge;
            itemTotal += additionalCharge;
          }
        }
        
        total += itemTotal;
        
        itemsHTML += `
          <div class="order-item">
            <div class="item-info">
              <img src="../admin/assets/img/products/${item.img}" alt="${item.name}" class="item-image">
              <div class="item-details">
                <h5>${item.name}</h5>
                <p>₱ ${itemPrice.toFixed(2)} each${additionalCharge > 0 ? ` + ₱${additionalCharge.toFixed(2)} takeout charge` : ''}</p>
              </div>
            </div>
            <div class="item-quantity">${item.quantity}</div>
            <div class="item-price">₱ ${itemTotal.toFixed(2)}</div>
          </div>
        `;
        
        hiddenHTML += `
          <input type="hidden" name="items[${index}][prod_id]" value="${item.id}">
          <input type="hidden" name="items[${index}][prod_name]" value="${item.name}">
          <input type="hidden" name="items[${index}][prod_price]" value="${itemPrice}">
          <input type="hidden" name="items[${index}][prod_qty]" value="${item.quantity}">
          <input type="hidden" name="items[${index}][additional_charge]" value="${additionalCharge}">
        `;
      });
      
      // Add additional charges summary if any
      if (additionalCharges > 0) {
        itemsHTML += `
          <div class="order-item" style="border-top: 2px solid var(--accent-gold); margin-top: 10px; padding-top: 15px;">
            <div class="item-info">
              <div class="item-details">
                <h5 style="color: var(--accent-gold);">Takeout Additional Charges</h5>
                <p style="color: var(--text-light);">₱1.00 per double variant and regular + spicy item</p>
              </div>
            </div>
            <div class="item-quantity"></div>
            <div class="item-price" style="color: var(--accent-gold);">+₱ ${additionalCharges.toFixed(2)}</div>
          </div>
        `;
      }
      
      orderItemsDiv.innerHTML = itemsHTML;
      hiddenItemsDiv.innerHTML = hiddenHTML;
      totalAmountDiv.textContent = `₱ ${total.toFixed(2)}`;
      
      // Populate compact JSON payload expected by server
      const compactItems = orderData.items.map((item, index) => ({
        prod_id: item.id,
        prod_name: item.name,
        prod_price: item.price,
        prod_qty: item.quantity,
        additional_charge: (function() {
          const orderType = document.querySelector('input[name="order_type"]:checked').value;
          if (orderType !== 'takeout') return 0;
          const name = (item.name || '').toLowerCase();
          if (name.includes('double') || name.includes('combo') || name.includes('regular + spicy')) {
            return 1 * item.quantity;
          }
          return 0;
        })()
      }));
      itemsJsonInput.value = JSON.stringify(compactItems);
    }
    
    // Initialize display
    displayOrderItems();
    
    // Handle order type selection
    document.querySelectorAll('input[name="order_type"]').forEach(radio => {
      radio.addEventListener('change', function() {
        // Update visual selection
        document.querySelectorAll('.order-type-card').forEach(card => {
          card.style.borderColor = 'rgba(192, 160, 98, 0.3)';
          card.style.background = 'rgba(26, 26, 46, 0.8)';
        });
        
        this.closest('.order-type-option').querySelector('.order-type-card').style.borderColor = 'var(--accent-gold)';
        this.closest('.order-type-option').querySelector('.order-type-card').style.background = 'rgba(26, 26, 46, 0.9)';
        
        // Recalculate totals with new order type
        displayOrderItems();
      });
    });
    
    // Handle form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
      const customerName = document.getElementById('customerName').value.trim();
      
      if (!customerName) {
        e.preventDefault();
        alert('Please enter customer name');
        return;
      }
      
      // Show loading
      document.getElementById('loading').style.display = 'block';
      document.getElementById('processOrderBtn').disabled = true;
    });
    
    // Clear session storage on page unload
    window.addEventListener('beforeunload', function() {
      sessionStorage.removeItem('currentOrder');
    });
  </script>
</body>
</html>
