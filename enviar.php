<?php
session_start();
require_once __DIR__ . '/includes/csrf.php';
requireValidCSRFRequest();
require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/contact_mail.php';
try {
    $contact = contactMessage($_POST);
    $conn = $pdo->open();
    if (reserveContactAttempt($conn, $_SERVER['REMOTE_ADDR'] ?? 'unknown') > 0) {
        throw new InvalidArgumentException('Demasiadas solicitudes. Intenta de nuevo en quince minutos.');
    }
    $mail = configuredMailer();
    $mail->addAddress(defined('MAIL_CONTACT_TO') ? MAIL_CONTACT_TO : 'alexdavidr@gmail.com');
    $mail->addReplyTo($contact['mail'], $contact['name']);
    $mail->isHTML(false);
    $mail->Subject = 'Contacto: ' . $contact['subject'];
    $mail->Body = "Nombre: " . $contact['name'] . "\nCorreo: " . $contact['mail'] . "\n\n" . $contact['message'];
    if (!$mail->send()) throw new RuntimeException('Fallo de envío.');
    $_SESSION['success'] = 'Tu mensaje fue enviado correctamente.';
} catch (InvalidArgumentException $e) {
    $_SESSION['error'] = $e->getMessage();
} catch (Throwable $e) {
    error_log('Error de contacto: ' . $e->getMessage());
    $_SESSION['error'] = 'No se pudo enviar el mensaje. Intenta de nuevo.';
}
$pdo->close();
header('location: contacto.php');
exit();