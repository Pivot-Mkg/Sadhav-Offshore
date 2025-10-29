<?php
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Utility: is this an AJAX request?
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Utility: send a JSON response and die
function sendJson($success, $message, $redirect = null) {
    header('Content-Type: application/json');
    $resp = ['success' => $success, 'message' => $message];
    if ($redirect) $resp['redirect'] = $redirect;
    echo json_encode($resp);
    exit;
}

// On error, either redirect or send JSON, as appropriate
function respondWithError($message) {
    if (isAjax()) {
        sendJson(false, $message);
    } else {
        $_SESSION['form_error'] = $message;
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '#contact-section');
        exit;
    }
}

function respondWithSuccess($message, $redirectUrl) {
    if (isAjax()) {
        sendJson(true, $message, $redirectUrl);
    } else {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondWithError('Invalid request method');
}

// Get form data and trim
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    respondWithError('All fields are required');
}

// Validation
if (strlen($name) < 2 || strlen($name) > 100) {
    respondWithError('Name must be between 2 and 100 characters');
}
if (!preg_match('/^[a-zA-Z\s.\-\'"]+$/', $name)) {
    respondWithError('Name contains invalid characters');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondWithError('Invalid email address');
}
if (strlen($email) > 254) {
    respondWithError('Email address is too long');
}
if (strlen($subject) < 5 || strlen($subject) > 200) {
    respondWithError('Subject must be between 5 and 200 characters');
}
if (strlen($message) < 10 || strlen($message) > 2000) {
    respondWithError('Message must be between 10 and 2000 characters');
}
// Spam protection
if (
    preg_match('/(http|www\.|@.*\.)/i', $name) ||
    substr_count(strtolower($message), 'http') > 2 ||
    preg_match('/\b(viagra|casino|lottery|winner|congratulations)\b/i', $message)
) {
    respondWithError('Your message appears to be spam');
}

// Try MAil
$mail = new PHPMailer(true);
try {

    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer Debug level $level; message: $str");
    };
    // debug 


    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pivotmkg@gmail.com';
    $mail->Password = 'jfot fxdn ezvo zsct'; // make sure this is secure
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('pivotmkg@gmail.com', 'Sadhav Offshore');
    $mail->addAddress('aakash@pivotmkg.com');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Contact Form Submission - ' . $subject;

    $html_message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="color-scheme" content="light">
        <meta name="supported-color-schemes" content="light">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #2c3e50; background: #ffffff; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: linear-gradient(135deg, #003f87 0%, #0056b3 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #7f8c8d; background: #f1f1f1; }
            .button { display: inline-block; padding: 10px 20px; background: #003f87; color: #ffffff; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>New Contact Form Submission</h1>
            </div>
            <div class="content">
                <p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
                <p><strong>Message:</strong></p>
                <p>' . nl2br(htmlspecialchars($message)) . '</p>
            </div>
            <div class="footer">
                <p>This email was sent from the contact form on Sadhav Offshore website</p>
            </div>
        </div>
    </body>
    </html>';

    $mail->Body = $html_message;
    $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

    $mail->send();

    if (isset($_SESSION['form_error'])) {
        unset($_SESSION['form_error']);
    }
    respondWithSuccess('Message sent successfully!', '/thank-you.html');

} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    respondWithError('Message could not be sent. Please try again later.');
}