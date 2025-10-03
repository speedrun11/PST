<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
require_once('classes/DataAutomation.php');

// Initialize data automation
try {
    $automation = new DataAutomation($mysqli);
    
    // Handle form submissions
    if ($_POST) {
        $action = $_POST['action'] ?? '';
        $results = [];
        
        switch ($action) {
            case 'weather':
                $days = (int)($_POST['days'] ?? 7);
                $results = $automation->insertWeatherData($days);
                $_SESSION['success'] = "Weather data: {$results['inserted']} records inserted";
                if (!empty($results['errors'])) {
                    $_SESSION['warning'] = "Some errors occurred: " . implode(', ', $results['errors']);
                }
                break;
                
            case 'holidays':
                $year = (int)($_POST['year'] ?? date('Y'));
                $results = $automation->insertHolidayData($year);
                $_SESSION['success'] = "Holiday data: {$results['inserted']} records inserted";
                if (!empty($results['errors'])) {
                    $_SESSION['warning'] = "Some errors occurred: " . implode(', ', $results['errors']);
                }
                break;
                
            case 'economic':
                $months = (int)($_POST['months'] ?? 12);
                $results = $automation->insertEconomicData($months);
                $_SESSION['success'] = "Economic data: {$results['inserted']} records inserted";
                if (!empty($results['errors'])) {
                    $_SESSION['warning'] = "Some errors occurred: " . implode(', ', $results['errors']);
                }
                break;
                
            case 'events':
                $days = (int)($_POST['days'] ?? 30);
                $results = $automation->insertLocalEventsData($days);
                $_SESSION['success'] = "Events data: {$results['inserted']} records inserted";
                if (!empty($results['errors'])) {
                    $_SESSION['warning'] = "Some errors occurred: " . implode(', ', $results['errors']);
                }
                break;
                
            case 'all':
                $results = $automation->runAllAutomation();
                $total_inserted = array_sum(array_column($results, 'inserted'));
                $_SESSION['success'] = "All data automation completed: $total_inserted total records inserted";
                break;
        }
    }
    
    // Get current status
    $status = $automation->getAutomationStatus();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Data automation error: " . $e->getMessage();
    $status = [];
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
              <h6 class="h2 text-white d-inline-block mb-0">Data Automation Center</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Data Automation</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button class="btn btn-sm btn-primary" onclick="runAllAutomation()">
                <i class="fas fa-magic"></i> Run All Automation
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
      
      <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <span class="alert-inner--icon"><i class="ni ni-notification-70"></i></span>
          <span class="alert-inner--text"><strong>Warning!</strong> <?php echo $_SESSION['warning']; ?></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php unset($_SESSION['warning']); ?>
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
      
      <!-- Data Status Overview -->
      <div class="row">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0 text-gold">Data Status Overview</h3>
              <p class="text-muted">Current status of external factors data</p>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Weather Data Status -->
                <div class="col-md-3">
                  <div class="card bg-gradient-info text-white">
                    <div class="card-body">
                      <h6 class="card-title">Weather Data</h6>
                      <h4><?php echo $status['weather']['total_records'] ?? 0; ?></h4>
                      <small>Total Records</small>
                      <div class="mt-2">
                        <small>Latest: <?php echo $status['weather']['latest_date'] ?? 'None'; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Holiday Data Status -->
                <div class="col-md-3">
                  <div class="card bg-gradient-warning text-white">
                    <div class="card-body">
                      <h6 class="card-title">Holiday Data</h6>
                      <h4><?php echo $status['holidays']['total_records'] ?? 0; ?></h4>
                      <small>Total Records</small>
                      <div class="mt-2">
                        <small>Latest: <?php echo $status['holidays']['latest_date'] ?? 'None'; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Economic Data Status -->
                <div class="col-md-3">
                  <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                      <h6 class="card-title">Economic Data</h6>
                      <h4><?php echo $status['economic']['total_records'] ?? 0; ?></h4>
                      <small>Total Records</small>
                      <div class="mt-2">
                        <small>Latest: <?php echo $status['economic']['latest_date'] ?? 'None'; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Events Data Status -->
                <div class="col-md-3">
                  <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                      <h6 class="card-title">Events Data</h6>
                      <h4><?php echo $status['events']['total_records'] ?? 0; ?></h4>
                      <small>Total Records</small>
                      <div class="mt-2">
                        <small>Latest: <?php echo $status['events']['latest_date'] ?? 'None'; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Automation Controls -->
      <div class="row mt-4">
        <!-- Weather Data Automation -->
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <h5 class="mb-0 text-gold">Weather Data Automation</h5>
            </div>
            <div class="card-body">
              <form method="POST">
                <input type="hidden" name="action" value="weather">
                <div class="form-group">
                  <label class="form-control-label">Number of Days</label>
                  <select class="form-control" name="days">
                    <option value="7">7 Days</option>
                    <option value="14">14 Days</option>
                    <option value="30">30 Days</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-info">
                  <i class="fas fa-cloud-sun"></i> Generate Weather Data
                </button>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Holiday Data Automation -->
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <h5 class="mb-0 text-gold">Holiday Data Automation</h5>
            </div>
            <div class="card-body">
              <form method="POST">
                <input type="hidden" name="action" value="holidays">
                <div class="form-group">
                  <label class="form-control-label">Year</label>
                  <select class="form-control" name="year">
                    <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>
                    <option value="<?php echo date('Y') + 1; ?>"><?php echo date('Y') + 1; ?></option>
                  </select>
                </div>
                <button type="submit" class="btn btn-warning">
                  <i class="fas fa-calendar-alt"></i> Generate Holiday Data
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <!-- Economic Data Automation -->
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <h5 class="mb-0 text-gold">Economic Data Automation</h5>
            </div>
            <div class="card-body">
              <form method="POST">
                <input type="hidden" name="action" value="economic">
                <div class="form-group">
                  <label class="form-control-label">Number of Months</label>
                  <select class="form-control" name="months">
                    <option value="6">6 Months</option>
                    <option value="12">12 Months</option>
                    <option value="24">24 Months</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-chart-line"></i> Generate Economic Data
                </button>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Events Data Automation -->
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header border-0">
              <h5 class="mb-0 text-gold">Local Events Data Automation</h5>
            </div>
            <div class="card-body">
              <form method="POST">
                <input type="hidden" name="action" value="events">
                <div class="form-group">
                  <label class="form-control-label">Number of Days</label>
                  <select class="form-control" name="days">
                    <option value="30">30 Days</option>
                    <option value="60">60 Days</option>
                    <option value="90">90 Days</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-calendar-check"></i> Generate Events Data
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Run All Automation -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h5 class="mb-0 text-gold">Complete Data Automation</h5>
            </div>
            <div class="card-body text-center">
              <p class="text-muted">Run all data automation processes at once</p>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="all">
                <button type="submit" class="btn btn-lg btn-gradient-primary">
                  <i class="fas fa-magic"></i> Run Complete Automation
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php require_once('partials/_footer.php'); ?>
  
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
  
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
    
    .text-gold {
      color: var(--accent-gold) !important;
    }
    
    .btn-gradient-primary {
      background: linear-gradient(135deg, var(--accent-gold), var(--accent-blue));
      border: none;
      color: white;
      transition: all var(--transition-speed) ease;
    }
    
    .btn-gradient-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
      filter: brightness(1.15);
    }
  </style>
  
  <script>
    function runAllAutomation() {
      if (confirm('This will run all data automation processes. Continue?')) {
        document.querySelector('form[action=""] input[name="action"][value="all"]').closest('form').submit();
      }
    }
  </script>
</body>
</html>
