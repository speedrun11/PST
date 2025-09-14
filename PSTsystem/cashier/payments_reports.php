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
    <title>PST - Payments Reports</title>
    
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

        .payment-method-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .payment-method-cash {
            background: var(--accent-green);
            color: var(--text-light);
        }
        
        .payment-method-gcash {
            background: var(--accent-blue);
            color: var(--text-light);
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
        
        .amount-display {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--accent-green);
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
            
            .card-header {
                padding: 1rem;
            }
            
            .card-header h3 {
                font-size: 1.2rem;
            }
            
            .amount-display {
                font-size: 1rem;
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
            
            .payment-method-badge {
                font-size: 0.65rem;
                padding: 0.3rem 0.6rem;
            }
            
            .amount-display {
                font-size: 0.9rem;
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
            // Get payment statistics
            $totalPayments = $mysqli->query("SELECT COUNT(*) as total FROM rpos_payments")->fetch_object()->total;
            $totalAmount = $mysqli->query("SELECT SUM(pay_amt) as total FROM rpos_payments")->fetch_object()->total ?? 0;
            $cashPayments = $mysqli->query("SELECT COUNT(*) as total FROM rpos_payments WHERE pay_method = 'Cash'")->fetch_object()->total;
            $gcashPayments = $mysqli->query("SELECT COUNT(*) as total FROM rpos_payments WHERE pay_method = 'GCash'")->fetch_object()->total;
            ?>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($totalPayments); ?></div>
                    <div class="stat-label">Total Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">₱<?php echo number_format($totalAmount, 2); ?></div>
                    <div class="stat-label">Total Amount</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($cashPayments); ?></div>
                    <div class="stat-label">Cash Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($gcashPayments); ?></div>
                    <div class="stat-label">GCash Payments</div>
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
                                        <i class="fas fa-credit-card text-gold"></i>
                                        Payment Records
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-gold" scope="col">
                                            <i class="fas fa-hashtag"></i> Payment Code
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-credit-card"></i> Method
                                        </th>
                                        <th class="text-gold" scope="col">
                                            <i class="fas fa-shopping-cart"></i> Order Code
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-money-bill-wave"></i> Amount
                                        </th>
                                        <th class="text-gold" scope="col">
                                            <i class="fas fa-calendar"></i> Date Paid
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Query to get ALL payments of all time
                                    $ret = "SELECT * FROM rpos_payments ORDER BY `created_at` DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($payment = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <th class="text-gold" scope="row">
                                                <strong><?php echo $payment->pay_code; ?></strong>
                                            </th>
                                            <td>
                                                <span class="payment-method-badge <?php echo strtolower($payment->pay_method) === 'cash' ? 'payment-method-cash' : 'payment-method-gcash'; ?>">
                                                    <i class="fas fa-<?php echo strtolower($payment->pay_method) === 'cash' ? 'money-bill' : 'mobile-alt'; ?>"></i>
                                                    <?php echo $payment->pay_method; ?>
                                                </span>
                                            </td>
                                            <td class="text-gold">
                                                <div class="font-weight-bold"><?php echo $payment->order_code; ?></div>
                                            </td>
                                            <td>
                                                <div class="amount-display">
                                                    ₱<?php echo number_format($payment->pay_amt, 2); ?>
                                                </div>
                                            </td>
                                            <td class="text-gold">
                                                <div class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($payment->created_at)); ?>
                                                </div>
                                                <small class="text-gold">
                                                    <?php echo date('g:i A', strtotime($payment->created_at)); ?>
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