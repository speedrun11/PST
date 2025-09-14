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
    <title>PST - Orders Reports</title>
    
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
            line-height: 1.6;
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
            border-radius: 15px;
            backdrop-filter: blur(8px);
            transition: all var(--transition-speed) ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(192, 160, 98, 0.4);
        }
        
        .table {
            color: var(--text-light);
            margin-bottom: 0;
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
        
        .text-gold {
            color: var(--accent-gold) !important;
        }
        
        .text-success {
            color: var(--accent-green) !important;
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
            white-space: nowrap;
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
            padding: 1.5rem;
        }

        .badge-danger {
            background-color: var(--accent-red);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: var(--accent-green);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: #ff9f43;
            color: var(--text-dark);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .order-type-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .order-items {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
        }
        
        .order-items:hover {
            white-space: normal;
            overflow: visible;
            background: rgba(192, 160, 98, 0.1);
            padding: 0.5rem;
            border-radius: 5px;
            position: relative;
            z-index: 10;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-speed) ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent-gold);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-gold);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .table-responsive {
            border-radius: 0 0 15px 15px;
            overflow: hidden;
        }
        
        /* Enhanced Mobile Responsiveness */
        @media (max-width: 1200px) {
            .table thead th {
                font-size: 0.8rem;
                padding: 0.8rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .table thead th {
                font-size: 0.7rem;
                padding: 0.6rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.6rem;
                font-size: 0.8rem;
            }
            
            .order-items {
                max-width: 150px;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .card-header h3 {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 576px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table thead th {
                font-size: 0.65rem;
                padding: 0.5rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
            
            .order-items {
                max-width: 100px;
            }
            
            .badge-danger, .badge-success, .badge-warning {
                font-size: 0.7rem;
                padding: 0.3rem 0.6rem;
            }
            
            .order-type-badge {
                font-size: 0.65rem;
                padding: 0.3rem 0.6rem;
            }
        }
        
        /* Print Styles */
        @media print {
            .card {
                box-shadow: none;
                border: 1px solid #ccc;
            }
            
            .table tbody tr:hover {
                background: transparent !important;
                transform: none !important;
            }
            
            .stats-cards {
                display: none;
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
      <!-- Statistics Cards -->
      <?php
      // Get statistics
      $totalOrders = $mysqli->query("SELECT COUNT(DISTINCT order_code) as total FROM rpos_orders")->fetch_object()->total;
      $totalRevenue = $mysqli->query("SELECT SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) as total FROM rpos_orders WHERE order_status = 'Paid'")->fetch_object()->total ?? 0;
      $pendingOrders = $mysqli->query("SELECT COUNT(DISTINCT order_code) as total FROM rpos_orders WHERE order_status = 'Pending'")->fetch_object()->total;
      $paidOrders = $mysqli->query("SELECT COUNT(DISTINCT order_code) as total FROM rpos_orders WHERE order_status = 'Paid'")->fetch_object()->total;
      ?>
      
      <div class="stats-cards">
        <div class="stat-card">
          <div class="stat-number"><?php echo number_format($totalOrders); ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo number_format($pendingOrders); ?></div>
          <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo number_format($paidOrders); ?></div>
          <div class="stat-label">Paid Orders</div>
        </div>
      </div>
      
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">
                    <i class="fas fa-chart-line text-gold"></i>
                    Orders Records
                  </h3>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th class="text-gold" scope="col">
                      <i class="fas fa-hashtag"></i> Code
                    </th>
                    <th scope="col">
                      <i class="fas fa-user"></i> Customer
                    </th>
                    <th class="text-gold" scope="col">
                      <i class="fas fa-shopping-cart"></i> Items
                    </th>
                    <th scope="col">
                      <i class="fas fa-tag"></i> Type
                    </th>
                    <th scope="col">
                      <i class="fas fa-money-bill-wave"></i> Total
                    </th>
                    <th scope="col">
                      <i class="fas fa-info-circle"></i> Status
                    </th>
                    <th scope="col">
                      <i class="fas fa-calendar"></i> Date
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT order_code, customer_name, 
                                  GROUP_CONCAT(CONCAT(prod_name,' x',prod_qty) SEPARATOR ', ') AS items,
                                  SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) AS total_amount,
                                  MIN(order_status) AS order_status,
                                  MIN(order_type) AS order_type,
                                  MIN(created_at) AS created_at
                           FROM rpos_orders
                           GROUP BY order_code, customer_name
                           ORDER BY MIN(created_at) DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($order = $res->fetch_object()) {

                  ?>
                    <tr>
                      <th class="text-gold" scope="row">
                        <strong><?php echo $order->order_code; ?></strong>
                      </th>
                      <td>
                        <div class="font-weight-bold"><?php echo htmlspecialchars($order->customer_name); ?></div>
                      </td>
                      <td class="text-gold order-items" title="<?php echo htmlspecialchars($order->items); ?>">
                        <?php echo htmlspecialchars($order->items); ?>
                      </td>
                      <td>
                        <span class="order-type-badge" style="background: <?php echo ($order->order_type==='takeout'?'var(--accent-gold)':'var(--accent-blue)'); ?>; color: var(--text-dark);">
                          <i class="fas fa-<?php echo ($order->order_type==='takeout'?'shopping-bag':'utensils'); ?>"></i>
                          <?php echo ucfirst($order->order_type ?? 'dine-in'); ?>
                        </span>
                      </td>
                      <td>
                        <div class="font-weight-bold text-success">
                          ₱<?php echo number_format($order->total_amount, 2); ?>
                        </div>
                      </td>
                      <td>
                        <?php
                          $status = trim($order->order_status);
                          if ($status === '' || strtolower($status) === 'pending' || strtolower($status) === 'not paid') {
                            echo "<span class='badge-warning'>Pending</span>";
                          } elseif (strtolower($status) === 'paid' || strtolower($status) === 'completed') {
                            echo "<span class='badge-success'>".htmlspecialchars($status)."</span>";
                          } elseif (strtolower($status) === 'cancelled') {
                            echo "<span class='badge-danger'>Cancelled</span>";
                          } else {
                            echo "<span class='badge-warning'>".htmlspecialchars($status)."</span>";
                          }
                        ?>
                      </td>
                      <td>
                        <div class="text-muted">
                          <?php echo date('M d, Y', strtotime($order->created_at)); ?>
                        </div>
                        <small class="text-gold">
                          <?php echo date('g:i A', strtotime($order->created_at)); ?>
                        </small>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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
</body>
</html>