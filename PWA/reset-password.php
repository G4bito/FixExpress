<?php
session_start();
require_once './dist/database/server.php';

// Set timezone to UTC for consistency
date_default_timezone_set('UTC');

// Redirect if no reset email in session
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_code = trim($_POST['reset_code']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = $_SESSION['reset_email'];
    
    error_log("Processing reset request - Code: $reset_code, Email: $email");
    
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify reset code
        // Debug timestamp
        error_log("Current UTC time: " . gmdate('Y-m-d H:i:s'));
        
        // Debug: Log the values being checked
        error_log("Checking reset code at: " . date('Y-m-d H:i:s'));
        
        $stmt = $conn->prepare("
            SELECT 
                pr.*,
                u.user_id,
                pr.expiry > NOW() as is_valid
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.user_id 
            WHERE u.email = ? 
            AND pr.reset_code = ? 
            AND pr.used = 0 
            ORDER BY pr.created_at DESC 
            LIMIT 1
        ");
        
        // Add debugging after query execution
        error_log("Reset code: " . $reset_code);
        error_log("Email: " . $email);
        $stmt->bind_param("ss", $email, $reset_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reset = $result->fetch_assoc();
            
            // Debug logging
            error_log("Reset record found - Code: " . $reset['reset_code']);
            error_log("Expiry time: " . $reset['expiry']);
            error_log("Is valid: " . ($reset['is_valid'] ? 'Yes' : 'No'));
            
            if ($reset['is_valid']) {
                // Update password
                $hashed_password = md5($new_password); // Using MD5 to match existing system
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $reset['user_id']);
                
                if ($update_stmt->execute()) {
                    // Mark reset code as used
                    $mark_used = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                    $mark_used->bind_param("i", $reset['id']);
                    $mark_used->execute();
                    
                    // Clear session and redirect to login
                    unset($_SESSION['reset_email']);
                    $_SESSION['password_reset_success'] = true;
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error updating password. Please try again.";
                }
            } else {
                $error = "Reset code has expired.";
                error_log("Code expired - expiry time was: " . $reset['expiry']);
            }
        } else {
            $error = "Invalid or expired reset code.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FixExpress</title>
    <link rel="stylesheet" href="./dist/assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Reset Password</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="reset_code">Enter Reset Code</label>
                    <input type="text" id="reset_code" name="reset_code" required maxlength="6">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>