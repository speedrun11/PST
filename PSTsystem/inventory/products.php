<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Handle delete action
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        // First get the product image to delete it
        $select = "SELECT prod_img FROM rpos_products WHERE prod_id = ?";
        $stmt = $mysqli->prepare($select);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_object();
        
        // Delete the product
        $delete = "DELETE FROM rpos_products WHERE prod_id = ?";
        $stmt = $mysqli->prepare($delete);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Delete the product image if it's not default
        if($product->prod_img != 'default.png') {
            $target_dir = "../admin/assets/img/products/";
            if(file_exists($target_dir . $product->prod_img)) {
                unlink($target_dir . $product->prod_img);
            }
        }
        
        $mysqli->commit();
        $_SESSION['success'] = "Product deleted successfully";
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = "Failed to delete product: " . $e->getMessage();
        header("Location: products.php");
        exit;
    }
}
?>

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
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Product Management</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Products</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="add_product.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add New Product
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Display success/error messages -->
      <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">All Products</h3>
                </div>
                <div class="col-4 text-right">
                  <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                      <input type="text" name="search" class="form-control bg-transparent text-light border-light" placeholder="Search..." value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>">
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col" class="text-gold">Product</th>
                    <th scope="col" class="text-gold">Code</th>
                    <th scope="col" class="text-gold">Category</th>
                    <th scope="col" class="text-gold">Price</th>
                    <th scope="col" class="text-gold">Current Stock</th>
                    <th scope="col" class="text-gold">Threshold</th>
                    <th scope="col" class="text-gold">Supplier</th>
                    <th scope="col" class="text-gold">Status</th>
                    <th scope="col" class="text-gold">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Pagination configuration
                  $results_per_page = 10;
                  
                  // Get current page from URL
                  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                  $page = max(1, $page); // Ensure page is at least 1
                  
                  // Calculate starting limit
                  $start_from = ($page-1) * $results_per_page;
                  
                  // Build search query
                  $search = '';
                  $params = [];
                  if(isset($_GET['search']) && !empty($_GET['search'])) {
                    $search_term = "%".trim($_GET['search'])."%";
                    $search = " WHERE prod_name LIKE ? OR prod_code LIKE ?";
                    $params = [$search_term, $search_term];
                  }
                  
                  // Get total number of products
                  $count_query = "SELECT COUNT(*) AS total FROM rpos_products $search";
                  $count_stmt = $mysqli->prepare($count_query);
                  if(!empty($params)) {
                    $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
                  }
                  $count_stmt->execute();
                  $count_result = $count_stmt->get_result();
                  $count_row = $count_result->fetch_assoc();
                  $total_products = $count_row['total'];
                  
                  // Calculate total pages
                  $total_pages = ceil($total_products / $results_per_page);
                  
                  // Get products with pagination
                  $ret = "SELECT * FROM rpos_products $search ORDER BY prod_name ASC LIMIT ?, ?";
                  $stmt = $mysqli->prepare($ret);
                  
                  if(!empty($params)) {
                    $params[] = $start_from;
                    $params[] = $results_per_page;
                    $stmt->bind_param(str_repeat('s', count($params)-2).'ii', ...$params);
                  } else {
                    $stmt->bind_param('ii', $start_from, $results_per_page);
                  }
                  
                  $stmt->execute();
                  $res = $stmt->get_result();
                  
                  while ($product = $res->fetch_object()) {
                    $status_class = '';
                    $status_text = '';
                    $percentage = ($product->prod_quantity / $product->prod_threshold) * 100;
                    
                    if ($percentage <= 25) {
                      $status_class = 'critical-stock';
                      $status_text = 'Critical';
                    } elseif ($percentage <= 50) {
                      $status_class = 'low-stock';
                      $status_text = 'Low';
                    } elseif ($percentage <= 100) {
                      $status_class = 'text-success';
                      $status_text = 'Normal';
                    } else {
                      $status_class = 'text-info';
                      $status_text = 'High';
                    }
                  ?>
                    <tr>
                      <th scope="row">
                        <div class="media align-items-center">
                          <a href="#" class="avatar rounded-circle mr-3">
                            <?php if(!empty($product->prod_img)): ?>
                              <img src="../admin/assets/img/products/<?php echo htmlspecialchars($product->prod_img); ?>" alt="<?php echo htmlspecialchars($product->prod_name); ?>">
                            <?php else: ?>
                              <img src="../admin/assets/img/products/default.png" alt="Default product image">
                            <?php endif; ?>
                          </a>
                          <div class="media-body">
                            <span class="mb-0 text-white"><?php echo htmlspecialchars($product->prod_name); ?></span>
                          </div>
                        </div>
                      </th>
                      <td class="text-white"><?php echo htmlspecialchars($product->prod_code); ?></td>
                      <td class="text-white"><?php echo htmlspecialchars($product->prod_category); ?></td>
                      <td class="text-success">₱<?php echo number_format($product->prod_price, 2); ?></td>
                      <td class="text-white"><?php echo htmlspecialchars($product->prod_quantity); ?></td>
                      <td class="text-white"><?php echo htmlspecialchars($product->prod_threshold); ?></td>
                      <td class="text-white">
                      <?php 
                      if($product->supplier_id) {
                          $supplier_query = "SELECT supplier_name FROM rpos_suppliers WHERE supplier_id = ?";
                          $supplier_stmt = $mysqli->prepare($supplier_query);
                          $supplier_stmt->bind_param('i', $product->supplier_id);
                          $supplier_stmt->execute();
                          $supplier_stmt->bind_result($supplier_name);
                          $supplier_stmt->fetch();
                          echo htmlspecialchars($supplier_name);
                          $supplier_stmt->close();
                      } else {
                          echo 'None';
                      }
                      ?>
                  </td>
                      <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                      <td>
                        <div class="dropdown">
                          <a class="btn btn-sm btn-icon-only text-gold" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                          </a>
                          <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                            <a class="dropdown-item text-white" href="update_product.php?update=<?php echo $product->prod_id; ?>">
                              <i class="fas fa-edit text-primary mr-2"></i> Edit
                            </a>
                            <a class="dropdown-item text-white" href="restock_product.php?restock=<?php echo $product->prod_id; ?>">
                              <i class="fas fa-arrow-up text-warning mr-2"></i> Restock
                            </a>
                            <a class="dropdown-item text-white" href="products.php?delete=<?php echo $product->prod_id; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                              <i class="fas fa-trash text-danger mr-2"></i> Delete
                            </a>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer py-4" style="background: rgba(26, 26, 46, 0.9); border-top: 1px solid rgba(192, 160, 98, 0.2);">
              <nav aria-label="...">
                <ul class="pagination justify-content-end mb-0">
                  <?php if($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="products.php?page=<?php echo $page-1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>" tabindex="-1">
                        <i class="fas fa-angle-left"></i>
                        <span class="sr-only">Previous</span>
                      </a>
                    </li>
                  <?php endif; ?>
                  
                  <?php 
                  // Show page numbers
                  $visible_pages = 3;
                  $start_page = max(1, $page - $visible_pages);
                  $end_page = min($total_pages, $page + $visible_pages);
                  
                  if($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link text-gold" href="products.php?page=1'.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">1</a></li>';
                    if($start_page > 2) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                  }
                  
                  for($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item '.$active.'">';
                    echo '<a class="page-link text-gold" href="products.php?page='.$i.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$i.'</a>';
                    echo '</li>';
                  }
                  
                  if($end_page < $total_pages) {
                    if($end_page < $total_pages - 1) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link text-gold" href="products.php?page='.$total_pages.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$total_pages.'</a></li>';
                  }
                  ?>
                  
                  <?php if($page < $total_pages): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="products.php?page=<?php echo $page+1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>">
                        <i class="fas fa-angle-right"></i>
                        <span class="sr-only">Next</span>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </nav>
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
    
    /* Fixed sidebar styling */
    .sidebar {
      position: fixed;
      z-index: 1000;
      height: 100vh;
      overflow-y: auto;
      width: 250px;
      background: rgba(26, 26, 46, 0.95) !important;
      border-right: 1px solid rgba(192, 160, 98, 0.2);
    }
    
    /* Main content adjustments */
    .main-content {
      position: relative;
      margin-left: 250px;
      width: calc(100% - 250px);
      min-height: 100vh;
      z-index: 1;
    }
    
    /* Top navigation bar */
    .topnav {
      position: sticky;
      top: 0;
      z-index: 800;
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
    
    .card-header {
      background: rgba(26, 26, 46, 0.9);
      border-bottom: 1px solid rgba(192, 160, 98, 0.3);
    }
    
    .card-header h3 {
      color: var(--accent-gold);
      font-family: 'Fredoka', sans-serif;
    }
    
    /* Table responsive fixes */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    
    .text-gold {
      color: var(--accent-gold) !important;
    }
    
    .text-success {
      color: var(--accent-gold) !important;
    }
    
    .text-info {
      color: var(--accent-blue) !important;
    }
    
    .critical-stock {
      color: var(--accent-red) !important;
      font-weight: 700;
    }
    
    .low-stock {
      color: #ff6b6b !important;
      font-weight: 600;
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
      transform: translateX(5px);
    }
    
    .dropdown-menu {
      background-color: rgba(26, 26, 46, 0.95) !important;
      border: 1px solid rgba(192, 160, 98, 0.2) !important;
    }
    
    .dropdown-item:hover {
      background-color: rgba(192, 160, 98, 0.1) !important;
    }
    
    .page-item.active .page-link {
      background-color: var(--accent-gold) !important;
      border-color: var(--accent-gold) !important;
      color: var(--text-dark) !important;
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
    
    .badge-warning {
      background-color: var(--accent-gold);
    }
    
    .badge-danger {
      background-color: var(--accent-red);
    }
    
    .nav-link {
      color: var(--text-light);
      transition: all var(--transition-speed) ease;
    }
    
    .nav-link:hover {
      color: var(--accent-gold);
    }
    
    .footer {
      background: rgba(26, 26, 46, 0.9) !important;
      border-top: 1px solid rgba(192, 160, 98, 0.2);
    }
    
    .breadcrumb {
      background-color: rgba(26, 26, 46, 0.8) !important;
    }
    
    .breadcrumb-item.active {
      color: var(--accent-gold) !important;
    }
    
    .card-footer {
      background: rgba(26, 26, 46, 0.9) !important;
      border-top: 1px solid rgba(192, 160, 98, 0.2) !important;
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        margin-left: -100%;
        transition: all 0.3s;
      }
      
      .sidebar.show {
        margin-left: 0;
      }
      
      .main-content {
        width: 100%;
        margin-left: 0;
      }
      
      .card {
        backdrop-filter: blur(4px);
      }
      
      .breadcrumb {
        padding: 0.25rem 0.5rem;
      }
    }
  </style>
  
  <script>
    // Handle delete action
    <?php if(isset($_GET['delete'])): ?>
      document.addEventListener('DOMContentLoaded', function() {
        swal({
          title: "Success",
          text: "Product has been deleted",
          icon: "success",
          buttons: false,
          timer: 1500
        });
      });
    <?php endif; ?>
    
    // Mobile sidebar toggle function
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    }
  </script>
</body>
</html>