<?php
include 'includes/conn.php';
session_start();

date_default_timezone_set('America/Bogota');

if (isset($_SESSION['admin'])) {
	header('location: admin/home.php');
}

if (isset($_SESSION['user'])) {
	$conn = $pdo->open();

	try {
		$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
		$stmt->execute(['id' => $_SESSION['user']]);
		$user = $stmt->fetch();
	} catch (PDOException $e) {
		echo "Hay algún problema en la conexión: " . $e->getMessage();
	}

	$pdo->close();
}

// Generar token CSRF
function generateCSRFToken()
{
	if (!isset($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

// Validar token CSRF
function validateCSRFToken($token)
{
	if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
		return false;
	}
	return true;
}

// Calcular precio con descuento
function precioConDescuento($precio, $descuento)
{
	if ($descuento > 0) {
		return $precio - ($precio * $descuento / 100);
	}
	return $precio;
}

// Registrar log
function registrarLog($conn, $tabla, $id_referencia, $anterior, $nueva, $tipo, $usuario)
{
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR']
		?? $_SERVER['HTTP_CLIENT_IP']
		?? $_SERVER['REMOTE_ADDR']
		?? '0.0.0.0';

	if ($tabla === 'logs_login') {
		$stmt = $conn->prepare("INSERT INTO logs_login 
			(email, tipo_operacion, ip, usuario_created) 
			VALUES (:email, :tipo, :ip, :usuario)");
		$stmt->execute([
			'email'   => $usuario,
			'tipo'    => $tipo,
			'ip'      => $ip,
			'usuario' => $usuario
		]);
	} else {
		$stmt = $conn->prepare("INSERT INTO $tabla 
			(id_referencia, informacion_anterior, nueva_informacion, tipo_operacion, ip, usuario_created) 
			VALUES (:id_ref, :anterior, :nueva, :tipo, :ip, :usuario)");
		$stmt->execute([
			'id_ref'   => $id_referencia,
			'anterior' => $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null,
			'nueva'    => $nueva ? json_encode($nueva, JSON_UNESCAPED_UNICODE) : null,
			'tipo'     => $tipo,
			'ip'       => $ip,
			'usuario'  => $usuario
		]);
	}
}
