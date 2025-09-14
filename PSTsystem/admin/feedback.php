<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'delete':
            $id = intval($_POST['feedback_id']);
            $delete_query = "DELETE FROM rpos_feedback WHERE feedback_id = ?";
            $stmt = $mysqli->prepare($delete_query);
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Feedback deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete feedback']);
            }
            $stmt->close();
            exit;
            
        case 'mark_read':
            $id = intval($_POST['feedback_id']);
            $update_query = "UPDATE rpos_feedback SET is_read = 1, read_at = NOW() WHERE feedback_id = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Feedback marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to mark feedback as read']);
            }
            $stmt->close();
            exit;
            
        case 'reply':
            $id = intval($_POST['feedback_id']);
            $reply_text = trim($_POST['reply_text']);
            $admin_email = $_SESSION['admin_email'] ?? 'admin@pst.com';
            
            if (empty($reply_text)) {
                echo json_encode(['success' => false, 'message' => 'Reply text cannot be empty']);
                exit;
            }
            
            // Get feedback details
            $feedback_query = "SELECT email, feedback_text, rating FROM rpos_feedback WHERE feedback_id = ?";
            $stmt = $mysqli->prepare($feedback_query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $feedback = $result->fetch_assoc();
            $stmt->close();
            
            if (!$feedback) {
                echo json_encode(['success' => false, 'message' => 'Feedback not found']);
                exit;
            }
            
            // Store reply in database
            $reply_query = "INSERT INTO rpos_feedback_replies (feedback_id, admin_email, reply_text, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($reply_query);
            $stmt->bind_param('is', $id, $admin_email, $reply_text);
            
            if ($stmt->execute()) {
                // Mark feedback as read
                $mark_read_query = "UPDATE rpos_feedback SET is_read = 1, read_at = NOW() WHERE feedback_id = ?";
                $mark_stmt = $mysqli->prepare($mark_read_query);
                $mark_stmt->bind_param('i', $id);
                $mark_stmt->execute();
                $mark_stmt->close();
                
                echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send reply']);
            }
            $stmt->close();
            exit;
    }
}

// Handle traditional delete (for backward compatibility)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $adn = "DELETE FROM rpos_feedback WHERE feedback_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    
    if ($stmt) {
        $success = "Feedback deleted successfully" && header("refresh:1; url=feedback.php");
    } else {
        $err = "Failed to delete feedback. Please try again.";
    }
}

// Get feedback statistics
$stats_query = "SELECT 
    COUNT(*) as total_feedback,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
    COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as neutral_feedback,
    COUNT(CASE WHEN is_read = 1 OR is_read IS NULL THEN 1 END) as read_feedback,
    COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread_feedback
    FROM rpos_feedback";
$stmt = $mysqli->prepare($stats_query);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();

// Get recent feedback count (last 7 days)
$recent_query = "SELECT COUNT(*) as recent_count FROM rpos_feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $mysqli->prepare($recent_query);
$stmt->execute();
$stmt->bind_result($recent_count);
$stmt->fetch();
$stmt->close();

require_once('partials/_head.php');
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
        
        /* Rating Colors */
        .rating-5 { color: var(--rating-high); }
        .rating-4 { color: var(--rating-high); }
        .rating-3 { color: var(--rating-medium); }
        .rating-2 { color: var(--rating-low); }
        .rating-1 { color: var(--rating-low); }
        
        /* Feedback Cards */
        .feedback-card {
            background: rgba(26, 26, 46, 0.9);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all var(--transition-speed) ease;
        }
        
        .feedback-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(192, 160, 98, 0.4);
        }
        
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(192, 160, 98, 0.2);
        }
        
        .feedback-rating {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .feedback-date {
            color: rgba(248, 245, 242, 0.7);
            font-size: 0.9rem;
        }
        
        .feedback-text {
            line-height: 1.6;
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        
        .feedback-email {
            color: var(--accent-gold);
            font-weight: 500;
        }
        
        .feedback-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        /* Statistics Cards */
        .stat-card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-speed) ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-gold);
            font-family: 'Fredoka', sans-serif;
        }
        
        .stat-label {
            color: rgba(248, 245, 242, 0.8);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Filter and Search */
        .filter-section {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-control {
            background-color: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(192, 160, 98, 0.3);
            color: var(--text-light);
        }
        
        .form-control:focus {
            background-color: rgba(26, 26, 46, 0.9);
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.2rem rgba(192, 160, 98, 0.25);
            color: var(--text-light);
        }
        
        .form-control::placeholder {
            color: rgba(248, 245, 242, 0.6);
        }
        
        /* Badge Styles */
        .badge-positive {
            background-color: var(--rating-high);
            color: white;
        }
        
        .badge-neutral {
            background-color: var(--rating-medium);
            color: white;
        }
        
        .badge-negative {
            background-color: var(--rating-low);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: rgba(248, 245, 242, 0.6);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--accent-gold);
            margin-bottom: 1rem;
        }
        
        /* Read/Unread States */
        .feedback-card.unread {
            border-left: 4px solid var(--accent-gold);
            background: rgba(26, 26, 46, 0.95);
        }
        
        .feedback-card.read {
            border-left: 4px solid var(--accent-green);
            background: rgba(26, 26, 46, 0.8);
            opacity: 0.9;
        }
        
        /* Animations */
        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }
        
        .fade-out {
            animation: fadeOut 0.3s ease-out forwards;
        }
        
        /* Loading States */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
        }
        
        .modal-header {
            border-radius: 10px 10px 0 0;
        }
        
        .modal-footer {
            border-radius: 0 0 10px 10px;
        }
        
        /* Notification Styles */
        .alert {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
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
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Customer Feedback</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background-color: rgba(26, 26, 46, 0.8); border-radius: 20px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home text-gold"></i></a></li>
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-gold">Dashboard</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page">Feedback</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button class="btn btn-sm btn-primary" onclick="refreshFeedback()">
                <i class="fas fa-sync-alt"></i> Refresh
              </button>
            </div>
          </div>
          
          <!-- Statistics Cards -->
          <div class="row">
            <div class="col-xl-3 col-lg-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_feedback']; ?></div>
                <div class="stat-label">Total Feedback</div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                <div class="stat-label">Average Rating</div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['positive_feedback']; ?></div>
                <div class="stat-label">Positive (4-5★)</div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unread_feedback']; ?></div>
                <div class="stat-label">Unread Feedback</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Filter Section -->
      <div class="filter-section">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="ratingFilter" class="text-gold">Filter by Rating</label>
              <select class="form-control" id="ratingFilter" onchange="filterFeedback()">
                <option value="">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="searchFeedback" class="text-gold">Search Feedback</label>
              <input type="text" class="form-control" id="searchFeedback" placeholder="Search feedback text..." onkeyup="filterFeedback()">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="sortBy" class="text-gold">Sort By</label>
              <select class="form-control" id="sortBy" onchange="sortFeedback()">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="rating-high">Highest Rating</option>
                <option value="rating-low">Lowest Rating</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Feedback Display -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Customer Feedback</h3>
                  <p class="text-muted">Manage and review customer feedback</p>
                </div>
                <div class="col text-right">
                  <span class="badge badge-info" id="feedbackCount"><?php echo $stats['total_feedback']; ?> feedback items</span>
                </div>
              </div>
            </div>
            <div class="card-body">
                  <?php
              $ret = "SELECT f.*, 
                      (SELECT COUNT(*) FROM rpos_feedback_replies WHERE feedback_id = f.feedback_id) as reply_count
                      FROM rpos_feedback f 
                      ORDER BY f.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  
              if ($res->num_rows > 0) {
                  while ($feedback = $res->fetch_object()) {
                      $rating_stars = str_repeat('★', $feedback->rating) . str_repeat('☆', 5 - $feedback->rating);
                      $rating_class = $feedback->rating >= 4 ? 'positive' : ($feedback->rating == 3 ? 'neutral' : 'negative');
                      $time_ago = time() - strtotime($feedback->created_at);
                      $time_display = '';
                      
                      if ($time_ago < 3600) {
                          $time_display = floor($time_ago / 60) . ' minutes ago';
                      } elseif ($time_ago < 86400) {
                          $time_display = floor($time_ago / 3600) . ' hours ago';
                      } else {
                          $time_display = date('M d, Y h:i A', strtotime($feedback->created_at));
                      }
              ?>
                <div class="feedback-card <?php echo ($feedback->is_read == 1) ? 'read' : 'unread'; ?>" data-rating="<?php echo $feedback->rating; ?>" data-text="<?php echo strtolower(htmlspecialchars($feedback->feedback_text)); ?>" data-feedback-id="<?php echo $feedback->feedback_id; ?>">
                  <div class="feedback-header">
                    <div class="feedback-rating rating-<?php echo $feedback->rating; ?>">
                        <?php echo $rating_stars; ?>
                      <span class="badge badge-<?php echo $rating_class; ?> ml-2"><?php echo $feedback->rating; ?>/5</span>
                      <?php if ($feedback->is_read == 0): ?>
                        <span class="badge badge-warning ml-1">NEW</span>
                      <?php endif; ?>
                      <?php if ($feedback->reply_count > 0): ?>
                        <span class="badge badge-info ml-1"><?php echo $feedback->reply_count; ?> Reply<?php echo $feedback->reply_count > 1 ? 'ies' : ''; ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="feedback-date">
                      <i class="fas fa-clock"></i> <?php echo $time_display; ?>
                    </div>
                  </div>
                  
                  <div class="feedback-text">
                    <?php echo nl2br(htmlspecialchars($feedback->feedback_text)); ?>
                  </div>
                  
                  <?php if ($feedback->email): ?>
                  <div class="feedback-email">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($feedback->email); ?>
                  </div>
                  <?php endif; ?>
                  
                  <div class="feedback-actions">
                    <button class="btn btn-sm btn-info" onclick="showReplyModal(<?php echo $feedback->feedback_id; ?>, '<?php echo htmlspecialchars($feedback->email); ?>')" <?php echo !$feedback->email ? 'disabled' : ''; ?>>
                      <i class="fas fa-reply"></i> Reply
                    </button>
                    <?php if ($feedback->is_read == 0): ?>
                    <button class="btn btn-sm btn-warning" onclick="markAsRead(<?php echo $feedback->feedback_id; ?>)">
                      <i class="fas fa-check"></i> Mark as Read
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-success" disabled>
                      <i class="fas fa-check"></i> Read
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-danger" onclick="deleteFeedback(<?php echo $feedback->feedback_id; ?>)">
                          <i class="fas fa-trash"></i> Delete
                    </button>
                  </div>
                </div>
              <?php 
                  }
              } else {
              ?>
                <div class="empty-state">
                  <i class="fas fa-comments"></i>
                  <h4>No Feedback Yet</h4>
                  <p>Customer feedback will appear here once customers start leaving reviews.</p>
                </div>
                  <?php } ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Reply Modal -->
      <div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content" style="background-color: rgba(26, 26, 46, 0.95); border: 1px solid rgba(192, 160, 98, 0.3);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(192, 160, 98, 0.3);">
              <h5 class="modal-title text-gold" id="replyModalLabel">Reply to Feedback</h5>
              <button type="button" class="close text-gold" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label class="text-gold">Customer Email:</label>
                <input type="text" class="form-control" id="customerEmail" readonly style="background-color: rgba(26, 26, 46, 0.8); color: var(--text-light);">
              </div>
              <div class="form-group">
                <label class="text-gold">Your Reply:</label>
                <textarea class="form-control" id="replyText" rows="6" placeholder="Type your reply here..." style="background-color: rgba(26, 26, 46, 0.8); color: var(--text-light);"></textarea>
              </div>
              <div class="form-group">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="sendEmail" checked>
                  <label class="form-check-label text-gold" for="sendEmail">
                    Send email notification to customer
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(192, 160, 98, 0.3);">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="sendReply()">
                <i class="fas fa-paper-plane"></i> Send Reply
              </button>
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
  
  <script>
    // Filter feedback based on rating and search text
    function filterFeedback() {
      const ratingFilter = document.getElementById('ratingFilter').value;
      const searchText = document.getElementById('searchFeedback').value.toLowerCase();
      const feedbackCards = document.querySelectorAll('.feedback-card');
      let visibleCount = 0;
      
      feedbackCards.forEach(card => {
        const cardRating = card.getAttribute('data-rating');
        const cardText = card.getAttribute('data-text');
        
        let showCard = true;
        
        // Filter by rating
        if (ratingFilter && cardRating !== ratingFilter) {
          showCard = false;
        }
        
        // Filter by search text
        if (searchText && !cardText.includes(searchText)) {
          showCard = false;
        }
        
        if (showCard) {
          card.style.display = 'block';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
      
      // Update feedback count
      document.getElementById('feedbackCount').textContent = visibleCount + ' feedback items';
    }
    
    // Sort feedback
    function sortFeedback() {
      const sortBy = document.getElementById('sortBy').value;
      const container = document.querySelector('.card-body');
      const feedbackCards = Array.from(document.querySelectorAll('.feedback-card'));
      
      feedbackCards.sort((a, b) => {
        switch (sortBy) {
          case 'newest':
            return 0; // Already sorted by newest in PHP
          case 'oldest':
            return 0; // Would need to reverse, but keeping simple for now
          case 'rating-high':
            return parseInt(b.getAttribute('data-rating')) - parseInt(a.getAttribute('data-rating'));
          case 'rating-low':
            return parseInt(a.getAttribute('data-rating')) - parseInt(b.getAttribute('data-rating'));
          default:
            return 0;
        }
      });
      
      // Re-append sorted cards
      feedbackCards.forEach(card => {
        container.appendChild(card);
      });
    }
    
    // Global variables for reply functionality
    let currentFeedbackId = null;
    let currentCustomerEmail = null;
    
    // Show reply modal
    function showReplyModal(feedbackId, customerEmail) {
      currentFeedbackId = feedbackId;
      currentCustomerEmail = customerEmail;
      document.getElementById('customerEmail').value = customerEmail;
      document.getElementById('replyText').value = '';
      $('#replyModal').modal('show');
    }
    
    // Send reply
    function sendReply() {
      const replyText = document.getElementById('replyText').value.trim();
      const sendEmail = document.getElementById('sendEmail').checked;
      
      if (!replyText) {
        alert('Please enter a reply message.');
        return;
      }
      
      if (!currentFeedbackId) {
        alert('Error: Feedback ID not found.');
        return;
      }
      
      // Show loading state
      const sendBtn = document.querySelector('#replyModal .btn-primary');
      const originalText = sendBtn.innerHTML;
      sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
      sendBtn.disabled = true;
      
      // Send AJAX request
      fetch('feedback.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reply&feedback_id=${currentFeedbackId}&reply_text=${encodeURIComponent(replyText)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success message
          showNotification('Reply sent successfully!', 'success');
          
          // Close modal
          $('#replyModal').modal('hide');
          
          // If email notification is enabled, open email client
          if (sendEmail && currentCustomerEmail) {
            const subject = 'Re: Your Feedback - PST System';
            const body = `Dear Customer,\n\nThank you for your feedback. Here is our response:\n\n${replyText}\n\nBest regards,\nPST Admin Team`;
            window.location.href = `mailto:${currentCustomerEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
          }
          
          // Refresh the page to show updated data
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotification(data.message || 'Failed to send reply', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while sending the reply', 'error');
      })
      .finally(() => {
        // Reset button state
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
      });
    }
    
    // Mark feedback as read
    function markAsRead(feedbackId) {
      if (!feedbackId) {
        alert('Error: Feedback ID not found.');
        return;
      }
      
      // Show loading state
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';
      btn.disabled = true;
      
      // Send AJAX request
      fetch('feedback.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&feedback_id=${feedbackId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('Feedback marked as read!', 'success');
          
          // Update the UI
          const feedbackCard = document.querySelector(`[data-feedback-id="${feedbackId}"]`);
          if (feedbackCard) {
            feedbackCard.classList.remove('unread');
            feedbackCard.classList.add('read');
            
            // Remove NEW badge
            const newBadge = feedbackCard.querySelector('.badge-warning');
            if (newBadge) {
              newBadge.remove();
            }
            
            // Update button
            btn.innerHTML = '<i class="fas fa-check"></i> Read';
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-success');
            btn.disabled = true;
          }
          
          // Update statistics
          updateUnreadCount();
        } else {
          showNotification(data.message || 'Failed to mark feedback as read', 'error');
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while marking feedback as read', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    }
    
    // Delete feedback
    function deleteFeedback(feedbackId) {
      if (!feedbackId) {
        alert('Error: Feedback ID not found.');
        return;
      }
      
      if (!confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
        return;
      }
      
      // Show loading state
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
      btn.disabled = true;
      
      // Send AJAX request
      fetch('feedback.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&feedback_id=${feedbackId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('Feedback deleted successfully!', 'success');
          
          // Remove the feedback card from UI
          const feedbackCard = document.querySelector(`[data-feedback-id="${feedbackId}"]`);
          if (feedbackCard) {
            feedbackCard.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
              feedbackCard.remove();
              updateFeedbackCount();
            }, 300);
          }
        } else {
          showNotification(data.message || 'Failed to delete feedback', 'error');
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting feedback', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    }
    
    // Refresh feedback page
    function refreshFeedback() {
      location.reload();
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        background-color: rgba(26, 26, 46, 0.95);
        border: 1px solid rgba(192, 160, 98, 0.3);
        color: var(--text-light);
      `;
      
      notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }
    
    // Update feedback count
    function updateFeedbackCount() {
      const visibleCards = document.querySelectorAll('.feedback-card:not([style*="display: none"])');
      document.getElementById('feedbackCount').textContent = visibleCards.length + ' feedback items';
    }
    
    // Update unread count (placeholder - would need server-side update)
    function updateUnreadCount() {
      // This would typically make an AJAX call to get updated statistics
      // For now, we'll just refresh the page after a short delay
      setTimeout(() => {
        location.reload();
      }, 2000);
    }
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
      // Optionally auto-refresh the page
      // location.reload();
    }, 300000);
    
    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.feedback-card, .stat-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
        card.classList.add('fade-in');
      });
    });
    
    // Add fade-in animation CSS
    const style = document.createElement('style');
    style.textContent = `
      .fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
      }
      
      @keyframes fadeInUp {
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>