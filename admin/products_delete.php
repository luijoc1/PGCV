<?php
	include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/product_deletion.php';
unset($_SESSION['error'], $_SESSION['success']);

	if(isset($_POST['delete'])){
		$id = $_POST['id'] ?? null;
		
		$conn = $pdo->open();

		try{
			$conn->beginTransaction();
			$stmt = $conn->prepare('SELECT * FROM products WHERE id=:id' . ($conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : ''));
			$validatedId = cartPositiveInteger($id);
			if ($validatedId === null) throw new InvalidArgumentException('El producto seleccionado es inválido.');
			$stmt->execute(['id' => $validatedId]);
			$previous = $stmt->fetch(PDO::FETCH_ASSOC);
			deleteUnreferencedProduct($conn, $id);
			registrarLog($conn, 'logs_productos', $validatedId, $previous, null, 'DELETE', $admin['email']);
			$conn->commit();

			$_SESSION['success'] = 'Producto eliminado exitosamente';
		}
		catch(InvalidArgumentException $e){
			if ($conn->inTransaction()) $conn->rollBack();
			$_SESSION['error'] = $e->getMessage();
		}
		catch(PDOException $e){
			if ($conn->inTransaction()) $conn->rollBack();
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
