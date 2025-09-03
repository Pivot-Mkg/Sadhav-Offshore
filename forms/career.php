<?php
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pivotmkg@gmail.com';
    $mail->Password = 'jfot fxdn ezvo zsct';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('pivotmkg@gmail.com', 'Sadhav Offshore');
    $mail->addAddress('aakash@pivotmkg.com');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Career Application - ' . $position;

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
        .logo { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .content { background: #ffffff; padding: 40px 30px; }
        .field { margin-bottom: 25px; }
        .label { font-weight: 600; color: #003f87; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .value { padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #003f87; color: #2c3e50; }
        .footer { background: #f8f9fa; color: #6c757d; padding: 20px; text-align: center; font-size: 12px; border-top: 1px solid #e9ecef; }
        .brand { color: #003f87; font-weight: bold; }
        .highlight { background: #e3f2fd; border-left-color: #2196f3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SADHAV OFFSHORE</div>
            <div>New Career Application</div>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">Applicant Name</div>
                <div class="value">' . htmlspecialchars($name) . '</div>
            </div>
            <div class="field">
                <div class="label">Email Address</div>
                <div class="value">' . htmlspecialchars($email) . '</div>
            </div>
            <div class="field">
                <div class="label">Phone Number</div>
                <div class="value">' . htmlspecialchars($phone) . '</div>
            </div>
            <div class="field">
                <div class="label">Position Applied For</div>
                <div class="value highlight">' . htmlspecialchars($position) . '</div>
            </div>
            <div class="field">
                <div class="label">Years of Experience</div>
                <div class="value">' . htmlspecialchars($experience) . '</div>
            </div>';

    if (!empty($message)) {
        $html_message .= '
            <div class="field">
                <div class="label">Additional Message</div>
                <div class="value">' . nl2br(htmlspecialchars($message)) . '</div>
            </div>';
    }

    $html_message .= '
        </div>
        <div class="footer">
            <p>This application was submitted from the <span class="brand">Sadhav Offshore</span> careers page</p>
            <p>© 2025 Sadhav Offshore Engineering Pvt Ltd. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

    $mail->Body = $html_message;
    $mail->send();

    echo json_encode(['success' => true, 'redirect' => 'thank-you.html']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
