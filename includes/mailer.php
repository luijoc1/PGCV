<?php
require_once __DIR__ . '/../vendor/autoload.php';

function applicationUrl($path, array $query = [], $base = null)
{
    $base = $base ?? (defined('APP_URL') ? APP_URL : (getenv('PGCV_APP_URL') ?: 'http://localhost/PGCV'));
    $parts = is_string($base) ? parse_url($base) : false;
    if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])
        || isset($parts['user'], $parts['pass']) || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment'])
        || preg_match('/[\x00-\x20\x7f]/', $base) || !is_string($path) || !preg_match('/\A[a-zA-Z0-9_\/-]+\.php\z/', $path) || strpos($path, '..') !== false) {
        throw new InvalidArgumentException('Configura una URL base válida para la aplicación.');
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/') . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
}

function configuredMailer()
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
    $mail->Port = defined('MAIL_PORT') ? MAIL_PORT : 465;
    $mail->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    if (!in_array($mail->SMTPSecure, ['ssl', 'tls'], true)) throw new RuntimeException('SMTP requiere cifrado TLS.');
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PASS;
    $mail->Timeout = 8;
    $mail->getSMTPInstance()->Timelimit = 15;
    $mail->SMTPDebug = 0;
    $ssl = ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false];
    $ca = defined('MAIL_CA_FILE') ? MAIL_CA_FILE : ini_get('openssl.cafile');
    if ($ca) {
        if (!is_readable($ca)) throw new RuntimeException('No se encontró el archivo de certificados configurado.');
        $ssl['cafile'] = $ca;
    }
    $mail->SMTPOptions = ['ssl' => $ssl];
    $mail->setFrom(MAIL_USER, 'Almacén los Almendros');
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    return $mail;
}
