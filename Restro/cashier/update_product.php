<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['updateProduct'])) {
  if (empty($_POST["prod_code"]) || empty($_POST["prod_name"]) || empty($_POST['prod_desc']) || empty($_POST['prod_price'])) {
    $err = "Blank Values Not Accepted";
  } else {
    $prod_id = $_POST['prod_id'];
    $prod_code  = $_POST['prod_code'];
    $prod_name = $_POST['prod_name'];
    $prod_desc = $_POST['prod_desc'];
    $prod_price = $_POST['prod_price'];
    $update = $_GET['update'];

    if (!empty($_FILES['prod_img']['name'])) {
      $prod_img = $_FILES['prod_img']['name'];
      move_uploaded_file($_FILES["prod_img"]["tmp_name"], "assets/img/products/" . $_FILES["prod_img"]["name"]);
      $postQuery = "UPDATE rpos_products SET prod_code=?, prod_name=?, prod_img=?, prod_desc=?, prod_price=? WHERE prod_id=?";
      $postStmt = $mysqli->prepare($postQuery);
      $rc = $postStmt->bind_param('ssssss', $prod_code, $prod_name, $prod_img, $prod_desc, $prod_price, $update);
    } else {
      $postQuery = "UPDATE rpos_products SET prod_code=?, prod_name=?, prod_desc=?, prod_price=? WHERE prod_id=?";
      $postStmt = $mysqli->prepare($postQuery);
      $rc = $postStmt->bind_param('sssss', $prod_code, $prod_name, $prod_desc, $prod_price, $update);
    }

    $postStmt->execute();
    if ($postStmt) {
      $success = "Product Updated" && header("refresh:1; url=products.php");
    } else {
      $err = "Please Try Again Or Try Later";
    }
  }
}
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Update Product</title>
    
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
    </style>
</head>
<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    $update = $_GET['update'];
    $ret = "SELECT * FROM  rpos_products WHERE prod_id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($prod = $res->fetch_object()) {
    ?>
      <!-- Header -->
      <div style="background-image: url(assets/img/theme/pastil.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
        <div class="container-fluid">
          <div class="header-body">
          </div>
        </div>
      </div>
      <!-- Page content -->
      <div class="container-fluid mt--8">
        <!-- Table -->
        <div class="row">
          <div class="col">
            <div class="card shadow">
              <div class="card-header border-0">
                <h3>Please Fill All Fields</h3>
              </div>
              <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Product Name</label>
                      <input type="text" name="prod_name" value="<?php echo $prod->prod_name; ?>" class="form-control">
                      <input type="hidden" name="prod_id" value="<?php echo $prod->prod_id; ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label>Product Code</label>
                      <input type="text" name="prod_code" value="<?php echo $prod->prod_code; ?>" class="form-control">
                    </div>
                  </div>
                  <hr>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Product Image</label>
                      <div class="file-input-container">
                          <label for="prod_img" class="file-input-label">
                              <span id="file-label-text"><?php echo $prod->prod_img ? $prod->prod_img : 'Choose product image...'; ?></span>
                          </label>
                          <input type="file" name="prod_img" id="prod_img" class="file-input" accept="image/*">
                          <div id="file-selected" class="file-selected">Current: <?php echo $prod->prod_img; ?></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label>Product Price</label>
                      <input type="text" name="prod_price" value="<?php echo $prod->prod_price; ?>" class="form-control">
                    </div>
                  </div>
                  <hr>
                  <div class="form-row">
                    <div class="col-md-12">
                      <label>Product Description</label>
                      <textarea rows="5" name="prod_desc" class="form-control"><?php echo $prod->prod_desc; ?></textarea>
                    </div>
                  </div>
                  <br>
                  <div class="form-row">
                    <div class="col-md-6">
                      <input type="submit" name="updateProduct" value="Update Product" class="btn btn-success">
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
      <?php
      require_once('partials/_footer.php');
    }
      ?>
      </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  <script>
    document.getElementById('prod_img').addEventListener('change', function(e) {
        var fileName = '';
        if (this.files && this.files.length > 0) {
            fileName = this.files[0].name;
            document.getElementById('file-label-text').textContent = fileName;
            document.getElementById('file-selected').textContent = 'Selected: ' + fileName;
        } else {
            document.getElementById('file-label-text').textContent = '<?php echo $prod->prod_img ? $prod->prod_img : "Choose product image..."; ?>';
            document.getElementById('file-selected').textContent = 'Current: <?php echo $prod->prod_img; ?>';
        }
    });
  </script>
</body>
</html>