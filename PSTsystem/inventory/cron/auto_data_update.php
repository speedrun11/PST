<?php
/**
 * Automated Data Update Cron Job
 * This script should be run daily via cron job or Windows Task Scheduler
 * 
 * Cron job example (run daily at 6 AM):
 * 0 6 * * * /usr/bin/php /path/to/auto_data_update.php
 * 
 * Windows Task Scheduler:
 * - Create a new task
 * - Set trigger to daily at 6:00 AM
 * - Action: Start a program
 * - Program: php.exe
 * - Arguments: C:\xampp\htdocs\PST\PSTsystem\inventory\cron\auto_data_update.php
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include necessary files
require_once('../config/config.php');
require_once('../classes/DataAutomation.php');

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/data_update.log';
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
    echo "[$timestamp] $message\n";
}

try {
    logMessage("Starting automated data update...");
    
    // Initialize data automation
    $automation = new DataAutomation($mysqli);
    
    // Run all automation processes
    $results = $automation->runAllAutomation();
    
    // Log results
    $total_inserted = 0;
    foreach ($results as $type => $result) {
        $inserted = $result['inserted'];
        $total_inserted += $inserted;
        
        if ($inserted > 0) {
            logMessage("$type: $inserted records inserted");
        }
        
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                logMessage("ERROR - $type: $error");
            }
        }
    }
    
    logMessage("Automated data update completed. Total records inserted: $total_inserted");
    
    // Send email notification (optional)
    if ($total_inserted > 0) {
        sendNotificationEmail($total_inserted, $results);
    }
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    exit(1);
}

/**
 * Send email notification about data update
 */
function sendNotificationEmail($total_inserted, $results) {
    // Email configuration (update with your SMTP settings)
    $to = 'admin@pastil.com'; // Update with your email
    $subject = 'Daily Data Automation Report - ' . date('Y-m-d');
    
    $message = "Daily Data Automation Report\n\n";
    $message .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $message .= "Total Records Inserted: $total_inserted\n\n";
    
    $message .= "Details:\n";
    foreach ($results as $type => $result) {
        $message .= "- $type: {$result['inserted']} records\n";
        if (!empty($result['errors'])) {
            $message .= "  Errors: " . count($result['errors']) . "\n";
        }
    }
    
    $message .= "\nThis is an automated message from the PST Data Automation System.";
    
    // Send email (you may need to configure SMTP settings)
    $headers = 'From: noreply@pastil.com' . "\r\n" .
               'Reply-To: noreply@pastil.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    if (mail($to, $subject, $message, $headers)) {
        logMessage("Notification email sent successfully");
    } else {
        logMessage("Failed to send notification email");
    }
}

logMessage("Script execution completed successfully");
?>
