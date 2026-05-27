<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

function flr_send_mail(array $opt): bool
{
    $SMTP_HOST = $opt['smtp_host'] ?? 'mail.flrosas.cl';
    $SMTP_PORT = (int)($opt['smtp_port'] ?? 587);
    $SMTP_USER = $opt['smtp_user'] ?? 'noresponder@flrosas.cl';
    $SMTP_PASS = $opt['smtp_pass'] ?? '';

    $fromEmail = $opt['from_email'] ?? $SMTP_USER;
    $fromName  = $opt['from_name']  ?? 'Fundación Las Rosas';

    $to       = (string)($opt['to'] ?? '');
    $subject  = (string)($opt['subject'] ?? '');
    $htmlBody = (string)($opt['html'] ?? '');
    $altBody  = (string)($opt['alt'] ?? strip_tags($htmlBody));

    $replyTo   = (string)($opt['reply_to'] ?? 'info@flrosas.cl');
    $replyName = (string)($opt['reply_name'] ?? 'Fundación Las Rosas');

    if ($to === '' || $subject === '' || $htmlBody === '') {
        error_log("FLR_MAIL ok=0 missing_fields");
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'error_log';

        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->Port       = $SMTP_PORT;
        /* $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; */
        $mail->SMTPSecure = $SMTP_PORT === 465
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($replyTo, $replyName);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("FLR_MAIL ok=0 invalid_to={$to}");
            return false;
        }

        $mail->addAddress($to);

        if (!empty($opt['bcc'])) {
            $mail->addBCC($opt['bcc']);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();

        error_log("FLR_MAIL ok=1 to={$to}");
        return true;

    } catch (Throwable $e) {
        error_log("FLR_MAIL ok=0 err=" . $e->getMessage());
        return false;
    }
}