<?php
	include 'includes/session.php';

	$conn = $pdo->open();

	$output = array('error'=>false);

	$id = $_POST['id'];
	$qty = $_POST['qty'];

	// Validar stock disponible
	if(isset($_SESSION['user'])){
		$stmt = $conn->prepare("SELECT products.stock FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE cart.id=:id");
		$stmt->execute(['id'=>$id]);
		$cart_item = $stmt->fetch();
		
		if(!$cart_item || $cart_item['stock'] < $qty){
			$output['error'] = true;
			$output['message'] = 'Cantidad solicitada excede el stock disponible. Stock disponible: '.($cart_item ? $cart_item['stock'] : 0);
			$pdo->close();
			echo json_encode($output);
			exit();
		}
		
		try{
			$stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id");
			$stmt->execute(['quantity'=>$qty, 'id'=>$id]);
			$output['message'] = 'Actualizado';
		}
		catch(PDOException $e){
			$output['error'] = true;
			$output['message'] = $e->getMessage();
		}
	}
	else{
		// Para usuarios no registrados, validar stock
		foreach($_SESSION['cart'] as $key => $row){
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
				$output['message'] = 'Actualizado';
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>