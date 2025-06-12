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
    <title>PST - Customer Dashboard</title>
    
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
            
            /* Table body styling */
            .table tbody {
                background: rgba(26, 26, 46, 0.8);
            }
            
            .table tbody td, .table tbody th {
                padding: 1rem;
                vertical-align: middle;
                border-top: 1px solid rgba(192, 160, 98, 0.1);
            }
            
            /* Table row hover effect */
            .table tbody tr {
                transition: all var(--transition-speed) ease;
            }
            
            .table tbody tr:hover {
                background: rgba(192, 160, 98, 0.1) !important;
                transform: translateX(5px);
            }
            
            /* Table container styling */
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
            <div class="col-xl-4 col-lg-6">
              <a href="orders.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Available Items</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span>
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
            <div class="col-xl-4 col-lg-6">
              <a href="orders_reports.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Orders</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $orders; ?></span>
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
            <div class="col-xl-4 col-lg-6">
              <a href="payments_reports.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Money Spend</h5>
                        <span class="h2 font-weight-bold mb-0">₱<?php echo $sales; ?></span>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape text-white rounded-circle shadow">
                          <i class="fas fa-wallet"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
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
                    <th class="text-gold" scope="col">Product</th>
                    <th scope="col">Unit Price</th>
                    <th class="text-gold" scope="col">#</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Status</th>
                    <th class="text-gold" scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $customer_id = $_SESSION['customer_id'];
                $ret = "SELECT * FROM  rpos_orders WHERE customer_id = '$customer_id' ORDER BY `rpos_orders`.`created_at` DESC LIMIT 10 ";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($order = $res->fetch_object()) {
                    $total = ($order->prod_price * $order->prod_qty);
                ?>
                    <tr>
                        <th class="text-gold" scope="row"><?php echo $order->order_code; ?></th>
                        <td><?php echo $order->customer_name; ?></td>
                        <td class="text-gold"><?php echo $order->prod_name; ?></td>
                        <td>$<?php echo $order->prod_price; ?></td>
                        <td class="text-gold"><?php echo $order->prod_qty; ?></td>
                        <td>$<?php echo $total; ?></td>
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

<!-- Recent Payments Table -->
<div class="card shadow spacing-between-tables">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">My Recent Payments</h3>
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
                $ret = "SELECT * FROM   rpos_payments WHERE customer_id ='$customer_id'   ORDER BY `rpos_payments`.`created_at` DESC LIMIT 10 ";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($payment = $res->fetch_object()) {
                ?>
                    <tr>
                        <th class="text-gold" scope="row">
                            <?php echo $payment->pay_code; ?>
                        </th>
                        <td>
                            $<?php echo $payment->pay_amt; ?>
                        </td>
                        <td class="text-gold">
                            <?php echo $payment->order_code; ?>
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
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>
</html>