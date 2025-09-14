<?php

session_start();

include('config/config.php');

include('config/checklogin.php');

check_login();



// CSV download handler for full report (products + ingredients)

if (isset($_GET['download']) && $_GET['download'] === 'all_csv') {

    header('Content-Type: text/csv; charset=utf-8');

    header('Content-Disposition: attachment; filename=inventory_full_report_'.date('Ymd_His').'.csv');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Section','Code','Name','Quantity','Threshold','Category/Unit']);



    // Products

    $pstmt = $mysqli->prepare("SELECT prod_code, prod_name, prod_quantity, prod_threshold, prod_category FROM rpos_products ORDER BY prod_name ASC");

    if ($pstmt) {

        $pstmt->execute();

        $pres = $pstmt->get_result();

        while ($p = $pres->fetch_assoc()) {

            fputcsv($output, ['Product', $p['prod_code'], $p['prod_name'], $p['prod_quantity'], $p['prod_threshold'], $p['prod_category']]);

        }

        $pstmt->close();

    }



    // Ingredients

    $istmt = $mysqli->prepare("SELECT ingredient_code, ingredient_name, ingredient_quantity, ingredient_threshold, ingredient_unit FROM rpos_ingredients ORDER BY ingredient_name ASC");

    if ($istmt) {

        $istmt->execute();

        $ires = $istmt->get_result();

        while ($i = $ires->fetch_assoc()) {

            // Convert grams to kilograms for CSV export

            $qty = (int)$i['ingredient_quantity'];

            $threshold = (int)$i['ingredient_threshold'];

            $unit = strtolower($i['ingredient_unit']);

            

            if ($unit === 'grams' || $unit === 'gram' || $unit === 'g') {

                $qty = round($qty / 1000, 2); // Convert to kg

                $threshold = round($threshold / 1000, 2); // Convert to kg

                $unit = 'kg'; // Update unit display

            }

            

            fputcsv($output, ['Ingredient', $i['ingredient_code'], $i['ingredient_name'], $qty, $threshold, $unit]);

        }

        $istmt->close();

    }

    fclose($output);

    exit;

}



require_once('partials/_head.php');



// Fetch all products

$ret = "SELECT * FROM rpos_products ORDER BY prod_name ASC";

$stmt = $mysqli->prepare($ret);

$stmt->execute();

$res = $stmt->get_result();

$labels = [];

$current_stock = [];

$thresholds = [];

// Dedicated arrays for Stock Level Report (products only, effective quantities)

$product_labels = [];

$product_current_stock = [];

$product_thresholds = [];

// Dedicated arrays for Ingredients Stock Level Report

$ingredient_labels = [];

$ingredient_current_stock = [];

$ingredient_thresholds = [];

$low_stock_rows = [];

$critical_stock_rows = [];

$products_by_id = [];

$inventory_valuation = 0;

$supplier_performance_data = [];

$last_report_generation = date('M d, Y H:i');

while ($row = $res->fetch_assoc()) {

  $products_by_id[$row['prod_id']] = $row;

}



// Process products for charts and reports

foreach ($products_by_id as $pid => $prod) {

  $qty = (int)$prod['prod_quantity'];

  // For the Stock Level Report (products only)

  $product_labels[] = $prod['prod_name'];

  $product_current_stock[] = $qty;

  $product_thresholds[] = (int)$prod['prod_threshold'];

  // For combined dataset (legacy; used elsewhere if needed)

  $labels[] = $prod['prod_name'] . ' (Product)';

  $current_stock[] = $qty;

  $thresholds[] = (int)$prod['prod_threshold'];

  $pct = ($prod['prod_threshold'] > 0) ? ($qty / $prod['prod_threshold']) : 1;

  // Add quantity to the row for rendering tables

  $prod_with_qty = $prod;

  $prod_with_qty['effective_quantity'] = $qty;

  // Calculate inventory valuation (using raw quantity)

  $inventory_valuation += $qty * (float)$prod['prod_price'];

  if ($pct <= 0.25) {

    $critical_stock_rows[] = $prod_with_qty;

  } elseif ($pct <= 1) {

    $low_stock_rows[] = $prod_with_qty;

  }

}



// Fetch all ingredients

$ret_ingredients = "SELECT * FROM rpos_ingredients ORDER BY ingredient_name ASC";

$stmt_ingredients = $mysqli->prepare($ret_ingredients);

$stmt_ingredients->execute();

$res_ingredients = $stmt_ingredients->get_result();

while ($row_ingredients = $res_ingredients->fetch_assoc()) {

  // Convert grams to kilograms for better readability

  $qty = (int)$row_ingredients['ingredient_quantity'];

  $threshold = (int)$row_ingredients['ingredient_threshold'];

  $unit = strtolower($row_ingredients['ingredient_unit']);

  

  if ($unit === 'grams' || $unit === 'gram' || $unit === 'g') {

    $qty = round($qty / 1000, 2); // Convert to kg

    $threshold = round($threshold / 1000, 2); // Convert to kg

  }

  

  $labels[] = $row_ingredients['ingredient_name'] . ' (Ingredient)';

  $current_stock[] = $qty;

  $thresholds[] = $threshold;

  // For Ingredients Stock Level Report

  $ingredient_labels[] = $row_ingredients['ingredient_name'];

  $ingredient_current_stock[] = $qty;

  $ingredient_thresholds[] = $threshold;

}



// Calculate supplier performance data (products + ingredients)

$supplier_query = "SELECT s.supplier_name, 

                   COUNT(DISTINCT p.prod_id) as products_supplied,

                   COUNT(DISTINCT i.ingredient_id) as ingredients_supplied,

                   (COUNT(DISTINCT p.prod_id) + COUNT(DISTINCT i.ingredient_id)) as total_items_supplied,

                   AVG(p.prod_price) as avg_product_price,

                   SUM(CASE WHEN p.prod_quantity <= p.prod_threshold THEN 1 ELSE 0 END) as low_stock_products,

                   SUM(CASE WHEN i.ingredient_quantity <= i.ingredient_threshold THEN 1 ELSE 0 END) as low_stock_ingredients,

                   (SUM(CASE WHEN p.prod_quantity <= p.prod_threshold THEN 1 ELSE 0 END) + 

                    SUM(CASE WHEN i.ingredient_quantity <= i.ingredient_threshold THEN 1 ELSE 0 END)) as total_low_stock_items

                   FROM rpos_suppliers s

                   LEFT JOIN rpos_products p ON s.supplier_id = p.supplier_id

                   LEFT JOIN rpos_ingredients i ON s.supplier_id = i.supplier_id

                   GROUP BY s.supplier_id, s.supplier_name

                   ORDER BY total_items_supplied DESC";

$supplier_stmt = $mysqli->prepare($supplier_query);

if ($supplier_stmt) {

  $supplier_stmt->execute();

  $supplier_res = $supplier_stmt->get_result();

  while ($supplier = $supplier_res->fetch_assoc()) {

    $supplier_performance_data[] = $supplier;

  }

  $supplier_stmt->close();

}



// Build inventory movement from logs (last 14 days)

$days = 14;

$movement_labels = [];

$stock_in_data = [];

$stock_out_data = [];

for ($i = $days - 1; $i >= 0; $i--) {

  $movement_labels[] = date('M d', strtotime('-'.$i.' day'));

}



// Aggregate product logs

$prod_q = $mysqli->prepare("SELECT DATE(activity_date) d, activity_type, SUM(quantity_change) q FROM rpos_inventory_logs WHERE activity_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY d, activity_type");

if ($prod_q) {

  $dparam = $days;

  $prod_q->bind_param('i', $dparam);

  $prod_q->execute();

  $prod_res = $prod_q->get_result();

  $per_day = [];

  while ($r = $prod_res->fetch_assoc()) {

    $d = date('M d', strtotime($r['d']));

    if (!isset($per_day[$d])) $per_day[$d] = ['in'=>0,'out'=>0];

    $type = $r['activity_type'];

    $q = (int)$r['q'];

    if (in_array($type, ['Restock','Add'])) {

      $per_day[$d]['in'] += max(0, $q);

    } elseif (in_array($type, ['Sale','Waste','Adjustment','Transfer'])) {

      $per_day[$d]['out'] += abs(min(0, $q));

    }

  }

  foreach ($movement_labels as $lab) {

    $stock_in_data[] = isset($per_day[$lab]) ? (int)$per_day[$lab]['in'] : 0;

    $stock_out_data[] = isset($per_day[$lab]) ? (int)$per_day[$lab]['out'] : 0;

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

              <h6 class="h2 text-white d-inline-block mb-0">Inventory Reports</h6>

              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">

                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">

                    <li class="breadcrumb-item"><a href="inventory_dashboard.php"><i class="fas fa-home text-gold"></i></a></li>

                    <li class="breadcrumb-item"><a href="inventory_dashboard.php" class="text-gold">Inventory</a></li>

                    <li class="breadcrumb-item active text-gold" aria-current="page">Reports</li>

                </ol>

              </nav>

            </div>

            <div class="col-lg-6 col-5 text-right no-print">

              <button class="btn btn-sm btn-primary" onclick="window.print()">

                <i class="fas fa-print"></i> Print Report

              </button>

              <a href="inventory_reports.php?download=all_csv" class="btn btn-sm btn-secondary">

                <i class="fas fa-file-csv"></i> Download Full Report (CSV)

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

                <!-- Stock Level Report (Products) -->

                <div class="col-md-6 mb-4">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Product Stock Level Report</h3>

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



                <!-- Ingredients Stock Level Report -->

                <div class="col-md-6 mb-4">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Ingredient Stock Level Report</h3>

                    </div>

                    <div class="card-body">

                      <div class="chart-container">

                        <canvas id="ingredientStockChart" height="300"></canvas>

                      </div>

                      <div class="mt-3">

                        <a href="generate_report.php?type=ingredients_stock" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download Full Report</a>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- Inventory Movement Report -->

              <div class="row">

                <div class="col-md-12 mb-4">

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

                              <td>Products below reorder threshold (<?php echo count($low_stock_rows); ?> items)</td>

                              <td><?php echo $last_report_generation; ?></td>

                              <td>

                                <a href="inventory_reports.php#low-stock" class="btn btn-sm btn-primary">

                                  <i class="fas fa-eye"></i> View

                                </a>

                              </td>

                            </tr>

                            <tr>

                              <th scope="row">Critical Stock Report</th>

                              <td>Products below 25% of threshold (<?php echo count($critical_stock_rows); ?> items)</td>

                              <td><?php echo $last_report_generation; ?></td>

                              <td>

                                <a href="inventory_reports.php#critical-stock" class="btn btn-sm btn-primary">

                                  <i class="fas fa-eye"></i> View

                                </a>

                              </td>

                            </tr>

                            <tr>

                              <th scope="row">Inventory Valuation</th>

                              <td>Total value: ₱<?php echo number_format($inventory_valuation, 2); ?></td>

                              <td><?php echo $last_report_generation; ?></td>

                              <td>

                                <a href="inventory_reports.php#valuation" class="btn btn-sm btn-primary">

                                  <i class="fas fa-eye"></i> View

                                </a>

                              </td>

                            </tr>

                            <tr>

                              <th scope="row">Supplier Performance</th>

                              <td><?php echo count($supplier_performance_data); ?> suppliers analyzed (products + ingredients)</td>

                              <td><?php echo $last_report_generation; ?></td>

                              <td>

                                <a href="inventory_reports.php#supplier-performance" class="btn btn-sm btn-primary">

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



              <!-- Low Stock List -->

              <div class="row mt-4" id="low-stock">

                <div class="col-12">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Low Stock (<= 100% of threshold)</h3>

                    </div>

                    <div class="card-body">

                      <div class="table-responsive">

                        <table class="table align-items-center table-flush">

                          <thead class="thead-dark">

                            <tr>

                              <th class="text-gold">Code</th>

                              <th class="text-gold">Product</th>

                              <th class="text-gold">Stock</th>

                              <th class="text-gold">Threshold</th>

                            </tr>

                          </thead>

                          <tbody>

                            <?php if(empty($low_stock_rows)): ?>

                              <tr><td colspan="4" class="text-center text-white">No low stock items</td></tr>

                            <?php else: foreach($low_stock_rows as $p): ?>

                              <tr>

                                <td class="text-white"><?php echo htmlspecialchars($p['prod_code']); ?></td>

                                <td class="text-white"><?php echo htmlspecialchars($p['prod_name']); ?></td>

                                <td class="text-white"><?php echo isset($p['effective_quantity']) ? (int)$p['effective_quantity'] : (int)$p['prod_quantity']; ?></td>

                                <td class="text-white"><?php echo (int)$p['prod_threshold']; ?></td>

                              </tr>

                            <?php endforeach; endif; ?>

                          </tbody>

                        </table>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- Critical Stock List -->

              <div class="row mt-4" id="critical-stock">

                <div class="col-12">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Critical Stock (<= 25% of threshold)</h3>

                    </div>

                    <div class="card-body">

                      <div class="table-responsive">

                        <table class="table align-items-center table-flush">

                          <thead class="thead-dark">

                            <tr>

                              <th class="text-gold">Code</th>

                              <th class="text-gold">Product</th>

                              <th class="text-gold">Stock</th>

                              <th class="text-gold">Threshold</th>

                            </tr>

                          </thead>

                          <tbody>

                            <?php if(empty($critical_stock_rows)): ?>

                              <tr><td colspan="4" class="text-center text-white">No critical stock items</td></tr>

                            <?php else: foreach($critical_stock_rows as $p): ?>

                              <tr>

                                <td class="text-white"><?php echo htmlspecialchars($p['prod_code']); ?></td>

                                <td class="text-white"><?php echo htmlspecialchars($p['prod_name']); ?></td>

                                <td class="text-white"><?php echo isset($p['effective_quantity']) ? (int)$p['effective_quantity'] : (int)$p['prod_quantity']; ?></td>

                                <td class="text-white"><?php echo (int)$p['prod_threshold']; ?></td>

                              </tr>

                            <?php endforeach; endif; ?>

                          </tbody>

                        </table>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- Inventory Valuation Section -->

              <div class="row mt-4" id="valuation">

                <div class="col-12">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Inventory Valuation Report</h3>

                    </div>

                    <div class="card-body">

                      <div class="row">

                        <div class="col-md-6">

                          <div class="card bg-gradient-primary text-white">

                            <div class="card-body">

                              <h4 class="card-title">Total Inventory Value</h4>

                              <h2 class="mb-0">₱<?php echo number_format($inventory_valuation, 2); ?></h2>

                              <p class="card-text">Based on effective stock levels</p>

                            </div>

                          </div>

                        </div>

                        <div class="col-md-6">

                          <div class="card bg-gradient-success text-white">

                            <div class="card-body">

                              <h4 class="card-title">Total Products</h4>

                              <h2 class="mb-0"><?php echo count($products_by_id); ?></h2>

                              <p class="card-text">Items in inventory</p>

                            </div>

                          </div>

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- Supplier Performance Section -->

              <div class="row mt-4" id="supplier-performance">

                <div class="col-12">

                  <div class="card shadow">

                    <div class="card-header border-0">

                      <h3 class="mb-0 text-gold">Supplier Performance Report</h3>

                    </div>

                    <div class="card-body">

                      <div class="table-responsive">

                        <table class="table align-items-center table-flush">

                          <thead class="thead-dark">

                            <tr>

                              <th class="text-gold">Supplier</th>

                              <th class="text-gold">Products Supplied</th>

                              <th class="text-gold">Ingredients Supplied</th>

                              <th class="text-gold">Total Items</th>

                              <th class="text-gold">Average Product Price</th>

                              <th class="text-gold">Low Stock Items</th>

                              <th class="text-gold">Performance</th>

                            </tr>

                          </thead>

                          <tbody>

                            <?php if(empty($supplier_performance_data)): ?>

                              <tr><td colspan="7" class="text-center text-white">No supplier data available</td></tr>

                            <?php else: foreach($supplier_performance_data as $supplier): ?>

                              <tr>

                                <td class="text-white"><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>

                                <td class="text-white"><?php echo (int)$supplier['products_supplied']; ?></td>

                                <td class="text-white"><?php echo (int)$supplier['ingredients_supplied']; ?></td>

                                <td class="text-white"><?php echo (int)$supplier['total_items_supplied']; ?></td>

                                <td class="text-white">₱<?php echo number_format((float)$supplier['avg_product_price'], 2); ?></td>

                                <td class="text-white"><?php echo (int)$supplier['total_low_stock_items']; ?></td>

                                <td class="text-white">

                                  <?php 

                                  $performance = 'Good';

                                  $class = 'text-success';

                                  $total_items = (int)$supplier['total_items_supplied'];

                                  $low_stock_items = (int)$supplier['total_low_stock_items'];

                                  

                                  if ($total_items > 0) {

                                    $low_stock_percentage = $low_stock_items / $total_items;

                                    if ($low_stock_percentage > 0.5) {

                                    $performance = 'Needs Attention';

                                    $class = 'text-warning';

                                  }

                                    if ($low_stock_percentage > 0.8) {

                                    $performance = 'Critical';

                                    $class = 'text-danger';

                                    }

                                  }

                                  ?>

                                  <span class="<?php echo $class; ?>"><?php echo $performance; ?></span>

                                </td>

                              </tr>

                            <?php endforeach; endif; ?>

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

          labels: <?php echo json_encode($product_labels); ?>,

          datasets: [{

            label: 'Current Stock',

            data: <?php echo json_encode($product_current_stock); ?>,

            backgroundColor: 'rgba(192, 160, 98, 0.7)',

            borderColor: 'rgba(192, 160, 98, 1)',

            borderWidth: 1

          }, {

            label: 'Reorder Threshold',

            data: <?php echo json_encode($product_thresholds); ?>,

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

      

      // Ingredient Stock Level Chart

      const ingredientCtx = document.getElementById('ingredientStockChart').getContext('2d');

      const ingredientChart = new Chart(ingredientCtx, {

        type: 'bar',

        data: {

          labels: <?php echo json_encode($ingredient_labels); ?>,

          datasets: [{

            label: 'Ingredient Stock',

            data: <?php echo json_encode($ingredient_current_stock); ?>,

            backgroundColor: 'rgba(74, 107, 87, 0.7)',

            borderColor: 'rgba(74, 107, 87, 1)',

            borderWidth: 1

          }, {

            label: 'Threshold',

            data: <?php echo json_encode($ingredient_thresholds); ?>,

            backgroundColor: 'rgba(58, 86, 115, 0.7)',

            borderColor: 'rgba(58, 86, 115, 1)',

            borderWidth: 1

          }]

        },

        options: {

          responsive: true,

          maintainAspectRatio: false,

          scales: {

            y: {

              beginAtZero: true,

              grid: { color: 'rgba(192, 160, 98, 0.1)' },

              ticks: { color: 'rgba(248, 245, 242, 0.8)' }

            },

            x: {

              grid: { color: 'rgba(192, 160, 98, 0.1)' },

              ticks: { color: 'rgba(248, 245, 242, 0.8)' }

            }

          },

          plugins: {

            legend: { labels: { color: 'rgba(248, 245, 242, 0.8)' } }

          }

        }

      });



      // Inventory Movement Chart (real data - last 14 days)

      const movementCtx = document.getElementById('inventoryMovementChart').getContext('2d');

      const movementChart = new Chart(movementCtx, {

        type: 'line',

        data: {

          labels: <?php echo json_encode($movement_labels); ?>,

          datasets: [{

            label: 'Stock In',

            data: <?php echo json_encode($stock_in_data); ?>,

            borderColor: 'rgba(74, 107, 87, 1)',

            backgroundColor: 'rgba(74, 107, 87, 0.1)',

            borderWidth: 2,

            fill: true

          }, {

            label: 'Stock Out',

            data: <?php echo json_encode($stock_out_data); ?>,

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