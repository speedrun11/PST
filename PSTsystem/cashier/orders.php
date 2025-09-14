<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Enhanced Order Management</title>
    
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
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(192, 160, 98, 0.4);
        }
        
        .table {
            color: var(--text-light);
        }
        
        .table thead th {
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
        }
        
        .table tbody tr {
            border-bottom: 1px solid rgba(192, 160, 98, 0.1);
            transition: all var(--transition-speed) ease;
        }
        
        .table tbody tr:hover {
            background: rgba(192, 160, 98, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, rgba(58, 86, 115, 0.8), rgba(74, 107, 87, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
            background: linear-gradient(135deg, rgba(58, 86, 115, 1), rgba(74, 107, 87, 0.8));
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
            background: linear-gradient(135deg, rgba(74, 107, 87, 1), rgba(58, 86, 115, 0.8));
        }
        
        .btn-warning {
            background: linear-gradient(135deg, rgba(192, 160, 98, 0.8), rgba(158, 43, 43, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-dark);
            font-weight: 600;
            transition: all var(--transition-speed) ease;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, rgba(192, 160, 98, 1), rgba(158, 43, 43, 0.8));
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        .text-gold {
            color: var(--accent-gold) !important;
        }
        
        .table thead {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 2px solid var(--accent-gold);
        }
        
        .table thead th {
            color: var(--accent-gold) !important;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 1rem;
        }
        
        .table tbody {
            background: rgba(26, 26, 46, 0.8);
        }
        
        .table tbody td, .table tbody th {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid rgba(192, 160, 98, 0.1);
        }
        
        .table tbody tr:hover {
            background: rgba(192, 160, 98, 0.1) !important;
            transform: translateX(5px);
        }
        
        .card-header {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }

        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        @media (max-width: 768px) {
            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        
        /* Enhanced POS Styles */
        .pos-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            height: calc(100vh - 200px);
        }
        
        .products-section {
            background: rgba(26, 26, 46, 0.8);
            margin-top: 150px;
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
        }
        
        .cart-section {
            background: rgba(26, 26, 46, 0.9);
            border-radius: 10px;
            padding: 20px;
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .product-card {
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 15px;
            transition: all var(--transition-speed) ease;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-gold);
            box-shadow: 0 8px 25px rgba(192, 160, 98, 0.2);
        }
        
        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .product-name {
            font-weight: 600;
            color: var(--accent-gold);
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 1.2em;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .add-to-cart-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-red));
            border: none;
            color: var(--text-dark);
            padding: 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: all var(--transition-speed) ease;
        }
        
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(192, 160, 98, 0.4);
        }
        
        .search-bar {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 25px;
            padding: 12px 20px;
            color: var(--text-light);
            width: 100%;
            margin-bottom: 20px;
        }
        
        .search-bar:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 2px rgba(192, 160, 98, 0.2);
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(192, 160, 98, 0.1);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            color: var(--accent-gold);
        }
        
        .cart-item-price {
            color: var(--text-light);
            font-size: 0.9em;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            background: var(--accent-gold);
            border: none;
            color: var(--text-dark);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .quantity-btn:hover {
            background: var(--accent-red);
            transform: scale(1.1);
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 5px;
            color: var(--text-light);
            padding: 5px;
        }
        
        .remove-item {
            background: var(--accent-red);
            border: none;
            color: var(--text-light);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .remove-item:hover {
            background: #ff4444;
            transform: scale(1.1);
        }
        
        .cart-total {
            background: rgba(192, 160, 98, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        
        .total-amount {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--accent-gold);
        }
        
        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-green), var(--accent-blue));
            border: none;
            color: var(--text-light);
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1em;
            margin-top: 15px;
            transition: all var(--transition-speed) ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 107, 87, 0.4);
        }
        
        .checkout-btn:disabled {
            background: rgba(74, 107, 87, 0.3);
            cursor: not-allowed;
            transform: none;
        }
        
        .empty-cart {
            text-align: center;
            color: rgba(192, 160, 98, 0.6);
            padding: 40px 20px;
        }
        
        .empty-cart i {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .cart-title {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-size: 1.3em;
            font-weight: 600;
        }
        
        .cart-count {
            background: var(--accent-gold);
            color: var(--text-dark);
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .clear-cart-btn {
            background: var(--accent-red);
            border: none;
            color: var(--text-light);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .clear-cart-btn:hover {
            background: #ff4444;
            transform: translateY(-1px);
        }
        
        .customer-info {
            background: rgba(26, 26, 46, 0.6);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .customer-input {
            width: 100%;
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 5px;
            padding: 10px;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .customer-input:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 2px rgba(192, 160, 98, 0.2);
        }
        
        .category-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .category-btn {
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            font-size: 0.9em;
        }
        
        .category-btn:hover, .category-btn.active {
            background: var(--accent-gold);
            color: var(--text-dark);
            border-color: var(--accent-gold);
        }
        
        @media (max-width: 768px) {
            .pos-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .cart-section {
                position: static;
                margin-top: 20px;
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
      <div class="pos-container">
        <!-- Products Section -->
        <div class="products-section">
          <div class="card-header">
            <h3 class="mb-0">Products Menu</h3>
                </div>
          
          <!-- Search Bar -->
          <input type="text" id="productSearch" class="search-bar" placeholder="Search products...">
          
          <!-- Category Filter -->
          <div class="category-filter">
            <button class="category-btn active" data-category="all">All Products</button>
            <?php
            $catQuery = "SELECT DISTINCT prod_category FROM rpos_products WHERE prod_category IS NOT NULL AND prod_category != ''";
            $catStmt = $mysqli->prepare($catQuery);
            $catStmt->execute();
            $catRes = $catStmt->get_result();
            while ($category = $catRes->fetch_object()) {
              echo '<button class="category-btn" data-category="' . htmlspecialchars($category->prod_category) . '">' . htmlspecialchars($category->prod_category) . '</button>';
            }
            ?>
              </div>
          
          <!-- Products Grid -->
          <div class="product-grid" id="productGrid">
                  <?php
            // Fetch products
            $ret = "SELECT * FROM rpos_products ORDER BY `rpos_products`.`created_at` DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                    // Compute available stock considering mirror/combo links
                    $available_qty = (int)$prod->prod_quantity;
                    $badge = '';
                    $link_stmt = $mysqli->prepare("SELECT l.relation, l.base_product_id, bp.prod_name, bp.prod_quantity 
                                                   FROM rpos_product_links l 
                                                   JOIN rpos_products bp ON bp.prod_id = l.base_product_id 
                                                   WHERE l.linked_product_id = ?");
                    if ($link_stmt) {
                      $link_stmt->bind_param('s', $prod->prod_id);
                      $link_stmt->execute();
                      $link_res = $link_stmt->get_result();
                      $bases = [];
                      $is_mirror = false;
                      while ($row = $link_res->fetch_assoc()) {
                        if ($row['relation'] === 'mirror') {
                          $is_mirror = true;
                          $available_qty = intdiv(max(0, (int)$row['prod_quantity']), 2);
                          $badge = '(Mirror of '.htmlspecialchars($row['prod_name']).')';
                        } else if ($row['relation'] === 'combo') {
                          $bases[] = $row;
                        }
                      }
                      if (!$is_mirror && count($bases) > 0) {
                        $mins = array_map(function($r){ return (int)$r['prod_quantity']; }, $bases);
                        $available_qty = count($mins) ? min($mins) : $available_qty;
                        $names = array_map(function($r){ return htmlspecialchars($r['prod_name']); }, $bases);
                        $badge = '(Combo of '.implode(' + ', $names).')';
                      }
                      $link_stmt->close();
                    }
                    $is_out = ($available_qty <= 0);
                  ?>
              <div class="product-card" data-category="<?php echo htmlspecialchars($prod->prod_category ?? 'uncategorized'); ?>" data-name="<?php echo htmlspecialchars(strtolower($prod->prod_name)); ?>">
                <img src="../admin/assets/img/products/<?php echo $prod->prod_img ?: 'default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($prod->prod_name); ?>" 
                     class="product-image">
                <div class="product-name"><?php echo htmlspecialchars($prod->prod_name); ?> <?php if($badge) { echo '<small class=\"text-gold\">'. $badge .'</small>'; } ?></div>
                <div class="product-price">₱ <?php echo number_format($prod->prod_price, 2); ?> <small class="text-gold">| Stock: <?php echo (int)$available_qty; ?></small></div>
                <button class="add-to-cart-btn" 
                        data-prod-id="<?php echo $prod->prod_id; ?>"
                        data-prod-name="<?php echo htmlspecialchars($prod->prod_name); ?>"
                        data-prod-price="<?php echo $prod->prod_price; ?>"
                        data-available="<?php echo (int)$available_qty; ?>"
                        data-prod-img="<?php echo $prod->prod_img ?: 'default.jpg'; ?>" <?php echo $is_out ? 'disabled' : ''; ?>>
                  <i class="fas fa-cart-plus"></i> <?php echo $is_out ? 'Out of Stock' : 'Add to Cart'; ?>
                          </button>
              </div>
                  <?php } ?>
          </div>
        </div>
        
        <!-- Cart Section -->
        <div class="cart-section">
          <div class="cart-header">
            <div>
              <div class="cart-title">Shopping Cart</div>
              <div class="cart-count" id="cartCount">0</div>
            </div>
            <button class="clear-cart-btn" id="clearCart">
              <i class="fas fa-trash"></i> Clear
            </button>
          </div>
          <!-- Order Type Selection -->
          <div class="order-type-selection" style="display:flex; gap:10px; margin-bottom:10px;">
            <label style="flex:1; cursor:pointer;">
              <input type="radio" name="order_type" value="dine-in" checked style="margin-right:6px;">
              <span style="color: var(--accent-gold); font-weight:600;">Dine-In</span>
            </label>
            <label style="flex:1; cursor:pointer;">
              <input type="radio" name="order_type" value="takeout" style="margin-right:6px;">
              <span style="color: var(--accent-gold); font-weight:600;">Takeout (+₱1 for Double/Combo)</span>
            </label>
          </div>
          
          <!-- Customer Info -->
          <div class="customer-info">
            <input type="text" id="customerName" class="customer-input" placeholder="Customer Name" required>
            <input type="text" id="customerId" class="customer-input" placeholder="Customer ID (Auto-generated)" readonly>
          </div>
          
          <!-- Cart Items -->
          <div id="cartItems">
            <div class="empty-cart">
              <i class="fas fa-shopping-cart"></i>
              <div>Your cart is empty</div>
              <div style="font-size: 0.9em; margin-top: 5px;">Add products to get started</div>
            </div>
          </div>
          
          <!-- Cart Total -->
          <div class="cart-total" id="cartTotal" style="display: none;">
            <div>Total Amount</div>
            <div class="total-amount" id="totalAmount">₱ 0.00</div>
          </div>
          
          <!-- Checkout Button -->
          <button class="checkout-btn" id="checkoutBtn" disabled>
            <i class="fas fa-credit-card"></i> Proceed to Checkout
          </button>
            </div>
          </div>
        </div>
      </div>
  
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  
  <script>
    // Shopping Cart Management
    let cart = [];
    let cartTotal = 0;
    let orderType = 'dine-in';
    
    // Initialize customer ID
    document.getElementById('customerId').value = 'CUST-' + new Date().getTime();
    
    // Add to cart functionality
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('add-to-cart-btn')) {
        const prodId = e.target.dataset.prodId;
        const prodName = e.target.dataset.prodName;
        const prodPrice = parseFloat(e.target.dataset.prodPrice);
        const prodImg = e.target.dataset.prodImg;
        const available = parseInt(e.target.dataset.available || '0');
        
        addToCart(prodId, prodName, prodPrice, prodImg, available);
      }
    });
    
    function addToCart(prodId, prodName, prodPrice, prodImg, available) {
      const existingItem = cart.find(item => item.id === prodId);
      
      if (existingItem) {
        if (typeof available === 'number' && !isNaN(available) && existingItem.quantity >= available) {
          alert('Insufficient stock for ' + prodName + '.');
          return;
        }
        existingItem.quantity += 1;
      } else {
        if (typeof available === 'number' && !isNaN(available) && available <= 0) {
          alert('This item is out of stock.');
          return;
        }
        cart.push({
          id: prodId,
          name: prodName,
          price: prodPrice,
          img: prodImg,
          quantity: 1,
          available: available
        });
      }
      
      updateCartDisplay();
    }
    
    function updateCartDisplay() {
      const cartItems = document.getElementById('cartItems');
      const cartCount = document.getElementById('cartCount');
      const cartTotalDiv = document.getElementById('cartTotal');
      const totalAmount = document.getElementById('totalAmount');
      const checkoutBtn = document.getElementById('checkoutBtn');
      
      if (cart.length === 0) {
        cartItems.innerHTML = `
          <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <div>Your cart is empty</div>
            <div style="font-size: 0.9em; margin-top: 5px;">Add products to get started</div>
          </div>
        `;
        cartTotalDiv.style.display = 'none';
        checkoutBtn.disabled = true;
        cartCount.textContent = '0';
        return;
      }
      
      cartCount.textContent = cart.length;
      cartTotalDiv.style.display = 'block';
      
      let cartHTML = '';
      cartTotal = 0;
      let totalAdditionalCharges = 0;
      
      cart.forEach((item, index) => {
        const available = (typeof item.available === 'number') ? item.available : undefined;
        if (typeof available === 'number' && item.quantity > available) {
          item.quantity = available;
        }
        let perItemAdditional = 0;
        if (orderType === 'takeout') {
          const nm = (item.name || '').toLowerCase();
          if (nm.includes('double') || nm.includes('combo') || nm.includes('regular + spicy')) {
            perItemAdditional = 1 * item.quantity; // ₱1 per qualifying item
          }
        }
        const itemTotal = (item.price * item.quantity) + perItemAdditional;
        cartTotal += itemTotal;
        totalAdditionalCharges += perItemAdditional;
        
        cartHTML += `
          <div class="cart-item">
            <div class="cart-item-info">
              <div class="cart-item-name">${item.name}</div>
              <div class="cart-item-price">₱ ${item.price.toFixed(2)} each${perItemAdditional>0?` + ₱${perItemAdditional.toFixed(2)} charge`:''}${(typeof available==='number')?` | Stock: ${available}`:''}</div>
            </div>
            <div class="quantity-controls">
              <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
              <input type="number" class="quantity-input" value="${item.quantity}" 
                     min="1" ${(typeof available==='number')?`max="${available}"`:''} onchange="setQuantity(${index}, this.value)">
              <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
              <button class="remove-item" onclick="removeItem(${index})" title="Remove item">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        `;
      });
      
      cartItems.innerHTML = cartHTML;
      totalAmount.textContent = `₱ ${cartTotal.toFixed(2)}`;
      checkoutBtn.disabled = false;
    }
    
    function updateQuantity(index, change) {
      const available = (typeof cart[index].available === 'number') ? cart[index].available : undefined;
      cart[index].quantity += change;
      if (cart[index].quantity < 1) {
        cart[index].quantity = 1;
      }
      if (typeof available === 'number' && cart[index].quantity > available) {
        cart[index].quantity = available;
      }
      updateCartDisplay();
    }
    
    function setQuantity(index, value) {
      const quantity = parseInt(value);
      const available = (typeof cart[index].available === 'number') ? cart[index].available : undefined;
      if (quantity >= 1) {
        cart[index].quantity = quantity;
        if (typeof available === 'number' && cart[index].quantity > available) {
          cart[index].quantity = available;
        }
        updateCartDisplay();
      }
    }
    
    function removeItem(index) {
      cart.splice(index, 1);
      updateCartDisplay();
    }
    
    // Clear cart
    document.getElementById('clearCart').addEventListener('click', function() {
      if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCartDisplay();
      }
    });
    
    // Checkout functionality
    document.getElementById('checkoutBtn').addEventListener('click', function() {
      const customerName = document.getElementById('customerName').value.trim();
      
      if (!customerName) {
        alert('Please enter customer name');
        return;
      }
      
      if (cart.length === 0) {
        alert('Cart is empty');
        return;
      }
      
      // Debug: Log cart contents
      console.log('Cart contents before checkout:', cart);
      
      // Create order data
      const orderData = {
        customer_name: customerName,
        customer_id: document.getElementById('customerId').value,
        order_type: orderType,
        items: cart.map(it => ({ id: it.id, name: it.name, price: it.price, quantity: it.quantity }))
      };
      
      console.log('Order data being sent:', orderData);
      
      // Send to checkout
      proceedToCheckout(orderData);
    });
    
    function proceedToCheckout(orderData) {
      // Directly process order on server and redirect to payments
      fetch('process_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
      })
      .then(res => res.json())
      .then(data => {
        if (data && data.success) {
          window.location.href = data.redirect || 'payments.php';
        } else {
          alert((data && data.error) ? data.error : 'Failed to process order.');
        }
      })
      .catch(err => {
        console.error('Checkout error:', err);
        alert('Network error while processing order.');
      });
    }
    
    // Search functionality
    // Order type radios
    document.querySelectorAll('input[name="order_type"]').forEach(r => {
      r.addEventListener('change', function(){
        orderType = this.value;
        updateCartDisplay();
      });
    });
    document.getElementById('productSearch').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const productCards = document.querySelectorAll('.product-card');
      
      productCards.forEach(card => {
        const productName = card.dataset.name;
        if (productName.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
    
    // Category filter
    document.querySelectorAll('.category-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
          if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl + Enter to checkout
      if (e.ctrlKey && e.key === 'Enter') {
        document.getElementById('checkoutBtn').click();
      }
      
      // Escape to clear search
      if (e.key === 'Escape') {
        document.getElementById('productSearch').value = '';
        document.getElementById('productSearch').dispatchEvent(new Event('input'));
      }
    });
  </script>
</body>
</html>