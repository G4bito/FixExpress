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
        
        $currentTime = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("
            SELECT 
                pr.*,
                u.user_id,
                CASE 
                    WHEN pr.used = 0 AND pr.expiry > ? THEN 1
                    ELSE 0
                END as is_valid
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.user_id 
            WHERE u.email = ? 
            AND pr.reset_code = ? 
            AND pr.used = 0 
            ORDER BY pr.created_at DESC 
            LIMIT 1
        ");
        
        $stmt->bind_param("sss", $currentTime, $email, $reset_code);
        
        // Add debugging after query execution
        error_log("Reset code: " . $reset_code);
        error_log("Email: " . $email);
        // Binding is done above with the currentTime
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reset = $result->fetch_assoc();
            
            // Debug logging
            error_log("Reset record found - Code: " . $reset['reset_code']);
            error_log("Current UTC time: " . gmdate('Y-m-d H:i:s'));
            error_log("Expiry time: " . $reset['expiry']);
            error_log("Time until expiry: " . (strtotime($reset['expiry']) - time()) . " seconds");
            error_log("Is valid: " . ($reset['is_valid'] ? 'Yes' : 'No'));
            
            if ($reset['is_valid']) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
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

    .password-group {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .toggle-password:hover {
        color: #d97843;
    }

    #reset_code {
        letter-spacing: 2px;
        font-size: 1.2em;
        text-align: center;
        background: #2c2c2c;
        border: 2px solid #d97843;
        color: white;
        border-radius: 5px;
        padding: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    #reset_code:focus {
        border-color: #c85a28;
        box-shadow: 0 4px 8px rgba(217, 120, 67, 0.2);
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

    .password-message {
        font-size: 12px;
        margin-top: 5px;
        display: block;
        text-align: left;
        position: absolute;
        left: 0;
        bottom: -20px;
    }
</style>
<body>
    <div id="resetPasswordPage" class="login-container">
        <div class="bottom-triangle"></div>
        <div class="bottom-triangle-inner"></div>

        <div class="login-form fade-in">
            <h1>Reset Password</h1>
            <?php if (isset($error)): ?>
                <div class="alert" style="color:red;text-align:center;margin-bottom:10px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" id="reset_code" name="reset_code" placeholder=" " required maxlength="6" pattern="[0-9]{6}" title="Please enter the 6-digit code">
                    <label for="reset_code">Reset Code</label>
                </div>
                
                <div class="form-group password-group">
                    <input type="password" id="new_password" name="new_password" placeholder=" " required>
                    <label for="new_password">New Password</label>
                    <svg class="toggle-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>
                
                <div class="form-group password-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                    <label for="confirm_password">Confirm Password</label>
                    <svg class="toggle-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <small class="password-message"></small>
                </div>
                
                <button type="submit" class="btn-login">Reset Password</button>

                <div class="signup-link">
                    Remember your password?<br>
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function(e) {
            // Find the password input that's inside the same password-group as this icon
            const passwordGroup = this.closest('.password-group');
            const input = passwordGroup.querySelector('input[type="password"], input[type="text"]');
            
            // Toggle password visibility
            const isVisible = input.type === 'password';
            
            // Change input type
            input.type = isVisible ? 'text' : 'password';
            
            // Change icon
            this.innerHTML = isVisible 
                ? `<path stroke-linecap="round" stroke-linejoin="round" 
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 
                    a11.05 11.05 0 012.944-4.78M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />`
                : `<path stroke-linecap="round" stroke-linejoin="round" 
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <circle cx="12" cy="12" r="3" />`;
        });
    });

    // Format reset code input to show only numbers
    const resetCodeInput = document.getElementById('reset_code');
    resetCodeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Password matching validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const message = document.querySelector('.password-message');

    function validatePasswords() {
        if (confirmPassword.value === "") {
            message.textContent = "";
            confirmPassword.style.borderBottomColor = "";
            return;
        }

        if (newPassword.value === confirmPassword.value) {
            message.textContent = "✅ Passwords match";
            message.style.color = "#4CAF50";
            return true;
        } else {
            message.textContent = "❌ Passwords do not match";
            message.style.color = "#f44336";
            return false;
        }
    }

    confirmPassword.addEventListener('input', validatePasswords);
    newPassword.addEventListener('input', validatePasswords);

    // Form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!validatePasswords()) {
            e.preventDefault();
            alert('Please make sure your passwords match.');
        }
    });

    </script>
</body>
</html>