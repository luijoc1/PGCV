<?php
use PHPMailer\PHPMailer\PHPMailer;
date_default_timezone_set('America/Bogota');
require 'vendor/autoload.php';
include 'includes/session.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/checkout.php';

if (!isset($_SESSION['user'], $user['id'])) {
    $_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
    header('location: login.php');
    exit();
}
requireValidCSRFRequest();
try {
    $billing = checkoutBilling($_POST);
    $checkoutToken = $_POST['checkout_token'] ?? null;
    if (!validCheckoutToken($checkoutToken)) {
        throw new InvalidArgumentException('El formulario de compra venció. Recarga la página antes de confirmar.');
    }
} catch (InvalidArgumentException $e) {
    $_SESSION['error'] = $e->getMessage();
    header('location: facturacion.php');
    exit();
}
$nombre_facturacion = $billing['nombre_facturacion'];
$documento = $billing['documento'];
$direccion = $billing['direccion'];
$telefono = $billing['telefono'];
$ciudad = $billing['ciudad'];
$metodo_pago = $billing['metodo_pago'];
$conn = $pdo->open();
$compra_ok = false;
$payid = null;
$salesid = null;
try {
    $result = completeCheckout($conn, $user['id'], $billing, $checkoutToken, function ($connection, $sale) use ($billing, $user) {
        registrarLog($connection, 'logs_ventas', $sale['sales_id'], null, array_merge($billing, [
            'pay_id' => $sale['pay_id'], 'user_id' => $user['id'], 'total' => $sale['total'],
        ]), 'INSERT', $user['email']);
    });
    $payid = $result['pay_id'];
    $salesid = $result['sales_id'];
    $total = $result['total'];
    $productos_comprados = $result['products'];
    $compra_ok = !$result['replayed'];
    $_SESSION['success'] = ($result['replayed'] ? 'Este pedido ya había sido registrado. ' : 'Pedido registrado correctamente. ') . 'Número de transacción: ' . $payid;
} catch (Throwable $e) {
    error_log('Error en compra: ' . $e->getMessage());
    $_SESSION['error'] = $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo completar el pedido. Intenta de nuevo.';
}

// La compra ya está guardada. Liberamos la sesión y la BD ANTES de enviar el correo,
// así un SMTP lento no bloquea al usuario ni otras pestañas.
$pdo->close();
session_write_close();

if ($compra_ok) {
	// ─── CORREO DE CONFIRMACIÓN ───────────────────────
	$metodos = [
		'tarjeta'       => 'Tarjeta de crédito / débito',
		'transferencia' => 'Transferencia / PSE',
		'efectivo'      => 'Efectivo (pago contraentrega)',
	];
	$metodo_texto = $metodos[$metodo_pago] ?? $metodo_pago;

	$tabla_productos = '';
	foreach ($productos_comprados as $p) {
		$precio_final = precioConDescuento($p['price'], $p['descuento']);
		$subtotal = $precio_final * $p['quantity'];
		$tabla_productos .= "
				<tr>
					<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0;'>" . htmlspecialchars($p['name']) . "</td>
					<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>" . $p['quantity'] . "</td>
					<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>&#36;" . number_format($precio_final, 2) . "</td>
					<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>&#36;" . number_format($subtotal, 2) . "</td>
				</tr>
			";
	}

	$nombre_safe    = htmlspecialchars($nombre_facturacion);
	$direccion_safe = htmlspecialchars($direccion . ', ' . $ciudad);

	$correo_body = '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e0e0e0;">
        <tr>
          <td style="background:#1a2e4a; padding:28px 36px; text-align:center;">
            <span style="font-size:18px; font-weight:bold; color:#ffffff;">🛍 Almacén los Almendros</span><br><br>
            <div style="font-size:28px;">✅</div>
            <h1 style="color:#ffffff; font-size:20px; margin:8px 0 4px;">¡Compra confirmada!</h1>
            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">Transacción N° ' . $payid . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px;">
            <p style="color:#444; font-size:15px; line-height:1.6; margin:0 0 20px;">
              Hola <strong style="color:#1a2e4a;">' . $nombre_safe . '</strong>, tu pedido ha sido recibido exitosamente.
            </p>
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa; border-radius:8px; border:1px solid #e0e0e0; margin-bottom:20px;">
              <tr><td style="padding:14px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#999;">Dirección de entrega</td>
                    <td style="font-size:12px; color:#999; text-align:right;">Método de pago</td>
                  </tr>
                  <tr>
                    <td style="font-size:14px; color:#1a2e4a; font-weight:bold;">' . $direccion_safe . '</td>
                    <td style="font-size:14px; color:#1a2e4a; font-weight:bold; text-align:right;">' . htmlspecialchars($metodo_texto) . '</td>
                  </tr>
                </table>
              </td></tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:8px; overflow:hidden; margin-bottom:20px;">
              <tr style="background:#1a2e4a;">
                <th style="padding:10px 12px; color:#fff; text-align:left; font-size:13px;">Producto</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Cant.</th>
                <th style="padding:10px 12px; color:#fff; text-align:right; font-size:13px;">Precio</th>
                <th style="padding:10px 12px; color:#fff; text-align:right; font-size:13px;">Subtotal</th>
              </tr>
              ' . $tabla_productos . '
              <tr style="background:#f5f7fa;">
                <td colspan="3" style="padding:10px 12px; font-weight:bold; font-size:14px;">Total</td>
                <td style="padding:10px 12px; font-weight:bold; font-size:14px; text-align:right;">&#36;' . number_format($total, 2) . '</td>
              </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
              <tr><td align="center">
                <a href="' . escapeHtml(applicationUrl('factura_pdf.php', ['id' => $salesid])) . '"
                   style="display:inline-block; background:#1a2e4a; color:#ffffff; text-decoration:none;
                          padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold;">
                  📄 Descargar factura PDF
                </a>
              </td></tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:8px;">
              <tr><td style="padding:12px 16px;">
                <p style="font-size:13px; color:#888; margin:0; line-height:1.5;">
                  ℹ️ Si tienes alguna duda sobre tu pedido, responde a este correo.
                </p>
              </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e0e0e0; padding:16px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:12px; color:#bbb;">© 2026 Almacén los Almendros</td>
                <td align="right" style="font-size:12px; color:#bbb;">Correo automático — no responder</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

	// Si PHPMailer no está instalado, no rompemos la compra (ya está guardada)
	if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
		error_log('PHPMailer no está instalado en vendor/: no se envió el correo de la compra #' . $payid);
		header('location: perfil.php');
		exit();
	}

	// Máximo 20 s en total para el correo; si falla, la compra ya está hecha
	set_time_limit(20);

	$mail = null;
	try {
		$mail = configuredMailer();
		$mail->addAddress($user['email']);
		$mail->addReplyTo(MAIL_USER);
		$mail->isHTML(true);
		$mail->CharSet = 'UTF-8';
		$mail->Subject = '¡Compra confirmada! Transacción N° ' . $payid;
		$mail->Body    = $correo_body;
		$mail->send();
	} catch (\Throwable $e) {
		// Si el correo falla no interrumpimos la compra, pero lo dejamos registrado
		error_log('Fallo correo compra #' . $payid . ': ' . $e->getMessage());
	}
}

header('location: perfil.php');
exit();
