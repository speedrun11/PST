<?php
// Add at the top of index.php before the HTML
include('config/config.php');

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;

    // Validate input
    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating";
    } elseif (empty($feedback)) {
        $error = "Please provide your feedback";
    } else {
        // Insert feedback into database
        $query = "INSERT INTO rpos_feedback (rating, feedback_text, email) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iss', $rating, $feedback, $email);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = true;
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>PST - Customer Feedback</title>
    
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
            --rating-high: #4a9c5a; /* Green for high ratings */
            --rating-medium: #d4a017; /* Gold for medium ratings */
            --rating-low: #c45c5c; /* Red for low ratings */
        }
        
        body {
            background-color: var(--primary-dark);
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
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
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .content {
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 2rem;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
        }
        
        .title {
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .tagline {
            color: var(--text-light);
            opacity: 0.8;
            margin-bottom: 2rem;
        }
        
        /* Feedback Form Styles - Updated to match dashboard */
        .feedback-form {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 2rem;
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
            text-align: left;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            border-radius: 8px;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            transition: all var(--transition-speed) ease;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 2px rgba(192, 160, 98, 0.3);
        }
        
        .rating-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            justify-content: center;
        }
        
        .rating-option {
            display: none;
        }
        
        .rating-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--accent-gold);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .rating-option:checked + .rating-label {
            background: var(--accent-gold);
            color: var(--text-dark);
            transform: scale(1.1);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, rgba(192, 160, 98, 0.8), rgba(192, 160, 98, 0.6));
            border: 1px solid rgba(192, 160, 98, 0.4);
            color: var(--text-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-family: 'Fredoka', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            filter: brightness(1.15);
        }
        
        .success-message {
            color: var(--accent-green);
            text-align: center;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(74, 107, 87, 0.2);
            border-radius: 8px;
            border-left: 3px solid var(--accent-green);
            display: <?php echo $success ? 'block' : 'none'; ?>;
        }
        
        .error-message {
            color: var(--accent-red);
            text-align: center;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(158, 43, 43, 0.2);
            border-radius: 8px;
            border-left: 3px solid var(--accent-red);
            margin-bottom: 1rem;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }
        
        /* Animations */
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 1rem;
            }
            
            .feedback-form {
                padding: 1.5rem;
                backdrop-filter: blur(4px);
            }
        }
    </style>
</head>
<body>
    <div class="full-height">
        <div class="flex-center">
            <div class="content animate-fade-in">
                <div class="logo-container delay-1">
                    <img src="img/pst-logo.png" alt="Pastil sa Tabi Logo" class="logo">
                </div>
                
                <h1 class="title animate-fade-in delay-1">
                    CUSTOMER FEEDBACK
                </h1>
                
                <p class="tagline animate-fade-in delay-2">
                    We value your opinion! Please share your experience with our system.
                </p>

                <div class="feedback-form animate-fade-in delay-3">
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="feedbackForm" method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">How would you rate your experience?</label>
                            <div class="rating-container">
                                <input type="radio" id="rating1" name="rating" value="1" class="rating-option" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'checked' : ''; ?>>
                                <label for="rating1" class="rating-label">1</label>
                                
                                <input type="radio" id="rating2" name="rating" value="2" class="rating-option" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'checked' : ''; ?>>
                                <label for="rating2" class="rating-label">2</label>
                                
                                <input type="radio" id="rating3" name="rating" value="3" class="rating-option" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'checked' : ''; ?>>
                                <label for="rating3" class="rating-label">3</label>
                                
                                <input type="radio" id="rating4" name="rating" value="4" class="rating-option" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'checked' : ''; ?>>
                                <label for="rating4" class="rating-label">4</label>
                                
                                <input type="radio" id="rating5" name="rating" value="5" class="rating-option" <?php echo (!isset($_POST['rating']) || (isset($_POST['rating']) && $_POST['rating'] == 5)) ? 'checked' : ''; ?>>
                                <label fo   r="rating5" class="rating-label">5</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="feedback" class="form-label">Your Feedback</label>
                            <textarea id="feedback" name="feedback" class="form-control" required placeholder="What did you like or what can we improve?"><?php echo isset($_POST['feedback']) ? htmlspecialchars($_POST['feedback']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email (optional)</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="If you'd like us to follow up" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn-submit animate-fade-in delay-4">
                            <i class="fas fa-paper-plane"></i>
                            <span>Submit Feedback</span>
                        </button>
                    </form>
                    
                    <?php if ($success): ?>
                        <div id="successMessage" class="success-message">
                            <i class="fas fa-check-circle"></i> Thank you for your feedback! We appreciate your time.
                        </div>
                        <script>
                            // Reset form after showing success message
                            setTimeout(() => {
                                document.getElementById('feedbackForm').reset();
                            }, 3000);
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>