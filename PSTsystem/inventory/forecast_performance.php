<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
require_once('classes/ForecastValidation.php');

// Initialize the forecast validation system
try {
    $validation = new ForecastValidation($mysqli);
    
    // Optional: Recalculate and persist validation using real sales/forecast data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revalidate') {
        $products = [];
        $ps = $mysqli->prepare("SELECT prod_id FROM rpos_products ORDER BY prod_name ASC");
        if ($ps) {
            $ps->execute();
            $rs = $ps->get_result();
            while ($row = $rs->fetch_assoc()) { $products[] = $row['prod_id']; }
            $ps->close();
        }
        $saved = 0; $errors = 0;
        foreach ($products as $pid) {
            try {
                $results = $validation->validateModel($pid); // uses real historical sales via SalesForecasting
                if ($results) {
                    $validation->storeValidationResults($pid, $results);
                    $saved++;
                }
            } catch (Exception $ex) { $errors++; }
        }
        $_SESSION['success'] = "Validation updated: $saved models saved" . ($errors ? ", $errors errors" : "");
    }
    
    // Get performance dashboard data
    $performance_data = $validation->getPerformanceDashboard();
    
    // Get detailed performance for selected product if requested
    $detailed_performance = null;
    $selected_product_id = null;
    if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
        $selected_product_id = $_GET['product_id'];
        $detailed_performance = $validation->getDetailedPerformance($selected_product_id);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Performance validation error: " . $e->getMessage();
    $performance_data = [];
    $detailed_performance = null;
}

// Calculate overall statistics
$total_products = count($performance_data);
$excellent_models = count(array_filter($performance_data, function($item) { return $item['overall_rating'] == 'excellent'; }));
$good_models = count(array_filter($performance_data, function($item) { return $item['overall_rating'] == 'good'; }));
$fair_models = count(array_filter($performance_data, function($item) { return $item['overall_rating'] == 'fair'; }));
$poor_models = count(array_filter($performance_data, function($item) { return $item['overall_rating'] == 'poor'; }));

$avg_accuracy_7 = $total_products > 0 ? array_sum(array_column($performance_data, 'accuracy_7_days')) / $total_products : 0;
$avg_accuracy_14 = $total_products > 0 ? array_sum(array_column($performance_data, 'accuracy_14_days')) / $total_products : 0;
$avg_accuracy_30 = $total_products > 0 ? array_sum(array_column($performance_data, 'accuracy_30_days')) / $total_products : 0;
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
              <h6 class="h2 text-white d-inline-block mb-0">Forecast Performance Dashboard</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Forecast Performance</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="revalidate">
                <button type="submit" class="btn btn-sm btn-info mr-2" title="Recalculate validation from real sales">
                  <i class="fas fa-calculator"></i> Recalculate & Save
                </button>
              </form>
              <button class="btn btn-sm btn-primary" onclick="refreshPerformance()">
                <i class="fas fa-sync-alt"></i> Refresh Data
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Performance Summary Cards -->
      <div class="row">
        <div class="col-xl-3 col-lg-6">
          <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Total Models</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo $total_products; ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                    <i class="fas fa-chart-line"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
          <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Excellent Models</h5>
                  <span class="h2 font-weight-bold mb-0 text-success"><?php echo $excellent_models; ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                    <i class="fas fa-star"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
          <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Avg Accuracy (7d)</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo round($avg_accuracy_7, 1); ?>%</span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                    <i class="fas fa-percentage"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
          <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Avg Accuracy (30d)</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo round($avg_accuracy_30, 1); ?>%</span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                    <i class="fas fa-chart-bar"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Model Performance Chart -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0 text-gold">Model Performance Overview</h3>
              <small class="text-muted">Based on real orders with statuses: Paid, Preparing, Ready, Completed</small>
            </div>
            <div class="card-body">
              <canvas id="performanceChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Detailed Performance Table -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0 text-gold">Individual Model Performance</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center table-flush">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" class="text-gold">Product</th>
                      <th scope="col" class="text-gold">Overall Rating</th>
                      <th scope="col" class="text-gold">7-Day Accuracy</th>
                      <th scope="col" class="text-gold">14-Day Accuracy</th>
                      <th scope="col" class="text-gold">30-Day Accuracy</th>
                      <th scope="col" class="text-gold">Recommendations</th>
                      <th scope="col" class="text-gold">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($performance_data as $item): ?>
                      <tr>
                        <th scope="row"><?php echo htmlspecialchars($item['product_name']); ?></th>
                        <td>
                          <span class="badge badge-<?php 
                            echo $item['overall_rating'] == 'excellent' ? 'success' : 
                                ($item['overall_rating'] == 'good' ? 'info' : 
                                ($item['overall_rating'] == 'fair' ? 'warning' : 'danger')); 
                          ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $item['overall_rating'])); ?>
                          </span>
                        </td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $item['accuracy_7_days'] > 80 ? 'bg-success' : ($item['accuracy_7_days'] > 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 style="width: <?php echo $item['accuracy_7_days']; ?>%">
                              <?php echo round($item['accuracy_7_days'], 1); ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $item['accuracy_14_days'] > 80 ? 'bg-success' : ($item['accuracy_14_days'] > 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 style="width: <?php echo $item['accuracy_14_days']; ?>%">
                              <?php echo round($item['accuracy_14_days'], 1); ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $item['accuracy_30_days'] > 80 ? 'bg-success' : ($item['accuracy_30_days'] > 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 style="width: <?php echo $item['accuracy_30_days']; ?>%">
                              <?php echo round($item['accuracy_30_days'], 1); ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge badge-info" title="<?php echo htmlspecialchars(implode('; ', $item['recommendations'] ?? [])); ?>">
                            <?php echo $item['recommendations_count']; ?> items
                          </span>
                        </td>
                        <td>
                          <a href="advanced_forecast_reports.php?product_id=<?php echo $item['product_id']; ?>" 
                             class="btn btn-sm btn-primary">
                            <i class="fas fa-chart-line"></i> View Forecast
                          </a>
                          <a href="?product_id=<?php echo $item['product_id']; ?>" 
                             class="btn btn-sm btn-info ml-1">
                            <i class="fas fa-analytics"></i> Performance
                          </a>
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
      
      <?php if ($detailed_performance): ?>
      <!-- Detailed Performance Analysis -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0 text-gold">Detailed Performance Analysis</h3>
              <p class="text-muted">Comprehensive performance metrics and recommendations</p>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Performance Summary -->
                <div class="col-md-4">
                  <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                      <h4 class="card-title">Overall Performance</h4>
                      <h2 class="mb-0"><?php echo $detailed_performance['performance_summary']['overall_score']; ?>%</h2>
                      <h3 class="mb-0">Grade: <?php echo $detailed_performance['performance_summary']['grade']; ?></h3>
                      <p class="card-text">Based on real forecast accuracy</p>
                    </div>
                  </div>
                </div>
                
                <!-- Trend Analysis -->
                <div class="col-md-4">
                  <div class="card bg-gradient-info text-white">
                    <div class="card-body">
                      <h4 class="card-title">Trend Analysis</h4>
                      <p><strong>Accuracy:</strong> <?php echo ucfirst($detailed_performance['trend_analysis']['accuracy_trend']); ?></p>
                      <p><strong>Bias:</strong> <?php echo ucfirst($detailed_performance['trend_analysis']['bias_trend']); ?></p>
                      <p><strong>Variance:</strong> <?php echo ucfirst($detailed_performance['trend_analysis']['variance_trend']); ?></p>
                    </div>
                  </div>
                </div>
                
                <!-- Detailed Metrics -->
                <div class="col-md-4">
                  <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                      <h4 class="card-title">Key Metrics</h4>
                      <p><strong>MAE (7d):</strong> <?php echo $detailed_performance['accuracy_metrics']['7_days']['mae']; ?></p>
                      <p><strong>MAPE (7d):</strong> <?php echo $detailed_performance['accuracy_metrics']['7_days']['mape']; ?>%</p>
                      <p><strong>Bias (7d):</strong> <?php echo $detailed_performance['accuracy_metrics']['7_days']['bias']; ?></p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Strengths and Weaknesses -->
              <div class="row mt-4">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="text-gold">Strengths</h5>
                    </div>
                    <div class="card-body">
                      <?php if (!empty($detailed_performance['performance_summary']['strengths'])): ?>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($detailed_performance['performance_summary']['strengths'] as $strength): ?>
                            <li class="list-group-item bg-transparent text-white">
                              <i class="fas fa-check-circle text-success mr-2"></i>
                              <?php echo htmlspecialchars($strength); ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p class="text-muted">No specific strengths identified</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="text-gold">Areas for Improvement</h5>
                    </div>
                    <div class="card-body">
                      <?php if (!empty($detailed_performance['performance_summary']['weaknesses'])): ?>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($detailed_performance['performance_summary']['weaknesses'] as $weakness): ?>
                            <li class="list-group-item bg-transparent text-white">
                              <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                              <?php echo htmlspecialchars($weakness); ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p class="text-muted">No major weaknesses identified</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Recommendations -->
              <div class="row mt-4">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="text-gold">Recommendations</h5>
                    </div>
                    <div class="card-body">
                      <?php if (!empty($detailed_performance['recommendations'])): ?>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($detailed_performance['recommendations'] as $recommendation): ?>
                            <li class="list-group-item bg-transparent text-white">
                              <i class="fas fa-lightbulb text-info mr-2"></i>
                              <?php echo htmlspecialchars($recommendation); ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p class="text-muted">No specific recommendations available</p>
                      <?php endif; ?>
                    </div>
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
    function refreshPerformance() {
      location.reload();
    }
    
    // Mobile sidebar toggle function
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    }
    
    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
      type: 'bar',
      data: {
        labels: ['7 Days', '14 Days', '30 Days'],
        datasets: [{
          label: 'Average Accuracy (%)',
          data: [<?php echo round($avg_accuracy_7, 1); ?>, <?php echo round($avg_accuracy_14, 1); ?>, <?php echo round($avg_accuracy_30, 1); ?>],
          backgroundColor: [
            'rgba(75, 192, 192, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)'
          ],
          borderColor: [
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)'
          ],
          borderWidth: 1
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
            max: 100,
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
  </script>
</body>
</html>
