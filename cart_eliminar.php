<?php
	include 'includes/session.php';
requireValidCSRFRequest(true);
	require_once __DIR__ . '/includes/cart_operations.php';
	header('Content-Type: application/json; charset=UTF-8');
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		header('Allow: POST');
		echo json_encode(['error' => true, 'message' => 'Usa POST para eliminar artículos.']);
		exit();
	}
	$id = cartPositiveInteger($_POST['id'] ?? null);
	if ($id === null) {
		echo json_encode(['error' => true, 'message' => 'El artículo es inválido.']);
		exit();
	}

	$conn = $pdo->open();

	$output = array('error'=>false);

	if(isset($_SESSION['user'])){
		try{
			deleteOwnedCart($conn, $_SESSION['user'], $id);
			$output['message'] = 'Eliminado';
			
		}
		catch(InvalidArgumentException $e){
			$output['error'] = true;
			$output['message'] = $e->getMessage();
		}
		catch(PDOException $e){
			$output['error'] = true;
			error_log('Error al eliminar artículo: ' . $e->getMessage());
			$output['message'] = 'No se pudo eliminar el artículo.';
		}
	}
	else{
		$output = ['error' => true, 'message' => 'Artículo no encontrado en tu carrito.'];
		foreach(($_SESSION['cart'] ?? []) as $key => $row){
			if($row['productid'] == $id){
				unset($_SESSION['cart'][$key]);
				$output['error'] = false;
				$output['message'] = 'Eliminado';
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>
