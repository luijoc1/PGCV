<?php
	include 'includes/session.php';

	// Verificar que el usuario esté logueado
	if(!isset($_SESSION['user'])){
		$_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
		header('location: login.php');
		exit();
	}

	$conn = $pdo->open();

	// Verificar que el carrito no esté vacío
	$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM cart WHERE user_id=:user_id");
	$stmt->execute(['user_id'=>$user['id']]);
	$cart_check = $stmt->fetch();

	if($cart_check['numrows'] == 0){
		$_SESSION['error'] = 'El carrito está vacío';
		$pdo->close();
		header('location: cart_ver.php');
		exit();
	}

	// Generar número de transacción secuencial (empezando desde 1)
	// Obtener el máximo número de transacción numérico
	$stmt = $conn->prepare("SELECT pay_id FROM sales WHERE pay_id REGEXP '^[0-9]+$' ORDER BY CAST(pay_id AS UNSIGNED) DESC LIMIT 1");
	$stmt->execute();
	$last_transaction = $stmt->fetch();
	
	if($last_transaction && is_numeric($last_transaction['pay_id'])){
		// Si hay transacciones previas, incrementar el número
		$payid = (int)$last_transaction['pay_id'] + 1;
	} else {
		// Si no hay transacciones o ninguna es numérica, empezar desde 1
		$payid = 1;
	}
	
	$date = date('Y-m-d');

	try{
		// Crear registro de venta
		$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
		$stmt->execute(['user_id'=>$user['id'], 'pay_id'=>$payid, 'sales_date'=>$date]);
		$salesid = $conn->lastInsertId();
		
		try{
			$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
			$stmt->execute(['user_id'=>$user['id']]);

			foreach($stmt as $row){
				// Verificar stock antes de procesar
				if($row['stock'] < $row['quantity']){
					throw new Exception('El producto "'.$row['name'].'" no tiene suficiente stock. Stock disponible: '.$row['stock']);
				}

				$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
				$stmt->execute(['sales_id'=>$salesid, 'product_id'=>$row['product_id'], 'quantity'=>$row['quantity']]);
				
				// Actualizar stock del producto
				$stmt = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
				$stmt->execute(['quantity'=>$row['quantity'], 'product_id'=>$row['product_id']]);
			}

			// Limpiar carrito
			$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
			$stmt->execute(['user_id'=>$user['id']]);

			$_SESSION['success'] = 'Compra realizada exitosamente. Número de transacción: '.$payid;

		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}
		catch(Exception $e){
			$_SESSION['error'] = $e->getMessage();
		}

	}
	catch(PDOException $e){
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();
	
	header('location: perfil.php');
	
?>