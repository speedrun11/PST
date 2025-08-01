<?php
$staff_id = $_SESSION['staff_id'];
$ret = "SELECT * FROM  rpos_staff  WHERE staff_id = '$staff_id'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($staff = $res->fetch_object()) {
?>
  <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light sidebar" id="sidenav-main">
    <div class="container-fluid">
      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>
      <!-- Brand -->
      <a class="navbar-brand pt-0" href="dashboard.php">
        <img src="../admin/assets/img/brand/repos.png" class="navbar-brand-img" alt="...">
      </a>
      <!-- User -->
      <ul class="nav align-items-center d-md-none">
        <li class="nav-item dropdown">
          <a class="nav-link nav-link-icon" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ni ni-bell-55 text-gold"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="media align-items-center">
              <span class="avatar avatar-sm rounded-circle">
                <img alt="Image placeholder" src="../admin/assets/img/brand/repos.png">
              </span>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
            <div class=" dropdown-header noti-title">
              <h6 class="text-overflow m-0 text-light">Welcome!</h6>
            </div>
            <a href="change_profile.php" class="dropdown-item text-light">
              <i class="ni ni-single-02 text-gold"></i>
              <span>My profile</span>
            </a>
            <div class="dropdown-divider" style="border-color: rgba(192, 160, 98, 0.2);"></div>
            <a href="logout.php" class="dropdown-item text-light">
              <i class="ni ni-user-run text-gold"></i>
              <span>Logout</span>
            </a>
          </div>
        </li>
      </ul>
      <!-- Collapse -->
      <div class="collapse navbar-collapse" id="sidenav-collapse-main">
        <!-- Collapse header -->
        <div class="navbar-collapse-header d-md-none">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a href="dashboard.php">
                <img src="../admin/assets/img/brand/repos.png">
              </a>
            </div>
            <div class="col-6 collapse-close">
              <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                <span style="filter: invert(1);"></span>
                <span style="filter: invert(1);"></span>
              </button>
            </div>
          </div>
        </div>
        <!-- Form -->
        <form class="mt-4 mb-3 d-md-none">
          <div class="input-group input-group-rounded input-group-merge">
            <input type="search" class="form-control form-control-rounded form-control-prepended bg-dark text-light" placeholder="Search" aria-label="Search">
            <div class="input-group-prepend">
              <div class="input-group-text bg-dark">
                <span class="fa fa-search text-gold"></span>
              </div>
            </div>
          </div>
        </form>
        <!-- Navigation -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link text-light" href="dashboard.php">
              <i class="ni ni-tv-2 text-gold"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-light" href="products.php">
              <i class="ni ni-bullet-list-67 text-gold"></i> Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-light" href="orders.php">
              <i class="ni ni-cart text-gold"></i> Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-light" href="payments.php">
              <i class="ni ni-credit-card text-gold"></i> Payments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-light" href="receipts.php">
              <i class="fas fa-file-invoice-dollar text-gold"></i> Receipts
            </a>
          </li>
        </ul>
        <!-- Divider -->
        <hr class="my-3" style="border-color: rgba(192, 160, 98, 0.2);">
        <!-- Heading -->
        <h6 class="navbar-heading text-gold">Reporting</h6>
        <!-- Navigation -->
        <ul class="navbar-nav mb-md-3">
          <li class="nav-item">
            <a class="nav-link text-light" href="orders_reports.php">
              <i class="fas fa-shopping-basket text-gold"></i> Orders Report
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-light" href="payments_reports.php">
              <i class="fas fa-funnel-dollar text-gold"></i> Payments Report
            </a>
          </li>
        </ul>
        <hr class="my-3" style="border-color: rgba(192, 160, 98, 0.2);">
        <ul class="navbar-nav mb-md-3">
          <li class="nav-item">
            <a class="nav-link text-light" href="logout.php">
              <i class="fas fa-sign-out-alt text-danger"></i> Log Out
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
<?php } ?>