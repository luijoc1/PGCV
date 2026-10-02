<?php
include 'includes/conn.php';
session_start();
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/output.php';
require_once __DIR__ . '/authentication.php';
require_once __DIR__ . '/pricing.php';

date_default_timezone_set('America/Bogota');

if (isset($_SESSION['admin'])) {
	$conn = $pdo->open();
	$account = authenticatedAccount($conn, $_SESSION, 1);
	$pdo->close();
	if ($account) {
		header('location: admin/home.php');
		exit();
	}
	clearAccountSession();
}

if (isset($_SESSION['user'])) {
	$conn = $pdo->open();

	try {
		$user = authenticatedAccount($conn, $_SESSION, 0);
		if (!$user) {
			clearAccountSession();
			unset($user);
		}
	} catch (PDOException $e) {
		clearAccountSession();
		error_log('Error al comprobar sesión: ' . $e->getMessage());
		http_response_code(503);
		exit('No se pudo comprobar la sesión. Intenta de nuevo.');
	}

	$pdo->close();
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
