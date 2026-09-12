<?php
require_once __DIR__ . '/mailer.php';

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
        // HTTP_REFERER is absent on plenty of requests; fall back to the page.
        $back = $_SERVER['HTTP_REFERER'] ?? '/contact.html';
        header('Location: ' . $back . '#contact-section');
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

if (!mailer_is_configured()) {
    error_log('contact.php: SMTP password not set in forms/config.php');
    respondWithError('Message could not be sent. Please try again later.');
}

$mail = null;
try {
    $mail = mailer_new(MAIL_TO_CONTACT);
    $mail->addReplyTo($email, $name);
    $mail->Subject = 'Contact Form Submission - ' . $subject;

    $mail->Body = mailer_render_email('New Contact Form Submission', [
        'Name'    => $name,
        'Email'   => $email,
        'Subject' => $subject,
        'Message' => $message,
    ], ['Message']);
    $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

    $mail->send();

    if (isset($_SESSION['form_error'])) {
        unset($_SESSION['form_error']);
    }
    respondWithSuccess('Message sent successfully!', '/thank-you.html');

} catch (Exception $e) {
    error_log('contact.php mailer error: ' . ($mail ? $mail->ErrorInfo : $e->getMessage()));
    respondWithError('Message could not be sent. Please try again later.');
}