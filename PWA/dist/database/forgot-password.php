<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include('./dist/database/loginserver.php');

if (isset($_POST['send_code'])) {
    $email = $_POST['email'];

    // Check if email exists
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $otp = rand(100000, 999999); // 6-digit code
        $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Store OTP & expiry
        mysqli_query($conn, "UPDATE users SET reset_code='$otp', reset_expires='$expires' WHERE email='$email'");

        // Send email
        $mail = new PHPMailer(true); 
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'youremail@gmail.com'; // your Gmail
            $mail->Password = 'your-app-password';   // Gmail App Password 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('youremail@gmail.com', 'FixExpress');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "<h3>Your one-time code is: <b>$otp</b></h3><p>This code will expire in 15 minutes.</p>";

            $mail->send();
            echo "<script>alert('A code has been sent to your email.'); window.location='verify-code.php?email=$email';</script>";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('No account found with that email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link rel="stylesheet" href="./dist/assets/css/login.css">
</head>
<body>
<div class="login-container">
  <div class="login-form">
    <h1>Forgot Password</h1>
    <form method="POST" action="">
      <div class="form-group">
        <input type="email" name="email" placeholder=" " required>
        <label>Email</label>
      </div>
      <button type="submit" name="send_code" class="btn-login">Send Code</button>
    </form>
  </div>
</div>
</body>
</html>