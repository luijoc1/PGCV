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
function precioConDescuento($precio, $descuento)
{
	if ($descuento > 0) {
		return $precio - ($precio * $descuento / 100);
	}
	return $precio;
}
