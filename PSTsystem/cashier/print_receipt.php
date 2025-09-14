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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PST - Point Of Sale</title>
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../admin/assets/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../admin/assets/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../admin/assets/img/icons/favicon-16x16.png">
    <link rel="manifest" href="../admin/assets/img/icons/site.webmanifest">
    <link rel="mask-icon" href="../admin/assets/img/icons/safari-pinned-tab.svg" color="#5bbad5">
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .well {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            padding: 20px;
        }

        .receipt-header {
            background-color: var(--primary-dark);
            color: var(--accent-gold);
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid var(--accent-gold);
            margin-bottom: 20px;
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--accent-gold);
        }

        address {
            margin-bottom: 20px;
            font-style: normal;
        }

        address strong {
            color: var(--primary-dark);
            font-size: 1.2rem;
            margin-bottom: 10px;
            display: block;
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

        .btn-success {
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
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(74, 107, 87, 0.2);
            width: 100%;
        }

        .btn-success:hover {
            background-color: #3a5a46;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 107, 87, 0.3);
        }

        @media print {
            @page {
                size: auto;
                margin: 0.5in;
            }
            /* Hide any automatic link URLs appended by some print styles */
            a[href]:after {
                content: none !important;
            }
            /* Hide generic header/footer elements if present */
            header, footer {
                display: none !important;
            }
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                padding: 0;
            }

            .well {
                box-shadow: none;
                border-radius: 0;
                margin: 0;
            }

            .btn-success {
                display: none;
            }
        }

        @media (max-width: 768px) {
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
// Fetch group header details and totals
$sumQry = "SELECT customer_name, MIN(created_at) AS created_at,
                  MIN(order_type) AS order_type,
                  SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) AS grand_total
           FROM rpos_orders
           WHERE order_code = ?
           GROUP BY customer_name";
$sumStmt = $mysqli->prepare($sumQry);
$sumStmt->bind_param('s', $order_code);
$sumStmt->execute();
$sumRes = $sumStmt->get_result();
$summary = $sumRes->fetch_object();

// Fetch line items
$itemsQry = "SELECT prod_name, prod_qty, prod_price, COALESCE(additional_charge,0) AS additional_charge
             FROM rpos_orders WHERE order_code = ? ORDER BY created_at ASC";
$itemsStmt = $mysqli->prepare($itemsQry);
$itemsStmt->bind_param('s', $order_code);
$itemsStmt->execute();
$itemsRes = $itemsStmt->get_result();
?>

<body>
    <div class="container">
        <div class="row">
            <div id="Receipt" class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                <div class="receipt-header">
                    <h2>Order Receipt</h2>
                </div>
                
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <address>
                            <strong>Pastil sa Pasig - Pagasa</strong>
                            <br>
                            29 Pag-asa Street
                            <br>
                            Pasig City, 1606 Metro Manila
                            <br>
                            +63 997 369 5988
                        </address>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                        <p>
                            <em>Date: <?php echo date('d/M/Y g:i', strtotime($summary->created_at)); ?></em>
                        </p>
                        <p>
                            <em class="text-success">Receipt #: <?php echo htmlspecialchars($order_code); ?></em>
                        </p>
                        <p>
                            <em>Customer: <?php echo htmlspecialchars($summary->customer_name); ?></em>
                        </p>
                        <p>
                            <em>Order Type: 
                                <span style="font-weight:600; color: <?php echo ($summary->order_type==='takeout'?'#c0a062':'#3a5673'); ?>;">
                                    <?php echo ucfirst($summary->order_type ?? 'dine-in'); ?>
                                </span>
                            </em>
                        </p>
                    </div>
                </div>
                
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
                        <?php
                          $baseSubtotal = 0;
                          $additionalTotal = 0;
                          while ($row = $itemsRes->fetch_object()) {
                            $lineBase = ($row->prod_price * $row->prod_qty);
                            $lineAdditional = (float)$row->additional_charge;
                            $lineTotal = $lineBase + $lineAdditional;
                            $baseSubtotal += $lineBase;
                            $additionalTotal += $lineAdditional;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row->prod_name); ?></strong></td>
                            <td class="text-center"><?php echo (int)$row->prod_qty; ?></td>
                            <td class="text-center">₱<?php echo number_format($row->prod_price, 2); ?></td>
                            <td class="text-center">₱<?php echo number_format($lineTotal, 2); ?></td>
                        </tr>
                        <?php if ($lineAdditional > 0): ?>
                        <tr>
                            <td colspan="4" style="padding-top:0; padding-bottom:10px;">
                                <small style="color:#666;">Includes takeout additional charge of ₱<?php echo number_format($lineAdditional,2); ?> (₱1 per qualifying item).</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php } ?>
                        <tr>
                            <td colspan="2"></td>
                            <td class="text-right">
                                <p><strong>Subtotal (Items):</strong></p>
                            </td>
                            <td class="text-center">
                                <p><strong>₱<?php echo number_format($baseSubtotal, 2); ?></strong></p>
                            </td>
                        </tr>
                        <?php if (($summary->order_type ?? 'dine-in') === 'takeout' && $additionalTotal > 0): ?>
                        <tr>
                            <td colspan="2"></td>
                            <td class="text-right">
                                <p><strong>Takeout Additional Charges:</strong></p>
                            </td>
                            <td class="text-center">
                                <p><strong>₱<?php echo number_format($additionalTotal, 2); ?></strong></p>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="2"></td>
                            <td class="text-right">
                                <h4><strong>Grand Total:</strong></h4>
                            </td>
                            <td class="text-center text-danger">
                                <h4><strong>₱<?php echo number_format((float)$summary->grand_total, 2); ?></strong></h4>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if (($summary->order_type ?? 'dine-in') === 'takeout'): ?>
                <div style="margin-top:10px; color:#666;">
                    <small>Note: Takeout orders include an additional ₱1 per Double variants and Regular + Spicy/Combo items.</small>
                </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #e0e0e0;">
                    <p style="color: #666; font-style: italic;">Thank you for your order!</p>
                    <p style="margin-top: 10px;">For inquiries, please contact us at +63 997 369 5988</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                <button id="print" onclick="printContent('Receipt');" class="btn btn-success">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
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
