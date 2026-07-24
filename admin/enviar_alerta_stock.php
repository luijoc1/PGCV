<?php
include 'includes/session.php';
include '../includes/config.php';

// Solo enviar una vez por día
if (isset($_SESSION['alerta_stock_enviada']) && $_SESSION['alerta_stock_enviada'] == date('Y-m-d')) {
    exit();
}

$conn = $pdo->open();

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE stock > 0 AND stock <= stock_minimo");
$stmt->execute();
$stock_critico = $stmt->fetch();

$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE stock = 0");
$stmt2->execute();
$sin_stock = $stmt2->fetch();

if ($stock_critico['total'] == 0 && $sin_stock['total'] == 0) {
    $pdo->close();
    exit();
}

$_SESSION['alerta_stock_enviada'] = date('Y-m-d');

$stmt3 = $conn->prepare("SELECT name, stock, stock_minimo FROM products WHERE stock <= stock_minimo ORDER BY stock ASC LIMIT 10");
$stmt3->execute();
$productos_criticos = $stmt3->fetchAll();

$lista_productos = '';
foreach ($productos_criticos as $pc) {
    $color = ($pc['stock'] == 0) ? '#e74c3c' : '#e67e22';
    $estado = ($pc['stock'] == 0) ? 'Sin stock' : 'Stock crítico';
    $lista_productos .= "
        <tr>
            <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0;'>" . $pc['name'] . "</td>
            <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>" . $pc['stock'] . "</td>
            <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>" . $pc['stock_minimo'] . "</td>
            <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>
                <span style='background:" . $color . "; color:#fff; padding:2px 8px; border-radius:20px; font-size:11px;'>" . $estado . "</span>
            </td>
        </tr>
    ";
}

$correo_alerta = '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e0e0e0;">
        <tr>
          <td style="background:#1a2e4a; padding:28px 36px; text-align:center;">
            <span style="font-size:18px; font-weight:bold; color:#ffffff;">Almacén los Almendros</span><br><br>
            <h1 style="color:#ffffff; font-size:20px; margin:8px 0 4px;">Alerta de Inventario</h1>
            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">' . date('d/m/Y') . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px;">
            <p style="color:#444; font-size:15px; margin:0 0 20px;">
              Se han detectado <strong>' . $stock_critico['total'] . ' producto(s) con stock crítico</strong> y 
              <strong>' . $sin_stock['total'] . ' producto(s) sin stock</strong>.
            </p>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:8px; overflow:hidden; margin-bottom:20px;">
              <tr style="background:#1a2e4a;">
                <th style="padding:10px 12px; color:#fff; text-align:left; font-size:13px;">Producto</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Stock</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Mínimo</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Estado</th>
              </tr>
              ' . $lista_productos . '
            </table>
            <div style="text-align:center;">
              <a href="http://localhost/PGCV/admin/inventario.php"
                 style="display:inline-block; background:#1a2e4a; color:#ffffff; text-decoration:none;
                        padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold;">
                Ver reporte de inventario
              </a>
            </div>
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
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PASS;
    $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom(MAIL_USER);
    $mail->addAddress(MAIL_USER);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Alerta de inventario - ' . date('d/m/Y');
    $mail->Body = $correo_alerta;
    $mail->send();
} catch (Exception $e) {
    // Si falla no interrumpimos
}

$pdo->close();
