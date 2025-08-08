<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Handle form submission
if(isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    
    // Validate input
    if(empty($category_name)) {
        $_SESSION['error'] = "Category name cannot be empty";
        header("Location: add_category.php");
        exit;
    }
    
    // Check if category already exists
    $check = "SELECT COUNT(*) FROM rpos_products WHERE prod_category = ?";
    $stmt = $mysqli->prepare($check);
    $stmt->bind_param('s', $category_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if($count > 0) {
        $_SESSION['error'] = "Category already exists";
        header("Location: add_category.php");
        exit;
    }
    
    $_SESSION['success'] = "Category added successfully";
    header("Location: categories.php");
    exit;
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
              <h6 class="h2 text-white d-inline-block mb-0">Add New Category</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="categories.php" class="text-gold">Categories</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Add Category</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="categories.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Categories
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
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-12">
                  <h3 class="mb-0 text-gold">Category Information</h3>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <form method="post">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="category_name">Category Name *</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="category_name" name="category_name" required placeholder="Enter category name">
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <button type="submit" name="add_category" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Category
                  </button>
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
      background-color: rgba(26, 26, 46, 0.5) !important;
      color: var(--text-light) !important;
      border: 1px solid rgba(192, 160, 98, 0.3) !important;
      transition: all var(--transition-speed) ease;
    }
    
    .form-control:focus {
      background-color: rgba(26, 26, 46, 0.7) !important;
      color: var(--text-light) !important;
      border-color: var(--accent-gold) !important;
      box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
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
    
    .alert {
      background-color: rgba(158, 43, 43, 0.2);
      border: 1px solid var(--accent-red);
      color: var(--text-light);
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
</body>
</html>