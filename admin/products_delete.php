<?php
	include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/product_deletion.php';

	if(isset($_POST['delete'])){
		$id = $_POST['id'] ?? null;
		
		$conn = $pdo->open();

		try{
			deleteUnreferencedProduct($conn, $id);

			$_SESSION['success'] = 'Producto eliminado exitosamente';
		}
		catch(InvalidArgumentException $e){
			$_SESSION['error'] = $e->getMessage();
		}
		catch(PDOException $e){
			error_log('Error al eliminar producto: ' . $e->getMessage());
			$_SESSION['error'] = 'No se pudo eliminar el producto. Inténtalo de nuevo.';
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Seleccione el producto para eliminar primero';
	}

	header('location: products.php');
	exit();
	
?>
