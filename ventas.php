<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	include 'includes/session.php';

	// Verificar que el usuario esté logueado
	if(!isset($_SESSION['user'])){
		$_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
		header('location: login.php');
		exit();
	}

	$conn = $pdo->open();

	// Verificar que el carrito no esté vacío
	$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM cart WHERE user_id=:user_id");
	$stmt->execute(['user_id'=>$user['id']]);
	$cart_check = $stmt->fetch();

	if($cart_check['numrows'] == 0){
		$_SESSION['error'] = 'El carrito está vacío';
		$pdo->close();
		header('location: cart_ver.php');
		exit();
	}

	// Generar número de transacción secuencial
	$stmt = $conn->prepare("SELECT pay_id FROM sales WHERE pay_id REGEXP '^[0-9]+$' ORDER BY CAST(pay_id AS UNSIGNED) DESC LIMIT 1");
	$stmt->execute();
	$last_transaction = $stmt->fetch();

	if($last_transaction && is_numeric($last_transaction['pay_id'])){
		$payid = (int)$last_transaction['pay_id'] + 1;
	} else {
		$payid = 1;
	}

	// Validar datos de facturación
	if(!isset($_POST['nombre_facturacion']) || empty($_POST['nombre_facturacion'])){
		$_SESSION['error'] = 'Completa los datos de facturación';
		header('location: facturacion.php');
		exit();
	}

	$nombre_facturacion = $_POST['nombre_facturacion'];
	$documento          = $_POST['documento'];
	$direccion          = $_POST['direccion'];
	$telefono           = $_POST['telefono'];
	$ciudad             = $_POST['ciudad'];
	$metodo_pago        = $_POST['metodo_pago'];

	// Calcular total del carrito
	$stmt = $conn->prepare("SELECT SUM(products.price * cart.quantity) AS total 
	                        FROM cart LEFT JOIN products ON products.id = cart.product_id 
	                        WHERE cart.user_id = :user_id");
	$stmt->execute(['user_id' => $user['id']]);
	$total_row = $stmt->fetch();
	$total = $total_row['total'] ?? 0;

	// Guardar productos antes de procesarlos (para el correo)
	$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
	$stmt->execute(['user_id'=>$user['id']]);
	$productos_comprados = $stmt->fetchAll();

	$date = date('Y-m-d');

	try{
		// Crear registro de venta
		$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date, nombre_facturacion, documento, direccion, telefono, ciudad, metodo_pago, total) 
		                        VALUES (:user_id, :pay_id, :sales_date, :nombre_facturacion, :documento, :direccion, :telefono, :ciudad, :metodo_pago, :total)");
		$stmt->execute([
			'user_id'            => $user['id'],
			'pay_id'             => $payid,
			'sales_date'         => $date,
			'nombre_facturacion' => $nombre_facturacion,
			'documento'          => $documento,
			'direccion'          => $direccion,
			'telefono'           => $telefono,
			'ciudad'             => $ciudad,
			'metodo_pago'        => $metodo_pago,
			'total'              => $total
		]);
		$salesid = $conn->lastInsertId();

		try{
			foreach($productos_comprados as $row){
				// Verificar stock
				if($row['stock'] < $row['quantity']){
					throw new Exception('El producto "'.$row['name'].'" no tiene suficiente stock. Stock disponible: '.$row['stock']);
				}

				$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
				$stmt->execute(['sales_id'=>$salesid, 'product_id'=>$row['product_id'], 'quantity'=>$row['quantity']]);

				$stmt = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
				$stmt->execute(['quantity'=>$row['quantity'], 'product_id'=>$row['product_id']]);
			}

			// Limpiar carrito
			$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
			$stmt->execute(['user_id'=>$user['id']]);

			// ─── CORREO DE CONFIRMACIÓN ───────────────────────
			$metodos = [
				'tarjeta'       => 'Tarjeta de crédito / débito',
				'transferencia' => 'Transferencia / PSE',
				'efectivo'      => 'Efectivo (pago contraentrega)',
			];
			$metodo_texto = $metodos[$metodo_pago] ?? $metodo_pago;

			$tabla_productos = '';
			foreach($productos_comprados as $p){
				$subtotal = $p['price'] * $p['quantity'];
				$tabla_productos .= "
					<tr>
						<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0;'>".$p['name']."</td>
						<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>".$p['quantity']."</td>
						<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>&#36;".number_format($p['price'],2)."</td>
						<td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>&#36;".number_format($subtotal,2)."</td>
					</tr>
				";
			}

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
            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">Transacción N° '.$payid.'</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px;">
            <p style="color:#444; font-size:15px; line-height:1.6; margin:0 0 20px;">
              Hola <strong style="color:#1a2e4a;">'.$nombre_facturacion.'</strong>, tu pedido ha sido recibido exitosamente.
            </p>
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa; border-radius:8px; border:1px solid #e0e0e0; margin-bottom:20px;">
              <tr><td style="padding:14px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#999;">Dirección de entrega</td>
                    <td style="font-size:12px; color:#999; text-align:right;">Método de pago</td>
                  </tr>
                  <tr>
                    <td style="font-size:14px; color:#1a2e4a; font-weight:bold;">'.$direccion.', '.$ciudad.'</td>
                    <td style="font-size:14px; color:#1a2e4a; font-weight:bold; text-align:right;">'.$metodo_texto.'</td>
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
              '.$tabla_productos.'
              <tr style="background:#f5f7fa;">
                <td colspan="3" style="padding:10px 12px; font-weight:bold; font-size:14px;">Total</td>
                <td style="padding:10px 12px; font-weight:bold; font-size:14px; text-align:right;">&#36;'.number_format($total,2).'</td>
              </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
              <tr><td align="center">
                <a href="http://localhost/PGCV/factura_pdf.php?id='.$salesid.'"
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

			require 'vendor/autoload.php';
			$mail = new PHPMailer(true);
			try {
				$mail->isSMTP();
				$mail->Host       = 'smtp.gmail.com';
				$mail->SMTPAuth   = true;
				$mail->Username   = 'arodrigueza.ingeniero@gmail.com';
				$mail->Password   = 'zqeemysndhnigtvu';
				$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true
					)
				);
				$mail->SMTPSecure = 'ssl';
				$mail->Port       = 465;
				$mail->setFrom('arodrigueza.ingeniero@gmail.com');
				$mail->addAddress($user['email']);
				$mail->addReplyTo('arodrigueza.ingeniero@gmail.com');
				$mail->isHTML(true);
				$mail->CharSet = 'UTF-8';
				$mail->Subject = '¡Compra confirmada! Transacción N° '.$payid;
				$mail->Body    = $correo_body;
				$mail->send();
			} catch (Exception $e) {
				// Si el correo falla no interrumpimos la compra
			}

			$_SESSION['success'] = 'Compra realizada exitosamente. Número de transacción: '.$payid;

		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}
		catch(Exception $e){
			$_SESSION['error'] = $e->getMessage();
		}

	}
	catch(PDOException $e){
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();

	header('location: perfil.php');

?>