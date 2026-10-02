<?php
	include 'includes/session.php';
requireValidCSRFRequest();
	require_once __DIR__ . '/../includes/image_upload.php';

	if(is_string($_GET['return'] ?? null) && preg_match('/\A[a-zA-Z0-9_-]+\.php\z/', $_GET['return'])){
		$return = $_GET['return'];
		
	}
	else{
		$return = 'home.php';
	}

	if(isset($_POST['save'])){
		$curr_password = $_POST['curr_password'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		if(password_verify($curr_password, $admin['password'])){
			try {
				$password = editedPasswordHash($password, $admin['password']);
			} catch (InvalidArgumentException $e) {
				$_SESSION['error'] = $e->getMessage();
				header('location: home.php');
				exit();
			}
			$filename = photoUploadOrRedirect($_FILES['photo'] ?? null, $admin['photo'], 'home.php');

			$conn = $pdo->open();

			try{
				$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, photo=:photo WHERE id=:id");
				$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'photo'=>$filename, 'id'=>$admin['id']]);

				$_SESSION['success'] = 'Cuenta actualizada con éxito';
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}

			$pdo->close();
			
		}
		else{
			$_SESSION['error'] = 'Contraseña incorrecta';
		}
	}
	else{
		$_SESSION['error'] = 'Rellene los detalles requeridos primero';
	}

	header('location:'.$return);
	exit();

?>
