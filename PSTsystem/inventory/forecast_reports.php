<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Include the SalesForecasting class
require_once('classes/SalesForecasting.php');

// Initialize the sales forecasting system
try {
    $forecasting = new SalesForecasting($mysqli);
    
    // Get forecast period from URL parameter (support 'timeframe' or 'days')
    $forecast_days = isset($_GET['timeframe']) ? (int)$_GET['timeframe'] : (isset($_GET['days']) ? (int)$_GET['days'] : 14);
    $forecast_days = max(7, min(90, $forecast_days)); // Limit between 7-90 days

    // Get all product forecasts with advanced analytics
    $raw_forecasts = $forecasting->getAllProductForecasts(100);
    
    // Normalize keys for UI expectations with real data
    $forecast_data = array_map(function($f){
        $status = $f['urgency'] ?? 'normal';
        $status_class = 'text-success';
        if ($status === 'critical') $status_class = 'critical-stock';
        elseif ($status === 'high' || $status === 'medium') $status_class = 'low-stock';
        
        return [
            'product' => $f['product_name'] ?? 'Unknown Product',
            'current_stock' => $f['current_stock'] ?? 0,
            'daily_usage' => $f['avg_daily_demand'] ?? 0,
            'forecast_days' => $f['days_until_stockout'] ?? 0,
            'recommended_order' => $f['recommended_order_quantity'] ?? 0,
            'confidence' => $f['confidence'] ?? 0,
            'forecast_date' => date('Y-m-d'),
            'status' => $status,
            'status_class' => $status_class,
            'product_id' => $f['product_id'] ?? '',
            'product_type' => $f['product_type'] ?? 'regular'
        ];
    }, $raw_forecasts);
    
} catch (Exception $e) {
    $_SESSION['error'] = "Forecasting system error: " . $e->getMessage();
    $forecast_data = [];
    $raw_forecasts = [];
}

// Get detailed forecast for a specific product if requested
$detailed_forecast = null;
$selected_product = null;
$historical_data = [];

if (isset($_GET['product_id']) && $_GET['product_id'] !== '') {
    try {
        $selected_product = $_GET['product_id'];
        $detailed_forecast = $forecasting->predictFutureDemand($selected_product, $forecast_days);
        $historical_data = $forecasting->getHistoricalSales($selected_product, 90);
    } catch (Exception $e) {
        $_SESSION['error'] = "Error getting detailed forecast: " . $e->getMessage();
        $detailed_forecast = null;
        $historical_data = [];
    }
}

// Load product list for selector
$products_list = [];
$plist_stmt = $mysqli->prepare("SELECT prod_id, prod_name FROM rpos_products ORDER BY prod_name ASC");
if ($plist_stmt) {
    $plist_stmt->execute();
    $plist_res = $plist_stmt->get_result();
    while ($p = $plist_res->fetch_assoc()) { $products_list[] = $p; }
    $plist_stmt->close();
}

// Calculate chart data
$critical = 0;
$warning = 0;
$normal = 0;

if (!empty($forecast_data)) {
    $critical = count(array_filter($forecast_data, function($item) { 
        return ($item['status'] ?? '') == 'critical'; 
    }));
    $warning = count(array_filter($forecast_data, function($item) { 
        $s = ($item['status'] ?? '');
        return ($s == 'high' || $s == 'medium');
    }));
    $normal = count(array_filter($forecast_data, function($item) { 
        return ($item['status'] ?? '') == 'normal' || ($item['status'] ?? '') == 'low'; 
    }));
}
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>
    
    <!-- Header -->
    <div style="background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Inventory Forecast Reports</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Forecast Reports</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
              </button>
              <a href="generate_forecast.php" class="btn btn-sm btn-primary ml-2">
                <i class="fas fa-sync-alt"></i> Generate Forecast
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Display success/error messages -->
      <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Inventory Forecast Overview</h3>
                </div>
                <div class="col-4 text-right">
                  <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                      <select class="form-control bg-transparent text-light border-light" name="product_id">
                        <option value="">All Products</option>
                        <?php foreach ($products_list as $pp): ?>
                          <option value="<?php echo htmlspecialchars($pp['prod_id']); ?>" <?php if($selected_product===$pp['prod_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($pp['prod_name']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <select class="form-control bg-transparent text-light border-light ml-2" name="timeframe">
                        <option value="7" <?php if($forecast_days==7) echo 'selected'; ?>>Next 7 Days</option>
                        <option value="14" <?php if($forecast_days==14) echo 'selected'; ?>>Next 14 Days</option>
                        <option value="30" <?php if($forecast_days==30) echo 'selected'; ?>>Next 30 Days</option>
                      </select>
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                          <i class="fas fa-filter"></i>
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row">
                <!-- Forecast Summary -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Forecast Summary</h3>
                    </div>
                    <div class="card-body">
                      <div class="chart-container">
                        <canvas id="forecastChart" height="300"></canvas>
                      </div>
                      <div class="mt-3">
                        <a href="generate_forecast.php?type=summary" class="btn btn-sm btn-primary">
                          <i class="fas fa-download"></i> Download Summary
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Critical Items -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Critical Items Forecast</h3>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                          <thead class="thead-dark">
                            <tr>
                              <th scope="col" class="text-gold">Product</th>
                              <th scope="col" class="text-gold">Type</th>
                              <th scope="col" class="text-gold">Days Left</th>
                              <th scope="col" class="text-gold">Confidence</th>
                              <th scope="col" class="text-gold">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
                            $has_critical_items = false;
                            if (!empty($forecast_data)): 
                                foreach ($forecast_data as $item): 
                                    $st = ($item['status'] ?? '');
                                    if ($st === 'critical' || $st === 'high' || $st === 'medium'):
                                        $has_critical_items = true;
                            ?>
                                <tr>
                                    <th scope="row"><?php echo htmlspecialchars($item['product'] ?? 'N/A'); ?></th>
                                    <td>
                                      <span class="badge badge-<?php 
                                        echo $item['product_type'] == 'double' ? 'info' : 
                                            ($item['product_type'] == 'combo' ? 'warning' : 'secondary'); 
                                      ?>">
                                        <?php echo ucfirst($item['product_type'] ?? 'regular'); ?>
                                      </span>
                                    </td>
                                    <td>
                                      <div class="d-flex align-items-center">
                                        <span class="mr-2"><?php echo number_format($item['forecast_days'] ?? 0, 1); ?></span>
                                        <div class="progress w-100">
                                          <div class="progress-bar <?php echo ($item['forecast_days'] ?? 0) < 7 ? 'bg-danger' : (($item['forecast_days'] ?? 0) < 14 ? 'bg-warning' : 'bg-success'); ?>" 
                                               role="progressbar" style="width: <?php echo min(100, (int)(100 - min(100, ($item['forecast_days'] ?? 0) / 30 * 100))); ?>%"></div>
                                        </div>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="d-flex align-items-center">
                                        <span class="mr-2"><?php echo round(($item['confidence'] ?? 0) * 100); ?>%</span>
                                        <div class="progress w-100" style="height: 6px;">
                                          <div class="progress-bar <?php echo ($item['confidence'] ?? 0) > 0.7 ? 'bg-success' : (($item['confidence'] ?? 0) > 0.4 ? 'bg-warning' : 'bg-danger'); ?>" 
                                               style="width: <?php echo round(($item['confidence'] ?? 0) * 100); ?>%"></div>
                                        </div>
                                      </div>
                                    </td>
                                    <td class="<?php echo htmlspecialchars($item['status_class'] ?? 'text-success'); ?>">
                                        <span class="badge badge-<?php 
                                            $urgency = $item['status'] ?? 'normal';
                                            echo $urgency == 'critical' ? 'danger' : 
                                                ($urgency == 'high' ? 'warning' : 
                                                ($urgency == 'medium' ? 'info' : 
                                                ($urgency == 'low' ? 'secondary' : 'success'))); 
                                        ?>" 
                                        title="<?php echo htmlspecialchars($item['urgency_details']['reason'] ?? ''); ?>">
                                            <?php echo ucfirst($item['status'] ?? 'normal'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if (!$has_critical_items): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        No critical items at this time
                                    </td>
                                </tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                      <div class="mt-3">
                        <a href="generate_forecast.php?type=critical" class="btn btn-sm btn-primary">
                          <i class="fas fa-download"></i> Download Critical Items
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Detailed Forecast Report -->
              <div class="row mt-4">
                <div class="col-12">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <div class="row align-items-center">
                        <div class="col-8">
                          <h3 class="mb-0 text-gold">Detailed Inventory Forecast</h3>
                        </div>
                        <div class="col-4 text-right">
                          <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="forecastDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-file-export"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="forecastDropdown">
                              <a class="dropdown-item text-white" href="generate_forecast.php?type=detailed&format=pdf">
                                <i class="fas fa-file-pdf text-danger mr-2"></i> PDF
                              </a>
                              <a class="dropdown-item text-white" href="generate_forecast.php?type=detailed&format=excel">
                                <i class="fas fa-file-excel text-success mr-2"></i> Excel
                              </a>
                              <a class="dropdown-item text-white" href="generate_forecast.php?type=detailed&format=csv">
                                <i class="fas fa-file-csv text-info mr-2"></i> CSV
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                          <thead class="thead-dark">
                            <tr>
                              <th scope="col" class="text-gold">Product</th>
                              <th scope="col" class="text-gold">Type</th>
                              <th scope="col" class="text-gold">Current Stock</th>
                              <th scope="col" class="text-gold">Avg Daily Demand</th>
                              <th scope="col" class="text-gold">Days Until Stockout</th>
                              <th scope="col" class="text-gold">Recommended Order</th>
                              <th scope="col" class="text-gold">Confidence</th>
                              <th scope="col" class="text-gold">Status</th>
                              <th scope="col" class="text-gold">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (!empty($forecast_data)): ?>
                                <?php foreach ($forecast_data as $item): ?>
                                    <tr>
                                        <th scope="row"><?php echo htmlspecialchars($item['product'] ?? 'N/A'); ?></th>
                                        <td>
                                          <span class="badge badge-<?php 
                                            echo $item['product_type'] == 'double' ? 'info' : 
                                                ($item['product_type'] == 'combo' ? 'warning' : 'secondary'); 
                                          ?>">
                                            <?php echo ucfirst($item['product_type'] ?? 'regular'); ?>
                                          </span>
                                        </td>
                                        <td><?php echo number_format($item['current_stock'] ?? 0, 0); ?></td>
                                        <td><?php echo number_format($item['daily_usage'] ?? 0, 2); ?></td>
                                        <td><?php echo number_format($item['forecast_days'] ?? 0, 1); ?></td>
                                        <td><?php echo number_format($item['recommended_order'] ?? 0, 0); ?></td>
                                        <td>
                                          <div class="d-flex align-items-center">
                                            <span class="mr-2"><?php echo round(($item['confidence'] ?? 0) * 100); ?>%</span>
                                            <div class="progress w-100" style="height: 8px;">
                                              <div class="progress-bar <?php echo ($item['confidence'] ?? 0) > 0.7 ? 'bg-success' : (($item['confidence'] ?? 0) > 0.4 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                   style="width: <?php echo round(($item['confidence'] ?? 0) * 100); ?>%"></div>
                                            </div>
                                          </div>
                                        </td>
                                        <td class="<?php echo htmlspecialchars($item['status_class'] ?? 'text-success'); ?>">
                                            <span class="badge badge-<?php 
                                                $urgency = $item['status'] ?? 'normal';
                                                echo $urgency == 'critical' ? 'danger' : 
                                                    ($urgency == 'high' ? 'warning' : 
                                                    ($urgency == 'medium' ? 'info' : 
                                                    ($urgency == 'low' ? 'secondary' : 'success'))); 
                                            ?>" 
                                            title="<?php echo htmlspecialchars($item['urgency_details']['reason'] ?? ''); ?>">
                                                <?php echo ucfirst($item['status'] ?? 'normal'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="restock_product.php?restock=<?php echo urlencode($item['product_id'] ?? ''); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-arrow-up"></i> Restock
                                            </a>
                                            <?php if (!empty($item['product_id'])): ?>
                                            <a href="?product_id=<?php echo urlencode($item['product_id']); ?>&days=<?php echo (int)$forecast_days; ?>" class="btn btn-sm btn-info ml-1">
                                                <i class="fas fa-chart-line"></i> Analyze
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        No forecast data available. Please generate forecasts first.
                                    </td>
                                </tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <?php if ($selected_product && !empty($detailed_forecast)): ?>
              <!-- Selected Product: Historical and Forecast Detail -->
              <div class="row mt-4">
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Historical Sales (90 days)</h3>
                      <small class="text-muted">Includes statuses: Paid, Preparing, Ready, Completed</small>
                    </div>
                    <div class="card-body">
                      <div class="chart-container">
                        <canvas id="historicalChart" height="300"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Future Demand (next <?php echo (int)$forecast_days; ?> days)</h3>
                    </div>
                    <div class="card-body">
                      <div class="chart-container mb-3">
                        <canvas id="futureChart" height="300"></canvas>
                      </div>
                      <div class="mb-3 text-right">
                        <?php 
                          $total_pred = 0; $avg_conf = 0; $cnt = 0;
                          if (!empty($detailed_forecast)) {
                            foreach ($detailed_forecast as $dfx) { $total_pred += (float)($dfx['predicted_demand'] ?? 0); $avg_conf += (float)($dfx['confidence'] ?? 0); $cnt++; }
                          }
                          $avg_conf = $cnt ? round(($avg_conf / $cnt) * 100) : 0;
                        ?>
                        <span class="badge badge-info mr-2">Total Predicted: <?php echo number_format($total_pred, 0); ?></span>
                        <span class="badge badge-success">Avg Confidence: <?php echo (int)$avg_conf; ?>%</span>
                      </div>
                      <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                          <thead class="thead-dark">
                            <tr>
                              <th class="text-gold">Date</th>
                              <th class="text-gold">Predicted Demand</th>
                              <th class="text-gold">Confidence</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($detailed_forecast as $df): ?>
                              <tr>
                                <td class="text-white"><?php echo htmlspecialchars($df['date']); ?></td>
                                <td class="text-white"><?php echo htmlspecialchars($df['predicted_demand']); ?></td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <span class="mr-2 text-white"><?php echo (int)round(($df['confidence'] ?? 0) * 100); ?>%</span>
                                    <div class="progress w-100">
                                      <div class="progress-bar" role="progressbar" style="width: <?php echo (int)round(($df['confidence'] ?? 0) * 100); ?>%"></div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
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
      font-family: 'Open Sans', sans-serif;
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
    
    .text-gold {
      color: var(--accent-gold) !important;
    }
    
    .critical-stock {
      color: var(--accent-red) !important;
      font-weight: 700;
    }
    
    .low-stock {
      color: #ff6b6b !important;
      font-weight: 600;
    }
    
    .text-success {
      color: var(--accent-gold) !important;
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
      transform: translateX(5px);
    }
    
    .dropdown-menu {
      background-color: rgba(26, 26, 46, 0.95) !important;
      border: 1px solid rgba(192, 160, 98, 0.2) !important;
    }
    
    .dropdown-item:hover {
      background-color: rgba(192, 160, 98, 0.1) !important;
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
    
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }
    
    .progress {
      background-color: rgba(192, 160, 98, 0.2);
      height: 10px;
      border-radius: 5px;
    }
    
    .progress-bar {
      background-color: var(--accent-gold);
    }
    
    @media print {
      body * {
        visibility: hidden;
      }
      .card, .card * {
        visibility: visible;
      }
      .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
        color: black !important;
      }
      .no-print {
        display: none !important;
      }
    }
    
    @media (max-width: 768px) {
      .card {
        backdrop-filter: blur(4px);
      }
    }
  </style>
  
  <script>
    // Initialize forecast chart
    document.addEventListener('DOMContentLoaded', function() {
      const forecastCtx = document.getElementById('forecastChart').getContext('2d');
      const forecastChart = new Chart(forecastCtx, {
        type: 'doughnut',
        data: {
          labels: ['Critical Items', 'Warning Items', 'Normal Items'],
          datasets: [{
            data: [
              <?php echo "$critical, $warning, $normal"; ?>
            ],
            backgroundColor: [
              'rgba(158, 43, 43, 0.7)',
              'rgba(255, 107, 107, 0.7)',
              'rgba(192, 160, 98, 0.7)'
            ],
            borderColor: [
              'rgba(158, 43, 43, 1)',
              'rgba(255, 107, 107, 1)',
              'rgba(192, 160, 98, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: 'rgba(248, 245, 242, 0.8)',
                padding: 20
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.raw || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = Math.round((value / total) * 100);
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          },
          cutout: '70%'
        }
      });

      // Add click event to forecast chart to filter the table
      forecastChart.canvas.onclick = function(evt) {
        const activePoints = forecastChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
        if (activePoints.length > 0) {
          const clickedIndex = activePoints[0].index;
          let statusFilter = '';
          
          switch(clickedIndex) {
            case 0: statusFilter = 'critical'; break;
            case 1: statusFilter = 'warning'; break;
            case 2: statusFilter = 'normal'; break;
          }
          
          // In a real implementation, you would filter the table here
          console.log('Filter by:', statusFilter);
        }
      };
      
      // Historical chart for selected product
      <?php if ($selected_product && !empty($historical_data)): 
        $hist_labels = array_column($historical_data, 'sale_date');
        $hist_values = array_map('intval', array_column($historical_data, 'daily_quantity'));
        // Compute 7-day moving average server-side for stability
        $hist_ma = [];
        $window = 7;
        for ($i = 0; $i < count($hist_values); $i++) {
          $start = max(0, $i - $window + 1);
          $slice = array_slice($hist_values, $start, $i - $start + 1);
          $hist_ma[] = round(array_sum($slice) / max(1, count($slice)), 2);
        }
      ?>
      const histCtx = document.getElementById('historicalChart').getContext('2d');
      new Chart(histCtx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($hist_labels); ?>,
          datasets: [
            {
              label: 'Units Sold',
              data: <?php echo json_encode($hist_values); ?>,
              backgroundColor: 'rgba(192, 160, 98, 0.6)',
              borderColor: 'rgba(192, 160, 98, 1)',
              borderWidth: 1,
              yAxisID: 'y'
            },
            {
              type: 'line',
              label: '7-day Moving Avg',
              data: <?php echo json_encode($hist_ma); ?>,
              borderColor: 'rgba(58, 86, 115, 1)',
              backgroundColor: 'rgba(58, 86, 115, 0.2)',
              borderWidth: 2,
              fill: true,
              tension: 0.2,
              yAxisID: 'y'
            }
          ]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(255,255,255,0.08)' },
              ticks: { color: 'rgba(255,255,255,0.8)' }
            },
            x: {
              grid: { color: 'rgba(255,255,255,0.05)' },
              ticks: { color: 'rgba(255,255,255,0.8)' }
            }
          }
        }
      });
      <?php endif; ?>

      // Future demand chart for selected product
      <?php if ($selected_product && !empty($detailed_forecast)): 
        $f_labels = array_column($detailed_forecast, 'date');
        $f_values = array_map(function($x){ return (float)($x['predicted_demand'] ?? 0); }, $detailed_forecast);
        $f_conf = array_map(function($x){ return (float)($x['confidence'] ?? 0); }, $detailed_forecast);
        // Build confidence band as ±10% of value scaled by confidence
        $band_upper = [];
        $band_lower = [];
        for ($i=0; $i<count($f_values); $i++) {
          $delta = ($f_values[$i] * 0.1) * max(0.2, $f_conf[$i]);
          $band_upper[] = round($f_values[$i] + $delta, 2);
          $band_lower[] = round(max(0, $f_values[$i] - $delta), 2);
        }
      ?>
      const futureCtx = document.getElementById('futureChart').getContext('2d');
      new Chart(futureCtx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($f_labels); ?>,
          datasets: [
            {
              label: 'Predicted Demand',
              data: <?php echo json_encode($f_values); ?>,
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.15)',
              fill: true,
              tension: 0.2
            },
            {
              label: 'Confidence Upper',
              data: <?php echo json_encode($band_upper); ?>,
              borderColor: 'rgba(58, 115, 86, 0.0)',
              backgroundColor: 'rgba(58, 115, 86, 0.1)',
              fill: '+1',
              pointRadius: 0,
              borderWidth: 0
            },
            {
              label: 'Confidence Lower',
              data: <?php echo json_encode($band_lower); ?>,
              borderColor: 'rgba(58, 115, 86, 0.0)',
              backgroundColor: 'rgba(58, 115, 86, 0.1)',
              fill: '-1',
              pointRadius: 0,
              borderWidth: 0
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { labels: { color: 'rgba(255,255,255,0.85)' } }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(255,255,255,0.08)' },
              ticks: { color: 'rgba(255,255,255,0.8)' }
            },
            x: {
              grid: { color: 'rgba(255,255,255,0.05)' },
              ticks: { color: 'rgba(255,255,255,0.8)' }
            }
          }
        }
      });
      <?php endif; ?>
    });

    // Handle print button
    document.querySelector('.btn-print').addEventListener('click', function() {
      window.print();
    });
  </script>
</body>
</html> 