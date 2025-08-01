<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Get forecast data (in a real system, this would come from your forecasting algorithm)
$forecast_data = array(
    array(
        'product' => 'Chicken Pastil',
        'current_stock' => 120,
        'daily_usage' => 15,
        'forecast_days' => 8,
        'status' => 'critical'
    ),
    array(
        'product' => 'Rice',
        'current_stock' => 85,
        'daily_usage' => 8,
        'forecast_days' => 10,
        'status' => 'warning'
    ),
    array(
        'product' => 'Banana Leaves',
        'current_stock' => 65,
        'daily_usage' => 5,
        'forecast_days' => 13,
        'status' => 'normal'
    ),
    array(
        'product' => 'Spices',
        'current_stock' => 42,
        'daily_usage' => 3,
        'forecast_days' => 14,
        'status' => 'normal'
    ),
    array(
        'product' => 'Packaging',
        'current_stock' => 78,
        'daily_usage' => 6,
        'forecast_days' => 13,
        'status' => 'normal'
    )
);

// Calculate forecast dates
foreach ($forecast_data as &$item) {
    $item['forecast_date'] = date('Y-m-d', strtotime("+{$item['forecast_days']} days"));
    $item['status_class'] = $item['status'] == 'critical' ? 'critical-stock' : ($item['status'] == 'warning' ? 'low-stock' : 'text-success');
}
unset($item);
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
                <i class="fas fa-sync-alt"></i> Regenerate Forecast
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
                      <select class="form-control bg-transparent text-light border-light" name="timeframe">
                        <option value="7">Next 7 Days</option>
                        <option value="14" selected>Next 14 Days</option>
                        <option value="30">Next 30 Days</option>
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
                              <th scope="col" class="text-gold">Days Left</th>
                              <th scope="col" class="text-gold">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($forecast_data as $item): ?>
                              <?php if ($item['status'] == 'critical' || $item['status'] == 'warning'): ?>
                                <tr>
                                  <th scope="row"><?php echo $item['product']; ?></th>
                                  <td><?php echo $item['forecast_days']; ?></td>
                                  <td class="<?php echo $item['status_class']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                  </td>
                                </tr>
                              <?php endif; ?>
                            <?php endforeach; ?>
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
                              <th scope="col" class="text-gold">Current Stock</th>
                              <th scope="col" class="text-gold">Daily Usage</th>
                              <th scope="col" class="text-gold">Days Left</th>
                              <th scope="col" class="text-gold">Forecast Date</th>
                              <th scope="col" class="text-gold">Status</th>
                              <th scope="col" class="text-gold">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($forecast_data as $item): ?>
                              <tr>
                                <th scope="row"><?php echo $item['product']; ?></th>
                                <td><?php echo $item['current_stock']; ?></td>
                                <td><?php echo $item['daily_usage']; ?></td>
                                <td><?php echo $item['forecast_days']; ?></td>
                                <td><?php echo $item['forecast_date']; ?></td>
                                <td class="<?php echo $item['status_class']; ?>">
                                  <?php echo ucfirst($item['status']); ?>
                                </td>
                                <td>
                                  <a href="restock_product.php?product=<?php echo urlencode($item['product']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-up"></i> Restock
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
              <?php 
                $critical = count(array_filter($forecast_data, function($item) { return $item['status'] == 'critical'; }));
                $warning = count(array_filter($forecast_data, function($item) { return $item['status'] == 'warning'; }));
                $normal = count(array_filter($forecast_data, function($item) { return $item['status'] == 'normal'; }));
                echo "$critical, $warning, $normal";
              ?>
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
    });

    // Handle print button
    document.querySelector('.btn-print').addEventListener('click', function() {
      window.print();
    });
  </script>
</body>
</html>