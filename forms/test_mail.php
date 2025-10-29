<?php

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pivotmkg@gmail.com';
    $mail->Password = 'jfot fxdn ezvo zsct'; // Use an App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('pivotmkg@gmail.com', 'Test');
    $mail->addAddress('aakash@pivotmkg.com');
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email';
    
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}