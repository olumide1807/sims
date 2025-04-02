<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendEmail($recipient, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'olumide1807@gmail.com';  // Your Gmail
        $mail->Password   = 'erof zrzy dums bwhn';  // Use App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email Details
        $mail->setFrom('olumide1807@gmail.com', 'Your Name');
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Send Email
        $mail->send();
        return "Email sent successfully!";
    } catch (Exception $e) {
        return "Email failed to send. Error: " . $mail->ErrorInfo;
    }
}
?>