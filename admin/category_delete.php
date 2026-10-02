<?php
	include 'includes/session.php';
requireValidCSRFRequest();

	if(isset($_POST['delete'])){
		$id = $_POST['id'];
		
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("DELETE FROM category WHERE id=:id");
			$stmt->execute(['id'=>$id]);

			$_SESSION['success'] = 'Categoría eliminada correctamente';
		}
		catch(PDOException $e){
			error_log('Error al eliminar categoría: ' . $e->getMessage());
			$_SESSION['error'] = 'No se pudo eliminar la categoría. Revisa si todavía contiene productos.';
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Seleccione la categoría para eliminar primero';
	}

	header('location: category.php');
	
?>
