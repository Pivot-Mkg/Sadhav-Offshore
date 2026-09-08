<?php
/**
 * Shared mail bootstrap for every form handler.
 *
 * Handlers require this file, call mailer_new() for a configured PHPMailer,
 * and mailer_attach_upload() to validate and attach an uploaded file.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * True when config.php still holds the placeholder password.
 */
function mailer_is_configured() {
    return SMTP_PASS !== '' && SMTP_PASS !== 'REPLACE_WITH_NEW_APP_PASSWORD';
}

/**
 * A PHPMailer instance wired to the SMTP settings in config.php.
 * Throws PHPMailer\PHPMailer\Exception on failure.
 */
function mailer_new() {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Never echo the SMTP conversation to the browser. Debug goes to the
    // error log only, and only when explicitly switched on here.
    $mail->SMTPDebug   = 0;
    $mail->Debugoutput = function ($str, $level) {
        error_log("PHPMailer [$level]: $str");
    };

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->isHTML(true);

    return $mail;
}

/**
 * Validate an uploaded file and attach it to $mail.
 *
 * $allowedExt - comma separated extension list, e.g. 'pdf,doc,docx'
 * $maxBytes   - size ceiling for this particular form
 *
 * Returns an error string on failure, or null on success (including when no
 * file was supplied and $required is false).
 */
function mailer_attach_upload(PHPMailer $mail, $field, $required, $prefix, $allowedExt, $maxBytes) {
    $hasFile = isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE;

    if (!$hasFile) {
        return $required ? 'Please attach a file.' : null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return 'That file is too large.';
        }
        return 'The file could not be uploaded. Please try again.';
    }

    if ($file['size'] <= 0) {
        return 'The uploaded file is empty.';
    }

    if ($file['size'] > $maxBytes) {
        return 'File must be under ' . round($maxBytes / 1048576) . ' MB.';
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return 'Invalid upload.';
    }

    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = explode(',', $allowedExt);

    if (!in_array($ext, $allowed, true)) {
        return 'Allowed file types: ' . strtoupper(str_replace(',', ', ', $allowedExt)) . '.';
    }

    // Attach straight from the temp path so no writable uploads/ dir is needed.
    // Rename it so the visitor's filename cannot shape the attachment name.
    $safeName = $prefix . '-' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $mail->addAttachment($file['tmp_name'], $safeName);

    return null;
}
