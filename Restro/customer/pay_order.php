<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (!isset($_POST['pay_code'])) {
    $_POST['pay_code'] = $mpesaCode;
}

if (isset($_POST['pay'])) {
    $pay_id = $_POST['pay_id'] ?? bin2hex(random_bytes(10));
    $pay_code = $_POST['pay_code'] ?? '';
    $pay_amt = $_POST['pay_amt'] ?? '';
    $pay_method = $_POST['pay_method'] ?? 'Cash';

    if (empty($pay_code)) {
        $err = "Payment code is required";
    } elseif(strlen($pay_code) != 10) {
        $err = "Payment Code must be exactly 10 characters";
    } elseif(empty($pay_amt)) {
        $err = "Payment amount is required";
    } else {
        $order_code = $_GET['order_code'];
        $customer_id = $_GET['customer_id'];
        $order_status = $_GET['order_status'];

        $postQuery = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) VALUES(?,?,?,?,?,?)";
        $upQry = "UPDATE rpos_orders SET order_status =? WHERE order_code =?";

        $postStmt = $mysqli->prepare($postQuery);
        $upStmt = $mysqli->prepare($upQry);

        $rc = $postStmt->bind_param('ssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method);
        $rc = $upStmt->bind_param('ss', $order_status, $order_code);

        $postStmt->execute();
        $upStmt->execute();
        
        if ($upStmt && $postStmt) {
            $success = "Paid" && header("refresh:1; url=payments_reports.php");
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
    <title>PST - Pay Order</title>
    
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
        
        .card-header {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        label {
            color: var(--accent-gold);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            background-color: rgba(26, 26, 46, 0.7);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .form-control:focus {
            background-color: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.2);
        }
        
        .form-control[readonly] {
            background-color: rgba(26, 26, 46, 0.5);
            color: var(--accent-gold);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%23c0a062' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(74, 107, 87, 0.6));
            border: 1px solid rgba(74, 107, 87, 0.4);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
            font-weight: 600;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: rgba(74, 107, 87, 0.6);
            color: var(--text-light);
        }
        
        hr {
            border-top: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .text-gold {
            color: var(--accent-gold) !important;
        }
        
        .text-danger {
            color: var(--accent-red) !important;
        }
        
        small.text-danger {
            font-size: 0.75rem;
            margin-left: 0.5rem;
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
    $ret = "SELECT * FROM  rpos_orders WHERE order_code ='$order_code' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($order = $res->fetch_object()) {
        $total = ($order->prod_price * $order->prod_qty);
    ?>
    
    <!-- Header -->
    <div class="header pb-8 pt-5 pt-md-8">
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
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3>Please Fill All Fields</h3>
            </div>
            <div class="card-body">
              <?php if (isset($err)) { ?>
                  <div class="alert alert-danger">
                      <?php echo $err; ?>
                  </div>
              <?php } ?>
              <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Payment ID</label>
                    <input type="text" name="pay_id" readonly value="<?php echo $payid;?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                      <label>Payment Code</label><small class="text-red"> System-generated 10-character code</small>
                      <input type="text" name="pay_code" maxlength="10" value="<?php echo $mpesaCode; ?>" class="form-control" required readonly>
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Amount (₱)</label>
                    <input type="text" name="pay_amt" readonly value="<?php echo $total;?>" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label>Payment Method</label>
                    <select class="form-control" name="pay_method" required>
                        <option value="Cash" selected>Cash</option>
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