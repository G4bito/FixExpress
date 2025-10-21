<?php
// Simple mailer wrapper using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Send an email using PHPMailer.
 * Returns true on success, false on failure.
 * Configure SMTP settings below.
 */
function sendEmail($to, $subject, $body, $isHtml = false)
{
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP configuration
        $mail->SMTPDebug = 0;  // Disable debug output for production
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'zyrelgabrielmaningding@gmail.com'; // Your Gmail
        $mail->Password = 'wzpsnmncrjdcnftl'; // Your generated app password
        $mail->SMTPSecure = 'tls';  // Changed from PHPMailer::ENCRYPTION_STARTTLS
        $mail->Port = 587;
        
        // Disable SSL verification for testing
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Additional debug settings
        $mail->Debugoutput = 'html';  // Make debug output more readable
        $mail->Timeout = 60;  // Increase timeout

        $mail->setFrom('zyrelgabrielmaningding@gmail.com', 'FixExpress Support');
        $mail->addAddress($to);

        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $isHtml ? $body : nl2br(htmlspecialchars($body));
        $mail->AltBody = $isHtml ? strip_tags($body) : $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
