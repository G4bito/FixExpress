<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('./dist/database/loginserver.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // --- Check if admin ---
    $adminQuery = "SELECT * FROM admin_accounts WHERE username = ? AND password = MD5(?)";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("ss", $username, $password);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();

    if ($adminResult->num_rows === 1) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: ./dist/admin/bookings.php");
        exit;
    }

    // --- Check if worker ---
    $workerQuery = "SELECT * FROM workers WHERE username = ? AND password = MD5(?)";
    $workerStmt = $conn->prepare($workerQuery);
    $workerStmt->bind_param("ss", $username, $password);
    $workerStmt->execute();
    $workerResult = $workerStmt->get_result();

    if ($workerResult->num_rows === 1) {
        $worker = $workerResult->fetch_assoc();

        // Check worker status before login
        if ($worker['status'] === 'Pending') {
            $error = "Your account is waiting for admin approval.";
        } elseif ($worker['status'] === 'Rejected') {
            $error = "Your application has been rejected.";
        } elseif ($worker['status'] === 'Approved') {
            $_SESSION['worker_logged_in'] = true;
            $_SESSION['worker_id'] = $worker['worker_id'];
            $_SESSION['worker_name'] = $worker['username'];

            // ✅ Redirect approved workers to workers.php
            header("Location: ./workers.php");
            exit;
        } else {
            $error = "Invalid worker status. Please contact support.";
        }
    }

    // --- Check if regular user ---
    $userQuery = "SELECT * FROM users WHERE username = ? AND password = MD5(?)";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("ss", $username, $password);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows === 1) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: ./index.php");
        exit;
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
                    <input type="text" name="username" placeholder=" " required>
                    <label>Username</label>
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder=" " required>
                    <label>Password</label>
                </div>

                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">Login</button>

                <div class="signup-link">
                    Don't have an account?<br>
                    <a href="signup.php">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
