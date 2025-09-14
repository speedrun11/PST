<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Function to generate random hexadecimal product ID
function generateProductId($length = 10) {
    return bin2hex(random_bytes($length/2));
}

// Function to generate product code in format: ABCD-1234
function generateProductCode() {
    $prefix = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
    $suffix = substr(str_shuffle("1234567890"), 0, 4);
    return $prefix . '-' . $suffix;
}

// Get all ingredients for selection
$ingredients = array();
$ret = "SELECT * FROM rpos_ingredients ORDER BY ingredient_name ASC";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($ingredient = $res->fetch_object()) {
    $ingredients[] = $ingredient;
}

// Get all products for quick-fill recipe and relationships
$all_products = array();
$prodListSql = "SELECT prod_id, prod_name, prod_category FROM rpos_products ORDER BY 
                CASE 
                  WHEN LOWER(prod_category) = 'food' THEN 1
                  WHEN LOWER(prod_category) = 'beverages' THEN 2
                  ELSE 3
                END,
                prod_name ASC";
$prodListStmt = $mysqli->prepare($prodListSql);
if ($prodListStmt) {
    $prodListStmt->execute();
    $prodListRes = $prodListStmt->get_result();
    while ($p = $prodListRes->fetch_object()) {
        $all_products[] = $p;
    }
    $prodListStmt->close();
}

// Generate a product code initially
$generated_code = generateProductCode();

// Handle form submission
if(isset($_POST['add_product'])) {
    // Generate product ID
    $prod_id = generateProductId();
    
    // Retrieve form data
    $prod_code = $_POST['prod_code'];
    $prod_name = $_POST['prod_name'];
    $prod_category = $_POST['prod_category'];
    $prod_price = $_POST['prod_price'];
    $prod_quantity = $_POST['prod_quantity'];
    $prod_threshold = $_POST['prod_threshold'];
    $prod_desc = $_POST['prod_desc'];
    $selected_ingredients = isset($_POST['ingredients']) ? $_POST['ingredients'] : array();
    $mirror_base_id = isset($_POST['mirror_base_id']) ? trim($_POST['mirror_base_id']) : '';
    $combo_base_a_id = isset($_POST['combo_base_a_id']) ? trim($_POST['combo_base_a_id']) : '';
    $combo_base_b_id = isset($_POST['combo_base_b_id']) ? trim($_POST['combo_base_b_id']) : '';

    // If this product is a mirror (double) or a combo, ignore initial stock fields
    if (!empty($mirror_base_id) || (!empty($combo_base_a_id) && !empty($combo_base_b_id))) {
        // Copy threshold from base if provided empty
        if (empty($prod_threshold)) {
            // For combo, copy min threshold of bases; for mirror, copy base threshold
            if (!empty($combo_base_a_id) && !empty($combo_base_b_id)) {
                $th_stmt = $mysqli->prepare("SELECT MIN(prod_threshold) AS prod_threshold FROM rpos_products WHERE prod_id IN (?, ?)");
                $th_stmt->bind_param('ss', $combo_base_a_id, $combo_base_b_id);
            } else {
                $th_stmt = $mysqli->prepare("SELECT prod_threshold FROM rpos_products WHERE prod_id = ?");
                $th_stmt->bind_param('s', $mirror_base_id);
            }
            if ($th_stmt) {
                $th_stmt->execute();
                $th_res = $th_stmt->get_result();
                if ($row = $th_res->fetch_assoc()) {
                    $prod_threshold = (int)$row['prod_threshold'];
                }
                $th_stmt->close();
            }
        }
        // Quantity will be computed from the base; store zero to avoid confusion
        $prod_quantity = 0;
    }
    
    // Handle file upload
    $prod_img = 'default.png';
    if(isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == 0) {
        $target_dir = "assets/img/products/";
        $target_file = $target_dir . basename($_FILES["prod_img"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is an actual image
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
    
    // Validate product code is unique
    $check_query = "SELECT prod_id FROM rpos_products WHERE prod_code = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('s', $prod_code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $err = "Product code already exists. Generating a new one.";
        $generated_code = generateProductCode();
        $check_stmt->close();
    } else {
        $check_stmt->close();
        
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // Insert into database with generated prod_id
            $query = "INSERT INTO rpos_products (prod_id, prod_code, prod_name, prod_category, prod_price, prod_quantity, prod_threshold, prod_img, prod_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $mysqli->error);
            }
            
            $stmt->bind_param('ssssdiiss', $prod_id, $prod_code, $prod_name, $prod_category, $prod_price, $prod_quantity, $prod_threshold, $prod_img, $prod_desc);
            
            if(!$stmt->execute()) {
                throw new Exception("Failed to add product: " . $stmt->error);
            }
            $stmt->close();
            
            // Ensure links table exists
            $link_sql = "CREATE TABLE IF NOT EXISTS rpos_product_links (
                id INT AUTO_INCREMENT PRIMARY KEY,
                linked_product_id VARCHAR(200) NOT NULL,
                base_product_id VARCHAR(200) NOT NULL,
                relation ENUM('mirror','combo') NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_link (linked_product_id, base_product_id, relation),
                KEY idx_linked (linked_product_id),
                KEY idx_base (base_product_id)
            ) ENGINE=InnoDB";
            $mysqli->query($link_sql);

            // If mirror, create product link record
            if (!empty($mirror_base_id)) {
                $ins_link = $mysqli->prepare("INSERT IGNORE INTO rpos_product_links (linked_product_id, base_product_id, relation) VALUES (?, ?, 'mirror')");
                if ($ins_link) {
                    $ins_link->bind_param('ss', $prod_id, $mirror_base_id);
                    $ins_link->execute();
                    $ins_link->close();
                }
            }

            // If combo, create two link records (to A and B)
            if (!empty($combo_base_a_id) && !empty($combo_base_b_id)) {
                $ins_combo = $mysqli->prepare("INSERT IGNORE INTO rpos_product_links (linked_product_id, base_product_id, relation) VALUES (?, ?, 'combo')");
                if ($ins_combo) {
                    $ins_combo->bind_param('ss', $prod_id, $combo_base_a_id);
                    $ins_combo->execute();
                    $ins_combo->bind_param('ss', $prod_id, $combo_base_b_id);
                    $ins_combo->execute();
                    $ins_combo->close();
                }
            }

            // Add ingredient relationships
            if(!empty($selected_ingredients)) {
                foreach($selected_ingredients as $ingredient_data) {
                    $ingredient_id = $ingredient_data['ingredient_id'];
                    $quantity_required = $ingredient_data['quantity_required'];
                    
                    $ingredient_query = "INSERT INTO rpos_product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";
                    $ingredient_stmt = $mysqli->prepare($ingredient_query);
                    
                    if (!$ingredient_stmt) {
                        throw new Exception("Failed to prepare ingredient statement: " . $mysqli->error);
                    }
                    
                    $ingredient_stmt->bind_param('ssd', $prod_id, $ingredient_id, $quantity_required);
                    
                    if(!$ingredient_stmt->execute()) {
                        throw new Exception("Failed to add ingredient relationship: " . $ingredient_stmt->error);
                    }
                    $ingredient_stmt->close();
                }
            }
            
            // Log the activity
            $previous_quantity = 0;
            $quantity_change = (int)$prod_quantity;
            $new_quantity = (int)$prod_quantity;
            $activity_type = 'Add';
            $notes = "Added new product: $prod_name";
            $reference_code = 'ADD-'.uniqid();
            
            $log_query = "INSERT INTO rpos_inventory_logs 
                         (product_id, activity_type, quantity_change, previous_quantity, new_quantity, staff_id, notes, reference_code) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $log_stmt = $mysqli->prepare($log_query);
            
            if ($log_stmt) {
                $log_stmt->bind_param('ssiiiiss', $prod_id, $activity_type, $quantity_change, $previous_quantity, $new_quantity, $_SESSION['staff_id'], $notes, $reference_code);
                if (!$log_stmt->execute()) {
                    error_log("Failed to log activity: " . $log_stmt->error);
                }
                $log_stmt->close();
    } else {
                error_log("Failed to prepare log statement: " . $mysqli->error);
            }
            
            $mysqli->commit();
            $_SESSION['success'] = "Product added successfully";
            header("Location: products.php");
            exit;
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $err = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Add Product</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
            font-family: 'Poppins', sans-serif;
        }
        
        .header {
            background: url(assets/img/theme/pastil.jpg) no-repeat center center;
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
        
        .form-control {
            background: rgba(26, 26, 46, 0.7);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
            height: auto;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            background: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
        }
        
        label {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            transition: all var(--transition-speed) ease;
            color: var(--text-light);
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: var(--accent-gold);
        }
        
        hr {
            border-top: 1px solid rgba(192, 160, 98, 0.3);
            margin: 1.5rem 0;
        }
        
        /* Enhanced file input styling */
        .file-input-container {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .file-input-label {
            display: block;
            width: 100%;
            background: rgba(26, 26, 46, 0.7);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 4px;
            padding: 10px 15px;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            text-align: center;
        }
        
        .file-input-label:hover {
            background: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
        }
        
        .file-input-label span {
            display: inline-block;
            margin-right: 10px;
        }
        
        .file-input-label::after {
            content: 'Browse';
            display: inline-block;
            background: rgba(192, 160, 98, 0.3);
            padding: 2px 10px;
            border-radius: 3px;
            margin-left: 10px;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        
        .file-selected {
            font-size: 0.85rem;
            margin-top: 5px;
            color: var(--accent-gold);
            font-style: italic;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row > div {
                margin-bottom: 1rem;
            }
            
            .form-control, .file-input-label {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .btn-success {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            
            label {
                font-size: 0.95rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
        }
        
        @media (max-width: 576px) {
            .form-control, .file-input-label {
                padding: 6px 10px;
                font-size: 0.85rem;
            }
            
            .btn-success {
                width: 100%;
            }
        }
                .text-gold {
            color: var(--accent-gold) !important;
        }

        .sidebar .nav-link:hover {
            color: var(--accent-gold) !important;
            background: rgba(192, 160, 98, 0.1);
        }

        .sidebar .dropdown-menu {
            background-color: rgba(26, 26, 46, 0.95);
            border: 1px solid rgba(192, 160, 98, 0.2);
        }

        .sidebar .dropdown-item:hover {
            background-color: rgba(192, 160, 98, 0.1);
        }
    </style>
</head>
<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>
    
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Add New Product</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-gold">Dashboard</a></li>
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
      <!-- Display success/error messages -->
      <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <?php if(isset($err)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Error!</strong> <?php echo $err; ?>
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
                  <h3 class="mb-0 text-gold">Product Information</h3>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <form method="post" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_code">Product Code</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="prod_code" name="prod_code" value="<?php echo $generated_code; ?>" readonly required>
                      <small class="text-muted">Auto-generated product code</small>
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
                        <option value="Beverages">Beverages</option>
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
                      <label class="form-control-label text-gold" for="prod_desc">Product Description</label>
                      <textarea rows="3" class="form-control bg-transparent text-light border-light" id="prod_desc" name="prod_desc" placeholder="Enter product description"></textarea>
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
                
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold">Quick-fill Recipe</label>
                      <div class="card" style="background: rgba(26, 26, 46, 0.5); border: 1px solid rgba(192, 160, 98, 0.3);">
                        <div class="card-body">
                          <div class="row align-items-end">
                            <div class="col-md-5">
                              <label class="text-gold">Copy from product A</label>
                              <select id="qf_product_a" class="form-control bg-transparent text-light border-light">
                                <option value="">Select product</option>
                                <?php foreach($all_products as $p): ?>
                                  <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?> (<?php echo $p->prod_category; ?>)</option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-2">
                              <label class="text-gold">x</label>
                              <input id="qf_mult_a" type="number" min="0" step="0.01" class="form-control bg-transparent text-light border-light" value="1">
                            </div>
                            <div class="col-md-5">
                              <label class="text-gold">Copy from product B (optional)</label>
                              <select id="qf_product_b" class="form-control bg-transparent text-light border-light">
                                <option value="">Select product</option>
                                <?php foreach($all_products as $p): ?>
                                  <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?> (<?php echo $p->prod_category; ?>)</option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-2 mt-3">
                              <label class="text-gold">x</label>
                              <input id="qf_mult_b" type="number" min="0" step="0.01" class="form-control bg-transparent text-light border-light" value="1">
                            </div>
                          </div>
                          <div class="text-right mt-3">
                            <button id="qf_apply" type="button" class="btn btn-sm btn-primary">Apply Quick-fill</button>
                          </div>
                          <small class="text-muted">Tip: Doubles = multiplier 2. Combos = pick two bases with multiplier 1.</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold">Mirror Base (for Double variants)</label>
                      <select id="mirror_base_id" name="mirror_base_id" class="form-control bg-transparent text-light border-light">
                        <option value="">None (standalone product)</option>
                        <?php foreach($all_products as $p): ?>
                          <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?> (<?php echo $p->prod_category; ?>)</option>
                        <?php endforeach; ?>
                      </select>
                      <small class="text-muted">If set, this product's stock mirrors the selected base. Initial stock will be ignored.</small>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold">Combo Bases (for Regular + Spicy)</label>
                      <div class="row">
                        <div class="col-md-6">
                          <select id="combo_base_a_id" name="combo_base_a_id" class="form-control bg-transparent text-light border-light">
                            <option value="">Select Base A</option>
                            <?php foreach($all_products as $p): ?>
                              <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?> (<?php echo $p->prod_category; ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <select id="combo_base_b_id" name="combo_base_b_id" class="form-control bg-transparent text-light border-light">
                            <option value="">Select Base B</option>
                            <?php foreach($all_products as $p): ?>
                              <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?> (<?php echo $p->prod_category; ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <small class="text-muted">If both are set, stock will be computed as the minimum of both bases. Initial stock will be ignored; threshold optional.</small>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold">Product Ingredients</label>
                      <div class="card" style="background: rgba(26, 26, 46, 0.5); border: 1px solid rgba(192, 160, 98, 0.3);">
                        <div class="card-body">
                          <p class="text-muted mb-3">Select ingredients and specify quantities required per product unit:</p>
                          <div id="ingredients-container">
                            <?php if(empty($ingredients)): ?>
                              <p class="text-warning">No ingredients available. Please add ingredients first.</p>
                            <?php else: ?>
                              <?php foreach($ingredients as $index => $ingredient): ?>
                                <div class="ingredient-row mb-3 p-3" style="background: rgba(26, 26, 46, 0.3); border-radius: 5px; border: 1px solid rgba(192, 160, 98, 0.2);">
                                  <div class="row align-items-center">
                  <div class="col-md-6">
                                      <div class="form-check">
                                        <input class="form-check-input ingredient-checkbox" type="checkbox" name="ingredients[<?php echo $index; ?>][ingredient_id]" value="<?php echo $ingredient->ingredient_id; ?>" id="ingredient_<?php echo $index; ?>">
                                        <label class="form-check-label text-white" for="ingredient_<?php echo $index; ?>">
                                          <?php echo htmlspecialchars($ingredient->ingredient_name); ?>
                                          <small class="text-muted">(<?php echo htmlspecialchars($ingredient->ingredient_unit); ?>)</small>
                                        </label>
                                      </div>
                                    </div>
                                    <div class="col-md-4">
                                      <input type="number" step="0.01" min="0" class="form-control ingredient-quantity bg-transparent text-light border-light" name="ingredients[<?php echo $index; ?>][quantity_required]" placeholder="Quantity" disabled>
                                    </div>
                                    <div class="col-md-2">
                                      <small class="text-muted"><?php echo htmlspecialchars($ingredient->ingredient_unit); ?></small>
                                    </div>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </div>
                        </div>
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
      background: url(assets/img/theme/pastil.jpg) no-repeat center center;
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
    
    .form-control:read-only {
      background-color: rgba(26, 26, 46, 0.3) !important;
      cursor: not-allowed;
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
    
    // Handle ingredient checkbox changes
    document.addEventListener('DOMContentLoaded', function() {
      const checkboxes = document.querySelectorAll('.ingredient-checkbox');
      const quantities = document.querySelectorAll('.ingredient-quantity');
      const mirrorSelect = document.getElementById('mirror_base_id');
      const comboA = document.getElementById('combo_base_a_id');
      const comboB = document.getElementById('combo_base_b_id');
      const qtyInput = document.getElementById('prod_quantity');
      const thresholdInput = document.getElementById('prod_threshold');
      
      checkboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
          const quantityInput = quantities[index];
          if (this.checked) {
            quantityInput.disabled = false;
            quantityInput.required = true;
            quantityInput.value = '1.00'; // Default quantity
          } else {
            quantityInput.disabled = true;
            quantityInput.required = false;
            quantityInput.value = '';
          }
        });
      });

      // Mirror base toggling: disable initial stock and make threshold optional
      function syncMirrorFields() {
        const isMirror = mirrorSelect && mirrorSelect.value !== '';
        const isCombo = (comboA && comboA.value !== '' && comboB && comboB.value !== '');
        if (qtyInput) {
          qtyInput.disabled = !!isMirror || !!isCombo;
          qtyInput.required = !(isMirror || isCombo);
          if (isMirror || isCombo) {
            qtyInput.value = '';
            qtyInput.placeholder = isCombo ? 'Computed as min of bases' : 'Mirrors base stock';
        } else {
            qtyInput.placeholder = 'Enter initial quantity';
          }
        }
        if (thresholdInput) {
          thresholdInput.required = !(isMirror || isCombo); // optional when computed
          if ((isMirror || isCombo) && !thresholdInput.value) {
            thresholdInput.placeholder = isCombo ? 'Optional (auto-copy min of bases)' : 'Optional (auto-copy from base)';
          } else if (!isMirror) {
            thresholdInput.placeholder = 'Enter threshold for low stock alert';
          }
        }
      }
      syncMirrorFields();
      mirrorSelect && mirrorSelect.addEventListener('change', syncMirrorFields);
      comboA && comboA.addEventListener('change', syncMirrorFields);
      comboB && comboB.addEventListener('change', syncMirrorFields);
    });

    // Quick-fill logic
    async function fetchRecipe(productId) {
      if (!productId) return [];
      const res = await fetch('../inventory/get_product_recipe.php?product_id=' + encodeURIComponent(productId));
      if (!res.ok) return [];
      const json = await res.json();
      return (json && json.ingredients) ? json.ingredients : [];
    }

    function applyRecipeToForm(mergedMap) {
      const rows = document.querySelectorAll('#ingredients-container .ingredient-row');
      rows.forEach(row => {
        const checkbox = row.querySelector('.ingredient-checkbox');
        const qty = row.querySelector('.ingredient-quantity');
        const ingId = checkbox ? checkbox.value : null;
        if (!ingId) return;
        if (mergedMap.has(ingId)) {
          checkbox.checked = true;
          qty.disabled = false;
          qty.required = true;
          qty.value = mergedMap.get(ingId).toFixed(2);
        }
      });
    }

    document.getElementById('qf_apply')?.addEventListener('click', async function() {
      const a = document.getElementById('qf_product_a')?.value || '';
      const b = document.getElementById('qf_product_b')?.value || '';
      const multA = parseFloat(document.getElementById('qf_mult_a')?.value || '1') || 1;
      const multB = parseFloat(document.getElementById('qf_mult_b')?.value || '1') || 1;

      const [ra, rb] = await Promise.all([fetchRecipe(a), fetchRecipe(b)]);
      const merged = new Map();
      ra.forEach(item => {
        const base = parseFloat(item.quantity_required) || 0;
        const val = (merged.get(item.ingredient_id) || 0) + base * multA;
        merged.set(item.ingredient_id, val);
      });
      rb.forEach(item => {
        const base = parseFloat(item.quantity_required) || 0;
        const val = (merged.get(item.ingredient_id) || 0) + base * multB;
        merged.set(item.ingredient_id, val);
      });
      applyRecipeToForm(merged);
    });
  </script>
</body>
</html>