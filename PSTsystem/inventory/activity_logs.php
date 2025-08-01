<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Pagination configuration
$results_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$start_from = ($page-1) * $results_per_page;

// Build search query
$search = '';
$params = [];
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = "%".trim($_GET['search'])."%";
    $search = " WHERE activity_type LIKE ? OR reference_code LIKE ? OR notes LIKE ?";
    $params = [$search_term, $search_term, $search_term];
}

// Get total number of logs
$count_query = "SELECT COUNT(*) AS total FROM rpos_inventory_logs $search";
$count_stmt = $mysqli->prepare($count_query);
if(!empty($params)) {
    $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_logs = $count_row['total'];
$total_pages = ceil($total_logs / $results_per_page);

// Get logs with pagination
$ret = "SELECT l.*, p.prod_name, s.staff_name 
        FROM rpos_inventory_logs l
        LEFT JOIN rpos_products p ON l.product_id = p.prod_id
        LEFT JOIN rpos_staff s ON l.staff_id = s.staff_id
        $search
        ORDER BY activity_date DESC LIMIT ?, ?";
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
              <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Logs
              </button>
              <a href="export_logs.php" class="btn btn-sm btn-primary ml-2">
                <i class="fas fa-file-export"></i> Export
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
                  <h3 class="mb-0 text-gold">Recent Inventory Activities</h3>
                </div>
                <div class="col-4 text-right">
                  <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                      <input type="text" name="search" class="form-control bg-transparent text-light border-light" placeholder="Search logs..." value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>">
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
                    <th scope="col" class="text-gold">Date & Time</th>
                    <th scope="col" class="text-gold">Activity</th>
                    <th scope="col" class="text-gold">Product</th>
                    <th scope="col" class="text-gold">Qty Change</th>
                    <th scope="col" class="text-gold">Staff</th>
                    <th scope="col" class="text-gold">Reference</th>
                    <th scope="col" class="text-gold">Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if($res->num_rows > 0): ?>
                    <?php while ($log = $res->fetch_object()): ?>
                      <tr>
                        <td><?php echo date('M d, Y H:i', strtotime($log->activity_date)); ?></td>
                        <td>
                          <?php 
                          $badge_class = '';
                          switch($log->activity_type) {
                            case 'Restock': $badge_class = 'badge-success'; break;
                            case 'Sale': $badge_class = 'badge-primary'; break;
                            case 'Adjustment': $badge_class = 'badge-warning'; break;
                            case 'Delete': $badge_class = 'badge-danger'; break;
                            default: $badge_class = 'badge-info';
                          }
                          ?>
                          <span class="badge <?php echo $badge_class; ?>"><?php echo $log->activity_type; ?></span>
                        </td>
                        <td><?php echo $log->prod_name ? htmlspecialchars($log->prod_name) : 'N/A'; ?></td>
                        <td>
                          <?php if($log->quantity_change > 0): ?>
                            <span class="text-success">+<?php echo $log->quantity_change; ?></span>
                          <?php else: ?>
                            <span class="text-danger"><?php echo $log->quantity_change; ?></span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($log->staff_name); ?></td>
                        <td><?php echo htmlspecialchars($log->reference_code); ?></td>
                        <td><?php echo $log->notes ? htmlspecialchars(substr($log->notes, 0, 30)) . (strlen($log->notes) > 30 ? '...' : '') : '—'; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center text-white py-4">No activity logs found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer py-4" style="background: rgba(26, 26, 46, 0.9); border-top: 1px solid rgba(192, 160, 98, 0.2);">
              <nav aria-label="...">
                <ul class="pagination justify-content-end mb-0">
                  <?php if($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="activity_logs.php?page=<?php echo $page-1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>" tabindex="-1">
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
                    echo '<li class="page-item"><a class="page-link text-gold" href="activity_logs.php?page=1'.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">1</a></li>';
                    if($start_page > 2) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                  }
                  
                  for($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item '.$active.'">';
                    echo '<a class="page-link text-gold" href="activity_logs.php?page='.$i.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$i.'</a>';
                    echo '</li>';
                  }
                  
                  if($end_page < $total_pages) {
                    if($end_page < $total_pages - 1) {
                      echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link text-gold" href="activity_logs.php?page='.$total_pages.(isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '').'">'.$total_pages.'</a></li>';
                  }
                  ?>
                  
                  <?php if($page < $total_pages): ?>
                    <li class="page-item">
                      <a class="page-link text-gold" href="activity_logs.php?page=<?php echo $page+1; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>">
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
    
    .badge-success {
      background-color: var(--accent-green) !important;
    }
    
    .badge-primary {
      background-color: var(--accent-blue) !important;
    }
    
    .badge-warning {
      background-color: var(--accent-gold) !important;
      color: var(--text-dark) !important;
    }
    
    .badge-danger {
      background-color: var(--accent-red) !important;
    }
    
    .badge-info {
      background-color: var(--accent-blue) !important;
    }
    
    .text-success {
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
    
    .page-item.active .page-link {
      background-color: var(--accent-gold) !important;
      border-color: var(--accent-gold) !important;
      color: var(--text-dark) !important;
    }
    
    @media print {
      body * {
        visibility: hidden;
      }
      .card, .card * {
        visibility: visible;
      }
      .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
        color: black !important;
      }
      .no-print {
        display: none !important;
      }
    }
    
    @media (max-width: 768px) {
      .card {
        backdrop-filter: blur(4px);
      }
      
      .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      .table thead th, .table tbody td {
        white-space: nowrap;
      }
    }
  </style>
  
  <script>
    // Handle print button
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelector('.btn-print').addEventListener('click', function() {
        window.print();
      });
      
      // Add click event to view full note
      document.querySelectorAll('[data-toggle="note-popover"]').forEach(element => {
        new bootstrap.Popover(element, {
          container: 'body',
          trigger: 'click',
          placement: 'top',
          html: true,
          content: function() {
            return $(this).attr('data-content');
          }
        });
      });
    });
  </script>
</body>
</html>