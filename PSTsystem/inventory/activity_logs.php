<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Pagination configuration
$results_per_page = 10;

// Get current page from URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Calculate starting limit
$start_from = ($page-1) * $results_per_page;

// Build filter query
$filter = '';
$params = [];
$types = '';

if(isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
    $filter = " WHERE l.activity_type = ?";
    $params[] = $_GET['filter_type'];
    $types .= 's';
}

if(isset($_GET['filter_product']) && !empty($_GET['filter_product'])) {
    if(empty($filter)) {
        $filter = " WHERE l.product_id = ?";
    } else {
        $filter .= " AND l.product_id = ?";
    }
    $params[] = $_GET['filter_product'];
    $types .= 's';
}

// Get total number of logs
$count_query = "SELECT COUNT(*) AS total FROM rpos_inventory_logs l $filter";
$count_stmt = $mysqli->prepare($count_query);
if(!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_logs = $count_row['total'];

// Calculate total pages
$total_pages = ceil($total_logs / $results_per_page);

// Get logs with pagination
$ret = "SELECT 
            l.log_id,
            l.product_id,
            l.activity_type,
            l.quantity_change,
            l.previous_quantity,
            l.new_quantity,
            l.staff_id,
            l.activity_date,
            l.notes,
            l.reference_code,
            p.prod_name, 
            p.prod_code, 
            s.staff_name 
        FROM rpos_inventory_logs l
        LEFT JOIN rpos_products p ON l.product_id = p.prod_id
        LEFT JOIN rpos_staff s ON l.staff_id = s.staff_id
        $filter 
        ORDER BY l.activity_date DESC 
        LIMIT ?, ?";
$stmt = $mysqli->prepare($ret);

// Prepare parameters for pagination
$pagination_params = [];
$pagination_types = '';

if(!empty($params)) {
    $pagination_params = array_merge($params, [$start_from, $results_per_page]);
    $pagination_types = $types . 'ii';
} else {
    $pagination_params = [$start_from, $results_per_page];
    $pagination_types = 'ii';
}

$stmt->bind_param($pagination_types, ...$pagination_params);
$stmt->execute();
$res = $stmt->get_result();

// Get all products for filter dropdown
$products = array();
$product_query = "SELECT prod_id, prod_name FROM rpos_products ORDER BY prod_name ASC";
$product_result = $mysqli->query($product_query);
while ($product = $product_result->fetch_object()) {
    $products[] = $product;
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
              <h6 class="h2 text-white d-inline-block mb-0">Inventory Activity Logs</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Activity Logs</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="products.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Inventory Activities</h3>
                </div>
                <div class="col-4 text-right">
                  <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#filterModal">
                    <i class="fas fa-filter"></i> Filter
                  </button>
                </div>
              </div>
            </div>

            <!-- Filter Modal -->
            <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="background: rgba(26, 26, 46, 0.95); border: 1px solid rgba(192, 160, 98, 0.3);">
                  <div class="modal-header" style="border-bottom: 1px solid rgba(192, 160, 98, 0.3);">
                    <h5 class="modal-title text-gold" id="filterModalLabel">Filter Activity Logs</h5>
                    <button type="button" class="close text-gold" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <form method="GET" action="activity_logs.php">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="filter_type">Activity Type</label>
                        <select class="form-control bg-transparent text-light border-light" id="filter_type" name="filter_type">
                          <option value="">All Types</option>
                          <option value="Add" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Add') echo 'selected'; ?>>Add</option>
                          <option value="Update" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Update') echo 'selected'; ?>>Update</option>
                          <option value="Restock" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Restock') echo 'selected'; ?>>Restock</option>
                          <option value="Sale" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Sale') echo 'selected'; ?>>Sale</option>
                          <option value="Adjustment" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Adjustment') echo 'selected'; ?>>Adjustment</option>
                          <option value="Waste" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Waste') echo 'selected'; ?>>Waste</option>
                          <option value="Transfer" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Transfer') echo 'selected'; ?>>Transfer</option>
                          <option value="Delete" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Delete') echo 'selected'; ?>>Delete</option>
                          <option value="Supplier Add" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Supplier Add') echo 'selected'; ?>>Supplier Add</option>
                          <option value="Supplier Update" <?php if(isset($_GET['filter_type']) && $_GET['filter_type'] == 'Supplier Update') echo 'selected'; ?>>Supplier Update</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="filter_product">Product</label>
                        <select class="form-control bg-transparent text-light border-light" id="filter_product" name="filter_product">
                          <option value="">All Products</option>
                          <?php foreach($products as $product): ?>
                            <option value="<?php echo $product->prod_id; ?>" <?php if(isset($_GET['filter_product']) && $_GET['filter_product'] == $product->prod_id) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($product->prod_name); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="text-center">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="activity_logs.php" class="btn btn-secondary">Reset</a>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <?php if($res->num_rows > 0): ?>
                <table class="table align-items-center table-flush">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" class="text-gold">Date & Time</th>
                      <th scope="col" class="text-gold">Activity</th>
                      <th scope="col" class="text-gold">Product</th>
                      <th scope="col" class="text-gold">Staff</th>
                      <th scope="col" class="text-gold">Quantity Change</th>
                      <th scope="col" class="text-gold">Stock Before</th>
                      <th scope="col" class="text-gold">Stock After</th>
                      <th scope="col" class="text-gold">Reference</th>
                      <th scope="col" class="text-gold">Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($log = $res->fetch_object()): ?>
                      <tr>
                          <td class="text-white">
                              <?php echo date('M d, Y h:i A', strtotime($log->activity_date)); ?>
                          </td>
                        <td>
                          <?php 
                            $badge_class = '';
                            switch($log->activity_type) {
                              case 'Add': 
                              case 'Supplier Add': 
                                $badge_class = 'badge-success'; break;
                              case 'Update': 
                              case 'Supplier Update': 
                                $badge_class = 'badge-primary'; break;
                              case 'Restock': $badge_class = 'badge-warning'; break;
                              case 'Delete': $badge_class = 'badge-danger'; break;
                              case 'Sale': $badge_class = 'badge-info'; break;
                              case 'Adjustment': $badge_class = 'badge-secondary'; break;
                              case 'Waste': $badge_class = 'badge-dark'; break;
                              case 'Transfer': $badge_class = 'badge-light text-dark'; break;
                              default: $badge_class = 'badge-info';
                            }
                          ?>
                          <span class="badge <?php echo $badge_class; ?>">
                            <?php echo htmlspecialchars($log->activity_type); ?>
                          </span>
                        </td>
                        <td class="text-white">
                          <?php if($log->prod_name): ?>
                            <?php echo htmlspecialchars($log->prod_name); ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($log->prod_code); ?></small>
                          <?php else: ?>
                            <span class="text-muted">Product deleted</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-white">
                          <?php echo $log->staff_name ? htmlspecialchars($log->staff_name) : '<span class="text-muted">Staff deleted</span>'; ?>
                        </td>
                        <td class="<?php echo ($log->quantity_change > 0) ? 'text-success' : 'text-danger'; ?>">
                          <?php echo ($log->quantity_change > 0) ? '+' : ''; ?><?php echo htmlspecialchars($log->quantity_change); ?>
                        </td>
                        <td class="text-white">
                          <?php echo htmlspecialchars($log->previous_quantity); ?>
                        </td>
                        <td class="text-white">
                          <?php echo htmlspecialchars($log->new_quantity); ?>
                        </td>
                        <td class="text-white">
                          <small><?php echo htmlspecialchars($log->reference_code); ?></small>
                        </td>
                        <td class="text-white">
                          <?php echo htmlspecialchars($log->notes); ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <div class="alert alert-warning text-center">
                  No activity logs found.
                </div>
              <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
              <div class="card-footer py-4" style="background: rgba(26, 26, 46, 0.9); border-top: 1px solid rgba(192, 160, 98, 0.2);">
                <nav aria-label="...">
                  <ul class="pagination justify-content-end mb-0">
                    <?php if($page > 1): ?>
                      <li class="page-item">
                        <a class="page-link text-gold" href="activity_logs.php?page=<?php echo $page-1; ?><?php 
                          if(isset($_GET['filter_type'])) echo '&filter_type='.urlencode($_GET['filter_type']); 
                          if(isset($_GET['filter_product'])) echo '&filter_product='.urlencode($_GET['filter_product']); 
                        ?>" tabindex="-1">
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
                      echo '<li class="page-item"><a class="page-link text-gold" href="activity_logs.php?page=1'.(isset($_GET['filter_type']) ? '&filter_type='.urlencode($_GET['filter_type']) : '').(isset($_GET['filter_product']) ? '&filter_product='.urlencode($_GET['filter_product']) : '').'">1</a></li>';
                      if($start_page > 2) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                      }
                    }
                    
                    for($i = $start_page; $i <= $end_page; $i++) {
                      $active = ($i == $page) ? 'active' : '';
                      echo '<li class="page-item '.$active.'">';
                      echo '<a class="page-link text-gold" href="activity_logs.php?page='.$i.(isset($_GET['filter_type']) ? '&filter_type='.urlencode($_GET['filter_type']) : '').(isset($_GET['filter_product']) ? '&filter_product='.urlencode($_GET['filter_product']) : '').'">'.$i.'</a>';
                      echo '</li>';
                    }
                    
                    if($end_page < $total_pages) {
                      if($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                      }
                      echo '<li class="page-item"><a class="page-link text-gold" href="activity_logs.php?page='.$total_pages.(isset($_GET['filter_type']) ? '&filter_type='.urlencode($_GET['filter_type']) : '').(isset($_GET['filter_product']) ? '&filter_product='.urlencode($_GET['filter_product']) : '').'">'.$total_pages.'</a></li>';
                    }
                    ?>
                    
                    <?php if($page < $total_pages): ?>
                      <li class="page-item">
                        <a class="page-link text-gold" href="activity_logs.php?page=<?php echo $page+1; ?><?php 
                          if(isset($_GET['filter_type'])) echo '&filter_type='.urlencode($_GET['filter_type']); 
                          if(isset($_GET['filter_product'])) echo '&filter_product='.urlencode($_GET['filter_product']); 
                        ?>">
                          <i class="fas fa-angle-right"></i>
                          <span class="sr-only">Next</span>
                        </a>
                      </li>
                    <?php endif; ?>
                  </ul>
                </nav>
              </div>
            <?php endif; ?>
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
    
    .badge-success {
      background-color: var(--accent-green);
    }
    
    .badge-primary {
      background-color: var(--accent-blue);
    }
    
    .badge-warning {
      background-color: var(--accent-gold);
      color: var(--text-dark);
    }
    
    .badge-danger {
      background-color: var(--accent-red);
    }
    
    .badge-info {
      background-color: var(--accent-blue);
    }
    
    .badge-secondary {
      background-color: #6c757d;
    }
    
    .badge-dark {
      background-color: #343a40;
    }
    
    .badge-light {
      background-color: #f8f9fa;
    }
    
    .modal-content {
      background: rgba(26, 26, 46, 0.95);
      border: 1px solid rgba(192, 160, 98, 0.3);
    }
    
    .modal-header {
      border-bottom: 1px solid rgba(192, 160, 98, 0.3);
    }
    
    .modal-footer {
      border-top: 1px solid rgba(192, 160, 98, 0.3);
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
    
    .btn-secondary {
      background: rgba(26, 26, 46, 0.5);
      border: 1px solid rgba(192, 160, 98, 0.3);
      color: var(--accent-gold);
    }
    
    .btn-secondary:hover {
      background: rgba(26, 26, 46, 0.7);
      color: var(--accent-gold);
      border-color: var(--accent-gold);
    }
    
    .page-item.active .page-link {
      background-color: var(--accent-gold) !important;
      border-color: var(--accent-gold) !important;
      color: var(--text-dark) !important;
    }
    
    @media (max-width: 768px) {
      .card {
        backdrop-filter: blur(4px);
      }
      
      .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
    }
  </style>
  
  <script>
    // Mobile sidebar toggle function
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    }
  </script>
</body>
</html>