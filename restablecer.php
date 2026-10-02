<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'includes/session.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/password_reset.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {

	if (!is_string($_POST['csrf_token'] ?? null) || !validateCSRFToken($_POST['csrf_token'])) {
		$_SESSION['error'] = 'Solicitud inválida. Intenta de nuevo.';
		header('location: password_olvidada.php');
		exit();
	}
	$email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['error'] = 'Ingresa un correo electrónico válido.';
		header('location: password_olvidada.php');
		exit();
	}

	$conn = $pdo->open();

	$stmt = $conn->prepare("SELECT id, email FROM users WHERE email=:email LIMIT 1");
	$stmt->execute(['email' => $email]);
	$row = $stmt->fetch();

	if ($row) {

		try {

			$code = issuePasswordReset($conn, $row['id']);

			$message = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
            </head>
            <body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;">

                <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 15px;">
                    <tr>
                        <td align="center">

                            <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #ddd;">

                                <tr>
                                    <td style="background:#1a2e4a;padding:30px;text-align:center;">
                                        <h2 style="color:#fff;margin:0;">
                                            Almacén los Almendros
                                        </h2>
                                        <p style="color:#d9d9d9;margin-top:8px;">
                                            Restablecimiento de contraseña
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:35px;">

                                        <p>Hola,</p>

                                        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>

                                        <p>
                                            <strong>Correo:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '
                                        </p>

                                        <p>
                                            Haz clic en el siguiente botón para crear una nueva contraseña:
                                        </p>

                                        <p style="text-align:center;margin:35px 0;">
                                            <a href="' . escapeHtml(applicationUrl('password_restablecer.php', ['code' => $code, 'user' => $row['id']])) . '"
                                            style="
                                            background:#1a2e4a;
                                            color:#ffffff;
                                            text-decoration:none;
                                            padding:14px 28px;
                                            border-radius:8px;
                                            display:inline-block;
                                            font-size:15px;">
                                            Restablecer contraseña
                                            </a>
                                        </p>

                                        <p style="color:#777;">
                                            Este enlace vence en una hora y solo puede usarse una vez.
                                            Si no solicitaste este cambio, puedes ignorar este correo.
                                        </p>

                                    </td>
                                </tr>

                                <tr>
                                    <td style="background:#f7f7f7;padding:18px;text-align:center;font-size:12px;color:#999;">
                                        © 2026 Almacén los Almendros
                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>
                </table>

            </body>
            </html>';

			require 'vendor/autoload.php';

			$mail = null;

			try {

				$mail = configuredMailer();
				$mail->addAddress($email);
				$mail->addReplyTo(MAIL_USER, 'Almacén los Almendros');

				$mail->isHTML(true);
				$mail->CharSet = 'UTF-8';
				$mail->Subject = 'Restablecimiento de contraseña';
				$mail->Body = $message;

				$mail->send();

				$_SESSION['success'] = 'Se envió un enlace de recuperación a tu correo electrónico.';
			} catch (Exception $e) {
				error_log('Error de correo de recuperación: ' . $e->getMessage());
				$_SESSION['error'] = 'No fue posible enviar el correo. Intenta de nuevo.';
			}
		} catch (\Throwable $e) {
			error_log('Error al generar recuperación: ' . $e->getMessage());
			$_SESSION['error'] = 'No fue posible generar el enlace. Intenta de nuevo.';
		}
	} else {
		$_SESSION['error'] = 'No existe una cuenta registrada con ese correo electrónico.';
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Ingresa un correo electrónico.';
}

header('location: password_olvidada.php');
exit();
