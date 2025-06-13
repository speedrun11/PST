<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $adn = "DELETE FROM rpos_staff WHERE staff_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=hrm.php");
  } else {
    $err = "Try Again Later";
  }
}
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Staff Management</title>
    
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
        
        .card-title {
            color: var(--accent-gold) !important;
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
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
        
        .btn-danger {
            background: linear-gradient(135deg, rgba(62, 62, 62, 0.8), rgba(40, 40, 40, 0.6));
            border: 1px solid rgba(158, 43, 43, 0.4);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.8), rgba(120, 30, 30, 0.6));
        }
        
        .text-gold {
            color: var(--accent-gold) !important;
        }
        
        .table thead {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 2px solid var(--accent-gold);
        }
        
        .table thead th {
            color: var(--accent-gold) !important;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 1rem;
        }
        
        .table tbody {
            background: rgba(26, 26, 46, 0.8);
        }
        
        .table tbody td, .table tbody th {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid rgba(192, 160, 98, 0.1);
        }
        
        .table tbody tr:hover {
            background: rgba(192, 160, 98, 0.1) !important;
            transform: translateX(5px);
        }
        
        .card-header {
            background: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        @media (max-width: 768px) {
            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }
            
            .table tbody td, .table tbody th {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
        }
                .badge-role {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-cashier {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.8), rgba(120, 30, 30, 0.6));
            color: white;
        }
        .badge-inventory {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.8), rgba(58, 86, 115, 0.6));
            color: white;
        }
        .badge-both {
            background: linear-gradient(135deg, rgba(192, 160, 98, 0.8), rgba(158, 43, 43, 0.6));
            color: white;
        }
            .form-control {
        background-color: rgba(26, 26, 46, 0.8) !important;
        border: 1px solid rgba(192, 160, 98, 0.3) !important;
        color: var(--text-light) !important;
        border-radius: 5px;
        transition: all var(--transition-speed) ease;
    }

    .form-control:focus {
        background-color: rgba(26, 26, 46, 0.9) !important;
        border-color: var(--accent-gold) !important;
        box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25) !important;
        color: var(--text-light) !important;
    }

    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    select.form-control option {
        background-color: var(--primary-dark);
        color: var(--text-light);
    }

    .form-control-sm {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%23c0a062' fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.5rem center !important;
        background-size: 16px 12px !important;
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
    <div class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- You can add card stats here like in dashboard if needed -->
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
                <div class="col">
                  <h3 class="mb-0">Staff Management</h3>
                </div>
                <div class="col text-right">
                  <a href="add_staff.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Staff
                  </a>
                </div>
              </div>
              <!-- Role filter dropdown -->
              <div class="row mt-3">
                <div class="col-md-3">
                  <form method="get" action="">
                    <div class="form-group mb-0">
                      <select class="form-control form-control-sm" name="role_filter" onchange="this.form.submit()">
                          <option value="">All Roles</option>
                          <option value="cashier" <?php echo (isset($_GET['role_filter']) && $_GET['role_filter'] == 'cashier' ? 'selected' : ''); ?>>Cashiers</option>
                          <option value="inventory" <?php echo (isset($_GET['role_filter']) && $_GET['role_filter'] == 'inventory' ? 'selected' : ''); ?>>Inventory Staff</option>
                          <option value="both" <?php echo (isset($_GET['role_filter']) && $_GET['role_filter'] == 'both' ? 'selected' : ''); ?>>Both Roles</option>
                      </select>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                  <tr>
                    <th class="text-gold" scope="col">Staff Number</th>
                    <th scope="col">Name</th>
                    <th class="text-gold" scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Build the query based on role filter
                  $ret = "SELECT * FROM rpos_staff";
                  if (isset($_GET['role_filter'])) {
                    $filter = $_GET['role_filter'];
                    if ($filter == 'cashier') {
                      $ret .= " WHERE FIND_IN_SET('cashier', staff_role) > 0";
                    } elseif ($filter == 'inventory') {
                      $ret .= " WHERE FIND_IN_SET('inventory', staff_role) > 0";
                    } elseif ($filter == 'both') {
                      $ret .= " WHERE FIND_IN_SET('cashier', staff_role) > 0 AND FIND_IN_SET('inventory', staff_role) > 0";
                    }
                  }
                  
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($staff = $res->fetch_object()) {
                    // Determine role badge class
                    $roles = explode(',', $staff->staff_role);
                    $badge_class = '';
                    $role_text = '';
                    
                    if (in_array('cashier', $roles) && in_array('inventory', $roles)) {
                      $badge_class = 'badge-both';
                      $role_text = 'Both Roles';
                    } elseif (in_array('cashier', $roles)) {
                      $badge_class = 'badge-cashier';
                      $role_text = 'Cashier';
                    } elseif (in_array('inventory', $roles)) {
                      $badge_class = 'badge-inventory';
                      $role_text = 'Inventory';
                    }
                  ?>
                    <tr>
                      <td class="text-gold"><?php echo $staff->staff_number; ?></td>
                      <td><?php echo $staff->staff_name; ?></td>
                      <td class="text-gold"><?php echo $staff->staff_email; ?></td>
                      <td>
                        <span class="badge-role <?php echo $badge_class; ?>">
                          <?php echo $role_text; ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex">
                          <a href="hrm.php?delete=<?php echo $staff->staff_id; ?>" class="btn btn-sm btn-danger mr-2">
                            <i class="fas fa-trash"></i> Delete
                          </a>
                          <a href="update_staff.php?update=<?php echo $staff->staff_id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i> Update
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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
</body>
</html>