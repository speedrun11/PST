<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Handle form submission
if (isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    // Validate inputs
    if (empty($name) || empty($phone)) {
        $_SESSION['error'] = "Supplier name and phone are required";
        header("Location: add_supplier.php");
        exit;
    }

    // Insert into database
    $query = "INSERT INTO rpos_suppliers (supplier_name, supplier_phone, supplier_email, supplier_address) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssss', $name, $phone, $email, $address);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Supplier added successfully";
        header("Location: suppliers.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add supplier: " . $mysqli->error;
        header("Location: add_supplier.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Add Supplier - Inventory Management</title>
  <?php require_once('partials/_head.php'); ?>
</head>
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
              <h6 class="h2 text-white d-inline-block mb-0">Add New Supplier</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                  <li class="breadcrumb-item"><a href="suppliers.php" class="text-gold">Suppliers</a></li>
                  <li class="breadcrumb-item active text-gold" aria-current="page">Add Supplier</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="suppliers.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Suppliers
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Display success/error messages -->
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-xl-8 order-xl-1">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Supplier Information</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="name">Supplier Name *</label>
                        <input type="text" id="name" name="name" class="form-control bg-transparent text-white border-light" placeholder="Enter supplier name" required>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" class="form-control bg-transparent text-white border-light" placeholder="Enter phone number" required>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control bg-transparent text-white border-light" placeholder="Enter email address">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="address">Address</label>
                        <textarea id="address" name="address" rows="4" class="form-control bg-transparent text-white border-light" placeholder="Enter supplier address"></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <button type="submit" name="add_supplier" class="btn btn-primary">
                          <i class="fas fa-save"></i> Save Supplier
                        </button>
                        <a href="suppliers.php" class="btn btn-secondary">
                          <i class="fas fa-times"></i> Cancel
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
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
    
    .form-control {
      background-color: rgba(26, 26, 46, 0.6) !important;
      border-color: rgba(192, 160, 98, 0.3) !important;
      color: var(--text-light) !important;
      transition: all var(--transition-speed) ease;
    }
    
    .form-control:focus {
      background-color: rgba(26, 26, 46, 0.8) !important;
      border-color: var(--accent-gold) !important;
      box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25) !important;
      color: var(--text-light) !important;
    }
    
    .form-control::placeholder {
      color: rgba(248, 245, 242, 0.5) !important;
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
    
    .btn-secondary {
      background: rgba(26, 26, 46, 0.6);
      border: 1px solid rgba(192, 160, 98, 0.3);
      color: var(--accent-gold);
      transition: all var(--transition-speed) ease;
    }
    
    .btn-secondary:hover {
      background: rgba(192, 160, 98, 0.1);
      color: var(--accent-gold);
      border-color: rgba(192, 160, 98, 0.4);
    }
    
    .nav-link {
      color: var(--text-light);
      transition: all var(--transition-speed) ease;
    }
    
    .nav-link:hover {
      color: var(--accent-gold);
    }
    
    .sidebar {
      background: rgba(26, 26, 46, 0.9) !important;
      border-right: 1px solid rgba(192, 160, 98, 0.2);
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
    
    @media (max-width: 768px) {
      .card {
        backdrop-filter: blur(4px);
      }
      
      .breadcrumb {
        padding: 0.25rem 0.5rem;
      }
    }
  </style>
  
  <script>
    // Simple form validation
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      
      form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        
        if (!name || !phone) {
          e.preventDefault();
          swal({
            title: "Error",
            text: "Supplier name and phone are required fields",
            icon: "error",
            buttons: false,
            timer: 2000
          });
        }
      });
    });
  </script>
</body>
</html>