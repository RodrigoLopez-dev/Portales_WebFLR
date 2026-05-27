<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

function flr_send_mail(array $opt): bool
{
    $enabled = filter_var($opt['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if (!$enabled) {
        error_log('FLR_MAIL ok=0 disabled');
        return false;
    }

    $smtpHost = trim((string)($opt['smtp_host'] ?? ''));
    $smtpPort = (int)($opt['smtp_port'] ?? 587);
    $smtpUser = trim((string)($opt['smtp_user'] ?? ''));
    $smtpPass = (string)($opt['smtp_pass'] ?? '');

    $fromEmail = trim((string)($opt['from_email'] ?? $smtpUser));
    $fromName = trim((string)($opt['from_name'] ?? 'Gestor de Proyectos'));

    $to = trim((string)($opt['to'] ?? ''));
    $subject = trim((string)($opt['subject'] ?? ''));
    $htmlBody = (string)($opt['html'] ?? '');
    $altBody = (string)($opt['alt'] ?? strip_tags($htmlBody));

    $replyTo = trim((string)($opt['reply_to'] ?? ''));
    $replyName = trim((string)($opt['reply_name'] ?? $fromName));
    $bcc = trim((string)($opt['bcc'] ?? ''));

    if (
        $smtpHost === '' ||
        $smtpUser === '' ||
        $smtpPass === '' ||
        $fromEmail === '' ||
        $to === '' ||
        $subject === '' ||
        $htmlBody === ''
    ) {
        error_log('FLR_MAIL ok=0 missing_fields');
        return false;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('FLR_MAIL ok=0 invalid_to');
        return false;
    }

    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('FLR_MAIL ok=0 invalid_from');
        return false;
    }

    if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        error_log('FLR_MAIL ok=0 invalid_reply_to');
        return false;
    }

    if ($bcc !== '' && !filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
        error_log('FLR_MAIL ok=0 invalid_bcc');
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'error_log';

        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->Port = $smtpPort;

        if ($smtpPort === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);

        if ($replyTo !== '') {
            $mail->addReplyTo($replyTo, $replyName);
        }

        $mail->addAddress($to);

        if ($bcc !== '') {
            $mail->addBCC($bcc);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();

        error_log('FLR_MAIL ok=1');
        return true;
    } catch (Throwable $e) {
        error_log('FLR_MAIL ok=0 err=' . $e->getMessage());
        return false;
    }
}