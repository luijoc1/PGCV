<?php
	include 'includes/session.php';

	$conn = $pdo->open();

	$output = array('error'=>false);

	$id = $_POST['id'];
	$quantity = $_POST['quantity'];

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
		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM cart WHERE user_id=:user_id AND product_id=:product_id");
		$stmt->execute(['user_id'=>$user['id'], 'product_id'=>$id]);
		$row = $stmt->fetch();
		if($row['numrows'] < 1){
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