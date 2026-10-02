<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/includes/image_upload.php';

if (!isset($_SESSION['user'], $user['id'])) {
    header('location: login.php');
    exit();
}

$conn = $pdo->open();

if (isset($_POST['edit'])) {
	$curr_password = $_POST['curr_password'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$contact = $_POST['contact'];
	$address = $_POST['address'];
	if (password_verify($curr_password, $user['password'])) {
		try {
			$password = editedPasswordHash($password, $user['password']);
		} catch (InvalidArgumentException $e) {
			$_SESSION['error'] = $e->getMessage();
			header('location: perfil.php');
			exit();
		}
		$filename = photoUploadOrRedirect($_FILES['photo'] ?? null, $user['photo'], 'perfil.php');

		try {
			$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, contact_info=:contact, address=:address, photo=:photo WHERE id=:id");
			$stmt->execute(['email' => $email, 'password' => $password, 'firstname' => $firstname, 'lastname' => $lastname, 'contact' => $contact, 'address' => $address, 'photo' => $filename, 'id' => $user['id']]);

			$_SESSION['success'] = 'Cuenta actualizada con éxito';
		} catch (PDOException $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	} else {
		$_SESSION['error'] = 'Contraseña incorrecta';
	}
} else {
	$_SESSION['error'] = 'Rellene el formulario de edición primero';
}

$pdo->close();

header('location: perfil.php');
exit();
