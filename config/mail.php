<?php
// File: config/mail.example.php
// Đổi tên thành mail.php và điền thông tin của bạn

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

define('MAIL_HOST',         'smtp.gmail.com');
define('MAIL_PORT',         587);
define('MAIL_USERNAME',     'your_gmail@gmail.com');
define('MAIL_PASSWORD',     'your_16_char_app_password');
define('MAIL_FROM_ADDRESS', 'your_gmail@gmail.com');
define('MAIL_FROM_NAME',    'NoteApp System');

function sendMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $body
): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}