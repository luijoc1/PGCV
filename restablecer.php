<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'includes/session.php';

if (isset($_POST['reset'])) {

	$email = $_POST['email'];

	$conn = $pdo->open();

	$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email=:email");
	$stmt->execute(['email' => $email]);
	$row = $stmt->fetch();

	if ($row['numrows'] > 0) {

		// Generar código de recuperación
		$set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = substr(str_shuffle($set), 0, 15);

		try {

			$stmt = $conn->prepare("UPDATE users SET reset_code=:code WHERE id=:id");
			$stmt->execute([
				'code' => $code,
				'id' => $row['id']
			]);

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
                                            <strong>Correo:</strong> ' . $email . '
                                        </p>

                                        <p>
                                            Haz clic en el siguiente botón para crear una nueva contraseña:
                                        </p>

                                        <p style="text-align:center;margin:35px 0;">
                                            <a href="http://localhost/PGCV/password_restablecer.php?code=' . $code . '&user=' . $row['id'] . '"
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

			$mail = new PHPMailer(true);

			try {

				$mail->isSMTP();
				$mail->Host = 'smtp.gmail.com';
				$mail->SMTPAuth = true;

				// Usa la configuración del config.php
				$mail->Username = MAIL_USER;
				$mail->Password = MAIL_PASS;

				$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				);

				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;

				$mail->setFrom(MAIL_USER, 'Almacén los Almendros');
				$mail->addAddress($email);
				$mail->addReplyTo(MAIL_USER, 'Almacén los Almendros');

				$mail->isHTML(true);
				$mail->CharSet = 'UTF-8';
				$mail->Subject = 'Restablecimiento de contraseña';
				$mail->Body = $message;

				$mail->send();

				$_SESSION['success'] = 'Se envió un enlace de recuperación a tu correo electrónico.';
			} catch (Exception $e) {
				$_SESSION['error'] = 'No fue posible enviar el correo.<br><strong>Detalle:</strong> ' . $mail->ErrorInfo;
			}
		} catch (PDOException $e) {
			$_SESSION['error'] = $e->getMessage();
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
