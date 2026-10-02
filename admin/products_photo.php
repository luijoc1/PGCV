<?php
	include 'includes/session.php';
requireValidCSRFRequest();
	require_once __DIR__ . '/../includes/image_upload.php';

	if(isset($_POST['upload'])){
		$id = $_POST['id'];

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
		$stmt->execute(['id'=>$id]);
		$row = $stmt->fetch();

		if (!$row) {
			$_SESSION['error'] = 'Producto no encontrado';
			header('location: products.php');
			exit();
		}
		$new_filename = photoUploadOrRedirect($_FILES['photo'] ?? null, '', 'products.php', true);
		
		try{
			$stmt = $conn->prepare("UPDATE products SET photo=:photo WHERE id=:id");
			$stmt->execute(['photo'=>$new_filename, 'id'=>$id]);
			$_SESSION['success'] = 'Foto del producto actualizada con éxito';
		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Seleccione el producto para actualizar la foto primero';
	}

	header('location: products.php');
?>
