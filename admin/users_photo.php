<?php
	include 'includes/session.php';
requireValidCSRFRequest();
	require_once __DIR__ . '/../includes/image_upload.php';

	if(isset($_POST['upload'])){
		$id = $_POST['id'];
		$filename = photoUploadOrRedirect($_FILES['photo'] ?? null, '', 'users.php', true);
		
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("UPDATE users SET photo=:photo WHERE id=:id");
			$stmt->execute(['photo'=>$filename, 'id'=>$id]);
			$_SESSION['success'] = 'Foto de usuario actualizada correctamente';
		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Seleccione el usuario para actualizar la foto primero';
	}

	header('location: users.php');
?>
