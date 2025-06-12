<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['ChangeProfile'])) {
  if (empty($_POST["customer_phoneno"]) || empty($_POST["customer_name"]) || empty($_POST['customer_email'])) {
    $err = "Blank Values Not Accepted";
  } else {
    $customer_name = $_POST['customer_name'];
    $customer_phoneno = $_POST['customer_phoneno'];
    $customer_email = $_POST['customer_email'];
    $customer_id = $_SESSION['customer_id'];

    $postQuery = "UPDATE rpos_customers SET customer_name =?, customer_phoneno =?, customer_email =?, customer_password =? WHERE  customer_id =?";
    $postStmt = $mysqli->prepare($postQuery);
    $rc = $postStmt->bind_param('sssss', $customer_name, $customer_phoneno, $customer_email, $customer_password, $customer_id);
    $postStmt->execute();
    if ($postStmt) {
      $success = "Profile Updated" && header("refresh:1; url=dashboard.php");
    } else {
      $err = "Please Try Again Or Try Later";
    }
  }
}
if (isset($_POST['changePassword'])) {

    $error = 0;
    if (isset($_POST['old_password']) && !empty($_POST['old_password'])) {
        $old_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['old_password']))));
    } else {
        $error = 1;
        $err = "Old Password Cannot Be Empty";
    }
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['new_password']))));
    } else {
        $error = 1;
        $err = "New Password Cannot Be Empty";
    }
    if (isset($_POST['confirm_password']) && !empty($_POST['confirm_password'])) {
        $confirm_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['confirm_password']))));
    } else {
        $error = 1;
        $err = "Confirmation Password Cannot Be Empty";
    }

    if (!$error) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "SELECT * FROM rpos_customers   WHERE customer_id = '$customer_id'";
        $res = mysqli_query($mysqli, $sql);
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($old_password != $row['customer_password']) {
                $err =  "Please Enter Correct Old Password";
            } elseif ($new_password != $confirm_password) {
                $err = "Confirmation Password Does Not Match";
            } else {

                $new_password  = sha1(md5($_POST['new_password']));
                $query = "UPDATE rpos_customers SET  customer_password =? WHERE customer_id =?";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('si', $new_password, $customer_id);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Change Profile</title>
    
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
            background: rgba(26, 26, 46, 0.9) !important;
            border-bottom: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .card-header h3 {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .form-control-alternative {
            background-color: rgba(26, 26, 46, 0.7);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control-alternative:focus {
            background-color: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 2px rgba(192, 160, 98, 0.2);
            color: var(--text-light);
        }
        
        .form-control-label {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .btn-success {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.9), rgba(74, 107, 87, 0.7));
            border: 1px solid rgba(74, 107, 87, 0.5);
            color: white;
            transition: all var(--transition-speed) ease;
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
            border-color: rgba(74, 107, 87, 0.8);
        }
        
        .text-white {
            color: var(--text-light) !important;
        }
        
        .text-gold {
            color: var(--accent-gold) !important;
        }
        
        .h3 {
            color: var(--accent-gold) !important;
            font-family: 'Fredoka', sans-serif;
        }
        
        .h5 {
            color: var(--text-light);
        }
        
        hr {
            border-top: 1px solid rgba(192, 160, 98, 0.3);
        }
        
        .heading-small {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .card {
                backdrop-filter: blur(4px);
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
        $customer_id = $_SESSION['customer_id'];
        $ret = "SELECT * FROM  rpos_customers  WHERE customer_id = '$customer_id'";
        $stmt = $mysqli->prepare($ret);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($customer = $res->fetch_object()) {
        ?>
            <!-- Header -->
            <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 600px; background-image: url(../admin/assets/img/theme/pastil.jpg); background-size: cover; background-position: center top;">
                <!-- Mask -->
                <span class="mask bg-gradient-dark opacity-8"></span>
                <!-- Header container -->
                <div class="container-fluid d-flex align-items-center">
                    <div class="row">
                        <div class="col-lg-7 col-md-10">
                            <h1 class="display-2 text-white">Hello <?php echo $customer->customer_name; ?></h1>
                            <p class="text-white mt-0 mb-5">This is your profile page. You can customize your profile as you want And also change password too</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page content -->
            <div class="container-fluid mt--8">
                <div class="row">
                    <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
                        <div class="card card-profile shadow">
                            <div class="row justify-content-center">
                                <div class="col-lg-3 order-lg-2">
                                    <div class="card-profile-image">
                                        <a href="#">
                                            <img src="../admin/assets/img/theme/user-a-min.png" class="rounded-circle">
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
                                    <h3>
                                        <?php echo $customer->customer_name; ?></span>
                                    </h3>
                                    <div class="h5 font-weight-300">
                                        <i class="fas fa-envelope mr-2"></i><?php echo $customer->customer_email; ?>
                                    </div>
                                    <div class="h5 font-weight-300">
                                        <i class="fas fa-phone mr-2"></i><?php echo $customer->customer_phoneno; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
<div class="col-xl-8 order-xl-1">
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col-8">
                    <h3 class="mb-0 text-gold">My Account</h3>
                </div>
                <div class="col-4 text-right">
                    <!-- Optional action button could go here -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="post">
                <h6 class="heading-small text-gold mb-4">User Information</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-username">Full Name</label>
                                <input type="text" name="customer_name" value="<?php echo $customer->customer_name; ?>" id="input-username" class="form-control form-control-alternative">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-phone">Phone Number</label>
                                <input type="text" id="input-phone" value="<?php echo $customer->customer_phoneno; ?>" name="customer_phoneno" class="form-control form-control-alternative">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-email">Email Address</label>
                                <input type="email" id="input-email" value="<?php echo $customer->customer_email; ?>" name="customer_email" class="form-control form-control-alternative">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <input type="submit" name="ChangeProfile" class="btn btn-success btn-block" value="Update Profile">
                        </div>
                    </div>
                </div>
            </form>
            <hr class="my-4">
            <form method="post">
                <h6 class="heading-small text-gold mb-4">Change Password</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-old-password">Old Password</label>
                                <input type="password" name="old_password" id="input-old-password" class="form-control form-control-alternative">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-new-password">New Password</label>
                                <input type="password" name="new_password" id="input-new-password" class="form-control form-control-alternative">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label text-gold" for="input-confirm-password">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="input-confirm-password" class="form-control form-control-alternative">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input type="submit" name="changePassword" class="btn btn-success btn-block" value="Change Password">
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
        }
            ?>
            </div>
    </div>
    <!-- Argon Scripts -->
    <?php
    require_once('partials/_sidebar.php');
    ?>
</body>

</html>