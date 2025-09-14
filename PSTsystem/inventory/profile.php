<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Update profile (name/email)
if (isset($_POST['update_profile'])) {
    $staff_id = $_SESSION['staff_id'];
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);

    if ($staff_name !== '' && $staff_email !== '') {
        $qry = "UPDATE rpos_staff SET staff_name = ?, staff_email = ? WHERE staff_id = ?";
        $stmt = $mysqli->prepare($qry);
        if ($stmt) {
            $stmt->bind_param('ssi', $staff_name, $staff_email, $staff_id);
            $stmt->execute();
            $_SESSION['success'] = 'Account updated';
            header('Location: profile.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update profile: ' . $mysqli->error;
        }
    } else {
        $_SESSION['error'] = 'Name and Email are required';
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $error = '';
    $old_password = isset($_POST['old_password']) ? sha1(md5($_POST['old_password'])) : '';
    $new_password = isset($_POST['new_password']) ? sha1(md5($_POST['new_password'])) : '';
    $confirm_password = isset($_POST['confirm_password']) ? sha1(md5($_POST['confirm_password'])) : '';

    if ($old_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Confirmation password does not match';
    }

    if ($error === '') {
        $staff_id = $_SESSION['staff_id'];
        $sql = "SELECT staff_password FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $staff_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if ($old_password !== $row['staff_password']) {
                    $_SESSION['error'] = 'Please enter correct old password';
                } else {
                    $upd = $mysqli->prepare('UPDATE rpos_staff SET staff_password = ? WHERE staff_id = ?');
                    if ($upd) {
                        $upd->bind_param('si', $new_password, $staff_id);
                        $upd->execute();
                        $_SESSION['success'] = 'Password changed';
                        header('Location: profile.php');
                        exit;
                    } else {
                        $_SESSION['error'] = 'Failed to change password: ' . $mysqli->error;
                    }
                }
            }
        }
    } else {
        $_SESSION['error'] = $error;
    }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>
    <?php
      $staff_id = $_SESSION['staff_id'];
      $ret = "SELECT * FROM rpos_staff WHERE staff_id = ?";
      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param('i', $staff_id);
      $stmt->execute();
      $res = $stmt->get_result();
      $staff = $res->fetch_object();
    ?>

    <!-- Header -->
    <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 300px; background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover; background-position: center top;">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-7 col-md-10">
            <h1 class="display-4 text-white">Hello <?php echo htmlspecialchars($staff->staff_name); ?></h1>
            <p class="text-white mt-0 mb-5">Manage your profile and password for the Inventory module.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
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
        <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
          <div class="card card-profile shadow" style="background: rgba(26, 26, 46, 0.8); border: 1px solid rgba(192, 160, 98, 0.2);">
            <div class="row justify-content-center">
              <div class="col-lg-3 order-lg-2">
                <div class="card-profile-image">
                  <img src="../admin/assets/img/theme/user-a-min.png" class="rounded-circle">
                </div>
              </div>
            </div>
            <div class="card-body pt-0 pt-md-4">
              <div class="text-center">
                <h3 class="text-white"><?php echo htmlspecialchars($staff->staff_name); ?></h3>
                <div class="h5 font-weight-300 text-white-50"><?php echo htmlspecialchars($staff->staff_email); ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-8 order-xl-1">
          <div class="card bg-secondary shadow" style="background: rgba(26, 26, 46, 0.8) !important; border: 1px solid rgba(192, 160, 98, 0.2);">
            <div class="card-header border-0" style="background: rgba(26, 26, 46, 0.9); border-bottom: 1px solid rgba(192, 160, 98, 0.3);">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-gold">My Account</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="post">
                <h6 class="heading-small text-gold mb-4">User information</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="input-username">User Name</label>
                        <input type="text" name="staff_name" value="<?php echo htmlspecialchars($staff->staff_name); ?>" id="input-username" class="form-control bg-transparent text-light border-light">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="input-email">Email address</label>
                        <input type="email" id="input-email" value="<?php echo htmlspecialchars($staff->staff_email); ?>" name="staff_email" class="form-control bg-transparent text-light border-light">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              <hr>
              <form method="post">
                <h6 class="heading-small text-gold mb-4">Change Password</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="old-password">Old Password</label>
                        <input type="password" name="old_password" id="old-password" class="form-control bg-transparent text-light border-light">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="new-password">New Password</label>
                        <input type="password" name="new_password" id="new-password" class="form-control bg-transparent text-light border-light">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label text-gold" for="confirm-password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm-password" class="form-control bg-transparent text-light border-light">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                      </div>
                    </div>
                  </div>
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

    .text-gold { color: var(--accent-gold) !important; }

    .card {
      background: rgba(26, 26, 46, 0.8);
      border: 1px solid rgba(192, 160, 98, 0.2);
      border-radius: 10px;
      backdrop-filter: blur(8px);
      transition: all var(--transition-speed) ease;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
      border-color: rgba(192, 160, 98, 0.4);
    }

    .card-header {
      background: rgba(26, 26, 46, 0.9);
      border-bottom: 1px solid rgba(192, 160, 98, 0.3);
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

    @media (max-width: 768px) {
      .card { backdrop-filter: blur(4px); }
    }
  </style>
</body>

</html>


