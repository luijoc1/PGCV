<?php
include 'includes/session.php';
requireValidCSRFRequest();

if (isset($_POST['edit'])) {
	$id = $_POST['id'];
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$address = $_POST['address'];
	$contact = $_POST['contact'];

	$conn = $pdo->open();

	// Obtener datos anteriores para el log
	$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
	$stmt->execute(['id' => $id]);
	$row = $stmt->fetch();
	if (!$row) {
		$_SESSION['error'] = 'Usuario no encontrado.';
		header('location: users.php');
		exit();
	}
	try {
		$password = editedPasswordHash($password, $row['password']);
	} catch (InvalidArgumentException $e) {
		$_SESSION['error'] = $e->getMessage();
		header('location: users.php');
		exit();
	}

	try {
		$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, address=:address, contact_info=:contact WHERE id=:id");
		$stmt->execute(['email' => $email, 'password' => $password, 'firstname' => $firstname, 'lastname' => $lastname, 'address' => $address, 'contact' => $contact, 'id' => $id]);

		// Log usuario editado
		registrarLog($conn, 'logs_usuarios', $id, [
			'firstname'    => $row['firstname'],
			'lastname'     => $row['lastname'],
			'email'        => $row['email'],
			'address'      => $row['address'],
			'contact_info' => $row['contact_info']
		], [
			'firstname'    => $firstname,
			'lastname'     => $lastname,
			'email'        => $email,
			'address'      => $address,
			'contact_info' => $contact
		], 'UPDATE', $admin['email'] ?? 'admin');

		$_SESSION['success'] = 'Usuario actualizado con éxito';
	} catch (PDOException $e) {
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Rellene el formulario de edición de usuario primero';
}

header('location: users.php');
exit();
