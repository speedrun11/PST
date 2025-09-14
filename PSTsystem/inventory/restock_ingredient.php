<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
$staff_id = $_SESSION['staff_id'];
if(isset($_GET['restock'])) {
    $id = $_GET['restock'];
    $query = "SELECT * FROM rpos_ingredients WHERE ingredient_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $ingredient = $res->fetch_object();
    if(!$ingredient) {
        $_SESSION['error'] = "Ingredient not found";
        header("Location: ingredients.php");
        exit;
    }
}
$suppliers = array();
$ret = "SELECT * FROM rpos_suppliers ORDER BY supplier_name ASC";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($supplier = $res->fetch_object()) {
    $suppliers[] = $supplier;
}
if(isset($_POST['restock_ingredient'])) {
    $ingredient_id = $_POST['ingredient_id'];
    $restock_quantity = (int)$_POST['restock_quantity'];
    $supplier_id = (int)$_POST['supplier_id'];
    $notes = trim($_POST['notes']);
    $restock_date = $_POST['restock_date'];
    $reference_code = 'RST-' . uniqid();
    if($restock_quantity <= 0) {
        $_SESSION['error'] = "Restock quantity must be greater than 0";
        header("Location: restock_ingredient.php?restock=".$ingredient_id);
        exit;
    }
    $supplier_name = '';
    foreach($suppliers as $s) {
        if($s->supplier_id == $supplier_id) {
            $supplier_name = $s->supplier_name;
            break;
        }
    }
    $notes = "Restocked from supplier: $supplier_name. " . $notes;
    $mysqli->begin_transaction();
    try {
        $current_quantity = $ingredient->ingredient_quantity;
        $new_quantity = $current_quantity + $restock_quantity;
        $update = "UPDATE rpos_ingredients SET ingredient_quantity = ?, last_restocked = ?, supplier_id = ? WHERE ingredient_id = ?";
        $stmt = $mysqli->prepare($update);
        $stmt->bind_param('isis', $new_quantity, $restock_date, $supplier_id, $ingredient_id);
        $stmt->execute();
        if($stmt->affected_rows === 0) {
            throw new Exception("Failed to update ingredient quantity");
        }
        $log_query = "INSERT INTO rpos_ingredient_logs (ingredient_id, activity_type, quantity_change, previous_quantity, new_quantity, staff_id, notes, reference_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $log_stmt = $mysqli->prepare($log_query);
        if ($log_stmt) {
            $activity_type = 'Restock';
            $log_stmt->bind_param('siiiiiss', $ingredient_id, $activity_type, $restock_quantity, $current_quantity, $new_quantity, $staff_id, $notes, $reference_code);
            if (!$log_stmt->execute()) {
                throw new Exception("Failed to log restock activity: " . $log_stmt->error);
            }
            $log_stmt->close();
        } else {
            throw new Exception("Failed to prepare log statement: " . $mysqli->error);
        }
        $mysqli->commit();
        $_SESSION['success'] = "Ingredient restocked successfully";
        header("Location: ingredients.php");
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: restock_ingredient.php?restock=".$ingredient_id);
        exit;
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
              <h6 class="h2 text-white d-inline-block mb-0">Restock Ingredient</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                  <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>
                  <li class="breadcrumb-item"><a href="ingredients.php" class="text-gold">Ingredients</a></li>
                  <li class="breadcrumb-item active text-gold" aria-current="page">Restock</li>
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
                  <h3 class="mb-0 text-gold">Restock <?php echo htmlspecialchars($ingredient->ingredient_name); ?></h3>
                </div>
                <div class="col-4 text-right">
                  <span class="badge badge-warning">Current Stock: <?php echo htmlspecialchars($ingredient->ingredient_quantity); ?></span>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="post">
                <input type="hidden" name="ingredient_id" value="<?php echo $ingredient->ingredient_id; ?>">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="restock_quantity">Quantity to Add *</label>
                      <input type="number" min="1" class="form-control bg-transparent text-light border-light" id="restock_quantity" name="restock_quantity" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="current_stock">Current Stock</label>
                      <input type="text" class="form-control bg-transparent text-light border-light" id="current_stock" value="<?php echo htmlspecialchars($ingredient->ingredient_quantity); ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-control-label text-gold" for="supplier_id">Supplier *</label>
                      <select class="form-control bg-transparent text-light border-light" id="supplier_id" name="supplier_id" required>
                          <option value="">Select Supplier</option>
                          <?php foreach($suppliers as $supplier): ?>
                              <option value="<?php echo $supplier->supplier_id; ?>" <?php if($ingredient->supplier_id == $supplier->supplier_id) echo 'selected'; ?>>
                                  <?php echo htmlspecialchars($supplier->supplier_name); ?>
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-control-label text-gold" for="restock_date">Restock Date *</label>
                      <input type="date" class="form-control bg-transparent text-light border-light" id="restock_date" name="restock_date" value="<?php echo date('Y-m-d'); ?>" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-control-label text-gold" for="notes">Notes</label>
                      <textarea class="form-control bg-transparent text-light border-light" id="notes" name="notes" rows="3" placeholder="Optional notes about this restock"></textarea>
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-md-12">
                    <div class="alert alert-warning" style="background-color: rgba(192, 160, 98, 0.2); border-color: var(--accent-gold);">
                      <i class="fas fa-info-circle"></i> After restocking, the new stock will be <strong><?php echo htmlspecialchars($ingredient->ingredient_quantity); ?> + [quantity added]</strong>
                    </div>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" name="restock_ingredient" class="btn btn-primary">
                    <i class="fas fa-arrow-up"></i> Restock Ingredient
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
    .badge-warning {
      background-color: var(--accent-gold);
      color: var(--text-dark);
    }
    .alert-warning {
      background-color: rgba(192, 160, 98, 0.2);
      border-color: var(--accent-gold);
      color: var(--text-light);
    }
    @media (max-width: 768px) {
      .card {
        backdrop-filter: blur(4px);
      }
    }
  </style>
  <script>
    document.getElementById('restock_quantity').addEventListener('input', function() {
      const currentStock = <?php echo $ingredient->ingredient_quantity; ?>;
      const restockQty = parseInt(this.value) || 0;
      const newStock = currentStock + restockQty;
      const alertBox = document.querySelector('.alert-warning');
      if (alertBox) {
        alertBox.innerHTML = `<i class="fas fa-info-circle"></i> After restocking, the new stock will be <strong>${currentStock} + ${restockQty} = ${newStock}</strong>`;
      }
    });
  </script>
</body>
</html>
