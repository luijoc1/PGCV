<?php
	include 'includes/session.php';
requireValidCSRFRequest(true);
	require_once __DIR__ . '/includes/cart_operations.php';
	header('Content-Type: application/json; charset=UTF-8');
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		header('Allow: POST');
		echo json_encode(['error' => true, 'message' => 'Usa POST para actualizar el carrito.']);
		exit();
	}
	$id = cartPositiveInteger($_POST['id'] ?? null);
	$qty = cartPositiveInteger($_POST['qty'] ?? null);
	if ($id === null || $qty === null) {
		echo json_encode(['error' => true, 'message' => 'El artículo y la cantidad deben ser enteros positivos.']);
		exit();
	}

	$conn = $pdo->open();

	$output = array('error'=>false);


	// Validar stock disponible
	if(isset($_SESSION['user'])){
		try{
			updateOwnedCart($conn, $_SESSION['user'], $id, $qty);
			$output['message'] = 'Actualizado';
		}
		catch(InvalidArgumentException $e){
			$output['error'] = true;
			$output['message'] = $e->getMessage();
		}
		catch(PDOException $e){
			$output['error'] = true;
			error_log('Error al actualizar carrito: ' . $e->getMessage());
			$output['message'] = 'No se pudo actualizar el carrito.';
		}
	}
	else{
		// Para usuarios no registrados, validar stock
		$output = ['error' => true, 'message' => 'Artículo no encontrado en tu carrito.'];
		foreach(($_SESSION['cart'] ?? []) as $key => $row){
			if($row['productid'] == $id){
				$stmt = $conn->prepare("SELECT stock FROM products WHERE id=:id");
				$stmt->execute(['id'=>$id]);
				$product = $stmt->fetch();
				
				if(!$product || $product['stock'] < $qty){
					$output['error'] = true;
					$output['message'] = 'Cantidad solicitada excede el stock disponible. Stock disponible: '.($product ? $product['stock'] : 0);
					$pdo->close();
					echo json_encode($output);
					exit();
				}
				
				$_SESSION['cart'][$key]['quantity'] = $qty;
				$output['error'] = false;
				$output['message'] = 'Actualizado';
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>
