<?php
include 'includes/conn.php';
session_start();

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

function precioConDescuento($precio, $descuento)
{
	if ($descuento > 0) {
		return $precio - ($precio * $descuento / 100);
	}
	return $precio;
}
