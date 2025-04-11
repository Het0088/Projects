<?php
// Configure error reporting for development
// Comment these lines in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data with sanitization
    $name = isset($_POST['name']) ? filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $subject = isset($_POST['subject']) ? filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING) : '';
    $message = isset($_POST['message']) ? filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING) : '';
    
    // Validate form data
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }
    
    // Set email recipient and subject
    $to = "support@smartscope.com"; // Change to your email
    $email_subject = "SmartScope Contact Form: $subject";
    
    // Build email content
    $email_body = "You have received a new message from the SmartScope contact form.\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Subject: $subject\n";
    $email_body .= "Message:\n$message\n";
    
    // Set email headers
    $headers = "From: noreply@smartscope.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Send email (with error handling)
    try {
        // For demonstration, we'll log the message rather than actually sending
        // In production, uncomment the mail() function below
        
        // Log submission for debugging
        $log_file = __DIR__ . '/contact_submissions.log';
        file_put_contents($log_file, date('[Y-m-d H:i:s]') . " $name ($email): $subject\n", FILE_APPEND);
        
        // Uncomment this line to actually send the email
        // $mail_sent = mail($to, $email_subject, $email_body, $headers);
        
        // For development/demonstration purposes, we'll simulate success
        $mail_sent = true;
        
        if ($mail_sent) {
            // Save submission to database if needed
            // saveContactSubmission($name, $email, $subject, $message);
            
            $response['success'] = true;
            $response['message'] = 'Your message has been sent successfully!';
        } else {
            throw new Exception("Failed to send email.");
        }
    } catch (Exception $e) {
        $response['message'] = 'Sorry, there was an error sending your message. Please try again later.';
        // Log the error
        error_log("Contact form error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
echo json_encode($response);

/**
 * Function to save contact form submissions to database
 * Uncomment and implement if you need database storage
 */
/*
function saveContactSubmission($name, $email, $subject, $message) {
    require_once 'db_config.php';
    
    try {
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
*/
?> 