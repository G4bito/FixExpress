<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('./dist/database/loginserver.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // --- Check if admin ---
    $adminQuery = "SELECT * FROM admin_accounts WHERE username = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("s", $username);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();

    if ($adminResult->num_rows === 1) {
        $admin = $adminResult->fetch_assoc();
        // Check password using modern hashing, with a fallback to MD5 for older accounts
        if (password_verify($password, $admin['password']) || md5($password) === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: ./dist/admin/bookings.php");
            exit;
        }
    }

    // --- Check if worker ---
    $workerQuery = "SELECT * FROM workers WHERE username = ?";
    $workerStmt = $conn->prepare($workerQuery);
    $workerStmt->bind_param("s", $username);
    $workerStmt->execute();
    $workerResult = $workerStmt->get_result();

    if ($workerResult->num_rows === 1) {
        $worker = $workerResult->fetch_assoc();
        // Check password using modern hashing, with a fallback to MD5 for older accounts
        if (password_verify($password, $worker['password']) || md5($password) === $worker['password']) {
            // Check worker status before login
            if ($worker['status'] === 'Pending') {
                $error = "Your account is waiting for admin approval.";
            } elseif ($worker['status'] === 'Rejected') {
                $error = "Your application has been rejected.";
            } elseif ($worker['status'] === 'Approved') {
                $_SESSION['worker_logged_in'] = true;
                $_SESSION['worker_id'] = $worker['worker_id'];
                $_SESSION['username'] = $worker['username']; // Use consistent session key
                header("Location: ./workers.php");
                exit;
            } else {
                $error = "Invalid worker status. Please contact support.";
            }
        }
    }

    // --- Check if regular user ---
    $userQuery = "SELECT * FROM users WHERE username = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("s", $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows === 1) {
        $user = $userResult->fetch_assoc();
        // Check password using modern hashing, with a fallback to MD5 for older accounts
        if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ./index.php");
            exit;
        }
    } else {
        if (empty($error)) {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup</title>
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
            color: #d97f3e;
        }

</style>
<body>
    <div id="loginPage" class="login-container">
        <div class="bottom-triangle"></div>
        <div class="bottom-triangle-inner"></div>

        <div class="login-form fade-in">
            <h1>Login</h1>
            <?php if (!empty($error)): ?>
                <div class="alert" style="color:red;text-align:center;margin-bottom:10px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" action="">
               <div class="form-group">
                <input type="text" name="username" placeholder=" " required maxlength="100">
                <label>Username</label>
            </div>

            <div class="form-group password-group">
                <input type="password" name="password" id="password" placeholder=" " required maxlength="30">
                    <label for="password">Password</label>

                    <!-- SVG eye icon -->
                    <svg class="toggle-password" id="togglePassword" xmlns="http://www.w3.org/2000/svg" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>


                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">Login</button>

                <div class="signup-link">
                    Don't have an account?<br>
                    <a href="signup.php">Sign up</a>
                </div>
                
                <?php if (isset($_SESSION['password_reset_success'])): ?>
                    <div class="alert" style="color:green;text-align:center;margin-top:10px;">
                        Password has been reset successfully!
                    </div>
                    <?php unset($_SESSION['password_reset_success']); ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
<script>
const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

let visible = false;

togglePassword.addEventListener("click", () => {
    visible = !visible;
    passwordInput.type = visible ? "text" : "password";

    // this is for the eye icon change
    togglePassword.innerHTML = visible
        ? `<path stroke-linecap="round" stroke-linejoin="round" 
                 d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 
                 a11.05 11.05 0 012.944-4.78M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />`
        : `<path stroke-linecap="round" stroke-linejoin="round" 
                 d="M2.458 12C3.732 7.943 7.523 5 12 5 
                 c4.478 0 8.268 2.943 9.542 7
                 -1.274 4.057-5.064 7-9.542 7
                 -4.477 0-8.268-2.943-9.542-7z" />
           <circle cx="12" cy="12" r="3" />`;
});
</script>

</html>
