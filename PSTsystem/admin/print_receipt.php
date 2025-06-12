<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>Pastil sa Pasig - Receipt</title>
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icons/favicon-16x16.png">
    <link rel="manifest" href="assets/img/icons/site.webmanifest">
    <link rel="mask-icon" href="assets/img/icons/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --border-radius: 12px;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .receipt-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .receipt-header {
            background-color: var(--primary-dark);
            color: var(--accent-gold);
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid var(--accent-gold);
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .receipt-body {
            padding: 30px;
        }

        .business-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .business-address {
            flex: 1;
            min-width: 250px;
        }

        .receipt-meta {
            flex: 1;
            text-align: right;
            min-width: 250px;
        }

        .table-container {
            margin: 30px 0;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th {
            background-color: var(--primary-dark);
            color: var(--accent-gold);
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e0e0e0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: var(--accent-gold);
            font-weight: 500;
        }

        .text-danger {
            color: var(--accent-red);
            font-weight: 500;
        }

        .total-row {
            background-color: #f9f9f9;
            font-weight: 600;
        }

        .print-section {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f7fa;
            border-radius: var(--border-radius);
        }

        .btn-print {
            background-color: var(--accent-green);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(74, 107, 87, 0.2);
        }

        .btn-print:hover {
            background-color: #3a5a46;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 107, 87, 0.3);
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .main-container {
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }

            .print-section {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .business-info {
                flex-direction: column;
                gap: 20px;
            }

            .business-address,
            .receipt-meta {
                text-align: center;
            }

            .table th,
            .table td {
                padding: 8px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>

<?php
$order_code = $_GET['order_code'];
$ret = "SELECT * FROM  rpos_orders WHERE order_code = '$order_code'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($order = $res->fetch_object()) {
    $total = ($order->prod_price * $order->prod_qty);
?>

<body>
    <div class="main-container">
        <div id="Receipt" class="receipt-container">
            <div class="receipt-header">
                <h1>Order Receipt</h1>
            </div>
            
            <div class="receipt-body">
                <div class="business-info">
                    <div class="business-address">
                        <h3 style="color: var(--primary-dark); margin-bottom: 10px;">Pastil sa Pasig - Pagasa</h3>
                        <p style="margin: 5px 0;">29 Pag-asa Street</p>
                        <p style="margin: 5px 0;">Pasig City, 1606 Metro Manila</p>
                        <p style="margin: 5px 0;">+63 997 369 5988</p>
                    </div>
                    
                    <div class="receipt-meta">
                        <p style="margin: 5px 0;"><strong>Date:</strong> <?php echo date('d/M/Y g:i A', strtotime($order->created_at)); ?></p>
                        <p style="margin: 5px 0;"><strong class="text-success">Receipt #:</strong> <?php echo $order->order_code; ?></p>
                        <p style="margin: 5px 0;"><strong>Customer:</strong> <?php echo $order->customer_name; ?></p>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php echo $order->prod_name; ?></strong></td>
                                <td class="text-center"><?php echo $order->prod_qty; ?></td>
                                <td class="text-center">₱<?php echo number_format($order->prod_price, 2); ?></td>
                                <td class="text-center">₱<?php echo number_format($total, 2); ?></td>
                            </tr>
                            
                            <tr class="total-row">
                                <td colspan="2"></td>
                                <td class="text-right"><strong>Subtotal:</strong></td>
                                <td class="text-center">₱<?php echo number_format($total, 2); ?></td>
                            </tr>
                            
                            <tr class="total-row">
                                <td colspan="2"></td>
                                <td class="text-right"><strong>Grand Total:</strong></td>
                                <td class="text-center text-danger"><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #e0e0e0;">
                    <p style="color: #666; font-style: italic;">Thank you for your order!</p>
                    <p style="margin-top: 10px;">For inquiries, please contact us at +63 997 369 5988</p>
                </div>
            </div>
        </div>
        
        <div class="print-section">
            <button id="print" onclick="printContent('Receipt');" class="btn-print">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
    </div>
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function printContent(el) {
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    }
</script>
<?php } ?>