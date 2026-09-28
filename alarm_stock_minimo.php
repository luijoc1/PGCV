<?php
include 'includes/conn.php';
include 'includes/config.php';
session_start();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


date_default_timezone_set('America/Bogota');

if (isset($_SESSION['admin'])) {
	header('location: admin/home.php');
}


//verficar la cantidad x productos. 
// ciclo para recorrer los productos 
//verficos si el campo stock_minimo diferente de 0 si es difernte 
//evaluo la cantidad de scotck versus stock_minimo. y envio el correo 


// ============================================
// 2. CONFIGURACIÓN DE CORREO
// ============================================
$correo_destino = "alexdavidr419@gmail.com";  // Cambia esto
$asunto = "Alerta: Stock Crítico de Productos";
 
// ============================================
// 3. TRAER TODOS LOS PRODUCTOS
// ============================================
try {
    $conn = $pdo->open();

    $consulta = "SELECT id, name, stock, stock_minimo FROM products 
    WHERE stock_minimo != 0 LIMIT 100";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si hay resultados
    if (empty($resultado)) {
        echo "No hay productos con stock crítico configurado.\n";
        exit;
    }
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
 
// ============================================
// 4. ARRAYS PARA ALMACENAR PRODUCTOS EN ALERTA
// ============================================
$productos_alerta = array();
$productos_normal = array();
 
// ============================================
// 5. CICLO: RECORRER LOS PRODUCTOS
// ============================================
foreach ($resultado as $producto) {
    $id = $producto['id'];
    $name = $producto['name'];
    $stock = $producto['stock'];
    $stock_minimo = $producto['stock_minimo'];
    
    // ============================================
    // 6. VERIFICAR SI STOCK ES MENOR O IGUAL AL CRÍTICO
    // ============================================
    if ($stock <= $stock_minimo) {
        // Producto en alerta
        $productos_alerta[] = array(
            'id' => $id,
            'name' => $name,
            'stock' => $stock,
            'stock_minimo' => $stock_minimo
        );
        
        echo "⚠️  ALERTA: {$name} - Stock: {$stock}, Crítico: {$stock_minimo}\n";
    } else {
        // Producto normal
        $productos_normal[] = array(
            'id' => $id,
            'name' => $name,
            'stock' => $stock,
            'stock_minimo' => $stock_minimo
        );
        
        echo "✓ OK: {$name} - Stock: {$stock}\n";
    }
}
 
// ============================================
// 7. ENVIAR CORREO SI HAY PRODUCTOS EN ALERTA
// ============================================
if (count($productos_alerta) > 0) {
    // Construir contenido del correo
    $cuerpo = "Hola,\n\n";
    $cuerpo .= "Se han detectado " . count($productos_alerta) . " productos con stock crítico:\n\n";
    
    foreach ($productos_alerta as $prod) {
        $cuerpo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $cuerpo .= "Producto: {$prod['name']}\n";
        $cuerpo .= "ID: {$prod['id']}\n";
        $cuerpo .= "Stock Actual: {$prod['stock']}\n";
        $cuerpo .= "Stock Crítico: {$prod['stock_minimo']}\n";
        $cuerpo .= "Estado: ⚠️  BAJO STOCK\n";
    }
    
    $cuerpo .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $cuerpo .= "Por favor, reponer inventario.\n";
    $cuerpo .= "Fecha: " . date("d/m/Y H:i:s") . "\n";
    
    // Encabezados del correo
   // $headers = "From: sistema@tuempresa.com\r\n";
   // $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
   
   
    // Enviar correo
    // if (mail($correo_destino, $asunto, $cuerpo, $headers)) {
    //     echo "\n✓ Correo enviado exitosamente a: {$correo_destino}\n";
    // } else {
    //     echo "\n✗ Error al enviar correo\n";
    // }

    //Load phpmailer
				require 'vendor/autoload.php';

				$mail = new PHPMailer(true);
				try {
					//Server settings
					$mail->isSMTP();
					$mail->Host = 'smtp.gmail.com';
					$mail->SMTPAuth = true;
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

					$mail->setFrom(MAIL_USER);

					//Recipients
					$mail->addAddress($correo_destino);
					$mail->addReplyTo(MAIL_USER);

					//Content
					$mail->isHTML(true);
					$mail->CharSet = 'UTF-8';
					$mail->Subject = 'Alarma Stock Minimos - Los Almendros';
					$mail->Body    = $cuerpo;

					$mail->send();
			 
				} catch (Exception $e) {
                    echo "Error en envio";
					 //header('location: registrarse.php');
				}
			   
} else {
    echo "\n✓ Todos los productos tienen stock normal.\n";
}
 
// ============================================
// 8. MOSTRAR RESUMEN
// ============================================
echo "\n╔════════════════════════════════════╗\n";
echo "║ RESUMEN DE VALIDACIÓN DE STOCK     ║\n";
echo "╠════════════════════════════════════╣\n";
echo "║ Productos OK: " . count($productos_normal) . "\n";
echo "║ Productos en ALERTA: " . count($productos_alerta) . "\n";
echo "╚════════════════════════════════════╝\n";
 
// ============================================
// 9. CERRAR CONEXIÓN
// ============================================
$pdo = null;  // PDO se cierra así
