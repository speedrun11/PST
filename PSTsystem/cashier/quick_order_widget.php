<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

// Get today's orders count (distinct order codes) for Paid/Preparing/Ready/Completed
$recent_orders_query = "SELECT COUNT(DISTINCT order_code) as count
                        FROM rpos_orders
                        WHERE order_status IN ('Paid','Preparing','Ready','Completed')
                          AND created_at >= CURDATE()
                          AND created_at < (CURDATE() + INTERVAL 1 DAY)";
$recent_stmt = $mysqli->prepare($recent_orders_query);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
$recent_orders = $recent_result->fetch_object()->count;

// Get pending orders count
$pending_orders_query = "SELECT COUNT(*) as count FROM rpos_orders WHERE order_status = ''";
$pending_stmt = $mysqli->prepare($pending_orders_query);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_orders = $pending_result->fetch_object()->count;

// Get today's revenue including additional charges for Paid/Preparing/Ready/Completed
$revenue_query = "SELECT SUM((prod_price * prod_qty) + COALESCE(additional_charge,0)) as revenue
                  FROM rpos_orders
                  WHERE order_status IN ('Paid','Preparing','Ready','Completed')
                    AND created_at >= CURDATE()
                    AND created_at < (CURDATE() + INTERVAL 1 DAY)";
$revenue_stmt = $mysqli->prepare($revenue_query);
$revenue_stmt->execute();
$revenue_result = $revenue_stmt->get_result();
$today_revenue = $revenue_result->fetch_object()->revenue ?: 0;
?>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Today's Orders</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $recent_orders; ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="ni ni-bag-17"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Today's Revenue</h5>
                        <span class="h2 font-weight-bold mb-0">₱ <?php echo number_format($today_revenue, 2); ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                            <i class="ni ni-money-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Quick Actions</h5>
                        <div class="mt-2">
                            <a href="orders.php" class="btn btn-sm btn-primary">
                                <i class="ni ni-bag-17"></i> New Order
                            </a>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="ni ni-settings-gear-65"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
