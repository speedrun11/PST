<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Check if suppliers table exists
$table_check = $mysqli->query("SHOW TABLES LIKE 'rpos_suppliers'");
if ($table_check->num_rows == 0) {
    $_SESSION['error'] = "Suppliers table doesn't exist. Please contact your administrator.";
    header("Location: inventory_dashboard.php");
    exit;
}

// Handle delete action
if(isset($_GET['delete'])) {
    $supplier_id = $_GET['delete'];
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        // First check if any products are associated with this supplier
        $check = "SELECT COUNT(*) FROM rpos_products WHERE supplier_id = ?";
        $stmt = $mysqli->prepare($check);
        if ($stmt) {
            $stmt->bind_param('i', $supplier_id);
            $stmt->execute();
            $stmt->bind_result($product_count);
            $stmt->fetch();
            $stmt->close();
            
            if($product_count > 0) {
                $_SESSION['error'] = "Cannot delete supplier - there are products associated with it";
                header("Location: suppliers.php");
                exit;
            }
            
            // Delete the supplier
            $delete = "DELETE FROM rpos_suppliers WHERE supplier_id = ?";
            $stmt = $mysqli->prepare($delete);
            $stmt->bind_param('i', $supplier_id);
            $stmt->execute();
            
            $mysqli->commit();
            $_SESSION['success'] = "Supplier deleted successfully";
        } else {
            throw new Exception("Database error: " . $mysqli->error);
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = "Failed to delete supplier: " . $e->getMessage();
    }
    header("Location: suppliers.php");
    exit;
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
              <h6 class="h2 text-white d-inline-block mb-0">Supplier Management</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Suppliers</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="add_supplier.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add New Supplier
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
                  <h3 class="mb-0 text-gold">All Suppliers</h3>
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
                    <th scope="col" class="text-gold">Supplier</th>
                    <th scope="col" class="text-gold">Contact</th>
                    <th scope="col" class="text-gold">Email</th>
                    <th scope="col" class="text-gold">Products Supplied</th>
                    <th scope="col" class="text-gold">Last Delivery</th>
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
                    $search = " WHERE supplier_name LIKE ? OR supplier_email LIKE ? OR supplier_phone LIKE ?";
                    $params = [$search_term, $search_term, $search_term];
                  }
                  
                  // Get total number of suppliers
                  $count_query = "SELECT COUNT(*) AS total FROM rpos_suppliers $search";
                  $count_stmt = $mysqli->prepare($count_query);
                  if ($count_stmt) {
                    if(!empty($params)) {
                      $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
                    }
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $count_row = $count_result->fetch_assoc();
                    $total_suppliers = $count_row['total'];
                    $count_stmt->close();
                  } else {
                    $total_suppliers = 0;
                    $_SESSION['error'] = "Database error: " . $mysqli->error;
                  }
                  
                  // Calculate total pages
                  $total_pages = ceil($total_suppliers / $results_per_page);
                  
                  // Get suppliers with pagination
                  $ret = "SELECT s.*, 
                          (SELECT COUNT(*) FROM rpos_products p WHERE p.supplier_id = s.supplier_id) as product_count,
                          (SELECT MAX(last_restocked) FROM rpos_products p WHERE p.supplier_id = s.supplier_id) as last_delivery
                          FROM rpos_suppliers s
                          $search
                          ORDER BY supplier_name ASC LIMIT ?, ?";
                  $stmt = $mysqli->prepare($ret);

                  if ($stmt) {
                    if(!empty($params)) {
                      $params[] = $start_from;
                      $params[] = $results_per_page;
                      $stmt->bind_param(str_repeat('s', count($params)-2).'ii', ...$params);
                    } else {
                      $stmt->bind_param('ii', $start_from, $results_per_page);
                    }
                    
                    $stmt->execute();
                    $res = $stmt->get_result();
                    
                    if ($res->num_rows == 0) {
                      echo '<tr><td colspan="6" class="text-center text-white py-4">No suppliers found. <a href="add_supplier.php" class="text-gold">Add your first supplier</a></td></tr>';
                    }
                    
                    while ($supplier = $res->fetch_object()) {
                    ?>
                      <tr>
                        <th scope="row">
                          <div class="media align-items-center">
                            <div class="media-body">
                              <span class="mb-0 text-white"><?php echo htmlspecialchars($supplier->supplier_name); ?></span>
                              <br>
                              <small class="text-muted"><?php echo htmlspecialchars($supplier->supplier_address); ?></small>
                            </div>
                          </div>
                        </th>
                        <td class="text-white"><?php echo htmlspecialchars($supplier->supplier_phone); ?></td>
                        <td class="text-white"><?php echo htmlspecialchars($supplier->supplier_email); ?></td>
                        <td class="text-white"><?php echo $supplier->product_count; ?></td>
                        <td class="text-white">
                          <?php 
                          if ($supplier->last_delivery) {
                            echo date('M d, Y', strtotime($supplier->last_delivery));
                          } else {
                            echo 'Never';
                          }
                          ?>
                        </td>
                        <td>
                          <div class="dropdown">
                            <a class="btn btn-sm btn-icon-only text-gold" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                              <a class="dropdown-item text-white" href="update_supplier.php?update=<?php echo $supplier->supplier_id; ?>">
                                <i class="fas fa-edit text-primary mr-2"></i> Edit
                              </a>
                              <a class="dropdown-item text-white" href="products.php?supplier=<?php echo $supplier->supplier_id; ?>">
                                <i class="fas fa-boxes text-info mr-2"></i> View Products
                              </a>
                              <a class="dropdown-item text-white" href="suppliers.php?delete=<?php echo $supplier->supplier_id; ?>" onclick="return confirm('Are you sure you want to delete this supplier?');">
                                <i class="fas fa-trash text-danger mr-2"></i> Delete
                              </a>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php 
                    }
                  } else {
                    echo '<tr><td colspan="6" class="text-center text-white py-4">Error loading suppliers: ' . $mysqli->error . '</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer py-4" style="background: rgba(26, 26, 46, 0.9); border-top: 1px solid rgba(192, 160, 98, 0.2);">
              <nav aria-label="...">
                <ul class="pagination justify-content-end mb-0">
                  <?php if($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="suppliers.php?page=<?php echo $page-1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>" tabindex="-1">
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
                    echo '<li class="page-item"><a class="page-link text-gold" href="suppliers.php?page=1'.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">1</a></li>';
                    if($start_page > 2) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                  }
                  
                  for($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item '.$active.'">';
                    echo '<a class="page-link text-gold" href="suppliers.php?page='.$i.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$i.'</a>';
                    echo '</li>';
                  }
                  
                  if($end_page < $total_pages) {
                    if($end_page < $total_pages - 1) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link text-gold" href="suppliers.php?page='.$total_pages.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$total_pages.'</a></li>';
                  }
                  ?>
                  
                  <?php if($page < $total_pages): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="suppliers.php?page=<?php echo $page+1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>">
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
    
    .text-gold {
      color: var(--accent-gold) !important;
    }
    
    .text-success {
      color: var(--accent-gold) !important;
    }
    
    .text-info {
      color: var(--accent-blue) !important;
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
    
    .sidebar {
      background: rgba(26, 26, 46, 0.9) !important;
      border-right: 1px solid rgba(192, 160, 98, 0.2);
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
    
    @media (max-width: 768px) {
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
          text: "Supplier has been deleted",
          icon: "success",
          buttons: false,
          timer: 1500
        });
      });
    <?php endif; ?>
  </script>
</body>
</html>