<?php
include 'includes/session.php';
requireValidCSRFRequest();

if (isset($_POST['delete'])) {
	$id = $_POST['id'];

	$conn = $pdo->open();

	// Obtener datos del usuario antes de eliminar
	$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
	$stmt->execute(['id' => $id]);
	$row = $stmt->fetch();

	try {
		$stmt = $conn->prepare("DELETE FROM users WHERE id=:id");
		$stmt->execute(['id' => $id]);

		// Log usuario eliminado
		registrarLog($conn, 'logs_usuarios', $id, [
			'firstname' => $row['firstname'],
			'lastname'  => $row['lastname'],
			'email'     => $row['email'],
			'type'      => $row['type'] == 1 ? 'admin' : 'cliente'
		], null, 'DELETE', $admin['email'] ?? 'admin');

		$_SESSION['success'] = 'Usuario eliminado exitosamente';
	} catch (PDOException $e) {
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Seleccionar usuario para eliminar primero';
}

header('location: users.php');
