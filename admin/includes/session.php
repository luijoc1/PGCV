<?php
include '../includes/conn.php';
session_start();

if (!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '') {
	header('location: ../index.php');
	exit();
}

$conn = $pdo->open();

$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute(['id' => $_SESSION['admin']]);
$admin = $stmt->fetch();

$pdo->close();
function precioConDescuento($precio, $descuento)
{
	if ($descuento > 0) {
		return $precio - ($precio * $descuento / 100);
	}
	return $precio;
}
