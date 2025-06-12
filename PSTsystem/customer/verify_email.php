<?php
session_start();
include('config/config.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $query = "SELECT * FROM rpos_customers WHERE verification_token = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        
        $updateQuery = "UPDATE rpos_customers SET is_verified = 1, verification_token = NULL WHERE customer_id = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param('s', $customer['customer_id']);
        $updateStmt->execute();
        
        if ($updateStmt) {
            $success = "Email verified successfully! You can now login to your account.";
            header("refresh:3; url=index.php");
        } else {
            $err = "Verification failed. Please try again or contact support.";
        }
    } else {
        $err = "Invalid verification token.";
    }
} else {
    $err = "No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Email Verification</title>
    
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

        .verification-container {
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

        .verification-container:hover {
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

        .success-message {
            color: #6bff6b;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            padding: 0.5rem;
            background: rgba(74, 107, 87, 0.2);
            border-radius: 6px;
            border-left: 3px solid var(--accent-green);
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
            .verification-container {
                padding: 2rem 1.5rem;
                width: calc(100% - 30px);
                backdrop-filter: blur(4px);
            }
            
            .logo {
                height: 70px;
            }
        }

        @media (max-width: 480px) {
            .verification-container {
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
        <div class="verification-container animate-fade-in">
            <div class="logo-container delay-1">
                <img src="img/pst-logo.png" alt="Restaurant POS Logo" class="logo">
            </div>
            
            <h1 class="title animate-fade-in delay-1">
                EMAIL VERIFICATION
            </h1>
            
            <p class="subtitle animate-fade-in delay-2">
                Verifying your email address
            </p>

            <?php if(isset($success)): ?>
                <div class="success-message animate-fade-in delay-2">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <p class="animate-fade-in delay-3">You will be redirected shortly. If not, <a href="index.php" class="btn-login">click here to login</a></p>
            <?php elseif(isset($err)): ?>
                <div class="error-message animate-fade-in delay-2">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
                </div>
                <p class="animate-fade-in delay-3">Please contact support if you need assistance.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const img = new Image();
            img.src = 'img/bgimg.jpg';
            
            img.onload = function() {
                document.body.style.backgroundImage = `linear-gradient(rgba(26, 26, 46, 0.85), rgba(26, 26, 46, 0.9)), url('${img.src}')`;
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