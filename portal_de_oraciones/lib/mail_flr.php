<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

function flr_send_mail($opt)
{
    if (!is_array($opt)) {
        error_log('FLR_MAIL ok=0 invalid_options');
        return false;
    }

    $SMTP_HOST = isset($opt['smtp_host']) ? $opt['smtp_host'] : 'mail.flrosas.cl';
    $SMTP_PORT = isset($opt['smtp_port']) ? (int)$opt['smtp_port'] : 587;
    $SMTP_USER = isset($opt['smtp_user']) ? $opt['smtp_user'] : 'noresponder@flrosas.cl';
    $SMTP_PASS = isset($opt['smtp_pass']) ? $opt['smtp_pass'] : '';

    $fromEmail = isset($opt['from_email']) ? $opt['from_email'] : $SMTP_USER;
    $fromName  = isset($opt['from_name']) ? $opt['from_name'] : 'Fundación Las Rosas';

    $to       = isset($opt['to']) ? (string)$opt['to'] : '';
    $subject  = isset($opt['subject']) ? (string)$opt['subject'] : '';
    $htmlBody = isset($opt['html']) ? (string)$opt['html'] : '';
    $altBody  = isset($opt['alt']) ? (string)$opt['alt'] : strip_tags($htmlBody);

    $replyTo   = isset($opt['reply_to']) ? (string)$opt['reply_to'] : 'info@flrosas.cl';
    $replyName = isset($opt['reply_name']) ? (string)$opt['reply_name'] : 'Fundación Las Rosas';

    if ($to === '' || $subject === '' || $htmlBody === '') {
        error_log('FLR_MAIL ok=0 missing_fields');
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
        $mail->SMTPSecure = 'tls';

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($replyTo, $replyName);
        $mail->addAddress($to);

        if (!empty($opt['bcc'])) {
            $mail->addBCC($opt['bcc']);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();

        error_log('FLR_MAIL ok=1 to=' . $to . ' host=' . $SMTP_HOST . ' port=' . $SMTP_PORT);
        return true;

    } catch (Exception $e) {
        error_log(
            'FLR_MAIL ok=0 to=' . $to .
            ' host=' . $SMTP_HOST .
            ' port=' . $SMTP_PORT .
            ' user=' . $SMTP_USER .
            ' err=' . $mail->ErrorInfo .
            ' ex=' . $e->getMessage()
        );
        return false;
    }
}