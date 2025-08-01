<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/activity_logger.php');
check_login();
require_once('partials/_head.php');

// Handle form submission
if(isset($_POST['add_product'])) {
    // Retrieve form data
    $prod_code = $_POST['prod_code'];
    $prod_name = $_POST['prod_name'];
    $prod_category = $_POST['prod_category'];
    $prod_price = $_POST['prod_price'];
    $prod_quantity = $_POST['prod_quantity'];
    $prod_threshold = $_POST['prod_threshold'];
    
    // Handle file upload
    $prod_img = 'default.png';
    if(isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == 0) {
        $target_dir = "../admin/assets/img/products/";
        $target_file = $target_dir . basename($_FILES["prod_img"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["prod_img"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $prod_img = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $prod_img;
            
            // Move uploaded file
            if(!move_uploaded_file($_FILES["prod_img"]["tmp_name"], $target_file)) {
                $err = "Sorry, there was an error uploading your file.";
                $prod_img = 'default.png';
            }
        }
    }
    
    // Insert into database
    $query = "INSERT INTO rpos_products (prod_code, prod_name, prod_category, prod_price, prod_quantity, prod_threshold, prod_img) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssdiis', $prod_code, $prod_name, $prod_category, $prod_price, $prod_quantity, $prod_threshold, $prod_img);
    
    if($stmt->execute()) {
        // Log the activity
        log_activity(
            $mysqli, 
            $mysqli->insert_id, // new product ID
            'Add', 
            $prod_quantity, 
            0, 
            $prod_quantity, 
            $_SESSION['staff_id'],
            'Product added to inventory',
            'ADD-' . uniqid()
        );
        
        $success = "Product added successfully";
        header("Location: products.php");
        exit;
    } else {
        $err = "Failed to add product";
    }
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
              <h6 class="h2 text-white d-inline-block mb-0">Add New Product</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="products.php" class="text-gold">Products</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Add Product</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="products.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-12">
                  <h3 class="mb-0 text-gold">Product Information</h3>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <form method="post" enctype="multipart/form-data">
                <?php if(isset($err)) { ?>
                  <div class="alert alert-danger">
                    <strong>Error!</strong> <?php echo $err; ?>
                  </div>
                <?php } ?>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_code">Product Code</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="prod_code" name="prod_code" required placeholder="Enter product code">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_name">Product Name</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="prod_name" name="prod_name" required placeholder="Enter product name">
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_category">Category</label>
                      <select class="form-control bg-transparent text-light border-light" id="prod_category" name="prod_category" required>
                        <option value="" disabled selected>Select category</option>
                        <option value="Food">Food</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Snack">Snack</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_price">Price (₱)</label>
                      <input type="number" step="0.01" class="form-control bg-transparent text-light border-light" id="prod_price" name="prod_price" required placeholder="0.00">
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_quantity">Initial Stock</label>
                      <input type="number" class="form-control bg-transparent text-light border-light" id="prod_quantity" name="prod_quantity" required placeholder="Enter initial quantity">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_threshold">Stock Threshold</label>
                      <input type="number" class="form-control bg-transparent text-light border-light" id="prod_threshold" name="prod_threshold" required placeholder="Enter threshold for low stock alert">
                      <small class="text-muted">System will alert when stock reaches this level</small>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_img">Product Image</label>
                      <div class="custom-file">
                        <input type="file" class="custom-file-input bg-transparent" id="prod_img" name="prod_img" accept="image/*">
                        <label class="custom-file-label bg-transparent text-light border-light" for="prod_img">Choose file...</label>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <button type="submit" name="add_product" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Product
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
    
    .custom-file-label {
      background-color: rgba(26, 26, 46, 0.5) !important;
      color: var(--text-light) !important;
      border: 1px solid rgba(192, 160, 98, 0.3) !important;
    }
    
    .custom-file-input:focus ~ .custom-file-label {
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
  
  <script>
    // Update file input label with selected filename
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
      var fileName = document.getElementById("prod_img").files[0].name;
      var nextSibling = e.target.nextElementSibling;
      nextSibling.innerText = fileName;
    });
  </script>
</body>
</html>