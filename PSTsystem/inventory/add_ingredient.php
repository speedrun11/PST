<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

function generateIngredientId($length = 10) {
    return bin2hex(random_bytes($length/2));
}

function generateIngredientCode() {
    $prefix = 'ING';
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . '-' . $random;
}

if(isset($_POST['add_ingredient'])) {
    $ingredient_id = generateIngredientId();
    $ingredient_code = generateIngredientCode();
    $ingredient_name = $_POST['ingredient_name'];
    $ingredient_unit = $_POST['ingredient_unit'];
    $ingredient_quantity = $_POST['ingredient_quantity'];
    $ingredient_threshold = $_POST['ingredient_threshold'];
    $ingredient_img = 'default.png';
    if(isset($_FILES['ingredient_img']) && $_FILES['ingredient_img']['error'] == 0) {
        $target_dir = "../admin/assets/img/ingredients/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["ingredient_img"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["ingredient_img"]["tmp_name"]);
        if($check !== false) {
            $ingredient_img = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $ingredient_img;
            if(!move_uploaded_file($_FILES["ingredient_img"]["tmp_name"], $target_file)) {
                $err = "Sorry, there was an error uploading your file.";
                $ingredient_img = 'default.png';
            }
        }
    }
    
    // Check if ingredient name already exists
    $check_query = "SELECT ingredient_id FROM rpos_ingredients WHERE ingredient_name = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param('s', $ingredient_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $err = "Ingredient name already exists";
        $check_stmt->close();
    } else {
      $check_stmt->close();
      $query = "INSERT INTO rpos_ingredients (ingredient_id, ingredient_code, ingredient_name, ingredient_img, ingredient_quantity, ingredient_threshold, ingredient_unit) VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt = $mysqli->prepare($query);
      if (!$stmt) {
          $err = "Prepare failed: " . $mysqli->error;
      } else {
          // Convert the integer values to variables that can be passed by reference
          $quantity = (int)$ingredient_quantity;
          $threshold = (int)$ingredient_threshold;
          $stmt->bind_param('ssssiis', $ingredient_id, $ingredient_code, $ingredient_name, $ingredient_img, $quantity, $threshold, $ingredient_unit);
          if($stmt->execute()) {
              // Log the activity
              $activity_type = 'Add';
              $reference_code = 'ADD-'.uniqid();
              $notes = "Added new ingredient: $ingredient_name";
              
              $log_query = "INSERT INTO rpos_ingredient_logs (ingredient_id, activity_type, quantity_change, previous_quantity, new_quantity, staff_id, notes, reference_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
              $log_stmt = $mysqli->prepare($log_query);
              
              if ($log_stmt) {
                  $staff_id = $_SESSION['staff_id'];
                  $prev_quantity = 0;
                  $new_quantity = (int)$ingredient_quantity;
                  $quantity_change = (int)$ingredient_quantity;
                  $log_stmt->bind_param('siiiiiss', $ingredient_id, $activity_type, $quantity_change, $prev_quantity, $new_quantity, $staff_id, $notes, $reference_code);
                  if (!$log_stmt->execute()) {
                      error_log("Failed to log ingredient activity: " . $log_stmt->error);
                  }
                  $log_stmt->close();
              }
              
              $_SESSION['success'] = "Ingredient added successfully";
              header("Location: ingredients.php");
              exit;
          } else {
              $err = "Failed to add ingredient: " . $stmt->error;
          }
          $stmt->close();
      }
    }
}
?>
<body>
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>
    <div style="background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Add New Ingredient</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                  <li class="breadcrumb-item"><a href="ingredients.php" class="text-gold">Ingredients</a></li>
                  <li class="breadcrumb-item active text-gold" aria-current="page">Add Ingredient</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="ingredients.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Ingredients
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col-12">
                  <h3 class="mb-0 text-gold">Ingredient Information</h3>
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
                      <label class="form-control-label text-gold" for="ingredient_name">Ingredient Name *</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="ingredient_name" name="ingredient_name" required placeholder="Enter ingredient name">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="ingredient_unit">Unit of Measurement *</label>
                      <select class="form-control bg-transparent text-light border-light" id="ingredient_unit" name="ingredient_unit" required>
                        <option value="">Select Unit</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="pieces">Pieces</option>
                        <option value="liters">Liters (L)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="cups">Cups</option>
                        <option value="tbsp">Tablespoons (tbsp)</option>
                        <option value="tsp">Teaspoons (tsp)</option>
                        <option value="boxes">Boxes</option>
                        <option value="bags">Bags</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="ingredient_quantity">Initial Stock *</label>
                      <input type="number" class="form-control bg-transparent text-light border-light" id="ingredient_quantity" name="ingredient_quantity" required placeholder="Enter initial quantity">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="ingredient_threshold">Stock Threshold *</label>
                      <input type="number" class="form-control bg-transparent text-light border-light" id="ingredient_threshold" name="ingredient_threshold" required placeholder="Enter threshold for low stock alert">
                      <small class="text-muted">System will alert when stock reaches this level</small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="ingredient_img">Ingredient Image</label>
                      <div class="custom-file">
                        <input type="file" class="custom-file-input bg-transparent" id="ingredient_img" name="ingredient_img" accept="image/*">
                        <label class="custom-file-label bg-transparent text-light border-light" for="ingredient_img">Choose file...</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="text-center mt-4">
                  <button type="submit" name="add_ingredient" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Ingredient
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
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
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
      var fileName = document.getElementById("ingredient_img").files[0]?.name || "Choose file";
      var nextSibling = e.target.nextElementSibling;
      nextSibling.innerText = fileName;
    });
  </script>
</body>
</html>
