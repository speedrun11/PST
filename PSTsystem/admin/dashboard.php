<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
require_once('partials/_analytics.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Admin Dashboard</title>
    
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
            background: url(assets/img/theme/pastil.jpg) no-repeat center center;
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
        
        .card-title {
            color: var(--accent-gold) !important;
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
        }
        
        .card-stats .card-body {
            padding: 1.5rem;
        }
        
        .icon-shape {
            background: linear-gradient(135deg, rgba(192, 160, 98, 0.8), rgba(192, 160, 98, 0.6)) !important;
        }
        
        .h2 {
            color: var(--text-light) !important;
            font-family: 'Fredoka', sans-serif;
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
        
        .badge-success {
            background-color: var(--accent-green);
        }
        
        .badge-danger {
            background-color: var(--accent-red);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.8), rgba(158, 43, 43, 0.6));
            border: 1px solid rgba(158, 43, 43, 0.4);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: rgba(158, 43, 43, 0.6);
        }
        
        .text-success {
            color: var(--accent-gold) !important;
        }
        
        .nav-link {
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .nav-link:hover {
            color: var(--accent-gold);
        }
        
        .sidebar {
            background: rgba(26, 26, 46, 0.9) !important;
            border-right: 1px solid rgba(192, 160, 98, 0.2);
        }
        
        .footer {
            background: rgba(26, 26, 46, 0.9) !important;
            border-top: 1px solid rgba(192, 160, 98, 0.2);
        }
        
        @media (max-width: 768px) {
            .card {
                backdrop-filter: blur(4px);
            }
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
        
        .table tbody tr {
            transition: all var(--transition-speed) ease;
        }
        
        .table tbody tr:hover {
            background: rgba(192, 160, 98, 0.1) !important;
            transform: translateX(5px);
        }
        
        .table-responsive {
            border-radius: 0 0 10px 10px;
            overflow: hidden;
        }
        
        .card-header {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            letter-spacing: 0.5px;
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
        }
        
        .spacing-between-tables {
            margin-top: 6.5rem;
        }
        
        .text-gold {
            color: var(--accent-gold) !important;
        }

        .sidebar .nav-link:hover {
            color: var(--accent-gold) !important;
            background: rgba(192, 160, 98, 0.1);
        }

        .sidebar .dropdown-menu {
            background-color: rgba(26, 26, 46, 0.95);
            border: 1px solid rgba(192, 160, 98, 0.2);
        }

        .sidebar .dropdown-item:hover {
            background-color: rgba(192, 160, 98, 0.1);
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: rgba(192, 160, 98, 0.3);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--primary-dark);
        }
        
        .timeline-content {
            background: rgba(26, 26, 46, 0.5);
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--accent-gold);
        }
        
        .timeline-title {
            color: var(--accent-gold);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .timeline-text {
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        
        .timeline-date {
            color: rgba(248, 245, 242, 0.6);
            font-size: 0.75rem;
        }
        
        /* Chart container */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Animation for cards */
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Hover effects for stats cards */
        .card-stats:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        /* Gradient backgrounds for different card types */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .bg-gradient-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
    <div class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Card stats -->
          <div class="row">
            <div class="col-xl-3 col-lg-6">
              <a href="products.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Products</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span>
                        <p class="mt-3 mb-0 text-sm">
                          <span class="text-warning mr-2">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $low_stock; ?> low stock
                          </span>
                        </p>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape text-white rounded-circle shadow">
                          <i class="fas fa-utensils"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-3 col-lg-6">
              <a href="orders.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Orders</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $orders; ?></span>
                        <p class="mt-3 mb-0 text-sm">
                          <span class="text-danger mr-2">
                            <i class="fas fa-clock"></i> <?php echo $pending_orders; ?> pending
                          </span>
                        </p>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape text-white rounded-circle shadow">
                          <i class="fas fa-shopping-cart"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-3 col-lg-6">
              <a href="payments.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Today's Sales</h5>
                        <span class="h2 font-weight-bold mb-0">₱<?php echo number_format($today_sales, 2); ?></span>
                        <p class="mt-3 mb-0 text-sm">
                          <span class="<?php echo $today_growth >= 0 ? 'text-success' : 'text-danger'; ?> mr-2">
                            <i class="fas fa-arrow-<?php echo $today_growth >= 0 ? 'up' : 'down'; ?>"></i> <?php echo abs(round($today_growth, 1)); ?>%
                          </span>
                          <span class="text-nowrap">vs yesterday</span>
                        </p>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape text-white rounded-circle shadow">
                          <i class="fas fa-peso-sign"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-3 col-lg-6">
              <a href="customers.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Customers</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $customers; ?></span>
                        <p class="mt-3 mb-0 text-sm">
                          <span class="text-success mr-2">
                            <i class="fas fa-user-plus"></i> <?php echo $new_customers_month; ?> new this month
                          </span>
                        </p>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape text-white rounded-circle shadow">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
          
          <!-- Additional KPI Cards -->
          <div class="row mt-4">
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase mb-0">Monthly Sales</h5>
                      <span class="h2 font-weight-bold mb-0">₱<?php echo number_format($month_sales, 2); ?></span>
                      <p class="mt-3 mb-0 text-sm">
                        <span class="<?php echo $month_growth >= 0 ? 'text-success' : 'text-danger'; ?> mr-2">
                          <i class="fas fa-arrow-<?php echo $month_growth >= 0 ? 'up' : 'down'; ?>"></i> <?php echo abs(round($month_growth, 1)); ?>%
                        </span>
                        <span class="text-nowrap">vs last month</span>
                      </p>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                        <i class="fas fa-chart-line"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase mb-0">Avg Order Value</h5>
                      <span class="h2 font-weight-bold mb-0">₱<?php echo number_format($avg_order_value, 2); ?></span>
                      <p class="mt-3 mb-0 text-sm">
                        <span class="text-info mr-2">
                          <i class="fas fa-calculator"></i> Last 30 days
                        </span>
                      </p>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                        <i class="fas fa-receipt"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase mb-0">Total Revenue</h5>
                      <span class="h2 font-weight-bold mb-0">₱<?php echo number_format($sales, 2); ?></span>
                      <p class="mt-3 mb-0 text-sm">
                        <span class="text-success mr-2">
                          <i class="fas fa-trophy"></i> All time
                        </span>
                      </p>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                        <i class="fas fa-coins"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase mb-0">Quick Actions</h5>
                      <div class="mt-2">
                        <a href="add_product.php" class="btn btn-sm btn-primary mr-1 mb-1">
                          <i class="fas fa-plus"></i> Add Product
                        </a>
                        <a href="orders.php" class="btn btn-sm btn-info mr-1 mb-1">
                          <i class="fas fa-eye"></i> View Orders
                        </a>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow">
                        <i class="fas fa-bolt"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Charts Row -->
      <div class="row mt-5">
        <div class="col-xl-8">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Sales Trend (Last 7 Days)</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <canvas id="salesChart" height="100"></canvas>
            </div>
          </div>
        </div>
        <div class="col-xl-4">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Order Status</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <canvas id="orderStatusChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Top Products and Recent Activity Row -->
      <div class="row mt-4">
        <div class="col-xl-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Top Selling Products</h3>
                  <p class="text-muted">Last 30 days</p>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center table-flush">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" class="text-gold">Product</th>
                      <th scope="col" class="text-gold">Quantity Sold</th>
                      <th scope="col" class="text-gold">Revenue</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($top_products as $product): ?>
                    <tr>
                      <th scope="row"><?php echo htmlspecialchars($product['prod_name']); ?></th>
                      <td><?php echo $product['total_qty']; ?></td>
                      <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Activity</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="timeline">
                <?php foreach ($recent_activity as $activity): ?>
                <div class="timeline-item">
                  <div class="timeline-marker bg-<?php echo $activity['type'] == 'order' ? 'primary' : 'success'; ?>"></div>
                  <div class="timeline-content">
                    <h6 class="timeline-title"><?php echo htmlspecialchars($activity['description']); ?></h6>
                    <p class="timeline-text">
                      <?php if ($activity['type'] == 'order'): ?>
                        Customer: <?php echo htmlspecialchars($activity['name']); ?><br>
                        Order: <?php echo htmlspecialchars($activity['code']); ?>
                      <?php else: ?>
                        Payment: <?php echo htmlspecialchars($activity['code']); ?>
                      <?php endif; ?>
                    </p>
                    <span class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?></span>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
          <!-- Recent Orders Table -->
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Orders</h3>
                </div>
                <div class="col text-right">
                  <a href="orders_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th class="text-gold" scope="col">Code</th>
                    <th scope="col">Customer</th>
                    <th class="text-gold" scope="col">Products</th>
                    <th scope="col">Items</th>
                    <th class="text-gold" scope="col">Total</th>
                    <th scope="col">Status</th>
                    <th class="text-gold" scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT order_code, customer_name, order_status, created_at, 
                          COUNT(*) as item_count, 
                          SUM(CAST(prod_price AS DECIMAL(10,2)) * CAST(prod_qty AS UNSIGNED)) as total_amount,
                          GROUP_CONCAT(prod_name SEPARATOR ', ') as products
                          FROM rpos_orders 
                          GROUP BY order_code, customer_name, order_status, created_at
                          ORDER BY created_at DESC 
                          LIMIT 7";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($order = $res->fetch_object()) {
                  ?>
                    <tr>
                      <th class="text-gold" scope="row"><?php echo $order->order_code; ?></th>
                      <td><?php echo $order->customer_name; ?></td>
                      <td class="text-gold"><?php echo htmlspecialchars($order->products); ?></td>
                      <td><?php echo $order->item_count; ?> items</td>
                      <td class="text-gold">₱<?php echo number_format($order->total_amount, 2); ?></td>
                      <td><?php if ($order->order_status == '') {
                            echo "<span class='badge badge-danger'>Not Paid</span>";
                          } else {
                            echo "<span class='badge badge-success'>$order->order_status</span>";
                          } ?></td>
                      <td class="text-gold"><?php echo date('d/M/Y g:i', strtotime($order->created_at)); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Payments</h3>
                </div>
                <div class="col text-right">
                  <a href="payments_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th class="text-gold" scope="col">Code</th>
                    <th scope="col">Amount</th>
                    <th class="text-gold" scope="col">Order Code</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM rpos_payments ORDER BY `rpos_payments`.`created_at` DESC LIMIT 7 ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($payment = $res->fetch_object()) {
                  ?>
                    <tr>
                      <th class="text-gold" scope="row"><?php echo $payment->pay_code; ?></th>
                      <td>₱<?php echo $payment->pay_amt; ?></td>
                      <td class="text-gold"><?php echo $payment->order_code; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?php echo json_encode($sales_trend); ?>;
    
    new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: salesData.map(item => {
          const date = new Date(item.sale_date);
          return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
          label: 'Daily Sales (₱)',
          data: salesData.map(item => parseFloat(item.daily_sales) || 0),
          borderColor: 'rgba(192, 160, 98, 1)',
          backgroundColor: 'rgba(192, 160, 98, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: 'rgba(192, 160, 98, 1)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 6
        }, {
          label: 'Daily Orders',
          data: salesData.map(item => parseInt(item.daily_orders) || 0),
          borderColor: 'rgba(74, 107, 87, 1)',
          backgroundColor: 'rgba(74, 107, 87, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.4,
          yAxisID: 'y1'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: 'white',
              font: {
                family: 'Poppins'
              }
            }
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            ticks: {
              color: 'white',
              callback: function(value) {
                return '₱' + value.toLocaleString();
              }
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            ticks: {
              color: 'white'
            },
            grid: {
              drawOnChartArea: false,
            },
          },
          x: {
            ticks: {
              color: 'white'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          }
        }
      }
    });
    
    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const orderStatusData = <?php echo json_encode($order_status_dist); ?>;
    
    new Chart(orderStatusCtx, {
      type: 'doughnut',
      data: {
        labels: orderStatusData.map(item => item.status),
        datasets: [{
          data: orderStatusData.map(item => item.count),
          backgroundColor: [
            'rgba(192, 160, 98, 0.8)',
            'rgba(74, 107, 87, 0.8)',
            'rgba(158, 43, 43, 0.8)',
            'rgba(58, 86, 115, 0.8)'
          ],
          borderColor: [
            'rgba(192, 160, 98, 1)',
            'rgba(74, 107, 87, 1)',
            'rgba(158, 43, 43, 1)',
            'rgba(58, 86, 115, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: 'white',
              font: {
                family: 'Poppins'
              },
              padding: 20
            }
          }
        }
      }
    });
    
    // Auto-refresh data every 5 minutes
    setInterval(function() {
      location.reload();
    }, 300000);
    
    // Add loading animation
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.card');
      cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
      });
    });
  </script>
</body>
</html>