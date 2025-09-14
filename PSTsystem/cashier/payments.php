<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
// Cancel entire pending order by order_code (mark as Cancelled)
if (isset($_GET['cancel_code'])) {
    $code = $_GET['cancel_code'];
    $adn = "UPDATE rpos_orders SET order_status = 'Cancelled' WHERE order_code = ? AND order_status = 'Pending'";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('s', $code);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $success = "Order cancelled" && header("refresh:1; url=payments.php");
    } else {
        $err = "Try Again Later";
    }
}
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Payment Management</title>
    
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
        
        .btn-outline-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-outline-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
            background: linear-gradient(135deg, rgba(74, 107, 87, 1), rgba(58, 86, 115, 0.8));
        }
        
        .btn-danger {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.8), rgba(120, 30, 30, 0.6));
            border: 1px solid rgba(158, 43, 43, 0.4);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, rgba(158, 43, 43, 1), rgba(120, 30, 30, 0.8));
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, rgba(74, 107, 87, 1), rgba(58, 86, 115, 0.8));
        }
        
        .text-success {
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
        }
        .text-gold {
            color: var(--accent-gold) !important;
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
        <div style="background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
        <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body">
                </div>
            </div>
        </div>
        <!-- Page content -->
        <div class="container-fluid mt--8">
            <?php if (isset($_SESSION['order_success'])): ?>
                <div class="alert alert-success" style="background: rgba(74, 107, 87, 0.2); border: 1px solid rgba(74, 107, 87, 0.4); color: #51cf66; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['order_success']; unset($_SESSION['order_success']); ?>
                </div>
            <?php endif; ?>
            <!-- Table -->
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">Order Payments</h3>
                                </div>
                                <div class="col text-right">
                                    <a href="orders.php" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus"></i> <i class="fas fa-utensils"></i>
                                        Make A New Order
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Items</th>
                                        <th scope="col">Order Type</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT order_code, customer_name, customer_id, MIN(order_type) AS order_type, MIN(created_at) AS created_at,
                                                    GROUP_CONCAT(CONCAT(prod_name,' x',prod_qty) SEPARATOR ', ') AS items,
                                                    SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) AS grand_total
                                            FROM rpos_orders
                                            WHERE order_status = 'Pending'
                                            GROUP BY order_code, customer_name, customer_id
                                            ORDER BY MIN(created_at) DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($grp = $res->fetch_object()) {
                                        $order_type = $grp->order_type ?? 'dine-in';
                                    ?>
                                        <tr>
                                            <th class="text-success" scope="row"><?php echo htmlspecialchars($grp->order_code); ?></th>
                                            <td><?php echo htmlspecialchars($grp->customer_name); ?></td>
                                            <td style="max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($grp->items); ?>"><?php echo htmlspecialchars($grp->items); ?></td>
                                            <td>
                                                <span class="badge <?php echo $order_type === 'takeout' ? 'badge-warning' : 'badge-info'; ?>" 
                                                      style="background: <?php echo $order_type === 'takeout' ? 'var(--accent-gold)' : 'var(--accent-blue)'; ?>; color: var(--text-dark);">
                                                    <i class="fas fa-<?php echo $order_type === 'takeout' ? 'shopping-bag' : 'utensils'; ?>"></i>
                                                    <?php echo ucfirst($order_type); ?>
                                                </span>
                                            </td>
                                            <td>₱ <?php echo number_format($grp->grand_total, 2); ?></td>
                                            <td><?php echo date('d/M/Y g:i', strtotime($grp->created_at)); ?></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="pay_order.php?order_code=<?php echo urlencode($grp->order_code);?>&customer_id=<?php echo urlencode($grp->customer_id);?>&order_status=Paid&order_type=<?php echo urlencode($order_type); ?>">
                                                        <button class="btn btn-sm btn-success mr-2">
                                                            <i class="fas fa-handshake"></i>
                                                            Pay Order
                                                        </button>
                                                    </a>
                                                    <a href="payments.php?cancel_code=<?php echo urlencode($grp->order_code); ?>">
                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="fas fa-window-close"></i>
                                                            Cancel Order
                                                        </button>
                                                    </a>
                                                </div>
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