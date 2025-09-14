<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

// Get product details to update
if(isset($_GET['update'])) {
    $id = $_GET['update'];
    $query = "SELECT * FROM rpos_products WHERE prod_id = ?";
    $stmt = $mysqli->prepare($query);
    // prod_id is a varchar, bind as string
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_object();
    
    if(!$product) {
        $_SESSION['error'] = "Product not found";
        header("Location: products.php");
        exit;
    }
}

// Load all ingredients and current product ingredient links
$all_ingredients = [];
$current_links = [];

// Fetch all ingredients
$ing_sql = "SELECT ingredient_id, ingredient_name, ingredient_unit FROM rpos_ingredients ORDER BY ingredient_name ASC";
$ing_stmt = $mysqli->prepare($ing_sql);
if ($ing_stmt) {
    $ing_stmt->execute();
    $ing_res = $ing_stmt->get_result();
    while ($row = $ing_res->fetch_object()) {
        $all_ingredients[] = $row;
    }
    $ing_stmt->close();
}

// Fetch current product ingredient links
if (isset($product)) {
    $link_sql = "SELECT ingredient_id, quantity_required FROM rpos_product_ingredients WHERE product_id = ?";
    $link_stmt = $mysqli->prepare($link_sql);
    if ($link_stmt) {
        $link_stmt->bind_param('s', $product->prod_id);
        $link_stmt->execute();
        $link_res = $link_stmt->get_result();
        while ($row = $link_res->fetch_assoc()) {
            $current_links[$row['ingredient_id']] = (float)$row['quantity_required'];
        }
        $link_stmt->close();
    }
}

// Get all products for quick-fill (excluding current one)
$all_products = [];
$prodListSql = "SELECT prod_id, prod_name FROM rpos_products WHERE prod_id != ? ORDER BY prod_name ASC";
$prodListStmt = $mysqli->prepare($prodListSql);
if ($prodListStmt) {
    $prodListStmt->bind_param('s', $product->prod_id);
    $prodListStmt->execute();
    $prodListRes = $prodListStmt->get_result();
    while ($p = $prodListRes->fetch_object()) {
        $all_products[] = $p;
    }
    $prodListStmt->close();
}

// Handle update action
if(isset($_POST['update_product'])) {
    $prod_id = $_POST['prod_id'];
    $prod_code = trim($_POST['prod_code']);
    $prod_name = trim($_POST['prod_name']);
    $prod_category = $_POST['prod_category'];
    $prod_price = (float)$_POST['prod_price'];
    $prod_quantity = (int)$_POST['prod_quantity'];
    $prod_threshold = (int)$_POST['prod_threshold'];
    $staff_id = $_SESSION['staff_id'];
    $selected_ingredients = isset($_POST['ingredients']) ? $_POST['ingredients'] : [];
    
    // Validate inputs
    if(empty($prod_code) || empty($prod_name) || empty($prod_category) || $prod_price <= 0) {
        $_SESSION['error'] = "Please fill all required fields with valid data";
        header("Location: update_product.php?update=".$prod_id);
        exit;
    }

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Only check for duplicates if the code or name has changed
        if($prod_code !== $product->prod_code || $prod_name !== $product->prod_name) {
            $check_query = "SELECT prod_id FROM rpos_products 
                            WHERE (prod_code = ? OR prod_name = ?) 
                            AND prod_id != ?";
            $check_stmt = $mysqli->prepare($check_query);
            $check_stmt->bind_param('ssi', $prod_code, $prod_name, $prod_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if($check_result->num_rows > 0) {
                $conflicts = [];
                while($row = $check_result->fetch_assoc()) {
                    $conflicts[] = $row['prod_id'];
                }
                throw new Exception("Product code or name already exists in records: ".implode(', ', $conflicts));
            }
        }
            
        // Handle image upload
        $prod_img = $product->prod_img; // Default to existing image
        
        if(!empty($_FILES['prod_img']['name'])) {
            $target_dir = "../admin/assets/img/products/";
            $imageFileType = strtolower(pathinfo($_FILES["prod_img"]["name"], PATHINFO_EXTENSION));
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES["prod_img"]["tmp_name"]);
            if($check === false) {
                throw new Exception("File is not an image.");
            }
            
            // Generate unique filename
            $new_filename = uniqid().'.'.$imageFileType;
            $target_file = $target_dir.$new_filename;
            
            // Upload file
            if(!move_uploaded_file($_FILES["prod_img"]["tmp_name"], $target_file)) {
                throw new Exception("Sorry, there was an error uploading your file.");
            }
            
            // Delete old image if it's not the default and exists
            if($product->prod_img != 'default.png' && file_exists($target_dir.$product->prod_img)) {
                unlink($target_dir.$product->prod_img);
            }
            
            $prod_img = $new_filename;
        }
        
        // Update product in database
        $update = "UPDATE rpos_products SET 
                    prod_code = ?, 
                    prod_name = ?, 
                    prod_category = ?, 
                    prod_price = ?, 
                    prod_quantity = ?, 
                    prod_threshold = ?, 
                    prod_img = ? 
                WHERE prod_id = ?";
                
        $stmt = $mysqli->prepare($update);
        $stmt->bind_param('sssddiss', 
            $prod_code, 
            $prod_name, 
            $prod_category, 
            $prod_price, 
            $prod_quantity, 
            $prod_threshold, 
            $prod_img, 
            $prod_id);
        $stmt->execute();
        
        // Do not hard fail if no row was changed; we may still update ingredients
        // Sync product ingredients: replace existing with submitted selection
        // First, delete all current links for this product
        $del_sql = "DELETE FROM rpos_product_ingredients WHERE product_id = ?";
        $del_stmt = $mysqli->prepare($del_sql);
        if ($del_stmt) {
            $del_stmt->bind_param('s', $prod_id);
            $del_stmt->execute();
            $del_stmt->close();
        }

        // Insert new links
        if (!empty($selected_ingredients)) {
            $ins_sql = "INSERT INTO rpos_product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";
            $ins_stmt = $mysqli->prepare($ins_sql);
            if (!$ins_stmt) {
                throw new Exception("Failed to prepare ingredient insert: " . $mysqli->error);
            }
            foreach ($selected_ingredients as $ingredient_data) {
                if (!isset($ingredient_data['ingredient_id'])) { continue; }
                $ing_id = $ingredient_data['ingredient_id'];
                $qty_req = isset($ingredient_data['quantity_required']) && $ingredient_data['quantity_required'] !== '' ? (float)$ingredient_data['quantity_required'] : 0.0;
                if ($qty_req <= 0) { continue; }
                $ins_stmt->bind_param('ssd', $prod_id, $ing_id, $qty_req);
                if (!$ins_stmt->execute()) {
                    throw new Exception("Failed to save ingredient link: " . $ins_stmt->error);
                }
            }
            $ins_stmt->close();
        }

        
        // Prepare activity log data
        $activity_type = 'Update';
        $reference_code = 'UPD-'.uniqid();
        $quantity_change = $prod_quantity - $product->prod_quantity;
        $previous_quantity = $product->prod_quantity;
        $new_quantity = $prod_quantity;
        
        // Determine notes based on changes
        $changes = [];
        if($prod_code != $product->prod_code) $changes[] = "code changed";
        if($prod_name != $product->prod_name) $changes[] = "name changed";
        if($prod_category != $product->prod_category) $changes[] = "category changed";
        if($prod_price != $product->prod_price) $changes[] = "price changed";
        if($prod_quantity != $product->prod_quantity) $changes[] = "quantity changed";
        if($prod_threshold != $product->prod_threshold) $changes[] = "threshold changed";
        if($prod_img != $product->prod_img) $changes[] = "image changed";
        
        $notes = "Product updated: " . implode(', ', $changes);
        
        // Log the activity directly
        $log_query = "INSERT INTO rpos_inventory_logs 
                     (product_id, activity_type, quantity_change, previous_quantity, new_quantity, staff_id, notes, reference_code) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $log_stmt = $mysqli->prepare($log_query);
        
        if ($log_stmt) {
            // product_id is varchar, bind as string
            $log_stmt->bind_param('ssiiiiss', 
                $prod_id, 
                $activity_type, 
                $quantity_change, 
                $previous_quantity, 
                $new_quantity, 
                $staff_id, 
                $notes, 
                $reference_code);
            
            if (!$log_stmt->execute()) {
                throw new Exception("Failed to log update activity: " . $log_stmt->error);
            }
            $log_stmt->close();
        } else {
            throw new Exception("Failed to prepare log statement: " . $mysqli->error);
        }
        
        $mysqli->commit();
        $_SESSION['success'] = "Product updated successfully";
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: update_product.php?update=".$prod_id);
        exit;
    }
}
?>

<!-- The rest of the HTML remains exactly the same -->
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
              <h6 class="h2 text-white d-inline-block mb-0">Update Product</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="products.php" class="text-gold">Products</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Update</li>
                </ol>
              </nav>
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
                <div class="col-8">
                  <h3 class="mb-0 text-gold">Edit Product Details</h3>
                </div>
                <div class="col-4 text-right">
                  <a href="products.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                  </a>
                </div>
              </div>
            </div>

            <div class="card-body">
              <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="prod_id" value="<?php echo $product->prod_id; ?>">
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_code">Product Code *</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="prod_code" name="prod_code" value="<?php echo htmlspecialchars($product->prod_code); ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_name">Product Name *</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="prod_name" name="prod_name" value="<?php echo htmlspecialchars($product->prod_name); ?>" required>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_category">Category *</label>
                      <select class="form-control bg-transparent text-light border-light" id="prod_category" name="prod_category" required>
                        <option value="">Select Category</option>
                        <option value="Food" <?php echo ($product->prod_category == 'Food') ? 'selected' : ''; ?>>Food</option>
                        <option value="Beverage" <?php echo ($product->prod_category == 'Beverage') ? 'selected' : ''; ?>>Beverage</option>
                        <option value="Snack" <?php echo ($product->prod_category == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                        <option value="Other" <?php echo ($product->prod_category == 'Other') ? 'selected' : ''; ?>>Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_price">Price (₱) *</label>
                      <input type="number" step="0.01" min="0.01" class="form-control bg-transparent text-light border-light" id="prod_price" name="prod_price" value="<?php echo htmlspecialchars($product->prod_price); ?>" required>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_quantity">Current Stock *</label>
                      <input type="number" min="0" class="form-control bg-transparent text-light border-light" id="prod_quantity" name="prod_quantity" value="<?php echo htmlspecialchars($product->prod_quantity); ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_threshold">Stock Threshold *</label>
                      <input type="number" min="1" class="form-control bg-transparent text-light border-light" id="prod_threshold" name="prod_threshold" value="<?php echo htmlspecialchars($product->prod_threshold); ?>" required>
                      <small class="text-muted">System will alert when stock falls below this number</small>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="prod_img">Product Image</label>
                      <div class="custom-file">
                        <input type="file" class="custom-file-input bg-transparent" id="prod_img" name="prod_img" accept="image/*">
                        <label class="custom-file-label bg-transparent text-light border-light" for="prod_img">Choose file</label>
                      </div>
                      <?php if(!empty($product->prod_img)): ?>
                        <div class="mt-3">
                          <img src="../admin/assets/img/products/<?php echo htmlspecialchars($product->prod_img); ?>" alt="Current Product Image" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                          <p class="text-muted mt-2">Current image: <?php echo htmlspecialchars($product->prod_img); ?></p>
                        </div>
                      <?php endif; ?>
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
                                  <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?></option>
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
                                  <option value="<?php echo $p->prod_id; ?>"><?php echo htmlspecialchars($p->prod_name); ?></option>
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
                      <label class="form-control-label text-gold">Product Ingredients</label>
                      <div class="card" style="background: rgba(26, 26, 46, 0.5); border: 1px solid rgba(192, 160, 98, 0.3);">
                        <div class="card-body">
                          <p class="text-muted mb-3">Select ingredients and specify quantities required per product unit:</p>
                          <div id="ingredients-container">
                            <?php if(empty($all_ingredients)): ?>
                              <p class="text-warning">No ingredients available. Please add ingredients first.</p>
                            <?php else: ?>
                              <?php foreach($all_ingredients as $index => $ingredient): 
                                  $checked = isset($current_links[$ingredient->ingredient_id]);
                                  $qtyVal = $checked ? number_format($current_links[$ingredient->ingredient_id], 2, '.', '') : '';
                                ?>
                                <div class="ingredient-row mb-3 p-3" style="background: rgba(26, 26, 46, 0.3); border-radius: 5px; border: 1px solid rgba(192, 160, 98, 0.2);">
                                  <div class="row align-items-center">
                                    <div class="col-md-6">
                                      <div class="form-check">
                                        <input class="form-check-input ingredient-checkbox" type="checkbox" name="ingredients[<?php echo $index; ?>][ingredient_id]" value="<?php echo $ingredient->ingredient_id; ?>" id="ingredient_<?php echo $index; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-white" for="ingredient_<?php echo $index; ?>">
                                          <?php echo htmlspecialchars($ingredient->ingredient_name); ?>
                                          <small class="text-muted">(<?php echo htmlspecialchars($ingredient->ingredient_unit); ?>)</small>
                                        </label>
                                      </div>
                                    </div>
                                    <div class="col-md-4">
                                      <input type="number" step="0.01" min="0" class="form-control ingredient-quantity bg-transparent text-light border-light" name="ingredients[<?php echo $index; ?>][quantity_required]" placeholder="Quantity" <?php echo $checked ? '' : 'disabled'; ?> value="<?php echo $qtyVal; ?>">
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
                
                <div class="text-center">
                  <button type="submit" name="update_product" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Product
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
    /* Reuse the same styles from products.php */
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
    
    .form-control, .custom-file-label {
      background-color: rgba(26, 26, 46, 0.5) !important;
      border-color: rgba(192, 160, 98, 0.3) !important;
      color: var(--text-light) !important;
    }
    
    .form-control:focus {
      background-color: rgba(26, 26, 46, 0.7) !important;
      border-color: var(--accent-gold) !important;
      color: var(--text-light) !important;
      box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
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
    
    .img-thumbnail {
      background-color: rgba(26, 26, 46, 0.5);
      border-color: rgba(192, 160, 98, 0.3);
    }
  </style>
  
  <script>
    // Handle ingredient checkbox changes to enable/disable quantity inputs
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.getElementById('ingredients-container');
      if (!container) return;
      container.querySelectorAll('.ingredient-row').forEach(function(row) {
        const checkbox = row.querySelector('.ingredient-checkbox');
        const quantity = row.querySelector('.ingredient-quantity');
        if (!checkbox || !quantity) return;
        const syncState = () => {
          if (checkbox.checked) {
            quantity.disabled = false;
            quantity.required = true;
            if (!quantity.value) quantity.value = '1.00';
          } else {
            quantity.disabled = true;
            quantity.required = false;
            quantity.value = '';
          }
        };
        checkbox.addEventListener('change', syncState);
        syncState();
      });
    });
    // Show file name when image is selected
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
      var fileName = document.getElementById("prod_img").files[0]?.name || "Choose file";
      var nextSibling = e.target.nextElementSibling;
      nextSibling.innerText = fileName;
    });
    
    // Show success message if update was successful
    <?php if(isset($success)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        swal({
          title: "Success",
          text: "<?php echo $success; ?>",
          icon: "success",
          buttons: false,
          timer: 1500
        });
      });
    <?php endif; ?>
    
    // Show error message if there was an error
    <?php if(isset($err)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        swal({
          title: "Error",
          text: "<?php echo $err; ?>",
          icon: "error",
        });
      });
    <?php endif; ?>

    // Quick-fill logic
    async function fetchRecipe(productId) {
      if (!productId) return [];
      const res = await fetch('get_product_recipe.php?product_id=' + encodeURIComponent(productId));
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