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
    <title>PST - Receipts Management</title>
    
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
        
        .btn-primary {
            background: linear-gradient(135deg, rgba(58, 86, 115, 0.8), rgba(74, 107, 87, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
            background: linear-gradient(135deg, rgba(58, 86, 115, 1), rgba(74, 107, 87, 0.8));
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
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-size: 1.25rem;
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
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
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
        <div style="background-image: url(assets/img/theme/pastil.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
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
                            <div class="col">
                                <h3 class="mb-0">Paid Orders</h3>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th class="text-success" scope="col">Customer</th>
                                        <th scope="col">Product</th>
                                        <th class="text-success" scope="col">Unit Price</th>
                                        <th scope="col">Qty</th>
                                        <th class="text-success" scope="col">Total Price</th>
                                        <th scope="col">Date</th>
                                        <th class="text-success" scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM  rpos_orders WHERE order_status = 'Paid' ORDER BY `rpos_orders`.`created_at` DESC  ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($order = $res->fetch_object()) {
                                        $total = ($order->prod_price * $order->prod_qty);

                                    ?>
                                        <tr>
                                            <th class="text-success" scope="row"><?php echo $order->order_code; ?></th>
                                            <td><?php echo $order->customer_name; ?></td>
                                            <td class="text-success"><?php echo $order->prod_name; ?></td>
                                            <td>₱ <?php echo $order->prod_price; ?></td>
                                            <td class="text-success"><?php echo $order->prod_qty; ?></td>
                                            <td>₱ <?php echo $total; ?></td>
                                            <td><?php echo date('d/M/Y g:i', strtotime($order->created_at)); ?></td>
                                            <td>
                                                <a target="_blank" href="print_receipt.php?order_code=<?php echo $order->order_code; ?>">
                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-print"></i>
                                                        Print Receipt
                                                    </button>
                                                </a>
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