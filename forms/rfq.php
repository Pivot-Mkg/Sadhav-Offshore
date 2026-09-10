<?php
/**
 * RFQ form handler - rfq.html
 * Responds with JSON; the page posts via fetch().
 */

require_once __DIR__ . '/mailer.php';

use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

function rfq_fail($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    rfq_fail('Method not allowed');
}

// Honeypot: real people never see this field, bots fill it in.
// Report success so the bot does not retry, but send nothing.
if (trim($_POST['rfq_subject'] ?? '') !== '') {
    echo json_encode(['success' => true, 'redirect' => 'thank-you.html']);
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$company  = trim($_POST['company'] ?? '');
$service  = trim($_POST['service_of_interest'] ?? '');
$details  = trim($_POST['project_details'] ?? '');
$timeline = trim($_POST['timeline'] ?? '');

if ($name === '' || $email === '' || $company === '' || $service === '' || $details === '') {
    rfq_fail('Please fill in all required fields.');
}
if (strlen($name) < 2 || strlen($name) > 100) {
    rfq_fail('Name must be between 2 and 100 characters.');
}
if (!preg_match('/^[a-zA-Z\s.\-\']+$/', $name)) {
    rfq_fail('Name contains invalid characters.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    rfq_fail('Please enter a valid email address.');
}
if (strlen($company) > 150) {
    rfq_fail('Company name is too long.');
}
if (strlen($service) > 150) {
    rfq_fail('Please choose a valid service.');
}
if (strlen($details) < 10 || strlen($details) > 5000) {
    rfq_fail('Project details must be between 10 and 5000 characters.');
}
if (strlen($timeline) > 150) {
    rfq_fail('Timeline is too long.');
}

// Basic spam heuristics, same shape as the other handlers.
if (
    preg_match('/(http|www\.|@.*\.)/i', $name) ||
    substr_count(strtolower($details), 'http') > 3 ||
    preg_match('/\b(viagra|casino|lottery|winner|congratulations)\b/i', $details)
) {
    rfq_fail('Your request appears to be spam.');
}

if (!mailer_is_configured()) {
    error_log('rfq.php: SMTP password not set in forms/config.php');
    rfq_fail('We could not send your request right now. Please try again later.');
}

$mail = null;

try {
    $mail = mailer_new();
    $mail->addReplyTo($email, $name);
    $mail->Subject = 'RFQ - ' . $company . ' - ' . $service;

    // Optional attachment (spec, drawing, scope document).
    $attachError = mailer_attach_upload($mail, 'attachment', false, 'rfq', RFQ_ALLOWED_EXT, RFQ_MAX_BYTES);
    if ($attachError !== null) {
        rfq_fail($attachError);
    }

    $mail->Body = mailer_render_email('New Request for Quote', [
        'Contact Name'    => $name,
        'Email'           => $email,
        'Company'         => $company,
        'Service'         => $service,
        'Timeline'        => $timeline !== '' ? $timeline : 'Not specified',
        'Project Details' => $details,
    ], ['Project Details']);

    $mail->AltBody = "RFQ from $name ($company)\nEmail: $email\nService: $service\nTimeline: $timeline\n\nDetails:\n$details";

    $mail->send();

    echo json_encode(['success' => true, 'redirect' => 'thank-you.html']);
} catch (Exception $e) {
    error_log('rfq.php mailer error: ' . ($mail ? $mail->ErrorInfo : $e->getMessage()));
    rfq_fail('Your request could not be sent. Please try again later.');
}
