<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
require_once('classes/SalesForecasting.php');

// Initialize the sales forecasting system
try {
    $forecasting = new SalesForecasting($mysqli);
    
    // Get forecast period from URL parameter
    $forecast_days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
    $forecast_days = max(7, min(90, $forecast_days)); // Limit between 7-90 days
    
    // Get all product forecasts with advanced analytics
    $forecast_data = $forecasting->getAllProductForecasts(100);
    
    // Get detailed forecast for a specific product if requested
    $detailed_forecast = null;
    $selected_product = null;
    $forecast_validation = null;
    if (isset($_GET['product_id'])) {
        $selected_product = $_GET['product_id'];
        $detailed_forecast = $forecasting->predictFutureDemand($selected_product, $forecast_days);
        
        // Get forecast validation data
        $forecast_validation = $forecasting->validateForecastAccuracy($selected_product, 7);
    }
  
  // Helper functions to fetch real external factor impacts per date
  function getWeatherImpactForDate($mysqli, $date) {
    $q = "SELECT `condition`, impact_factor FROM rpos_weather_data WHERE weather_date = ? ORDER BY weather_date DESC LIMIT 1";
    $st = $mysqli->prepare($q);
    if (!$st) return [1.0, null];
    $st->bind_param('s', $date);
    $st->execute();
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    return [$row['impact_factor'] ?? 1.0, $row['condition'] ?? null];
  }
  function getHolidayImpactForDate($mysqli, $date) {
    $q = "SELECT holiday_name, impact_factor FROM rpos_holiday_data WHERE holiday_date = ? LIMIT 1";
    $st = $mysqli->prepare($q);
    if (!$st) return [1.0, null];
    $st->bind_param('s', $date);
    $st->execute();
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    return [$row['impact_factor'] ?? 1.0, $row['holiday_name'] ?? null];
  }
  function getEconomicImpactForDate($mysqli, $date) {
    // Use the most recent indicator up to the date
    $q = "SELECT impact_factor, inflation_rate, unemployment_rate, consumer_confidence FROM rpos_economic_data WHERE data_date <= ? ORDER BY data_date DESC LIMIT 1";
    $st = $mysqli->prepare($q);
    if (!$st) return [1.0, null];
    $st->bind_param('s', $date);
    $st->execute();
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if ($row) {
      $meta = 'INF ' . ($row['inflation_rate'] ?? '-') . '%, UNEMP ' . ($row['unemployment_rate'] ?? '-') . '%, CONF ' . ($row['consumer_confidence'] ?? '-');
    } else { $meta = null; }
    return [$row['impact_factor'] ?? 1.0, $meta];
  }
  function getEventsImpactForDate($mysqli, $date) {
    $q = "SELECT event_type, event_name, impact_factor FROM rpos_local_events WHERE event_date = ?";
    $st = $mysqli->prepare($q);
    if (!$st) return [1.0, []];
    $st->bind_param('s', $date);
    $st->execute();
    $res = $st->get_result();
    $events = [];
    $factors = [];
    while ($row = ($res ? $res->fetch_assoc() : null)) {
      $events[] = trim(($row['event_type'] ?? '') . (isset($row['event_name']) && $row['event_name'] ? (': ' . $row['event_name']) : ''));
      $factors[] = (float)($row['impact_factor'] ?? 1.0);
    }
    $factor = !empty($factors) ? array_sum($factors) / count($factors) : 1.0;
    return [$factor, $events];
  }
    
    // Get historical sales data for charts
    $historical_data = [];
    if ($selected_product) {
        $historical_data = $forecasting->getHistoricalSales($selected_product, 90);
    }
    
    // Get external factors data for display
    $external_factors_data = [
        'weather' => $forecasting->getRealWeatherDataPublic() ?? [],
        'holidays' => $forecasting->getRealHolidayDataPublic() ?? [],
        'economic' => $forecasting->getRealEconomicDataPublic() ?? [],
        'events' => $forecasting->getRealLocalEventsDataPublic() ?? []
    ];
} catch (Exception $e) {
    $_SESSION['error'] = "Forecasting system error: " . $e->getMessage();
    $forecast_data = [];
    $detailed_forecast = null;
    $historical_data = [];
    $selected_product = null;
    $forecast_validation = null;
    $external_factors_data = [
        'weather' => [],
        'holidays' => [],
        'economic' => [],
        'events' => []
    ];
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
              <h6 class="h2 text-white d-inline-block mb-0">Advanced Sales Forecasting</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Advanced Forecasting</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="data_automation.php" class="btn btn-sm btn-warning mr-2">
                <i class="fas fa-magic"></i> Data Automation
              </a>
              <button class="btn btn-sm btn-info mr-2" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh Data
              </button>
              <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <span class="alert-inner--icon"><i class="ni ni-like-2"></i></span>
          <span class="alert-inner--text"><strong>Success!</strong> <?php echo $_SESSION['success']; ?></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <span class="alert-inner--icon"><i class="ni ni-support-16"></i></span>
          <span class="alert-inner--text"><strong>Error!</strong> <?php echo $_SESSION['error']; ?></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Advanced Sales Forecasting & Demand Prediction</h3>
                  <p class="text-muted">AI-powered forecasting using historical sales data and external factors</p>
                </div>
                <div class="col-4 text-right">
                  <form method="GET" class="form-inline">
                    <?php if ($selected_product): ?>
                      <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($selected_product); ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-sm">
                      <select class="form-control bg-transparent text-light border-light" name="days">
                        <option value="7" <?php echo $forecast_days == 7 ? 'selected' : ''; ?>>7 Days</option>
                        <option value="14" <?php echo $forecast_days == 14 ? 'selected' : ''; ?>>14 Days</option>
                        <option value="30" <?php echo $forecast_days == 30 ? 'selected' : ''; ?>>30 Days</option>
                        <option value="60" <?php echo $forecast_days == 60 ? 'selected' : ''; ?>>60 Days</option>
                        <option value="90" <?php echo $forecast_days == 90 ? 'selected' : ''; ?>>90 Days</option>
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
              <!-- Advanced Forecasting Controls -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-control-label text-gold">Product Analysis</label>
                    <select class="form-control bg-transparent text-light border-light" onchange="viewProductForecast(this.value)">
                      <option value="">Select Product for Detailed Analysis</option>
                      <?php 
                      $products_query = "SELECT prod_id, prod_name FROM rpos_products ORDER BY prod_name ASC";
                      $products_stmt = $mysqli->prepare($products_query);
                      $products_stmt->execute();
                      $products_result = $products_stmt->get_result();
                      while ($product = $products_result->fetch_assoc()): ?>
                        <option value="<?php echo $product['prod_id']; ?>" <?php echo $selected_product == $product['prod_id'] ? 'selected' : ''; ?>>
                          <?php echo $product['prod_name']; ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-control-label text-gold">Forecast Summary</label>
                    <div class="row text-center">
                      <div class="col-4">
                        <div class="card bg-gradient-danger text-white">
                          <div class="card-body">
                            <h4 class="mb-0"><?php echo count(array_filter($forecast_data, function($item) { return $item['urgency'] == 'critical'; })); ?></h4>
                            <small>Critical</small>
                          </div>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="card bg-gradient-warning text-white">
                          <div class="card-body">
                            <h4 class="mb-0"><?php echo count(array_filter($forecast_data, function($item) { return $item['urgency'] == 'high'; })); ?></h4>
                            <small>High Priority</small>
                          </div>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="card bg-gradient-success text-white">
                          <div class="card-body">
                            <h4 class="mb-0"><?php echo count(array_filter($forecast_data, function($item) { return $item['urgency'] == 'normal'; })); ?></h4>
                            <small>Normal</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Advanced Forecast Table -->
              <div class="table-responsive">
                <?php if (empty($forecast_data)): ?>
                  <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle"></i> No forecast data available. Please check your product data and try again.
                  </div>
                <?php else: ?>
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
                      <th scope="col" class="text-gold">Urgency</th>
                      <th scope="col" class="text-gold">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($forecast_data as $item): ?>
                      <tr>
                        <th scope="row"><?php echo htmlspecialchars($item['product_name']); ?></th>
                        <td>
                          <span class="badge badge-<?php 
                            echo $item['product_type'] == 'double' ? 'info' : 
                                ($item['product_type'] == 'combo' ? 'warning' : 'secondary'); 
                          ?>">
                            <?php echo ucfirst($item['product_type'] ?? 'regular'); ?>
                          </span>
                        </td>
                        <td><?php echo $item['current_stock']; ?></td>
                        <td><?php echo $item['avg_daily_demand']; ?></td>
                        <td><?php echo $item['days_until_stockout']; ?></td>
                        <td><?php echo $item['recommended_order_quantity']; ?></td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $item['confidence'] > 0.7 ? 'bg-success' : ($item['confidence'] > 0.4 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 style="width: <?php echo $item['confidence'] * 100; ?>%">
                              <?php echo round($item['confidence'] * 100); ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge badge-<?php 
                            echo $item['urgency'] == 'critical' ? 'danger' : 
                                ($item['urgency'] == 'high' ? 'warning' : 
                                ($item['urgency'] == 'medium' ? 'info' : 'success')); 
                          ?>">
                            <?php echo ucfirst($item['urgency']); ?>
                          </span>
                        </td>
                        <td>
                          <a href="?product_id=<?php echo $item['product_id']; ?>&days=<?php echo $forecast_days; ?>" 
                             class="btn btn-sm btn-primary">
                            <i class="fas fa-chart-line"></i> Analyze
                          </a>
                          <?php if ($item['recommended_order_quantity'] > 0): ?>
                            <a href="restock_product.php?restock=<?php echo $item['product_id']; ?>" 
                               class="btn btn-sm btn-success">
                              <i class="fas fa-plus"></i> Restock
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- External Factors Dashboard -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0 text-gold">External Factors Analysis</h3>
              <p class="text-muted">Real-time data affecting sales forecasts</p>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Weather Data -->
                <div class="col-md-3">
                  <div class="card bg-gradient-info text-white">
                    <div class="card-body">
                      <h6 class="card-title">Weather Impact</h6>
                      <h4><?php echo count($external_factors_data['weather'] ?? []); ?></h4>
                      <small>Recent Data Points</small>
                      <?php if (!empty($external_factors_data['weather'])): ?>
                        <div class="mt-2">
                          <small>Latest: <?php echo $external_factors_data['weather'][0]['condition'] ?? 'N/A'; ?></small>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                
                <!-- Holiday Data -->
                <div class="col-md-3">
                  <div class="card bg-gradient-warning text-white">
                    <div class="card-body">
                      <h6 class="card-title">Holiday Calendar</h6>
                      <h4><?php echo count($external_factors_data['holidays'] ?? []); ?></h4>
                      <small>Upcoming Holidays</small>
                      <?php 
                      $upcoming_holidays = array_filter($external_factors_data['holidays'] ?? [], function($holiday) {
                          return strtotime($holiday['holiday_date']) >= time();
                      });
                      if (!empty($upcoming_holidays)): 
                        $next_holiday = reset($upcoming_holidays);
                      ?>
                        <div class="mt-2">
                          <small>Next: <?php echo $next_holiday['holiday_name'] ?? 'N/A'; ?></small>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                
                <!-- Economic Data -->
                <div class="col-md-3">
                  <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                      <h6 class="card-title">Economic Indicators</h6>
                      <h4><?php echo count($external_factors_data['economic'] ?? []); ?></h4>
                      <small>Data Points</small>
                      <?php if (!empty($external_factors_data['economic'])): ?>
                        <div class="mt-2">
                          <small>Latest Impact: <?php echo ($external_factors_data['economic'][0]['impact_factor'] ?? 1.0) * 100; ?>%</small>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                
                <!-- Local Events -->
                <div class="col-md-3">
                  <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                      <h6 class="card-title">Local Events</h6>
                      <h4><?php echo count($external_factors_data['events'] ?? []); ?></h4>
                      <small>Recent Events</small>
                      <?php if (!empty($external_factors_data['events'])): ?>
                        <div class="mt-2">
                          <small>Latest: <?php echo $external_factors_data['events'][0]['event_type'] ?? 'N/A'; ?></small>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Product Analysis -->
      <?php if ($selected_product && $detailed_forecast): ?>
        <div class="row mt-4">
          <div class="col-12">
            <div class="card shadow">
              <div class="card-header border-0">
                <h3 class="mb-0 text-gold">Detailed Product Analysis</h3>
                <p class="text-muted">Historical sales data and future demand prediction</p>
              </div>
              <div class="card-body">
                <div class="row">
                  <!-- Historical Sales Chart -->
                  <div class="col-md-8">
                    <h5 class="text-gold">Historical Sales Trend (Last 90 Days)</h5>
                    <?php if (!empty($historical_data)): ?>
                      <canvas id="historicalChart" height="100"></canvas>
                    <?php else: ?>
                      <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No historical sales data available for this product.
                      </div>
                    <?php endif; ?>
                  </div>
                  
                  <!-- Forecast Summary -->
                  <div class="col-md-4">
                    <h5 class="text-gold">Forecast Summary</h5>
                    <div class="card bg-gradient-primary text-white">
                      <div class="card-body">
                        <h6>Next 7 Days</h6>
                        <h3><?php echo array_sum(array_slice(array_column($detailed_forecast, 'predicted_demand'), 0, 7)); ?></h3>
                        <small>Predicted Units</small>
                      </div>
                    </div>
                    <div class="card bg-gradient-info text-white mt-2">
                      <div class="card-body">
                        <h6>Next 30 Days</h6>
                        <h3><?php echo array_sum(array_column($detailed_forecast, 'predicted_demand')); ?></h3>
                        <small>Predicted Units</small>
                      </div>
                    </div>
                    <div class="card bg-gradient-success text-white mt-2">
                      <div class="card-body">
                        <h6>Average Confidence</h6>
                        <h3><?php echo round(array_sum(array_column($detailed_forecast, 'confidence')) / count($detailed_forecast) * 100); ?>%</h3>
                        <small>Forecast Accuracy</small>
                      </div>
                    </div>
                    
                    <!-- Forecast Validation -->
                    <?php if ($forecast_validation && is_array($forecast_validation)): ?>
                    <div class="card bg-gradient-warning text-white mt-2">
                      <div class="card-body">
                        <h6>Forecast Validation</h6>
                        <div class="row text-center">
                          <div class="col-6">
                            <h4><?php echo $forecast_validation['mape'] ?? 'N/A'; ?>%</h4>
                            <small>MAPE</small>
                          </div>
                          <div class="col-6">
                            <h4><?php echo ucfirst($forecast_validation['overall_rating'] ?? 'Unknown'); ?></h4>
                            <small>Rating</small>
                          </div>
                        </div>
                        <div class="mt-2">
                          <small>Based on <?php echo $forecast_validation['data_points'] ?? 0; ?> data points</small>
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
                
                <!-- Future Demand Chart -->
                <div class="row mt-4">
                  <div class="col-12">
                    <h5 class="text-gold">Future Demand Prediction (Next <?php echo $forecast_days; ?> Days)</h5>
                    <canvas id="forecastChart" height="100"></canvas>
                  </div>
                </div>
                
                <!-- External Factors Breakdown -->
                <div class="row mt-4">
                  <div class="col-12">
                    <h5 class="text-gold">External Factors Impact Analysis</h5>
                    <div class="table-responsive">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Weather</th>
                            <th>Holiday</th>
                            <th>Economic</th>
                            <th>Events</th>
                            <th>Combined Impact</th>
                            <th>Predicted Demand</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach (array_slice($detailed_forecast, 0, 14) as $forecast): 
                            $dateKey = $forecast['date'];
                            // Fetch real factors for the date
                            list($weather_factor, $weather_label) = getWeatherImpactForDate($mysqli, $dateKey);
                            list($holiday_factor, $holiday_label) = getHolidayImpactForDate($mysqli, $dateKey);
                            list($economic_factor, $economic_meta) = getEconomicImpactForDate($mysqli, $dateKey);
                            list($events_factor, $events_list) = getEventsImpactForDate($mysqli, $dateKey);
                            $external_factor = round($weather_factor * $holiday_factor * $economic_factor * $events_factor, 3);
                          ?>
                            <tr>
                              <td><?php echo date('M j', strtotime($forecast['date'])); ?></td>
                              <td>
                                <span class="badge badge-<?php echo $weather_factor > 1.0 ? 'success' : ($weather_factor < 1.0 ? 'danger' : 'secondary'); ?>" title="<?php echo htmlspecialchars($weather_label ?? 'N/A'); ?>">
                                  <?php echo round(($weather_factor - 1) * 100, 1); ?>%
                                </span>
                              </td>
                              <td>
                                <span class="badge badge-<?php echo $holiday_factor > 1.0 ? 'warning' : 'secondary'; ?>" title="<?php echo htmlspecialchars($holiday_label ?? 'None'); ?>">
                                  <?php echo round(($holiday_factor - 1) * 100, 1); ?>%
                                </span>
                              </td>
                              <td>
                                <span class="badge badge-<?php echo $economic_factor > 1.0 ? 'info' : 'secondary'; ?>" title="<?php echo htmlspecialchars($economic_meta ?? 'N/A'); ?>">
                                  <?php echo round(($economic_factor - 1) * 100, 1); ?>%
                                </span>
                              </td>
                              <td>
                                <?php $events_title = !empty($events_list) ? implode("; ", array_slice($events_list, 0, 3)) : 'None'; ?>
                                <span class="badge badge-<?php echo $events_factor > 1.0 ? 'primary' : 'secondary'; ?>" title="<?php echo htmlspecialchars($events_title); ?>">
                                  <?php echo round(($events_factor - 1) * 100, 1); ?>%
                                </span>
                              </td>
                              <td>
                                <strong class="text-<?php echo $external_factor > 1.0 ? 'success' : ($external_factor < 1.0 ? 'danger' : 'muted'); ?>">
                                  <?php echo round(($external_factor - 1) * 100, 1); ?>%
                                </strong>
                              </td>
                              <td>
                                <strong><?php echo $forecast['predicted_demand']; ?></strong>
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
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer -->
  <?php require_once('partials/_footer.php'); ?>
  
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
    
    /* Fixed sidebar styling */
    .sidebar {
      position: fixed;
      z-index: 1000;
      height: 100vh;
      overflow-y: auto;
      width: 250px;
      background: rgba(26, 26, 46, 0.95) !important;
      border-right: 1px solid rgba(192, 160, 98, 0.2);
    }
    
    /* Main content adjustments */
    .main-content {
      position: relative;
      margin-left: 250px;
      width: calc(100% - 250px);
      min-height: 100vh;
      z-index: 1;
    }
    
    /* Top navigation bar */
    .topnav {
      position: sticky;
      top: 0;
      z-index: 800;
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
    
    /* Table responsive fixes */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    
    .text-gold {
      color: var(--accent-gold) !important;
    }
    
    .text-success {
      color: var(--accent-gold) !important;
    }
    
    .text-info {
      color: var(--accent-blue) !important;
    }
    
    .critical-stock {
      color: var(--accent-red) !important;
      font-weight: 700;
    }
    
    .low-stock {
      color: #ff6b6b !important;
      font-weight: 600;
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
    
    .page-item.active .page-link {
      background-color: var(--accent-gold) !important;
      border-color: var(--accent-gold) !important;
      color: var(--text-dark) !important;
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
    
    .badge-warning {
      background-color: var(--accent-gold);
    }
    
    .badge-danger {
      background-color: var(--accent-red);
    }
    
    .nav-link {
      color: var(--text-light);
      transition: all var(--transition-speed) ease;
    }
    
    .nav-link:hover {
      color: var(--accent-gold);
    }
    
    .footer {
      background: rgba(26, 26, 46, 0.9) !important;
      border-top: 1px solid rgba(192, 160, 98, 0.2);
    }
    
    .breadcrumb {
      background-color: rgba(26, 26, 46, 0.8) !important;
    }
    
    .breadcrumb-item.active {
      color: var(--accent-gold) !important;
    }
    
    .card-footer {
      background: rgba(26, 26, 46, 0.9) !important;
      border-top: 1px solid rgba(192, 160, 98, 0.2) !important;
    }
    
    /* Progress bar styling */
    .progress {
      background-color: rgba(26, 26, 46, 0.5);
      border-radius: 10px;
    }
    
    .progress-bar {
      border-radius: 10px;
    }
    
    /* Form controls */
    .form-control {
      background-color: rgba(26, 26, 46, 0.8);
      border: 1px solid rgba(192, 160, 98, 0.3);
      color: var(--text-light);
    }
    
    .form-control:focus {
      background-color: rgba(26, 26, 46, 0.9);
      border-color: var(--accent-gold);
      box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
      color: var(--text-light);
    }
    
    .form-control::placeholder {
      color: rgba(248, 245, 242, 0.6);
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        margin-left: -100%;
        transition: all 0.3s;
      }
      
      .sidebar.show {
        margin-left: 0;
      }
      
      .main-content {
        width: 100%;
        margin-left: 0;
      }
      
      .card {
        backdrop-filter: blur(4px);
      }
      
      .breadcrumb {
        padding: 0.25rem 0.5rem;
      }
    }
  </style>
  
  <script>
    function viewProductForecast(productId) {
      if (productId) {
        window.location.href = '?product_id=' + productId + '&days=<?php echo $forecast_days; ?>';
      }
    }
    
    function updateForecastPeriod(days) {
      window.location.href = '?days=' + days + '<?php echo $selected_product ? "&product_id=" . $selected_product : ""; ?>';
    }
    
    // Mobile sidebar toggle function
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    }
    
    <?php if ($selected_product && $detailed_forecast && !empty($historical_data)): ?>
    // Historical Sales Chart
    const historicalCtx = document.getElementById('historicalChart').getContext('2d');
    const historicalData = <?php echo json_encode($historical_data); ?>;
    
    new Chart(historicalCtx, {
      type: 'line',
      data: {
        labels: historicalData.map(item => item.sale_date),
        datasets: [{
          label: 'Daily Sales',
          data: historicalData.map(item => item.daily_quantity),
          borderColor: 'rgb(75, 192, 192)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: 'white'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: 'white'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          },
          x: {
            ticks: {
              color: 'white'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          }
        }
      }
    });
    
    // Future Demand Chart
    const forecastCtx = document.getElementById('forecastChart').getContext('2d');
    const forecastData = <?php echo json_encode($detailed_forecast); ?>;
    
    new Chart(forecastCtx, {
      type: 'line',
      data: {
        labels: forecastData.map(item => item.date),
        datasets: [{
          label: 'Predicted Demand',
          data: forecastData.map(item => item.predicted_demand),
          borderColor: 'rgb(255, 99, 132)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: 'white'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: 'white'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          },
          x: {
            ticks: {
              color: 'white'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            }
          }
        }
      }
    });
    <?php endif; ?>
  </script>
</body>
</html>

