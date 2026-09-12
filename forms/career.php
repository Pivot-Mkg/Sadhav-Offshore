<?php
require_once __DIR__ . '/mailer.php';

use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$position = trim($_POST['position'] ?? '');
$experience = trim($_POST['experience'] ?? '');
$message = trim($_POST['message'] ?? '');

// Required field validation
if (empty($name) || empty($email) || empty($phone) || empty($position)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Name validation
if (strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Name must be between 2 and 100 characters']);
    exit;
}

if (!preg_match('/^[a-zA-Z\s\.\-\']+$/', $name)) {
    echo json_encode(['success' => false, 'message' => 'Name contains invalid characters']);
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (strlen($email) > 254) {
    echo json_encode(['success' => false, 'message' => 'Email address is too long']);
    exit;
}

// Phone validation
if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,15}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Position validation
if (strlen($position) < 2 || strlen($position) > 100) {
    echo json_encode(['success' => false, 'message' => 'Position must be between 2 and 100 characters']);
    exit;
}

// Experience validation
if (!empty($experience) && (!is_numeric($experience) || $experience < 0 || $experience > 50)) {
    echo json_encode(['success' => false, 'message' => 'Experience must be a valid number between 0 and 50']);
    exit;
}

// Message validation (optional field)
if (!empty($message) && (strlen($message) < 10 || strlen($message) > 1000)) {
    echo json_encode(['success' => false, 'message' => 'Message must be between 10 and 1000 characters']);
    exit;
}

// Basic spam protection
if (
    preg_match('/(http|www\.|@.*\.)/i', $name) ||
    (!empty($message) && substr_count(strtolower($message), 'http') > 1)
) {
    echo json_encode(['success' => false, 'message' => 'Application appears to be spam']);
    exit;
}

if (!mailer_is_configured()) {
    error_log('career.php: SMTP password not set in forms/config.php');
    echo json_encode(['success' => false, 'message' => 'We could not submit your application right now. Please try again later.']);
    exit;
}

$mail = null;

try {
    $mail = mailer_new(MAIL_TO_CAREER);
    $mail->addReplyTo($email, $name);
    $mail->Subject = 'Career Application - ' . $position;

    // Attach the CV. Required, and validated by mailer_attach_upload().
    $resumeError = mailer_attach_upload($mail, 'resume', true, 'resume', RESUME_ALLOWED_EXT, RESUME_MAX_BYTES);
    if ($resumeError !== null) {
        echo json_encode(['success' => false, 'message' => $resumeError]);
        exit;
    }

    $rows = [
        'Applicant Name'        => $name,
        'Email Address'         => $email,
        'Phone Number'          => $phone,
        'Position Applied For'  => $position,
        'Years of Experience'   => $experience,
    ];
    if (!empty($message)) {
        $rows['Additional Message'] = $message;
    }

    $mail->Body = mailer_render_email('New Career Application', $rows, ['Additional Message']);
    $mail->AltBody = "Applicant Name: $name\nEmail: $email\nPhone: $phone\nPosition: $position\nExperience: $experience"
        . (!empty($message) ? "\n\nAdditional Message:\n$message" : '');

    $mail->send();

    echo json_encode(['success' => true, 'redirect' => 'thank-you.html']);
} catch (Exception $e) {
    error_log('career.php mailer error: ' . ($mail ? $mail->ErrorInfo : $e->getMessage()));
    echo json_encode(['success' => false, 'message' => 'Your application could not be sent. Please try again later.']);
}
