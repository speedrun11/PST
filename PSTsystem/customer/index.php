<?php
session_start();
include('config/config.php');
if (isset($_POST['login'])) {
    $customer_email = $_POST['customer_email'];
    $customer_password = sha1(md5($_POST['customer_password']));
    $stmt = $mysqli->prepare("SELECT customer_email, customer_password, customer_id  FROM  rpos_customers WHERE (customer_email =? AND customer_password =?)");
    $stmt->bind_param('ss',  $customer_email, $customer_password);
    $stmt->execute(); 
    $stmt->bind_result($customer_email, $customer_password, $customer_id);
    $rs = $stmt->fetch();
    $_SESSION['customer_id'] = $customer_id;
    if ($rs) {
        header("location:dashboard.php");
    } else {
        $err = "Incorrect Authentication Credentials ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Customer Login</title>
    
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
            --button-width: 280px;
            --transition-speed: 0.4s;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            position: relative;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--text-light);
            line-height: 1.6;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(rgba(26, 26, 46, 0.85), rgba(26, 26, 46, 0.9)),
                url('img/bgimg.jpg') no-repeat center center;
            background-size: cover;
            z-index: -1;
        }

        .full-height {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
            flex: 1;
            padding: 20px;
        }

        .login-container {
            text-align: center;
            background-color: rgba(26, 26, 46, 0.8);
            padding: 3rem 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(192, 160, 98, 0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            margin: 20px 0;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .logo-container {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .logo {
            height: 80px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            transition: transform 0.5s ease;
        }

        .logo:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        .title {
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: rgb(161, 3, 3);
            position: relative;
            display: inline-block;
            text-shadow:
                0 0 1px #ff5f1f,
                0 0 3px #ff5f1f,
                2px 2px 0 #ff5f1f,
                -2px -2px 0 #ff5f1f,
                3px 3px 4px orange;
        }

        .title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
        }

        .subtitle {
            font-size: clamp(0.9rem, 3vw, 1.1rem);
            margin-bottom: 2rem;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            line-height: 1.8;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-group {
            position: relative;
            display: flex;
            width: 100%;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
        }

        .input-group-prepend {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-right: none;
        }

        .input-group-text {
            color: var(--accent-gold);
            font-size: 1rem;
        }

        .form-control {
            flex: 1;
            padding: 0.75rem 1rem;
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-left: none;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--accent-gold);
        }

        .btn-login {
            display: inline-block;
            padding: 1rem 1.5rem;
            font-size: clamp(0.85rem, 3vw, 1rem);
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
            width: 100%;
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.7), rgba(158, 43, 43, 0.5));
            border: 1px solid rgba(158, 43, 43, 0.4);
            color: var(--text-light);
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
        }

        .btn-create {
            display: inline-block;
            padding: 1rem 1.5rem;
            font-size: clamp(0.85rem, 3vw, 1rem);
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
            width: 100%;
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.7), rgba(74, 107, 87, 0.5));
            border: 1px solid rgba(74, 107, 87, 0.4);
            color: var(--text-light);
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-create:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 0.9rem;
        }

        .remember-me input {
            margin-right: 0.5rem;
            accent-color: var(--accent-gold);
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            padding: 0.5rem;
            background: rgba(158, 43, 43, 0.2);
            border-radius: 6px;
            border-left: 3px solid var(--accent-red);
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 1.5rem;
        }

        .button-group .btn-login {
            flex: 1;
            margin-top: 0;
        }

        .button-group .btn-create {
            flex: 1;
            margin-top: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }

        @media (max-width: 768px) {
            .login-container {
                padding: 2rem 1.5rem;
                width: calc(100% - 30px);
                backdrop-filter: blur(4px);
            }
            
            .logo {
                height: 70px;
            }

            .button-group {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.8rem 1.2rem;
                border-radius: 8px;
            }
            
            .logo {
                height: 60px;
            }
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 2px solid var(--accent-gold);
            outline-offset: 3px;
        }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
        <div class="login-container animate-fade-in">
            <div class="logo-container delay-1">
                <img src="img/pst-logo.png" alt="PST Logo" class="logo">
            </div>
            
            <h1 class="title animate-fade-in delay-1">
                CUSTOMER LOGIN
            </h1>
            
            <p class="subtitle animate-fade-in delay-2">
                Sign in to access your account
            </p>

            <?php if(isset($err)) { echo "<div class='error-message animate-fade-in delay-2'>$err</div>"; } ?>

            <form method="post" role="form" class="animate-fade-in delay-3">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input class="form-control" required name="customer_email" placeholder="Email" type="email">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input class="form-control" required name="customer_password" placeholder="Password" type="password">
                    </div>
                </div>
                <div class="remember-me">
                    <input type="checkbox" id="rememberCheck">
                    <label for="rememberCheck">Remember Me</label>
                </div>
                <div class="button-group">
                    <button type="submit" name="login" class="btn-login">LOG IN</button>
                    <a href="create_account.php" class="btn-create">CREATE ACCOUNT</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const img = new Image();
            img.src = 'img/bgimg.jpg';
            
            img.onload = function() {
                document.body.style.backgroundImage = linear-gradient(rgba(26, 26, 46, 0.85), rgba(26, 26, 46, 0.9)), url('${img.src}');
            };
            
            img.onerror = function() {
                document.body.style.background = 'linear-gradient(#1a1a2e, #16213e)';
            };
            
            const logo = new Image();
            logo.src = 'img/pst-logo.png';
            
            setTimeout(() => {
                document.body.classList.add('loaded');
            }, 100);
        });
    </script>
</body>
</html>