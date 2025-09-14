<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Get latest 20 product activity logs (no filters, no pagination)
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
        ORDER BY l.activity_date DESC 
        LIMIT 20";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$prod_res = $stmt->get_result();
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
          <!-- Inventory Activities (Product Logs) -->
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Product Activities</h3>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <?php if($prod_res && $prod_res->num_rows > 0): ?>
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
                    <?php while ($log = $prod_res->fetch_object()): ?>
                      <tr>
                        <td class="text-white"><?php echo date('M d, Y h:i A', strtotime($log->activity_date)); ?></td>
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
                          <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($log->activity_type); ?></span>
                        </td>
                        <td class="text-white">
                          <?php if($log->prod_name): ?>
                            <?php echo htmlspecialchars($log->prod_name); ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($log->prod_code); ?></small>
                          <?php else: ?>
                            <span class="text-muted">Product deleted</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-white"><?php echo $log->staff_name ? htmlspecialchars($log->staff_name) : '<span class="text-muted">Staff deleted</span>'; ?></td>
                        <td class="<?php echo ($log->quantity_change > 0) ? 'text-success' : 'text-danger'; ?>"><?php echo ($log->quantity_change > 0 ? '+' : '') . htmlspecialchars($log->quantity_change); ?></td>
                        <td class="text-white"><?php echo htmlspecialchars($log->previous_quantity); ?></td>
                        <td class="text-white"><?php echo htmlspecialchars($log->new_quantity); ?></td>
                        <td class="text-white"><small><?php echo htmlspecialchars($log->reference_code); ?></small></td>
                        <td class="text-white"><?php echo htmlspecialchars($log->notes); ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <div class="alert alert-warning text-center">No product activity logs found.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Ingredient Activities -->
          <div class="card shadow mt-4">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Ingredient Activities</h3>
                </div>
              </div>
            </div>
            <?php
              // Load recent ingredient logs (apply type filter if present)
              $ing_filter = '';
              $ing_params = [];
              $ing_types = '';
              if(isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
                $ing_filter = ' WHERE il.activity_type = ?';
                $ing_params[] = $_GET['filter_type'];
                $ing_types .= 's';
              }
              $ing_sql = "SELECT 
                            il.log_id,
                            il.activity_type,
                            il.quantity_change,
                            il.previous_quantity,
                            il.new_quantity,
                            il.staff_id,
                            il.activity_date,
                            il.notes,
                            il.reference_code,
                            i.ingredient_name,
                            i.ingredient_unit,
                            sup.supplier_name,
                            st.staff_name
                          FROM rpos_ingredient_logs il
                          LEFT JOIN rpos_ingredients i ON il.ingredient_id = i.ingredient_id
                          LEFT JOIN rpos_suppliers sup ON i.supplier_id = sup.supplier_id
                          LEFT JOIN rpos_staff st ON il.staff_id = st.staff_id
                          $ing_filter
                          ORDER BY il.activity_date DESC
                          LIMIT 20";
              $ing_stmt = $mysqli->prepare($ing_sql);
              if ($ing_stmt) {
                if(!empty($ing_params)) {
                  $ing_stmt->bind_param($ing_types, ...$ing_params);
                }
                $ing_stmt->execute();
                $ing_res = $ing_stmt->get_result();
              } else {
                $ing_res = false;
              }
            ?>
            <div class="table-responsive">
              <?php if($ing_res && $ing_res->num_rows > 0): ?>
                <table class="table align-items-center table-flush">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" class="text-gold">Date & Time</th>
                      <th scope="col" class="text-gold">Activity</th>
                      <th scope="col" class="text-gold">Ingredient</th>
                      <th scope="col" class="text-gold">Supplier</th>
                      <th scope="col" class="text-gold">Staff</th>
                      <th scope="col" class="text-gold">Quantity Change</th>
                      <th scope="col" class="text-gold">Stock Before</th>
                      <th scope="col" class="text-gold">Stock After</th>
                      <th scope="col" class="text-gold">Reference</th>
                      <th scope="col" class="text-gold">Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($ilog = $ing_res->fetch_object()): ?>
                      <tr>
                        <td class="text-white"><?php echo date('M d, Y h:i A', strtotime($ilog->activity_date)); ?></td>
                        <td>
                          <?php 
                            $ibadge = '';
                            switch($ilog->activity_type) {
                              case 'Restock': $ibadge = 'badge-warning'; break;
                              case 'Waste': $ibadge = 'badge-dark'; break;
                              case 'Adjustment': $ibadge = 'badge-secondary'; break;
                              case 'Transfer': $ibadge = 'badge-light text-dark'; break;
                              case 'Add': $ibadge = 'badge-success'; break;
                              case 'Update': $ibadge = 'badge-primary'; break;
                              default: $ibadge = 'badge-info';
                            }
                          ?>
                          <span class="badge <?php echo $ibadge; ?>"><?php echo htmlspecialchars($ilog->activity_type); ?></span>
                        </td>
                        <td class="text-white">
                          <?php echo $ilog->ingredient_name ? htmlspecialchars($ilog->ingredient_name) : '<span class="text-muted">Ingredient deleted</span>'; ?>
                          <?php if($ilog->ingredient_unit): ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($ilog->ingredient_unit); ?></small>
                          <?php endif; ?>
                        </td>
                        <td class="text-white"><?php echo $ilog->supplier_name ? htmlspecialchars($ilog->supplier_name) : '—'; ?></td>
                        <td class="text-white"><?php echo $ilog->staff_name ? htmlspecialchars($ilog->staff_name) : '<span class="text-muted">Staff deleted</span>'; ?></td>
                        <td class="<?php echo ($ilog->quantity_change > 0) ? 'text-success' : 'text-danger'; ?>"><?php echo ($ilog->quantity_change > 0 ? '+' : '') . htmlspecialchars($ilog->quantity_change); ?></td>
                        <td class="text-white"><?php echo htmlspecialchars($ilog->previous_quantity); ?></td>
                        <td class="text-white"><?php echo htmlspecialchars($ilog->new_quantity); ?></td>
                        <td class="text-white"><small><?php echo htmlspecialchars($ilog->reference_code); ?></small></td>
                        <td class="text-white"><?php echo htmlspecialchars($ilog->notes); ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <div class="alert alert-warning text-center">
                  No ingredient activity logs found.
                </div>
              <?php endif; ?>
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