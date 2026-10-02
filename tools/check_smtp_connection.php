<?php
// Checks TLS and authentication only: no recipients, MAIL FROM, DATA or send().
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/mailer.php';
$mail = null; $stage = 'configuration';
try {
    $mail = configuredMailer();
    if (trim($mail->Username) === '' || trim($mail->Password) === '') throw new RuntimeException('Missing credentials.');
    $ssl = $mail->SMTPOptions['ssl'] ?? [];
    if (($ssl['verify_peer'] ?? false) !== true || ($ssl['verify_peer_name'] ?? false) !== true || ($ssl['allow_self_signed'] ?? true) !== false) throw new RuntimeException('Certificate verification required.');
    echo json_encode(['host' => $mail->Host, 'port' => $mail->Port, 'encryption' => $mail->SMTPSecure, 'certificate_validation' => true, 'ca_readable' => !isset($ssl['cafile']) || is_readable($ssl['cafile'])]) . "\n";
    $mail->SMTPAuth = false;
    $stage = 'connection_tls';
    if (!$mail->smtpConnect()) throw new RuntimeException('Connection failed.');
    echo "Conexión SMTP y validación TLS correctas.\n";
    $stage = 'authentication';
    if (!$mail->getSMTPInstance()->authenticate($mail->Username, $mail->Password, $mail->AuthType, $mail->getOAuth())) throw new RuntimeException('Authentication failed.');
    echo "Autenticación SMTP correcta. No se envió ningún mensaje.\n";
} catch (Throwable $e) {
    $error = $mail ? $mail->getSMTPInstance()->getError() : [];
    // Never print server details, exception text or debug transcripts: they
    // may contain personal addresses or authentication material.
    $code = (string) ($error['smtp_code'] ?? '');
    $enhanced = (string) ($error['smtp_code_ex'] ?? '');
    fwrite(STDERR, json_encode(['failed_stage' => $stage, 'smtp_code' => preg_match('/\A[0-9]+\z/', $code) ? $code : '', 'smtp_code_ex' => preg_match('/\A[0-9]+\.[0-9]+\.[0-9]+\z/', $enhanced) ? $enhanced : '', 'message_sent' => false]) . "\n");
    exit(1);
} finally {
    if ($mail) $mail->smtpClose();
}
