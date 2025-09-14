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
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(192, 160, 98, 0.4);
        }
        
        .order-group {
            background: rgba(26, 26, 46, 0.9);
            border: 2px solid var(--accent-gold);
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .order-group-header {
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-red));
            color: var(--text-dark);
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-group-body {
            padding: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
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
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-details h6 {
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
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }
        
        .total-label {
            font-size: 1.1em;
            color: var(--accent-gold);
            margin-bottom: 5px;
        }
        
        .total-amount {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--accent-gold);
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.4);
        }
        
        .status-paid {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.4);
        }
        
        .status-completed {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
            border: 1px solid rgba(0, 123, 255, 0.4);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-green), var(--accent-blue));
            color: var(--text-light);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 107, 87, 0.4);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-red));
            color: var(--text-dark);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(192, 160, 98, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--accent-green), var(--accent-blue));
            color: var(--text-light);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 107, 87, 0.4);
        }
        
        .empty-orders {
            text-align: center;
            color: rgba(192, 160, 98, 0.6);
            padding: 40px 20px;
        }
        
        .empty-orders i {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .stats-card {
            background: rgba(26, 26, 46, 0.9);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2em;
            font-weight: 700;
            color: var(--accent-gold);
        }
        
        .stats-label {
            color: var(--text-light);
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .item-info {
                width: 100%;
            }
            
            .btn-group {
                flex-direction: column;
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
      
      <!-- Statistics -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stats-card">
            <div class="stats-number" id="totalOrders">0</div>
            <div class="stats-label">Total Orders</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <div class="stats-number" id="pendingOrders">0</div>
            <div class="stats-label">Pending Orders</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <div class="stats-number" id="paidOrders">0</div>
            <div class="stats-label">Paid Orders</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <div class="stats-number" id="totalRevenue">₱0</div>
            <div class="stats-label">Total Revenue</div>
          </div>
        </div>
      </div>
      
      <!-- Orders List -->
      <div class="card">
        <div class="card-header">
          <h3 class="mb-0">
            <i class="fas fa-shopping-cart"></i> Order Management
          </h3>
        </div>
        <div class="card-body">
          <div id="ordersList">
            <!-- Orders will be populated by JavaScript -->
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
    // Load orders data
    function loadOrders() {
      fetch('get_orders_data.php')
        .then(response => response.json())
        .then(data => {
          displayOrders(data.orders);
          updateStats(data.stats);
        })
        .catch(error => {
          console.error('Error loading orders:', error);
          document.getElementById('ordersList').innerHTML = `
            <div class="empty-orders">
              <i class="fas fa-exclamation-triangle"></i>
              <div>Error loading orders</div>
            </div>
          `;
        });
    }
    
    function displayOrders(orders) {
      const ordersList = document.getElementById('ordersList');
      
      if (!orders || orders.length === 0) {
        ordersList.innerHTML = `
          <div class="empty-orders">
            <i class="fas fa-shopping-cart"></i>
            <div>No orders found</div>
            <div style="font-size: 0.9em; margin-top: 5px;">Orders will appear here when customers place them</div>
          </div>
        `;
        return;
      }
      
      let ordersHTML = '';
      
      orders.forEach(orderGroup => {
        let orderItemsHTML = '';
        let groupTotal = 0;
        
        orderGroup.items.forEach(item => {
          const itemTotal = parseFloat(item.prod_price) * parseInt(item.prod_qty);
          groupTotal += itemTotal;
          
          orderItemsHTML += `
            <div class="order-item">
              <div class="item-info">
                <img src="../admin/assets/img/products/${item.prod_img || 'default.jpg'}" alt="${item.prod_name}" class="item-image">
                <div class="item-details">
                  <h6>${item.prod_name}</h6>
                  <p>₱${parseFloat(item.prod_price).toFixed(2)} each</p>
                </div>
              </div>
              <div class="item-quantity">${item.prod_qty}</div>
              <div class="item-price">₱${itemTotal.toFixed(2)}</div>
            </div>
          `;
        });
        
        const statusClass = orderGroup.status === 'Paid' ? 'status-paid' : 
                           orderGroup.status === 'Completed' ? 'status-completed' : 'status-pending';
        
        ordersHTML += `
          <div class="order-group">
            <div class="order-group-header">
              <div>
                <i class="fas fa-user"></i> ${orderGroup.customer_name}
                <span style="margin-left: 15px; font-size: 0.9em;">Order: ${orderGroup.order_code}</span>
              </div>
              <div>
                <span class="order-status ${statusClass}">${orderGroup.status || 'Pending'}</span>
              </div>
            </div>
            <div class="order-group-body">
              ${orderItemsHTML}
              <div class="order-total">
                <div class="total-label">Order Total</div>
                <div class="total-amount">₱${groupTotal.toFixed(2)}</div>
              </div>
              <div class="btn-group">
                <button class="btn btn-primary" onclick="viewOrderDetails('${orderGroup.order_group_id}')">
                  <i class="fas fa-eye"></i> View Details
                </button>
                ${orderGroup.status !== 'Paid' ? `
                  <button class="btn btn-warning" onclick="processPayment('${orderGroup.order_group_id}')">
                    <i class="fas fa-credit-card"></i> Process Payment
                  </button>
                ` : ''}
                <button class="btn btn-success" onclick="markCompleted('${orderGroup.order_group_id}')">
                  <i class="fas fa-check"></i> Mark Complete
                </button>
              </div>
            </div>
          </div>
        `;
      });
      
      ordersList.innerHTML = ordersHTML;
    }
    
    function updateStats(stats) {
      document.getElementById('totalOrders').textContent = stats.totalOrders || 0;
      document.getElementById('pendingOrders').textContent = stats.pendingOrders || 0;
      document.getElementById('paidOrders').textContent = stats.paidOrders || 0;
      document.getElementById('totalRevenue').textContent = '₱' + (stats.totalRevenue || 0).toFixed(2);
    }
    
    function viewOrderDetails(orderGroupId) {
      // Implement order details view
      alert('Order details for: ' + orderGroupId);
    }
    
    function processPayment(orderGroupId) {
      if (confirm('Process payment for this order?')) {
        // Implement payment processing
        alert('Payment processed for: ' + orderGroupId);
        loadOrders(); // Reload orders
      }
    }
    
    function markCompleted(orderGroupId) {
      if (confirm('Mark this order as completed?')) {
        // Implement order completion
        alert('Order completed: ' + orderGroupId);
        loadOrders(); // Reload orders
      }
    }
    
    // Load orders on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadOrders();
      
      // Auto-refresh every 30 seconds
      setInterval(loadOrders, 30000);
    });
  </script>
</body>
</html>
