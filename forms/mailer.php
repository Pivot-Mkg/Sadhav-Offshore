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
function mailer_new($to = null) {
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
    $mail->addAddress($to ?: MAIL_TO);
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

/**
 * The one HTML template every form's notification email uses.
 *
 * $subtitle   - e.g. 'New Contact Form Submission'
 * $rows       - ordered ['Label' => 'value', ...] for the field boxes
 * $longFields - labels from $rows whose value may contain newlines
 *   (line breaks are preserved instead of collapsed)
 *
 * Built with a table layout and inline / bgcolor styling rather than a
 * <style> block or CSS gradients: Outlook and Microsoft 365 routinely strip
 * both, and a gradient header with white text becomes invisible white-on-
 * white when that happens - the field itself still takes up its padding, so
 * the email just shows as a blank gap above the content. bgcolor+inline
 * background-color together is the standard fallback that survives that.
 */
function mailer_render_email($subtitle, array $rows, array $longFields = []) {
    $font = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif";

    $fieldsHtml = '';
    foreach ($rows as $label => $value) {
        $value = (string) $value;
        $displayValue = in_array($label, $longFields, true)
            ? nl2br(htmlspecialchars($value))
            : htmlspecialchars($value);

        $fieldsHtml .= '
        <tr>
            <td style="padding:0 30px 20px 30px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-family:' . $font . ';font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:#003f87;padding-bottom:8px;">' . htmlspecialchars($label) . '</td>
                    </tr>
                    <tr>
                        <td bgcolor="#f8f9fa" style="background-color:#f8f9fa;border-left:4px solid #003f87;padding:14px 16px;font-family:' . $font . ';font-size:14px;line-height:1.6;color:#2c3e50;">' . $displayValue . '</td>
                    </tr>
                </table>
            </td>
        </tr>';
    }

    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
</head>
<body style="margin:0;padding:0;background-color:#eef1f5;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f5;">
<tr>
<td align="center" style="padding:24px 16px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;">
<tr>
<td bgcolor="#003f87" style="background-color:#003f87;padding:28px 24px;text-align:center;">
<div style="font-family:' . $font . ';font-size:20px;font-weight:700;color:#ffffff;letter-spacing:1px;">SADHAV OFFSHORE</div>
<div style="font-family:' . $font . ';font-size:14px;color:#ffffff;margin-top:6px;">' . htmlspecialchars($subtitle) . '</div>
</td>
</tr>
<tr><td style="height:24px;line-height:24px;font-size:0;">&nbsp;</td></tr>' . $fieldsHtml . '
<tr><td style="height:8px;line-height:8px;font-size:0;">&nbsp;</td></tr>
<tr>
<td bgcolor="#f8f9fa" style="background-color:#f8f9fa;padding:18px 24px;text-align:center;border-top:1px solid #e9ecef;font-family:' . $font . ';font-size:12px;color:#6c757d;">
This message was submitted from the <strong style="color:#003f87;">Sadhav Offshore</strong> website
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>';
}
