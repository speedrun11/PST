<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['pay'])) {
  if (empty($_POST["pay_code"]) || empty($_POST["pay_amt"]) || empty($_POST['pay_method'])) {
    $err = "Blank Values Not Accepted";
  } else {
    $pay_code = $_POST['pay_code'];
    $order_code = $_GET['order_code'];
    $customer_id = $_GET['customer_id'];
    $pay_amt  = $_POST['pay_amt'];
    $pay_method = $_POST['pay_method'];
    $pay_id = $_POST['pay_id'];
    $order_status = $_GET['order_status'];
    $order_type = $_GET['order_type'] ?? 'dine-in';

    // Calculate the grand total securely on server side for this order_code
    $sumQry = "SELECT SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) AS grand_total FROM rpos_orders WHERE order_code = ?";
    $sumStmt = $mysqli->prepare($sumQry);
    $sumStmt->bind_param('s', $order_code);
    $sumStmt->execute();
    $sumRes = $sumStmt->get_result();
    $sumRow = $sumRes->fetch_object();
    $grand_total = $sumRow ? (float)$sumRow->grand_total : 0.0;

    // Override posted amount with computed grand total for integrity
    $pay_amt = $grand_total;

    // Insert one payment record for the whole order_code
    $postQuery = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method, order_type) VALUES(?,?,?,?,?,?,?)";
    $postStmt = $mysqli->prepare($postQuery);
    $postStmt->bind_param('sssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method, $order_type);

    // Mark all rows with this order_code as Paid
    $upQry = "UPDATE rpos_orders SET order_status = ? WHERE order_code = ?";
    $upStmt = $mysqli->prepare($upQry);
    $upStmt->bind_param('ss', $order_status, $order_code);

    $ok1 = $postStmt->execute();
    $ok2 = $upStmt->execute();
    
    if ($ok1 && $ok2) {
      $success = "Payment processed successfully for " . ucfirst($order_type) . " order!" && header("refresh:1; url=receipts.php");
    } else {
      $err = "Please Try Again Or Try Later";
    }
  }
}
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Process Payment</title>
    
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
        
        .card-header {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .form-control {
            background-color: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .form-control:focus {
            background-color: rgba(26, 26, 46, 0.8);
            border-color: var(--accent-gold);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
        }
        
        label {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
        }
        
        hr {
            border-top: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, rgba(74, 107, 87, 1), rgba(58, 86, 115, 0.8));
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%23c0a062' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        
        @media (max-width: 768px) {
            .form-row > div {
                margin-bottom: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        .form-control[readonly] {
            background-color: rgba(26, 26, 46, 0.4);
            border: 1px solid rgba(192, 160, 98, 0.2);
            color: var(--accent-gold);
            cursor: not-allowed;
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
    $order_code = $_GET['order_code'];
    // Fetch a summary for display
    $ret = "SELECT customer_name, MIN(order_type) AS order_type, 
                   SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) AS grand_total
            FROM rpos_orders 
            WHERE order_code = ?
            GROUP BY customer_name";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    $res = $stmt->get_result();
    $summary = $res->fetch_object();
    if ($summary) {
        $total = (float)$summary->grand_total;
    ?>
    
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <!-- Table -->
      <div class="row">
        <div class="col-md mx-auto">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="text-gold">Process Payment</h3>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Payment ID</label>
                    <input type="text" name="pay_id" readonly value="<?php echo $payid;?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label>Payment Code</label>
                    <input type="text" name="pay_code" value="<?php echo $mpesaCode; ?>" class="form-control" value="">
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-4">
                    <label>Amount (₱)</label>
                    <input type="text" name="pay_amt" readonly value="<?php echo number_format($total,2);?>" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label>Order Type</label>
                    <div class="order-type-display" style="padding: 10px; background: rgba(26, 26, 46, 0.8); border: 1px solid rgba(192, 160, 98, 0.3); border-radius: 5px; text-align: center;">
                      <i class="fas fa-<?php echo ($summary->order_type ?? 'dine-in') === 'takeout' ? 'shopping-bag' : 'utensils'; ?>" 
                         style="color: var(--accent-gold); margin-right: 5px;"></i>
                      <span style="color: var(--accent-gold); font-weight: 600;">
                        <?php echo ucfirst($summary->order_type ?? 'dine-in'); ?>
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label>Payment Method</label>
                    <select class="form-control" name="pay_method">
                        <option selected>Cash</option>
                        <option>GCash</option>
                    </select>
                  </div>
                </div>
                <br>
                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" name="pay" value="Pay Order" class="btn btn-success">
                  </div>
                </div>
              </form>
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
  require_once('partials/_scripts.php'); }
  ?>
</body>
</html>