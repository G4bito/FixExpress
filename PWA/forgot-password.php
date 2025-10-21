<?php
session_start();
require_once './dist/database/server.php';

// Set timezone to UTC for consistency
date_default_timezone_set('UTC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists in users table
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate a random reset code
        $reset_code = sprintf("%06d", mt_rand(0, 999999));
        // Set expiry time 1 hour from now using MySQL's timestamp
        $expiry = date('Y-m-d H:i:s', time() + 3600); // Code expires in 1 hour
        
        // For debugging
        error_log("Generated reset code: " . $reset_code);
        error_log("Expiry time set to: " . $expiry);
        
        // Store the reset code in the database
        $reset_stmt = $conn->prepare("INSERT INTO password_resets (user_id, reset_code, expiry, used) VALUES (?, ?, ?, 0)");
        $reset_stmt->bind_param("iss", $user['user_id'], $reset_code, $expiry);
        
        if ($reset_stmt->execute()) {
            // Send email with reset code
            require_once __DIR__ . '/dist/includes/mailer.php';

            $subject = "Password Reset Code - FixExpress";
            $message = "Hello " . $user['username'] . ",\n\n";
            $message .= "You have requested to reset your password. Here is your reset code:\n\n";
            $message .= $reset_code . "\n\n";
            $message .= "This code will expire in 1 hour.\n\n";
            $message .= "If you did not request this reset, please ignore this email.\n\n";
            $message .= "Best regards,\nFixExpress Team";

            ob_start(); // Start output buffering
            if (sendEmail($email, $subject, $message, false)) {
                ob_end_clean(); // Clear any output
                $_SESSION['reset_email'] = $email;
                header("Location: reset-password.php");
                exit();
            } else {
                ob_end_clean(); // Clear any output
                $error = "Failed to send reset code. Please try again.";
            }
        } else {
            $error = "Error generating reset code. Please try again.";
        }
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FixExpress</title>
    <link rel="stylesheet" href="./dist/assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Enter your email address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="submit-btn">Send Reset Code</button>
            </form>
            
            <div class="links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>