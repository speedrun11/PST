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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Inventory Management System for Pastil sa Tabi">
    <meta name="author" content="Your Company">
    <title>PST - Inventory Management System</title>
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../admin/assets/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../admin/assets/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../admin/assets/img/icons/favicon-16x16.png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href="assets/vendor/nucleo/css/nucleo.css" rel="stylesheet">
    <link href="assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="assets/css/argon.css?v=1.0.0" rel="stylesheet">
    <!-- Custom CSS -->
    <link type="text/css" href="assets/css/custom.css" rel="stylesheet">
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
            font-family: 'Open Sans', sans-serif;
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
        
        .badge-warning {
            background-color: #c0a062;
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
        
        .inventory-alert {
            background: rgba(158, 43, 43, 0.2);
            border-left: 3px solid var(--accent-red);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
        }
        
        .stock-level {
            height: 10px;
            border-radius: 5px;
            background: rgba(192, 160, 98, 0.2);
            margin-top: 5px;
        }
        
        .stock-level .progress-bar {
            background: linear-gradient(90deg, var(--accent-green), var(--accent-gold));
        }
        
        .low-stock {
            color: #ff6b6b;
            font-weight: 600;
        }
        
        .critical-stock {
            color: var(--accent-red);
            font-weight: 700;
        }
        
        .forecast-card {
            border-left: 4px solid var(--accent-gold);
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
        
        @media (max-width: 768px) {
            .card {
                backdrop-filter: blur(4px);
            }
            
            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>
    
    <!-- Header -->
    <div style="background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Card stats -->
          <div class="row">
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Total Products</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span>
                      <?php 
                        // Compute products change since last month
                        $current_month = (int)date('m');
                        $current_year = (int)date('Y');
                        $prev_month = $current_month - 1; $prev_year = $current_year;
                        if ($prev_month < 1) { $prev_month = 12; $prev_year -= 1; }
                        $q_now = $mysqli->prepare("SELECT COUNT(*) FROM rpos_products WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
                        $q_prev = $mysqli->prepare("SELECT COUNT(*) FROM rpos_products WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
                        $cnt_now = 0; $cnt_prev = 0; $pct = 0;
                        if ($q_now && $q_prev) {
                          $q_now->bind_param('ii', $current_year, $current_month); $q_now->execute(); $q_now->bind_result($cnt_now); $q_now->fetch(); $q_now->close();
                          $q_prev->bind_param('ii', $prev_year, $prev_month); $q_prev->execute(); $q_prev->bind_result($cnt_prev); $q_prev->fetch(); $q_prev->close();
                          if ($cnt_prev > 0) { $pct = round((($cnt_now - $cnt_prev) / $cnt_prev) * 100, 1); }
                        }
                        if ($pct !== 0) {
                      ?>
                      <p class="mt-3 mb-0 text-muted text-sm">
                        <span class="<?php echo ($pct >= 0) ? 'text-success' : 'text-danger'; ?> mr-2">
                          <i class="fa <?php echo ($pct >= 0) ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i> <?php echo ($pct >= 0 ? $pct : -$pct); ?>%
                        </span>
                        <span class="text-nowrap">Since last month</span>
                      </p>
                      <?php } ?>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                        <i class="fas fa-boxes"></i>
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
                      <h5 class="card-title text-uppercase text-muted mb-0">Low Stock Items</h5>
                      <span class="h2 font-weight-bold mb-0">
                        <?php 
                        $query = "SELECT COUNT(*) FROM rpos_products WHERE prod_quantity <= prod_threshold";
                        $stmt = $mysqli->prepare($query);
                        $stmt->execute();
                        $stmt->bind_result($low_stock_products);
                        $stmt->fetch();
                        $stmt->close();
                        
                        $query_ingredients = "SELECT COUNT(*) FROM rpos_ingredients WHERE ingredient_quantity <= ingredient_threshold";
                        $stmt_ingredients = $mysqli->prepare($query_ingredients);
                        $stmt_ingredients->execute();
                        $stmt_ingredients->bind_result($low_stock_ingredients);
                        $stmt_ingredients->fetch();
                        $stmt_ingredients->close();
                        
                        echo $low_stock_products + $low_stock_ingredients;
                        ?>
                      </span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                        <i class="fas fa-exclamation-triangle"></i>
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
                      <h5 class="card-title text-uppercase text-muted mb-0">Critical Stock</h5>
                      <span class="h2 font-weight-bold mb-0">
                        <?php 
                        $query = "SELECT COUNT(*) FROM rpos_products WHERE prod_quantity <= (prod_threshold * 0.5)";
                        $stmt = $mysqli->prepare($query);
                        $stmt->execute();
                        $stmt->bind_result($critical_stock_products);
                        $stmt->fetch();
                        $stmt->close();
                        
                        $query_ingredients = "SELECT COUNT(*) FROM rpos_ingredients WHERE ingredient_quantity <= (ingredient_threshold * 0.5)";
                        $stmt_ingredients = $mysqli->prepare($query_ingredients);
                        $stmt_ingredients->execute();
                        $stmt_ingredients->bind_result($critical_stock_ingredients);
                        $stmt_ingredients->fetch();
                        $stmt_ingredients->close();
                        
                        echo $critical_stock_products + $critical_stock_ingredients;
                        ?>
                      </span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                        <i class="fas fa-skull-crossbones"></i>
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
                              <h5 class="card-title text-uppercase text-muted mb-0">Active Suppliers</h5>
                              <span class="h2 font-weight-bold mb-0">
                                  <?php 
                                  $query = "SELECT COUNT(*) FROM rpos_suppliers";
                                  $stmt = $mysqli->prepare($query);
                                  $stmt->execute();
                                  $stmt->bind_result($supplier_count);
                                  $stmt->fetch();
                                  $stmt->close();
                                  echo $supplier_count;
                                  ?>
                              </span>
                          </div>
                          <div class="col-auto">
                              <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                  <i class="fas fa-truck"></i>
                              </div>
                          </div>
                      </div>
                      <p class="mt-3 mb-0 text-muted text-sm">
                          <a href="suppliers.php" class="text-gold">View all suppliers</a>
                      </p>
                  </div>
              </div>
          </div>

            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Total Ingredients</h5>
                      <span class="h2 font-weight-bold mb-0">
                        <?php 
                        $query = "SELECT COUNT(*) FROM rpos_ingredients";
                        $stmt = $mysqli->prepare($query);
                        $stmt->execute();
                        $stmt->bind_result($ingredients_count);
                        $stmt->fetch();
                        $stmt->close();
                        echo $ingredients_count;
                        ?>
                      </span>
                      <?php 
                        // Compute ingredients change since last month
                        $q_now = $mysqli->prepare("SELECT COUNT(*) FROM rpos_ingredients WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
                        $q_prev = $mysqli->prepare("SELECT COUNT(*) FROM rpos_ingredients WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
                        $cnt_now = 0; $cnt_prev = 0; $pct = 0;
                        if ($q_now && $q_prev) {
                          $q_now->bind_param('ii', $current_year, $current_month); $q_now->execute(); $q_now->bind_result($cnt_now); $q_now->fetch(); $q_now->close();
                          $q_prev->bind_param('ii', $prev_year, $prev_month); $q_prev->execute(); $q_prev->bind_result($cnt_prev); $q_prev->fetch(); $q_prev->close();
                          if ($cnt_prev > 0) { $pct = round((($cnt_now - $cnt_prev) / $cnt_prev) * 100, 1); }
                        }
                        if ($pct !== 0) {
                      ?>
                      <p class="mt-3 mb-0 text-muted text-sm">
                        <span class="<?php echo ($pct >= 0) ? 'text-success' : 'text-danger'; ?> mr-2">
                          <i class="fa <?php echo ($pct >= 0) ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i> <?php echo ($pct >= 0 ? $pct : -$pct); ?>%
                        </span>
                        <span class="text-nowrap">Since last month</span>
                      </p>
                      <?php } ?>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                        <i class="fas fa-seedling"></i>
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
                      <h5 class="card-title text-uppercase text-muted mb-0">Inventory Value</h5>
                      <span class="h2 font-weight-bold mb-0">
                        ₱<?php 
                        $query = "SELECT SUM(prod_price * prod_quantity) FROM rpos_products";
                        $stmt = $mysqli->prepare($query);
                        $stmt->execute();
                        $stmt->bind_result($inventory_value);
                        $stmt->fetch();
                        $stmt->close();
                        echo number_format($inventory_value, 2);
                        ?>
                      </span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                        <i class="fas fa-coins"></i>
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
      <!-- Low stock alerts -->
      <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Stock Alerts</h3>
                </div>
                <div class="col text-right">
                  <a href="products.php" class="btn btn-sm btn-primary">Manage Inventory</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Item</th>
                    <th scope="col">Category/Unit</th>
                    <th scope="col">Current Stock</th>
                    <th scope="col">Threshold</th>
                    <th scope="col">Status</th>
                    <th scope="col">Last Restocked</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Get low stock products
                  $ret = "SELECT * FROM rpos_products WHERE prod_quantity <= prod_threshold ORDER BY (prod_quantity/prod_threshold) ASC LIMIT 3";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($product = $res->fetch_object()) {
                    $status_class = '';
                    $status_text = '';
                    $percentage = ($product->prod_threshold > 0) ? (($product->prod_quantity / $product->prod_threshold) * 100) : 100;
                    
                    if ($percentage <= 25) {
                      $status_class = 'critical-stock';
                      $status_text = 'Critical';
                    } else {
                      $status_class = 'low-stock';
                      $status_text = 'Low';
                    }
                  ?>
                    <tr>
                      <th scope="row"><?php echo htmlspecialchars($product->prod_name) . ' (Product)'; ?></th>
                      <td><?php echo htmlspecialchars($product->prod_category); ?></td>
                      <td><?php echo $product->prod_quantity; ?></td>
                      <td><?php echo (int)$product->prod_threshold; ?></td>
                      <td class="<?php echo $status_class; ?>">
                        <?php if ($status_text === 'Critical'): ?>
                          <i class="fas fa-exclamation-circle mr-1"></i>
                        <?php else: ?>
                          <i class="fas fa-exclamation-triangle mr-1"></i>
                        <?php endif; ?>
                        <?php echo $status_text; ?>
                      </td>
                      <td><?php echo $product->last_restocked ? date('M d, Y', strtotime($product->last_restocked)) : 'Never'; ?></td>
                    </tr>
                  <?php } 
                  
                  // Get low stock ingredients
                  $ret_ingredients = "SELECT * FROM rpos_ingredients WHERE ingredient_quantity <= ingredient_threshold ORDER BY (ingredient_quantity/ingredient_threshold) ASC LIMIT 2";
                  $stmt_ingredients = $mysqli->prepare($ret_ingredients);
                  $stmt_ingredients->execute();
                  $res_ingredients = $stmt_ingredients->get_result();
                  while ($ingredient = $res_ingredients->fetch_object()) {
                    $status_class = '';
                    $status_text = '';
                    $percentage = ($ingredient->ingredient_quantity / $ingredient->ingredient_threshold) * 100;
                    
                    if ($percentage <= 25) {
                      $status_class = 'critical-stock';
                      $status_text = 'Critical';
                    } else {
                      $status_class = 'low-stock';
                      $status_text = 'Low';
                    }
                  ?>
                    <tr>
                      <th scope="row"><?php echo $ingredient->ingredient_name . ' (Ingredient)'; ?></th>
                      <td><?php echo $ingredient->ingredient_unit; ?></td>
                      <td><?php echo $ingredient->ingredient_quantity; ?></td>
                      <td><?php echo $ingredient->ingredient_threshold; ?></td>
                      <td class="<?php echo $status_class; ?>">
                        <?php if ($status_text === 'Critical'): ?>
                          <i class="fas fa-exclamation-circle mr-1"></i>
                        <?php else: ?>
                          <i class="fas fa-exclamation-triangle mr-1"></i>
                        <?php endif; ?>
                        <?php echo $status_text; ?>
                      </td>
                      <td><?php echo $ingredient->last_restocked ? date('M d, Y', strtotime($ingredient->last_restocked)) : 'Never'; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Inventory Forecast -->
      <div class="row mt-5">
        <div class="col-xl-6">
          <div class="card shadow forecast-card">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Inventory Forecast</h3>
                </div>
                <div class="col text-right">
                  <a href="advanced_forecast_reports.php" class="btn btn-sm btn-primary">Advanced Forecasting</a>
                  <a href="forecast_reports.php" class="btn btn-sm btn-secondary ml-2">Basic Reports</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="inventory-alert">
                <i class="fas fa-info-circle"></i> Based on sales trends, you may need to restock these items soon:
              </div>
              <ul class="list-group list-group-flush">
                <?php
                // Fetch all products
                $ret = "SELECT * FROM rpos_products ORDER BY prod_name ASC";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                $forecast_items = array();
                while ($row = $res->fetch_assoc()) {
                  $current_stock = (int)$row['prod_quantity'];
                  $daily_usage = max(1, (int)($row['prod_threshold'] / 7)); // fallback: threshold/7 days
                  $forecast_days = $daily_usage > 0 ? floor($current_stock / $daily_usage) : 0;
                  $urgency = 'low';
                  if ($forecast_days <= 3) {
                    $urgency = 'high';
                  } elseif ($forecast_days <= 7) {
                    $urgency = 'medium';
                  }
                  $forecast_items[] = array(
                    'name' => $row['prod_name'] . ' (Product)',
                    'days_left' => $forecast_days,
                    'urgency' => $urgency
                  );
                }
                
                // Fetch all ingredients
                $ret_ingredients = "SELECT * FROM rpos_ingredients ORDER BY ingredient_name ASC";
                $stmt_ingredients = $mysqli->prepare($ret_ingredients);
                $stmt_ingredients->execute();
                $res_ingredients = $stmt_ingredients->get_result();
                while ($row_ingredients = $res_ingredients->fetch_assoc()) {
                  $current_stock_ingredients = (int)$row_ingredients['ingredient_quantity'];
                  $daily_usage_ingredients = max(1, (int)($row_ingredients['ingredient_threshold'] / 7)); // fallback: threshold/7 days
                  $forecast_days_ingredients = $daily_usage_ingredients > 0 ? floor($current_stock_ingredients / $daily_usage_ingredients) : 0;
                  $urgency_ingredients = 'low';
                  if ($forecast_days_ingredients <= 3) {
                    $urgency_ingredients = 'high';
                  } elseif ($forecast_days_ingredients <= 7) {
                    $urgency_ingredients = 'medium';
                  }
                  $forecast_items[] = array(
                    'name' => $row_ingredients['ingredient_name'] . ' (Ingredient)',
                    'days_left' => $forecast_days_ingredients,
                    'urgency' => $urgency_ingredients
                  );
                }
                
                // Sort by days_left ascending
                usort($forecast_items, function($a, $b) { return $a['days_left'] - $b['days_left']; });
                // Show top 5 urgent items
                foreach (array_slice($forecast_items, 0, 5) as $item) {
                  $icon = '';
                  $text_class = '';
                  if ($item['urgency'] == 'high') {
                    $icon = 'fas fa-exclamation-circle';
                    $text_class = 'critical-stock';
                  } elseif ($item['urgency'] == 'medium') {
                    $icon = 'fas fa-exclamation-triangle';
                    $text_class = 'low-stock';
                  } else {
                    $icon = 'fas fa-info-circle';
                    $text_class = 'text-success';
                  }
                  echo '<li class="list-group-item bg-transparent border-light">';
                  echo '<i class="' . $icon . ' ' . $text_class . ' mr-2"></i>';
                  echo '<strong>' . htmlspecialchars($item['name']) . '</strong> - Estimated stock lasts ' . $item['days_left'] . ' more days';
                  echo '</li>';
                }
                ?>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- Recent Inventory Activities -->
        <div class="col-xl-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Inventory Activities</h3>
                </div>
                <div class="col text-right">
                  <a href="inventory_logs.php" class="btn btn-sm btn-primary">View All</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Activity</th>
                    <th scope="col">Product</th>
                    <th scope="col">Qty Changed</th>
                    <th scope="col">Staff</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT l.*, p.prod_name, s.staff_name 
                          FROM rpos_inventory_logs l
                          JOIN rpos_products p ON l.product_id = p.prod_id
                          JOIN rpos_staff s ON l.staff_id = s.staff_id
                          ORDER BY l.activity_date DESC LIMIT 5";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($log = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo $log->activity_type; ?></td>
                      <td><?php echo $log->prod_name; ?></td>
                      <td><?php echo $log->quantity_change; ?></td>
                      <td><?php echo $log->staff_name; ?></td>
                      <td><?php echo date('M d, H:i', strtotime($log->activity_date)); ?></td>
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
  <?php require_once('partials/_scripts.php'); ?>
  
  <script>
    // Initialize charts for inventory analytics
    document.addEventListener('DOMContentLoaded', function() {
      // In a complete implementation, we would initialize charts here
      // using Chart.js or similar library to visualize inventory data
    });
  </script>
</body>
</html>