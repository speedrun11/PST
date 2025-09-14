<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'delete':
            $id = intval($_POST['product_id']);
            $delete_query = "DELETE FROM rpos_products WHERE prod_id = ?";
            $stmt = $mysqli->prepare($delete_query);
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
            }
            $stmt->close();
            exit;
            
        case 'toggle_status':
            $id = intval($_POST['product_id']);
            $status = intval($_POST['status']);
            // For now, we'll use quantity to determine status (0 = inactive, >0 = active)
            $quantity = $status ? 1 : 0;
            $update_query = "UPDATE rpos_products SET prod_quantity = ? WHERE prod_id = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param('ii', $quantity, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update product status']);
            }
            $stmt->close();
            exit;
            
        case 'bulk_delete':
            $ids = $_POST['product_ids'];
            if (empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'No products selected']);
                exit;
            }
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $delete_query = "DELETE FROM rpos_products WHERE prod_id IN ($placeholders)";
            $stmt = $mysqli->prepare($delete_query);
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => count($ids) . ' products deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete products']);
            }
            $stmt->close();
            exit;
    }
}

// Handle traditional delete (for backward compatibility)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $adn = "DELETE FROM rpos_products WHERE prod_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Product Deleted Successfully";
        $stmt->close();
        header("Location: products.php");
        exit;
    } else {
        $err = "Error Deleting Product or Product Not Found";
    }
    $stmt->close();
}

// Get product statistics with relationships
$stats_query = "SELECT 
    COUNT(DISTINCT p.prod_id) as total_products,
    COUNT(DISTINCT CASE WHEN p.prod_quantity > 0 THEN p.prod_id END) as active_products,
    COUNT(DISTINCT CASE WHEN p.prod_quantity = 0 THEN p.prod_id END) as inactive_products,
    COUNT(DISTINCT CASE WHEN p.prod_quantity <= p.prod_threshold THEN p.prod_id END) as low_stock_products,
    AVG(p.prod_price) as avg_price,
    SUM(p.prod_quantity * p.prod_price) as total_inventory_value,
    COUNT(DISTINCT CASE WHEN pl.relation = 'mirror' THEN p.prod_id END) as double_products,
    COUNT(DISTINCT CASE WHEN pl.relation = 'combo' THEN p.prod_id END) as combo_products,
    COUNT(DISTINCT CASE WHEN pl.relation IS NULL THEN p.prod_id END) as regular_products
    FROM rpos_products p
    LEFT JOIN rpos_product_links pl ON p.prod_id = pl.linked_product_id";
$stmt = $mysqli->prepare($stats_query);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();

// Get recent products (last 7 days)
$recent_query = "SELECT COUNT(*) as recent_count FROM rpos_products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $mysqli->prepare($recent_query);
$stmt->execute();
$stmt->bind_result($recent_count);
$stmt->fetch();
$stmt->close();
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Product Management</title>
    
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
            transition: all var(--transition-speed) ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, rgba(62, 62, 62, 0.8), rgba(40, 40, 40, 0.6));
            border: 1px solid rgba(158, 43, 43, 0.4);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.8), rgba(120, 30, 30, 0.6));
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, rgba(74, 107, 87, 1), rgba(58, 86, 115, 0.8));
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
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        /* Product Cards */
        .product-card {
            background: rgba(26, 26, 46, 0.9);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all var(--transition-speed) ease;
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(192, 160, 98, 0.4);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(192, 160, 98, 0.3);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--accent-gold);
            margin-bottom: 0.5rem;
        }
        
        .product-code {
            font-size: 0.9rem;
            color: rgba(248, 245, 242, 0.7);
            font-family: 'Courier New', monospace;
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--accent-green);
        }
        
        .product-stock {
            font-size: 0.9rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .stock-high {
            background-color: rgba(74, 107, 87, 0.3);
            color: var(--accent-green);
        }
        
        .stock-medium {
            background-color: rgba(192, 160, 98, 0.3);
            color: var(--accent-gold);
        }
        
        .stock-low {
            background-color: rgba(158, 43, 43, 0.3);
            color: #ff6b6b;
        }
        
        .stock-out {
            background-color: rgba(158, 43, 43, 0.5);
            color: #ff4757;
        }
        
        /* Statistics Cards */
        .stat-card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-speed) ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .stat-label {
            color: rgba(248, 245, 242, 0.8);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Filter and Search */
        .filter-section {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-control {
            background-color: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
        }
        
        .form-control:focus {
            background-color: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
            color: var(--text-light);
        }
        
        .form-control::placeholder {
            color: rgba(248, 245, 242, 0.6);
        }
        
        /* Status Badges */
        .status-active {
            background-color: var(--accent-green);
            color: white;
        }
        
        .status-inactive {
            background-color: #6c757d;
            color: white;
        }
        
        /* Product Type Badges */
        .badge-info {
            background-color: var(--accent-blue);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--accent-gold);
            color: var(--primary-dark);
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .badge i {
            margin-right: 0.25rem;
        }
        
        /* Bulk Actions */
        .bulk-actions {
            background: rgba(26, 26, 46, 0.9);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .bulk-actions.show {
            display: block;
        }
        
        /* Checkbox Styling */
        .custom-checkbox {
            position: relative;
            display: inline-block;
        }
        
        .custom-checkbox input[type="checkbox"] {
            opacity: 0;
            position: absolute;
            cursor: pointer;
        }
        
        .custom-checkbox .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: rgba(26, 26, 46, 0.8);
            border: 2px solid rgba(192, 160, 98, 0.3);
            border-radius: 4px;
        }
        
        .custom-checkbox input:checked ~ .checkmark {
            background-color: var(--accent-gold);
            border-color: var(--accent-gold);
        }
        
        .custom-checkbox .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        
        .custom-checkbox .checkmark:after {
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: rgba(248, 245, 242, 0.6);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--accent-gold);
            margin-bottom: 1rem;
        }
        
        /* View Toggle */
        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }
        
        .view-toggle .btn {
            padding: 0.5rem 1rem;
        }
        
        .view-toggle .btn.active {
            background-color: var(--accent-gold);
            border-color: var(--accent-gold);
            color: var(--primary-dark);
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
            
            .product-card {
                padding: 1rem;
            }
            
            .product-image {
                width: 60px;
                height: 60px;
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
    <div class="header pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Product Management</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-gold">Dashboard</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Products</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <div class="view-toggle">
                <button class="btn btn-sm btn-outline-light" onclick="toggleView('table')" id="tableViewBtn">
                  <i class="fas fa-table"></i> Table
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="toggleView('grid')" id="gridViewBtn">
                  <i class="fas fa-th"></i> Grid
                </button>
              </div>
            </div>
          </div>
          
          <!-- Statistics Cards -->
          <div class="row">
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Products</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_products']; ?></div>
                <div class="stat-label">Active</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['low_stock_products']; ?></div>
                <div class="stat-label">Low Stock</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number">₱<?php echo number_format($stats['avg_price'], 0); ?></div>
                <div class="stat-label">Avg Price</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number">₱<?php echo number_format($stats['total_inventory_value'], 0); ?></div>
                <div class="stat-label">Inventory Value</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['double_products']; ?></div>
                <div class="stat-label">Double Products</div>
              </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['combo_products']; ?></div>
                <div class="stat-label">Combo Products</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Filter Section -->
      <div class="filter-section">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="searchProducts" class="text-gold">Search Products</label>
              <input type="text" class="form-control" id="searchProducts" placeholder="Search by name or code..." onkeyup="filterProducts()">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="statusFilter" class="text-gold">Status</label>
              <select class="form-control" id="statusFilter" onchange="filterProducts()">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="stockFilter" class="text-gold">Stock Level</label>
              <select class="form-control" id="stockFilter" onchange="filterProducts()">
                <option value="">All Stock</option>
                <option value="high">High Stock</option>
                <option value="medium">Medium Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="priceFilter" class="text-gold">Price Range</label>
              <select class="form-control" id="priceFilter" onchange="filterProducts()">
                <option value="">All Prices</option>
                <option value="0-50">₱0 - ₱50</option>
                <option value="50-100">₱50 - ₱100</option>
                <option value="100-200">₱100 - ₱200</option>
                <option value="200+">₱200+</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="typeFilter" class="text-gold">Product Type</label>
              <select class="form-control" id="typeFilter" onchange="filterProducts()">
                <option value="">All Types</option>
                <option value="regular">Regular</option>
                <option value="double">Double (Mirror)</option>
                <option value="combo">Combo</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="sortBy" class="text-gold">Sort By</label>
              <select class="form-control" id="sortBy" onchange="sortProducts()">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name-asc">Name A-Z</option>
                <option value="name-desc">Name Z-A</option>
                <option value="price-asc">Price Low-High</option>
                <option value="price-desc">Price High-Low</option>
                <option value="stock-asc">Stock Low-High</option>
                <option value="stock-desc">Stock High-Low</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bulk Actions -->
      <div class="bulk-actions" id="bulkActions">
        <div class="row align-items-center">
          <div class="col-md-6">
            <span class="text-gold" id="selectedCount">0 products selected</span>
          </div>
          <div class="col-md-6 text-right">
            <button class="btn btn-sm btn-danger" onclick="bulkDelete()">
              <i class="fas fa-trash"></i> Delete Selected
            </button>
            <button class="btn btn-sm btn-warning" onclick="bulkToggleStatus()">
              <i class="fas fa-toggle-on"></i> Toggle Status
            </button>
            <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
              <i class="fas fa-times"></i> Clear
            </button>
          </div>
        </div>
      </div>
      
      <!-- Products Display -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Product Management</h3>
                  <p class="text-muted">Manage your product inventory</p>
                </div>
                <div class="col text-right">
                  <a href="add_product.php" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Add New Product
                  </a>
                  <button class="btn btn-sm btn-primary" onclick="refreshProducts()">
                    <i class="fas fa-sync-alt"></i> Refresh
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Table View -->
            <div id="tableView" class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">
                      <div class="custom-checkbox">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <span class="checkmark"></span>
                      </div>
                    </th>
                    <th scope="col">Image</th>
                    <th class="text-gold" scope="col">Product Code</th>
                    <th class="text-gold" scope="col">Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Type</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody id="productsTableBody">
                  <?php
                  $ret = "SELECT p.*, 
                         GROUP_CONCAT(pl.relation) as relations,
                         GROUP_CONCAT(pl.base_product_id) as base_product_ids,
                         COUNT(pl.linked_product_id) as link_count
                         FROM rpos_products p
                         LEFT JOIN rpos_product_links pl ON p.prod_id = pl.linked_product_id
                         GROUP BY p.prod_id
                         ORDER BY 
                           CASE 
                             WHEN LOWER(p.prod_category) = 'food' THEN 1
                             WHEN LOWER(p.prod_category) = 'beverages' THEN 2
                             ELSE 3
                           END,
                           p.prod_name ASC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                      // Compute display quantity considering links (mirror/combo)
                      $display_quantity = (int)$prod->prod_quantity;
                      $link_badge = '';
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
                            // For mirror (double) variants, display stock is half of base stock (integer division)
                            $base_qty = (int)$row['prod_quantity'];
                            $display_quantity = intdiv(max(0, $base_qty), 2);
                            $link_badge = '(Mirror of '.htmlspecialchars($row['prod_name']).')';
                            // mirror should have only one base; break acceptable
                          } else if ($row['relation'] === 'combo') {
                            $bases[] = $row;
                          }
                        }
                        if (!$is_mirror && count($bases) > 0) {
                          // For combo, available stock is min of base stocks
                          $mins = array_map(function($r){ return (int)$r['prod_quantity']; }, $bases);
                          $display_quantity = count($mins) ? min($mins) : $display_quantity;
                          // Compose badge label
                          $names = array_map(function($r){ return htmlspecialchars($r['prod_name']); }, $bases);
                          $link_badge = '(Combo of '.implode(' + ', $names).')';
                        }
                        $link_stmt->close();
                      }

                      $stock_class = '';
                      $stock_text = '';
                      $prod_status = $display_quantity > 0 ? 1 : 0; // Determine status based on display quantity
                      
                      // Determine product type based on relationship data
                      $product_type = 'regular';
                      $type_badge_class = 'badge-secondary';
                      $type_icon = 'fas fa-box';
                      
                      if ($prod->relations) {
                          $relations = explode(',', $prod->relations);
                          if (in_array('mirror', $relations)) {
                              $product_type = 'double';
                              $type_badge_class = 'badge-info';
                              $type_icon = 'fas fa-copy';
                          } elseif (in_array('combo', $relations)) {
                              $product_type = 'combo';
                              $type_badge_class = 'badge-warning';
                              $type_icon = 'fas fa-layer-group';
                          }
                      }
                      
                      if ($display_quantity == 0) {
                          $stock_class = 'stock-out';
                          $stock_text = 'Out of Stock';
                      } elseif ($display_quantity <= $prod->prod_threshold) {
                          $stock_class = 'stock-low';
                          $stock_text = 'Low Stock';
                      } elseif ($display_quantity <= $prod->prod_threshold * 2) {
                          $stock_class = 'stock-medium';
                          $stock_text = 'Medium Stock';
                      } else {
                          $stock_class = 'stock-high';
                          $stock_text = 'High Stock';
                      }
                  ?>
                    <tr data-product-id="<?php echo $prod->prod_id; ?>" data-name="<?php echo strtolower($prod->prod_name); ?>" data-code="<?php echo strtolower($prod->prod_code); ?>" data-status="<?php echo $prod_status; ?>" data-stock="<?php echo $display_quantity; ?>" data-price="<?php echo $prod->prod_price; ?>" data-type="<?php echo $product_type; ?>">
                      <td>
                        <div class="custom-checkbox">
                          <input type="checkbox" class="product-checkbox" value="<?php echo $prod->prod_id; ?>" onchange="updateSelection()">
                          <span class="checkmark"></span>
                        </div>
                      </td>
                      <td>
                        <?php
                        if ($prod->prod_img) {
                          echo "<img src='assets/img/products/$prod->prod_img' class='product-image'>";
                        } else {
                          echo "<img src='assets/img/products/default.jpg' class='product-image'>";
                        }
                        ?>
                      </td>
                      <td class="text-gold"><?php echo $prod->prod_code; ?></td>
                      <td class="text-gold"><?php echo $prod->prod_name; ?></td>
                      <td class="product-price">₱<?php echo number_format($prod->prod_price, 2); ?></td>
                      <td>
                        <span class="product-stock <?php echo $stock_class; ?>">
                          <?php echo $display_quantity; ?> - <?php echo $stock_text; ?>
                        </span>
                        <?php if(!empty($link_badge)): ?>
                          <br><small class="text-muted"><?php echo $link_badge; ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge <?php echo $type_badge_class; ?>" title="<?php echo ucfirst($product_type); ?> Product<?php echo $prod->base_product_ids ? ' (Base: ' . $prod->base_product_ids . ')' : ''; ?>">
                          <i class="<?php echo $type_icon; ?>"></i> <?php echo ucfirst($product_type); ?>
                        </span>
                        <?php if ($prod->link_count > 1): ?>
                          <small class="text-muted d-block">+<?php echo $prod->link_count - 1; ?> more</small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge <?php echo $prod_status ? 'status-active' : 'status-inactive'; ?>">
                          <?php echo $prod_status ? 'Active' : 'Inactive'; ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex">
                          <button class="btn btn-sm btn-info mr-1" onclick="viewProduct(<?php echo $prod->prod_id; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                          </button>
                          <a href="update_product.php?update=<?php echo $prod->prod_id; ?>" class="btn btn-sm btn-primary mr-1" title="Edit Product">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-sm btn-<?php echo $prod_status ? 'warning' : 'success'; ?>" onclick="toggleProductStatus(<?php echo $prod->prod_id; ?>, <?php echo $prod_status; ?>)" title="<?php echo $prod_status ? 'Deactivate' : 'Activate'; ?>">
                            <i class="fas fa-<?php echo $prod_status ? 'pause' : 'play'; ?>"></i>
                          </button>
                          <button class="btn btn-sm btn-danger ml-1" onclick="deleteProduct(<?php echo $prod->prod_id; ?>)" title="Delete Product">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            
            <!-- Grid View -->
            <div id="gridView" class="card-body" style="display: none;">
              <div class="row" id="productsGrid">
                <?php
                $ret = "SELECT p.*, 
                       GROUP_CONCAT(pl.relation) as relations,
                       GROUP_CONCAT(pl.base_product_id) as base_product_ids,
                       COUNT(pl.linked_product_id) as link_count
                       FROM rpos_products p
                       LEFT JOIN rpos_product_links pl ON p.prod_id = pl.linked_product_id
                       GROUP BY p.prod_id
                       ORDER BY 
                         CASE 
                           WHEN LOWER(p.prod_category) = 'food' THEN 1
                           WHEN LOWER(p.prod_category) = 'beverages' THEN 2
                           ELSE 3
                         END,
                         p.prod_name ASC";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($prod = $res->fetch_object()) {
                    // Compute display quantity considering links (mirror/combo)
                    $display_quantity = (int)$prod->prod_quantity;
                    $link_badge = '';
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
                          // For mirror (double) variants, display stock is half of base stock (integer division)
                          $base_qty = (int)$row['prod_quantity'];
                          $display_quantity = intdiv(max(0, $base_qty), 2);
                          $link_badge = '(Mirror of '.htmlspecialchars($row['prod_name']).')';
                          // mirror should have only one base; break acceptable
                        } else if ($row['relation'] === 'combo') {
                          $bases[] = $row;
                        }
                      }
                      if (!$is_mirror && count($bases) > 0) {
                        // For combo, available stock is min of base stocks
                        $mins = array_map(function($r){ return (int)$r['prod_quantity']; }, $bases);
                        $display_quantity = count($mins) ? min($mins) : $display_quantity;
                        // Compose badge label
                        $names = array_map(function($r){ return htmlspecialchars($r['prod_name']); }, $bases);
                        $link_badge = '(Combo of '.implode(' + ', $names).')';
                      }
                      $link_stmt->close();
                    }

                    $stock_class = '';
                    $stock_text = '';
                    $prod_status = $display_quantity > 0 ? 1 : 0; // Determine status based on display quantity
                    
                    // Determine product type based on relationship data
                    $product_type = 'regular';
                    $type_badge_class = 'badge-secondary';
                    $type_icon = 'fas fa-box';
                    
                    if ($prod->relations) {
                        $relations = explode(',', $prod->relations);
                        if (in_array('mirror', $relations)) {
                            $product_type = 'double';
                            $type_badge_class = 'badge-info';
                            $type_icon = 'fas fa-copy';
                        } elseif (in_array('combo', $relations)) {
                            $product_type = 'combo';
                            $type_badge_class = 'badge-warning';
                            $type_icon = 'fas fa-layer-group';
                        }
                    }
                    
                    if ($display_quantity == 0) {
                        $stock_class = 'stock-out';
                        $stock_text = 'Out of Stock';
                    } elseif ($display_quantity <= $prod->prod_threshold) {
                        $stock_class = 'stock-low';
                        $stock_text = 'Low Stock';
                    } elseif ($display_quantity <= $prod->prod_threshold * 2) {
                        $stock_class = 'stock-medium';
                        $stock_text = 'Medium Stock';
                    } else {
                        $stock_class = 'stock-high';
                        $stock_text = 'High Stock';
                    }
                ?>
                  <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="product-card" data-product-id="<?php echo $prod->prod_id; ?>" data-name="<?php echo strtolower($prod->prod_name); ?>" data-code="<?php echo strtolower($prod->prod_code); ?>" data-status="<?php echo $prod_status; ?>" data-stock="<?php echo $display_quantity; ?>" data-price="<?php echo $prod->prod_price; ?>" data-type="<?php echo $product_type; ?>">
                      <div class="d-flex align-items-start">
                        <div class="custom-checkbox mr-3">
                          <input type="checkbox" class="product-checkbox" value="<?php echo $prod->prod_id; ?>" onchange="updateSelection()">
                          <span class="checkmark"></span>
          </div>
                        <div class="product-image-container mr-3">
                          <?php
                          if ($prod->prod_img) {
                            echo "<img src='assets/img/products/$prod->prod_img' class='product-image'>";
                          } else {
                            echo "<img src='assets/img/products/default.jpg' class='product-image'>";
                          }
                          ?>
                        </div>
                        <div class="product-info flex-grow-1">
                          <div class="product-name"><?php echo $prod->prod_name; ?></div>
                          <div class="product-code"><?php echo $prod->prod_code; ?></div>
                          <div class="product-price">₱<?php echo number_format($prod->prod_price, 2); ?></div>
                          <div class="product-stock <?php echo $stock_class; ?>">
                            <?php echo $display_quantity; ?> - <?php echo $stock_text; ?>
                          </div>
                          <?php if(!empty($link_badge)): ?>
                            <small class="text-muted d-block mt-1"><?php echo $link_badge; ?></small>
                          <?php endif; ?>
                          <div class="mt-2">
                            <span class="badge <?php echo $type_badge_class; ?> mr-1" title="<?php echo ucfirst($product_type); ?> Product<?php echo $prod->base_product_ids ? ' (Base: ' . $prod->base_product_ids . ')' : ''; ?>">
                              <i class="<?php echo $type_icon; ?>"></i> <?php echo ucfirst($product_type); ?>
                            </span>
                            <span class="badge <?php echo $prod_status ? 'status-active' : 'status-inactive'; ?>">
                              <?php echo $prod_status ? 'Active' : 'Inactive'; ?>
                            </span>
                            <?php if ($prod->link_count > 1): ?>
                              <small class="text-muted d-block mt-1">+<?php echo $prod->link_count - 1; ?> more relationships</small>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <div class="product-actions mt-3">
                        <div class="d-flex justify-content-between">
                          <button class="btn btn-sm btn-info" onclick="viewProduct(<?php echo $prod->prod_id; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                          </button>
                          <a href="update_product.php?update=<?php echo $prod->prod_id; ?>" class="btn btn-sm btn-primary" title="Edit Product">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-sm btn-<?php echo $prod_status ? 'warning' : 'success'; ?>" onclick="toggleProductStatus(<?php echo $prod->prod_id; ?>, <?php echo $prod_status; ?>)" title="<?php echo $prod_status ? 'Deactivate' : 'Activate'; ?>">
                            <i class="fas fa-<?php echo $prod_status ? 'pause' : 'play'; ?>"></i>
                          </button>
                          <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $prod->prod_id; ?>)" title="Delete Product">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php
      require_once('partials/_footer.php');
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  
  <script>
    let currentView = 'table';
    let selectedProducts = new Set();
    
    // Toggle between table and grid view
    function toggleView(view) {
      currentView = view;
      const tableView = document.getElementById('tableView');
      const gridView = document.getElementById('gridView');
      const tableBtn = document.getElementById('tableViewBtn');
      const gridBtn = document.getElementById('gridViewBtn');
      
      if (view === 'table') {
        tableView.style.display = 'block';
        gridView.style.display = 'none';
        tableBtn.classList.add('active');
        gridBtn.classList.remove('active');
      } else {
        tableView.style.display = 'none';
        gridView.style.display = 'block';
        gridBtn.classList.add('active');
        tableBtn.classList.remove('active');
      }
    }
    
    // Filter products
    function filterProducts() {
      const searchTerm = document.getElementById('searchProducts').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value;
      const stockFilter = document.getElementById('stockFilter').value;
      const priceFilter = document.getElementById('priceFilter').value;
      const typeFilter = document.getElementById('typeFilter').value;
      
      const products = document.querySelectorAll('[data-product-id]');
      let visibleCount = 0;
      
      products.forEach(product => {
        const name = product.getAttribute('data-name');
        const code = product.getAttribute('data-code');
        const status = product.getAttribute('data-status');
        const stock = parseInt(product.getAttribute('data-stock'));
        const price = parseFloat(product.getAttribute('data-price'));
        const type = product.getAttribute('data-type');
        
        let showProduct = true;
        
        // Search filter
        if (searchTerm && !name.includes(searchTerm) && !code.includes(searchTerm)) {
          showProduct = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
          showProduct = false;
        }
        
        // Stock filter
        if (stockFilter) {
          const stockLevel = getStockLevel(stock);
          if (stockLevel !== stockFilter) {
            showProduct = false;
          }
        }
        
        // Price filter
        if (priceFilter) {
          const priceRange = priceFilter.split('-');
          if (priceRange.length === 2) {
            const min = parseFloat(priceRange[0]);
            const max = parseFloat(priceRange[1]);
            if (price < min || price > max) {
              showProduct = false;
            }
          } else if (priceFilter === '200+') {
            if (price < 200) {
              showProduct = false;
            }
          }
        }
        
        // Type filter
        if (typeFilter && type !== typeFilter) {
          showProduct = false;
        }
        
        if (showProduct) {
          product.style.display = currentView === 'table' ? 'table-row' : 'block';
          visibleCount++;
        } else {
          product.style.display = 'none';
        }
      });
      
      updateProductCount(visibleCount);
    }
    
    // Get stock level based on quantity
    function getStockLevel(quantity) {
      if (quantity === 0) return 'out';
      if (quantity <= 10) return 'low';
      if (quantity <= 20) return 'medium';
      return 'high';
    }
    
    // Sort products
    function sortProducts() {
      const sortBy = document.getElementById('sortBy').value;
      const container = currentView === 'table' ? 
        document.getElementById('productsTableBody') : 
        document.getElementById('productsGrid');
      
      const products = Array.from(container.children);
      
      products.sort((a, b) => {
        switch (sortBy) {
          case 'newest':
            return 0; // Already sorted by newest in PHP
          case 'oldest':
            return 0; // Would need to reverse
          case 'name-asc':
            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
          case 'name-desc':
            return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
          case 'price-asc':
            return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
          case 'price-desc':
            return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
          case 'stock-asc':
            return parseInt(a.getAttribute('data-stock')) - parseInt(b.getAttribute('data-stock'));
          case 'stock-desc':
            return parseInt(b.getAttribute('data-stock')) - parseInt(a.getAttribute('data-stock'));
          default:
            return 0;
        }
      });
      
      // Re-append sorted products
      products.forEach(product => {
        container.appendChild(product);
      });
    }
    
    // Toggle select all
    function toggleSelectAll() {
      const selectAll = document.getElementById('selectAll');
      const checkboxes = document.querySelectorAll('.product-checkbox');
      
      checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
          selectedProducts.add(checkbox.value);
        } else {
          selectedProducts.delete(checkbox.value);
        }
      });
      
      updateSelection();
    }
    
    // Update selection
    function updateSelection() {
      const checkboxes = document.querySelectorAll('.product-checkbox');
      selectedProducts.clear();
      
      checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
          selectedProducts.add(checkbox.value);
        }
      });
      
      const selectedCount = selectedProducts.size;
      document.getElementById('selectedCount').textContent = `${selectedCount} product${selectedCount !== 1 ? 's' : ''} selected`;
      
      const bulkActions = document.getElementById('bulkActions');
      if (selectedCount > 0) {
        bulkActions.classList.add('show');
      } else {
        bulkActions.classList.remove('show');
      }
      
      // Update select all checkbox
      const selectAll = document.getElementById('selectAll');
      if (selectedCount === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
      } else if (selectedCount === checkboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
      } else {
        selectAll.indeterminate = true;
      }
    }
    
    // Clear selection
    function clearSelection() {
      selectedProducts.clear();
      const checkboxes = document.querySelectorAll('.product-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = false;
      });
      document.getElementById('selectAll').checked = false;
      document.getElementById('selectAll').indeterminate = false;
      updateSelection();
    }
    
    // Bulk delete
    function bulkDelete() {
      if (selectedProducts.size === 0) {
        alert('Please select products to delete.');
        return;
      }
      
      if (!confirm(`Are you sure you want to delete ${selectedProducts.size} product${selectedProducts.size !== 1 ? 's' : ''}? This action cannot be undone.`)) {
        return;
      }
      
      const productIds = Array.from(selectedProducts);
      
      fetch('products.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=bulk_delete&product_ids=${JSON.stringify(productIds)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          // Remove selected products from UI
          productIds.forEach(id => {
            const product = document.querySelector(`[data-product-id="${id}"]`);
            if (product) {
              product.style.animation = 'fadeOut 0.3s ease-out forwards';
              setTimeout(() => {
                product.remove();
                updateProductCount();
              }, 300);
            }
          });
          clearSelection();
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting products', 'error');
      });
    }
    
    // Bulk toggle status
    function bulkToggleStatus() {
      if (selectedProducts.size === 0) {
        alert('Please select products to toggle status.');
        return;
      }
      
      // This would need to be implemented with a proper API endpoint
      alert('Bulk status toggle feature would be implemented here.');
    }
    
    // Delete single product
    function deleteProduct(productId) {
      if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        return;
      }
      
      fetch('products.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&product_id=${productId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const product = document.querySelector(`[data-product-id="${productId}"]`);
          if (product) {
            product.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
              product.remove();
              updateProductCount();
            }, 300);
          }
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the product', 'error');
      });
    }
    
    // Toggle product status
    function toggleProductStatus(productId, currentStatus) {
      const newStatus = currentStatus ? 0 : 1;
      const statusText = newStatus ? 'activate' : 'deactivate';
      
      if (!confirm(`Are you sure you want to ${statusText} this product?`)) {
        return;
      }
      
      fetch('products.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle_status&product_id=${productId}&status=${newStatus}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          // Update UI
          const product = document.querySelector(`[data-product-id="${productId}"]`);
          if (product) {
            product.setAttribute('data-status', newStatus);
            const statusBadge = product.querySelector('.badge');
            const statusButton = product.querySelector(`[onclick*="toggleProductStatus"]`);
            
            if (newStatus) {
              statusBadge.className = 'badge status-active';
              statusBadge.textContent = 'Active';
              statusButton.className = 'btn btn-sm btn-warning mr-1';
              statusButton.title = 'Deactivate';
              statusButton.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
              statusBadge.className = 'badge status-inactive';
              statusBadge.textContent = 'Inactive';
              statusButton.className = 'btn btn-sm btn-success mr-1';
              statusButton.title = 'Activate';
              statusButton.innerHTML = '<i class="fas fa-play"></i>';
            }
          }
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating product status', 'error');
      });
    }
    
    // View product details
    function viewProduct(productId) {
      // This would open a modal or redirect to a detailed view
      alert(`View product details for ID: ${productId}`);
    }
    
    // Refresh products
    function refreshProducts() {
      location.reload();
    }
    
    // Update product count
    function updateProductCount(count = null) {
      if (count === null) {
        const visibleProducts = document.querySelectorAll('[data-product-id]:not([style*="display: none"])');
        count = visibleProducts.length;
      }
      // Update any product count display if needed
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        background-color: rgba(26, 26, 46, 0.95);
        border: 1px solid rgba(192, 160, 98, 0.3);
        color: var(--text-light);
      `;
      
      notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      // Set default view
      toggleView('table');
      
      // Add fade-in animation
      const cards = document.querySelectorAll('.stat-card, .product-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
        card.classList.add('fade-in');
      });
    });
    
    // Add fade-in animation CSS
    const style = document.createElement('style');
    style.textContent = `
      .fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
      }
      
      @keyframes fadeInUp {
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      @keyframes fadeOut {
        to {
          opacity: 0;
          transform: translateX(100px);
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>