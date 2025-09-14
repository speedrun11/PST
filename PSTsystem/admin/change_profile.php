<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if (isset($_POST['ChangeProfile'])) {
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];
    $Qry = "UPDATE rpos_admin SET admin_name =?, admin_email =? WHERE admin_id =?";
    $postStmt = $mysqli->prepare($Qry);
    $rc = $postStmt->bind_param('sss', $admin_name, $admin_email, $admin_id);
    $postStmt->execute();
    if ($postStmt) {
        $success = "Account Updated" && header("refresh:1; url=dashboard.php");
    } else {
        $err = "Please Try Again Or Try Later";
    }
}
if (isset($_POST['changePassword'])) {

    $error = 0;
    if (isset($_POST['old_password']) && !empty($_POST['old_password'])) {
        $old_password = mysqli_real_escape_string($mysqli, trim($_POST['old_password']));
    } else {
        $error = 1;
        $err = "Old Password Cannot Be Empty";
    }
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = mysqli_real_escape_string($mysqli, trim($_POST['new_password']));
    } else {
        $error = 1;
        $err = "New Password Cannot Be Empty";
    }
    if (isset($_POST['confirm_password']) && !empty($_POST['confirm_password'])) {
        $confirm_password = mysqli_real_escape_string($mysqli, trim($_POST['confirm_password']));
    } else {
        $error = 1;
        $err = "Confirmation Password Cannot Be Empty";
    }

    if (!$error) {
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT * FROM rpos_admin WHERE admin_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (!password_verify($old_password, $row['admin_password'])) {
                $err = "Please Enter Correct Old Password";
            } elseif ($new_password != $confirm_password) {
                $err = "Confirmation Password Does Not Match";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE rpos_admin SET admin_password =? WHERE admin_id =?";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('ss', $hashed_password, $admin_id);
                $stmt->execute();

                if ($stmt) {
                    $success = "Password Changed" && header("refresh:1; url=dashboard.php");
                } else {
                    $err = "Please Try Again Or Try Later";
                }
            }
        }
    }
}
require_once('partials/_head.php');
?>

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
        $admin_id = $_SESSION['admin_id'];
        $ret = "SELECT * FROM rpos_admin WHERE admin_id = ?";
        $stmt = $mysqli->prepare($ret);
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($admin = $res->fetch_object()) {
        ?>
            <!-- Header -->
            <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 300px; background-image: url(assets/img/theme/pastil.jpg); background-size: cover; background-position: center top;">
                <!-- Mask -->
                <span class="mask bg-gradient-dark opacity-8"></span>
                <!-- Header container -->
                <div class="container-fluid d-flex align-items-center">
                    <div class="row">
                        <div class="col-lg-7 col-md-10">
                            <h1 class="display-4 text-white">Hello <?php echo $admin->admin_name; ?></h1>
                            <p class="text-white mt-0 mb-5">Manage your profile and password for the Admin module.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page content -->
            <div class="container-fluid mt--8">
                <div class="row">
                    <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
                        <div class="card card-profile shadow" style="background: rgba(26, 26, 46, 0.8); border: 1px solid rgba(192, 160, 98, 0.2);">
                            <div class="row justify-content-center">
                                <div class="col-lg-3 order-lg-2">
                                    <div class="card-profile-image">
                                        <a href="#">
                                            <img src="assets/img/theme/user-a-min.png" class="rounded-circle">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
                                <div class="d-flex justify-content-between">
                                </div>
                            </div>
                            <div class="card-body pt-0 pt-md-4">
                                <div class="row">
                                    <div class="col">
                                        <div class="card-profile-stats d-flex justify-content-center mt-md-5">
                                            <div>
                                            </div>
                                            <div>
                                            </div>
                                            <div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-white"><?php echo $admin->admin_name; ?></h3>
                                    <div class="h5 font-weight-300 text-white-50"><?php echo $admin->admin_email; ?></div>
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
                                    <div class="col-4 text-right">
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
                                                    <input type="text" name="admin_name" value="<?php echo $admin->admin_name; ?>" id="input-username" class="form-control bg-transparent text-light border-light">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label text-gold" for="input-email">Email address</label>
                                                    <input type="email" id="input-email" value="<?php echo $admin->admin_email; ?>" name="admin_email" class="form-control bg-transparent text-light border-light">
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <input type="submit" id="input-email" name="ChangeProfile" class="btn btn-primary" value="Update Profile">
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
                                                    <input type="submit" id="input-email" name="changePassword" class="btn btn-primary" value="Change Password">
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
</body>

</html>
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

                </div>

              </div>

              <div class="card-body pt-0 pt-md-4">

                <div class="row">

                  <div class="col">

                    <div class="card-profile-stats d-flex justify-content-center mt-md-5">

                      <div>

                      </div>

                      <div>

                      </div>

                      <div>

                      </div>

                    </div>

                  </div>

                </div>

                <div class="text-center">

                  <h3>

                    <?php echo $admin->admin_name; ?></span>

                  </h3>

                  <div class="h5 font-weight-300">

                    <i class="ni location_pin mr-2"></i><?php echo $admin->admin_email; ?>

                  </div>

                </div>

              </div>

            </div>

          </div>

          <div class="col-xl-8 order-xl-1">

            <div class="card bg-secondary shadow">

              <div class="card-header bg-white border-0">

                <div class="row align-items-center">

                  <div class="col-8">

                    <h3 class="mb-0">My account</h3>

                  </div>

                  <div class="col-4 text-right">

                  </div>

                </div>

              </div>

              <div class="card-body">

                <form method="post">

                  <h6 class="heading-small text-muted mb-4">User information</h6>

                  <div class="pl-lg-4">

                    <div class="row">

                      <div class="col-lg-6">

                        <div class="form-group">

                          <label class="form-control-label" for="input-username">User Name</label>

                          <input type="text" name="admin_name" value="<?php echo $admin->admin_name; ?>" id="input-username" class="form-control form-control-alternative" ">

                      </div>

                    </div>

                    <div class=" col-lg-6">

                          <div class="form-group">

                            <label class="form-control-label" for="input-email">Email address</label>

                            <input type="email" id="input-email" value="<?php echo $admin->admin_email; ?>" name="admin_email" class="form-control form-control-alternative">

                          </div>

                        </div>



                        <div class="col-lg-12">

                          <div class="form-group">

                            <input type="submit" id="input-email" name="ChangeProfile" class="btn btn-success form-control-alternative" value="Submit"">

                      </div>

                    </div>

                  </div>

                </div>

              </form>

              <hr>

              <form method =" post">

                            <h6 class="heading-small text-muted mb-4">Change Password</h6>

                            <div class="pl-lg-4">

                              <div class="row">

                                <div class="col-lg-12">

                                  <div class="form-group">

                                    <label class="form-control-label" for="input-username">Old Password</label>

                                    <input type="password" name="old_password" id="input-username" class="form-control form-control-alternative">

                                  </div>

                                </div>



                                <div class="col-lg-12">

                                  <div class="form-group">

                                    <label class="form-control-label" for="input-email">New Password</label>

                                    <input type="password" name="new_password" class="form-control form-control-alternative">

                                  </div>

                                </div>



                                <div class="col-lg-12">

                                  <div class="form-group">

                                    <label class="form-control-label" for="input-email">Confirm New Password</label>

                                    <input type="password" name="confirm_password" class="form-control form-control-alternative">

                                  </div>

                                </div>



                                <div class="col-lg-12">

                                  <div class="form-group">

                                    <input type="submit" id="input-email" name="changePassword" class="btn btn-success form-control-alternative" value="Change Password">

                                  </div>

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

      <?php

      require_once('partials/_footer.php');

      ?>

      </div>

  </div>

  <!-- Argon Scripts -->

  <?php

  require_once('partials/_sidebar.php');

  ?>

</body>



</html>
