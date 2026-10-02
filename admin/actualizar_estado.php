<?php
include 'includes/session.php';
require_once __DIR__ . '/../includes/mailer.php';
requireValidCSRFRequest(true);
require_once __DIR__ . '/../includes/sale_status.php';
header('Content-Type: application/json; charset=utf-8');
include '../includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('America/Bogota');

$output = ['success' => false];

if (isset($_POST['id']) && isset($_POST['estado'])) {
  $id = filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
  $estado = $_POST['estado'];

  $estados_validos = ['pendiente', 'en_proceso', 'enviado', 'entregado'];
  if ($id === false || !in_array($estado, $estados_validos, true)) {
    http_response_code(400);
    echo json_encode($output);
    exit();
  }

  $conn = $pdo->open();

  try {
    // Obtener estado anterior para el log
    $stmt = $conn->prepare("SELECT sales.*, users.email, users.firstname, users.lastname 
                        FROM sales LEFT JOIN users ON users.id=sales.user_id 
                        WHERE sales.id=:id");
    $stmt->execute(['id' => $id]);
    $sale = $stmt->fetch();
    if (!$sale) {
      http_response_code(404);
      $pdo->close();
      echo json_encode($output);
      exit();
    }
    $estado_anterior = $sale['estado'];

    // Conservar la fecha original de compra al actualizar el estado.
    updateSaleStatus($conn, $id, $estado);

    // Log cambio de estado
    registrarLog($conn, 'logs_ventas', $id, [
      'pay_id'  => $sale['pay_id'],
      'estado'  => $estado_anterior,
      'cliente' => $sale['firstname'] . ' ' . $sale['lastname'],
      'email'   => $sale['email']
    ], [
      'pay_id'  => $sale['pay_id'],
      'estado'  => $estado,
      'cliente' => $sale['firstname'] . ' ' . $sale['lastname'],
      'email'   => $sale['email']
    ], 'UPDATE', $admin['email'] ?? 'admin');

    $estados_texto = [
      'pendiente'  => ' Pendiente',
      'en_proceso' => ' En proceso',
      'enviado'    => ' Enviado',
      'entregado'  => '✅ Entregado',
    ];
    $estado_texto = $estados_texto[$estado] ?? $estado;

    $colores = [
      'pendiente'  => '#f39c12',
      'en_proceso' => '#3a8eff',
      'enviado'    => '#8e44ad',
      'entregado'  => '#27ae60',
    ];
    $color = $colores[$estado] ?? '#1a2e4a';

    // Enviar correo al cliente
    $correo_body = '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e0e0e0;">
        <tr>
          <td style="background:#1a2e4a; padding:28px 36px; text-align:center;">
            <span style="font-size:18px; font-weight:bold; color:#ffffff;">Almacén los Almendros</span><br><br>
            <h1 style="color:#ffffff; font-size:20px; margin:8px 0 4px;">Actualización de tu pedido</h1>
            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">Transacción N° ' . escapeHtml($sale['pay_id']) . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px; text-align:center;">
            <p style="color:#444; font-size:15px; margin:0 0 20px;">
              Hola <strong>' . escapeHtml($sale['firstname']) . ' ' . escapeHtml($sale['lastname']) . '</strong>, el estado de tu pedido ha sido actualizado.
            </p>
            <div style="display:inline-block; background:' . $color . '; color:#fff; padding:12px 32px; border-radius:8px; font-size:18px; font-weight:bold; margin-bottom:20px;">
              ' . $estado_texto . '
            </div>
            <p style="color:#888; font-size:13px; margin:20px 0 0;">
              Si tienes alguna duda, contáctanos respondiendo este correo.
            </p>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e0e0e0; padding:16px 36px; text-align:center;">
            <p style="font-size:12px; color:#bbb; margin:0;">© 2026 Almacén los Almendros — Correo automático</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

    require_once '../vendor/autoload.php';
    $mail = null;
    try {
      $mail = configuredMailer();
      $mail->addAddress($sale['email']);
      $mail->isHTML(true);
      $mail->CharSet = 'UTF-8';
      $mail->Subject = 'Actualización de tu pedido N° ' . $sale['pay_id'];
      $mail->Body = $correo_body;
      $mail->send();
    } catch (Exception $e) {
      error_log('Fallo de correo de estado: ' . $e->getMessage());
      // Si falla el correo no interrumpimos
    }

    $output['success'] = true;
  } catch (PDOException $e) {
    $output['success'] = false;
    $output['error'] = $e->getMessage();
  }

  $pdo->close();
}

echo json_encode($output);
