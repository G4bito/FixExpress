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
        
        // Set expiry time 1 hour from now
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Code expires in 1 hour
        
        // Invalidate any existing unused codes for this user
        $invalidate_stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0");
        $invalidate_stmt->bind_param("i", $user['user_id']);
        $invalidate_stmt->execute();
        
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
<style>
    /* This will handle the auto fill */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    textarea:-webkit-autofill,
    select:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 1000px #2a2a2a inset !important; 
        -webkit-text-fill-color: #ffffff !important; 
        caret-color: #ffffff !important;
        transition: background-color 9999s ease-in-out 0s !important;
    }

    .btn-login {
        width: 100%;
        padding: 15px;
        background: linear-gradient(to bottom, #d97f3e, #151010);
        border: none;
        border-radius: 30px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
        margin-bottom: 30px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(217, 127, 62, 0.3);
    }

    .form-group input[type="password"],
    .form-group input[type="text"],
    .form-group input[type="email"] {
        width: 100%;
        padding: 12px;
        background: #2c2c2c;
        border: 2px solid #d97843;
        color: white;
        border-radius: 5px;
        outline: none;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .form-group input:focus {
        border-color: #c85a28;
        box-shadow: 0 4px 8px rgba(217, 120, 67, 0.2);
    }

    .form-group label {
        color: #ffffffff;
    }

    .signup-link {
        margin-top: 20px;
        color: rgba(255, 255, 255, 0.9);
    }

    .signup-link a {
        color: #d97843;
        text-decoration: none;
        transition: color 0.3s ease;
        position: relative;
    }

    .signup-link a:hover {
        color: #c85a28;
    }

    .signup-link a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 1px;
        background-color: #c85a28;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .signup-link a:hover::after {
        transform: scaleX(1);
    }

    .alert {
        background: rgba(184, 117, 58, 0.1);
        border: 1px solid #b8753a;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>
<body>
    <div id="forgotPasswordPage" class="login-container">
        <div class="bottom-triangle"></div>
        <div class="bottom-triangle-inner"></div>

        <div class="login-form fade-in">
            <h1>Password Reset</h1>
            <?php if (isset($error)): ?>
                <div class="alert" style="color:red;text-align:center;margin-bottom:10px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Email Address</label>
                </div>
                
                <button type="submit" class="btn-login">Send Reset Code</button>

                <div class="signup-link">
                    Remember your password?<br>
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>