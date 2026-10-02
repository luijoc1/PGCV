<?php
	include 'includes/session.php';
requireValidCSRFRequest();

	if(isset($_POST['add'])){
		$id = $_POST['id'];
		$product = $_POST['product'];
		$quantity = $_POST['quantity'];

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM cart WHERE product_id=:id AND user_id=:user");
		$stmt->execute(['id'=>$product, 'user'=>$id]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'El producto existe en el carrito';
		}
		else{
			try{
				$stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user, :product, :quantity)");
				$stmt->execute(['user'=>$id, 'product'=>$product, 'quantity'=>$quantity]);

				$_SESSION['success'] = 'Producto agregado al carrito';
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
		}

		$pdo->close();

		header('location: cart.php?user='.$id);
	}

?>
