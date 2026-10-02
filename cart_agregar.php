<?php
	include 'includes/session.php';
requireValidCSRFRequest(true);
	require_once __DIR__ . '/includes/cart_operations.php';
	header('Content-Type: application/json; charset=UTF-8');
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		header('Allow: POST');
		echo json_encode(['error' => true, 'message' => 'Usa POST para agregar artículos.']);
		exit();
	}
	$id = cartPositiveInteger($_POST['id'] ?? null);
	$quantity = cartPositiveInteger($_POST['quantity'] ?? null);
	if ($id === null || $quantity === null) {
		echo json_encode(['error' => true, 'message' => 'El producto y la cantidad deben ser enteros positivos.']);
		exit();
	}

	$conn = $pdo->open();

	$output = array('error'=>false);


	// Validar stock disponible
	$stmt = $conn->prepare("SELECT stock FROM products WHERE id=:id");
	$stmt->execute(['id'=>$id]);
	$product = $stmt->fetch();
	
	if(!$product || $product['stock'] <= 0){
		$output['error'] = true;
		$output['message'] = 'Producto sin stock disponible';
		$pdo->close();
		echo json_encode($output);
		exit();
	}

	if($quantity > $product['stock']){
		$output['error'] = true;
		$output['message'] = 'Cantidad solicitada excede el stock disponible. Stock disponible: '.$product['stock'];
		$pdo->close();
		echo json_encode($output);
		exit();
	}

	if(isset($_SESSION['user'])){
		$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id=:user_id AND product_id=:product_id LIMIT 1");
		$stmt->execute(['user_id'=>$user['id'], 'product_id'=>$id]);
		$row = $stmt->fetch();
		if(!$row){
			try{
				$stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
				$stmt->execute(['user_id'=>$user['id'], 'product_id'=>$id, 'quantity'=>$quantity]);
				$output['message'] = 'Artículo agregado al carrito';
				
			}
			catch(PDOException $e){
				$output['error'] = true;
				$output['message'] = $e->getMessage();
			}
		}
		else{
			// Verificar stock total (cantidad en carrito + cantidad nueva)
			$new_total = $row['quantity'] + $quantity;
			if($new_total > $product['stock']){
				$output['error'] = true;
				$output['message'] = 'La cantidad total excede el stock disponible. Stock disponible: '.$product['stock'];
			}
			else{
				$output['error'] = true;
				$output['message'] = 'Producto ya en el carrito';
			}
		}
	}
	else{
		if(!isset($_SESSION['cart'])){
			$_SESSION['cart'] = array();
		}

		$exist = array();
		$total_cart_qty = 0;

		foreach($_SESSION['cart'] as $row){
			array_push($exist, $row['productid']);
			if($row['productid'] == $id){
				$total_cart_qty += $row['quantity'];
			}
		}

		if(in_array($id, $exist)){
			// Verificar stock total (cantidad en carrito + cantidad nueva)
			$new_total = $total_cart_qty + $quantity;
			if($new_total > $product['stock']){
				$output['error'] = true;
				$output['message'] = 'La cantidad total excede el stock disponible. Stock disponible: '.$product['stock'];
			}
			else{
				$output['error'] = true;
				$output['message'] = 'Producto ya en el carrito';
			}
		}
		else{
			$data['productid'] = $id;
			$data['quantity'] = $quantity;

			if(array_push($_SESSION['cart'], $data)){
				$output['message'] = 'Artículo agregado al carrito';
			}
			else{
				$output['error'] = true;
				$output['message'] = 'No se puede agregar un artículo al carrito';
			}
		}

	}

	$pdo->close();
	echo json_encode($output);

?>
