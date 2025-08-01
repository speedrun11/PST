<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
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
              <h6 class="h2 text-white d-inline-block mb-0">Inventory Reports</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Reports</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
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
                  <h3 class="mb-0 text-gold">Generate Inventory Reports</h3>
                </div>
                <div class="col-4 text-right">
                  <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                      <input type="date" class="form-control bg-transparent text-light border-light" name="report_date" value="<?php echo date('Y-m-d'); ?>">
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
                <!-- Stock Level Report -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Stock Level Report</h3>
                    </div>
                    <div class="card-body">
                      <div class="chart-container">
                        <canvas id="stockLevelChart" height="300"></canvas>
                      </div>
                      <div class="mt-3">
                        <a href="generate_report.php?type=stock" class="btn btn-sm btn-primary">
                          <i class="fas fa-download"></i> Download Full Report
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Inventory Movement Report -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <h3 class="mb-0 text-gold">Inventory Movement</h3>
                    </div>
                    <div class="card-body">
                      <div class="chart-container">
                        <canvas id="inventoryMovementChart" height="300"></canvas>
                      </div>
                      <div class="mt-3">
                        <a href="generate_report.php?type=movement" class="btn btn-sm btn-primary">
                          <i class="fas fa-download"></i> Download Full Report
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Detailed Reports Section -->
              <div class="row mt-4">
                <div class="col-12">
                  <div class="card shadow">
                    <div class="card-header border-0">
                      <div class="row align-items-center">
                        <div class="col-8">
                          <h3 class="mb-0 text-gold">Detailed Inventory Reports</h3>
                        </div>
                        <div class="col-4 text-right">
                          <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="reportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-file-export"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="reportDropdown">
                              <a class="dropdown-item text-white" href="generate_report.php?type=low_stock&format=pdf">
                                <i class="fas fa-file-pdf text-danger mr-2"></i> Low Stock (PDF)
                              </a>
                              <a class="dropdown-item text-white" href="generate_report.php?type=critical_stock&format=excel">
                                <i class="fas fa-file-excel text-success mr-2"></i> Critical Stock (Excel)
                              </a>
                              <a class="dropdown-item text-white" href="generate_report.php?type=all_products&format=csv">
                                <i class="fas fa-file-csv text-info mr-2"></i> All Products (CSV)
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
                              <th scope="col" class="text-gold">Report Type</th>
                              <th scope="col" class="text-gold">Description</th>
                              <th scope="col" class="text-gold">Last Generated</th>
                              <th scope="col" class="text-gold">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <th scope="row">Low Stock Report</th>
                              <td>Products below reorder threshold</td>
                              <td><?php echo date('M d, Y H:i'); ?></td>
                              <td>
                                <a href="generate_report.php?type=low_stock" class="btn btn-sm btn-primary">
                                  <i class="fas fa-eye"></i> View
                                </a>
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">Critical Stock Report</th>
                              <td>Products below 25% of threshold</td>
                              <td><?php echo date('M d, Y H:i'); ?></td>
                              <td>
                                <a href="generate_report.php?type=critical_stock" class="btn btn-sm btn-primary">
                                  <i class="fas fa-eye"></i> View
                                </a>
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">Inventory Valuation</th>
                              <td>Total value of current inventory</td>
                              <td><?php echo date('M d, Y H:i'); ?></td>
                              <td>
                                <a href="generate_report.php?type=valuation" class="btn btn-sm btn-primary">
                                  <i class="fas fa-eye"></i> View
                                </a>
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">Supplier Performance</th>
                              <td>Delivery timeliness and quality</td>
                              <td><?php echo date('M d, Y H:i'); ?></td>
                              <td>
                                <a href="generate_report.php?type=supplier_performance" class="btn btn-sm btn-primary">
                                  <i class="fas fa-eye"></i> View
                                </a>
                              </td>
                            </tr>
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
    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
      // Stock Level Chart
      const stockCtx = document.getElementById('stockLevelChart').getContext('2d');
      const stockChart = new Chart(stockCtx, {
        type: 'bar',
        data: {
          labels: ['Chicken Pastil', 'Rice', 'Banana Leaves', 'Spices', 'Packaging'],
          datasets: [{
            label: 'Current Stock',
            data: [120, 85, 65, 42, 78],
            backgroundColor: 'rgba(192, 160, 98, 0.7)',
            borderColor: 'rgba(192, 160, 98, 1)',
            borderWidth: 1
          }, {
            label: 'Reorder Threshold',
            data: [50, 40, 30, 20, 25],
            backgroundColor: 'rgba(158, 43, 43, 0.7)',
            borderColor: 'rgba(158, 43, 43, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(192, 160, 98, 0.1)'
              },
              ticks: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            },
            x: {
              grid: {
                color: 'rgba(192, 160, 98, 0.1)'
              },
              ticks: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            }
          }
        }
      });
      
      // Inventory Movement Chart
      const movementCtx = document.getElementById('inventoryMovementChart').getContext('2d');
      const movementChart = new Chart(movementCtx, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          datasets: [{
            label: 'Stock In',
            data: [120, 190, 170, 220, 180, 200],
            borderColor: 'rgba(74, 107, 87, 1)',
            backgroundColor: 'rgba(74, 107, 87, 0.1)',
            borderWidth: 2,
            fill: true
          }, {
            label: 'Stock Out',
            data: [80, 120, 150, 180, 160, 190],
            borderColor: 'rgba(158, 43, 43, 1)',
            backgroundColor: 'rgba(158, 43, 43, 0.1)',
            borderWidth: 2,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(192, 160, 98, 0.1)'
              },
              ticks: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            },
            x: {
              grid: {
                color: 'rgba(192, 160, 98, 0.1)'
              },
              ticks: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                color: 'rgba(248, 245, 242, 0.8)'
              }
            }
          }
        }
      });
    });
    
    // Handle print button
    document.querySelector('.btn-print').addEventListener('click', function() {
      window.print();
    });
  </script>
</body>
</html>