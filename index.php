<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="Pastil sa Tabi - Authentic Filipino Cuisine">
    <meta name="keywords" content="Pastil, Filipino food, Mindanao cuisine, authentic flavors">
    <meta property="og:title" content="PASTIL SA TABI - Authentic Filipino Flavors">
    <meta property="og:description" content="Experience the authentic taste of Mindanao's beloved rice dish">
    
    <title>PASTIL SA TABI - Authentic Filipino Flavors</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="icon" type="image/png" href="favicon.png">
    
    <link rel="preload" href="img/bgimg.jpg" as="image">

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
        
        html, body {
            background: 
                linear-gradient(rgba(26, 26, 46, 0.85), rgba(26, 26, 46, 0.9)),
                url('img/bgimg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            height: 100%;
            min-height: 100vh;
            line-height: 1.6;
            scroll-behavior: smooth;
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

        .content {
            text-align: center;
            background-color: rgba(26, 26, 46, 0.8);
            padding: 3rem 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 800px;
            border: 1px solid rgba(192, 160, 98, 0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }

        .content:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .logo-container {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .logo {
            height: 100px;
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
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color:rgb(161, 3, 3);
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

        .tagline {
            font-size: clamp(1rem, 3.5vw, 1.3rem);
            margin-bottom: 2.5rem;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }

        .links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.2rem;
            margin-top: 2rem;
            width: 100%;
        }

        .links > a {
            color: var(--text-light);
            padding: 1rem 1.5rem;
            font-size: clamp(0.85rem, 3vw, 1rem);
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            width: var(--button-width);
            min-height: 54px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .links > a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .links > a:hover::before {
            left: 100%;
        }

        .links > a:nth-child(1) {
            background: linear-gradient(135deg, rgba(158, 43, 43, 0.7), rgba(158, 43, 43, 0.5));
            border: 1px solid rgba(158, 43, 43, 0.4);
        }

        .links > a:nth-child(2) {
            background: linear-gradient(135deg, rgba(58, 86, 115, 0.7), rgba(58, 86, 115, 0.5));
            border: 1px solid rgba(58, 86, 115, 0.4);
        }

        .links > a:nth-child(3) {
            background: linear-gradient(135deg, rgba(74, 107, 87, 0.7), rgba(74, 107, 87, 0.5));
            border: 1px solid rgba(74, 107, 87, 0.4);
        }

        .links > a:nth-child(4) {
            background: linear-gradient(135deg, rgba(192, 160, 98, 0.7), rgba(192, 160, 98, 0.5));
            border: 1px solid rgba(192, 160, 98, 0.4);
        }

        .links > a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
        }

        .logo-icon {
            font-size: clamp(1rem, 4vw, 1.1rem);
            color: var(--accent-gold);
            transition: transform var(--transition-speed) ease;
        }

        .links > a:hover .logo-icon {
            transform: scale(1.2);
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
        .delay-4 { animation-delay: 0.8s; }

        @media (max-width: 768px) {
            .content {
                padding: 2rem 1.5rem;
                width: calc(100% - 30px);
                backdrop-filter: blur(4px);
            }
            
            .logo {
                height: 80px;
            }
            
            .links {
                gap: 1rem;
            }
            
            .links > a {
                width: 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 1.8rem 1.2rem;
                border-radius: 8px;
            }
            
            .logo {
                height: 70px;
            }
            
            .tagline {
                margin-bottom: 1.8rem;
            }
        }

        a:focus-visible {
            outline: 2px solid var(--accent-gold);
            outline-offset: 3px;
        }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="logo-container animate-fade-in">
                <img src="img/pst-logo.png" alt="Pastil sa Tabi Logo" class="logo">
            </div>
            
            <h1 class="title animate-fade-in delay-1">
                PASTIL SA TABI
            </h1>
            
            <p class="tagline animate-fade-in delay-2">
                Experience the authentic taste of Mindanao's beloved rice dish in every bite
            </p>

            <div class="links">
                <a href="Restro/admin/" class="animate-fade-in delay-3">
                    <i class="fas fa-user-shield logo-icon"></i> Admin Portal
                </a>
                <a href="Restro/cashier/" class="animate-fade-in delay-3">
                    <i class="fas fa-cash-register logo-icon"></i> Cashier Login
                </a>
                <a href="Restro/inventory" class="animate-fade-in delay-4">
                    <i class="fas fa-boxes logo-icon"></i> Inventory Staff
                </a>
                <a href="Restro/customer" class="animate-fade-in delay-4">
                    <i class="fas fa-utensils logo-icon"></i> Customer Order
                </a>
            </div>
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
            
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then(registration => {
                        console.log('ServiceWorker registration successful');
                    }).catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }
        });
    </script>
</body>
</html>