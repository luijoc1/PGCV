<?php
	include 'includes/session.php';
	$conn = $pdo->open();

	$output = '';

	if(isset($_SESSION['user'])){
		if(isset($_SESSION['cart'])){
			foreach($_SESSION['cart'] as $row){
				$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM cart WHERE user_id=:user_id AND product_id=:product_id");
				$stmt->execute(['user_id'=>$user['id'], 'product_id'=>$row['productid']]);
				$crow = $stmt->fetch();
				if($crow['numrows'] < 1){
					$stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
					$stmt->execute(['user_id'=>$user['id'], 'product_id'=>$row['productid'], 'quantity'=>$row['quantity']]);
				}
				else{
					$stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE user_id=:user_id AND product_id=:product_id");
					$stmt->execute(['quantity'=>$row['quantity'], 'user_id'=>$user['id'], 'product_id'=>$row['productid']]);
				}
			}
			unset($_SESSION['cart']);
		}

		try{
			$total = 0;
			$stmt = $conn->prepare("SELECT *, cart.id AS cartid FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user");
			$stmt->execute(['user'=>$user['id']]);
			foreach($stmt as $row){
				$image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/noimage.jpg';
				$stock_available = $row['stock'];
				
				// Si el stock es menor que la cantidad en el carrito, ajustar la cantidad
				if($stock_available < $row['quantity']){
					$new_quantity = ($stock_available > 0) ? $stock_available : 0;
					if($new_quantity == 0){
						// Eliminar del carrito si no hay stock
						$stmt_del = $conn->prepare("DELETE FROM cart WHERE id=:id");
						$stmt_del->execute(['id'=>$row['cartid']]);
						continue;
					}
					else{
						// Actualizar cantidad al stock disponible
						$stmt_upd = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id");
						$stmt_upd->execute(['quantity'=>$new_quantity, 'id'=>$row['cartid']]);
						$row['quantity'] = $new_quantity;
					}
				}
				
				$subtotal = $row['price']*$row['quantity'];
				$total += $subtotal;
				$stock_warning = ($stock_available <= 5 && $stock_available > 0) ? '<br><small class="text-warning">Stock bajo: '.$stock_available.'</small>' : '';
				$output .= "
					<tr>
						<td><button type='button' data-id='".$row['cartid']."' class='btn btn-danger btn-flat cart_delete'><i class='fa fa-remove'></i></button></td>
						<td><img src='".$image."' width='30px' height='30px'></td>
						<td>".$row['name'].$stock_warning."</td>
						<td>&#36; ".number_format($row['price'], 2)."</td>
						<td class='input-group'>
							<span class='input-group-btn'>
            					<button type='button' id='minus' class='btn btn-default btn-flat minus' data-id='".$row['cartid']."'><i class='fa fa-minus'></i></button>
            				</span>
            				<input type='text' class='form-control' value='".$row['quantity']."' id='qty_".$row['cartid']."' max='".$stock_available."' data-stock='".$stock_available."'>
				            <span class='input-group-btn'>
				                <button type='button' id='add' class='btn btn-default btn-flat add' data-id='".$row['cartid']."'><i class='fa fa-plus'></i>
				                </button>
				            </span>
						</td>
						<td>&#36; ".number_format($subtotal, 2)."</td>
					</tr>
				";
			}
			$output .= "
				<tr>
					<td colspan='5' align='right'><b>Total</b></td>
					<td><b>&#36; ".number_format($total, 2)."</b></td>
				<tr>
			";

		}
		catch(PDOException $e){
			$output .= $e->getMessage();
		}

	}
	else{
		if(count($_SESSION['cart']) != 0){
			$total = 0;
			foreach($_SESSION['cart'] as $key => $row){
				$stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname FROM products LEFT JOIN category ON category.id=products.category_id WHERE products.id=:id AND products.stock > 0");
				$stmt->execute(['id'=>$row['productid']]);
				$product = $stmt->fetch();
				
				if(!$product){
					// Eliminar del carrito si el producto no existe o no tiene stock
					unset($_SESSION['cart'][$key]);
					continue;
				}
				
				// Ajustar cantidad si excede el stock disponible
				if($row['quantity'] > $product['stock']){
					$_SESSION['cart'][$key]['quantity'] = $product['stock'];
					$row['quantity'] = $product['stock'];
				}
				
				$image = (!empty($product['photo'])) ? 'images/'.$product['photo'] : 'images/noimage.jpg';
				$subtotal = $product['price']*$row['quantity'];
				$total += $subtotal;
				$stock_warning = ($product['stock'] <= 5 && $product['stock'] > 0) ? '<br><small class="text-warning">Stock bajo: '.$product['stock'].'</small>' : '';
				$output .= "
					<tr>
						<td><button type='button' data-id='".$row['productid']."' class='btn btn-danger btn-flat cart_delete'><i class='fa fa-remove'></i></button></td>
						<td><img src='".$image."' width='30px' height='30px'></td>
						<td>".$product['name'].$stock_warning."</td>
						<td>&#36; ".number_format($product['price'], 2)."</td>
						<td class='input-group'>
							<span class='input-group-btn'>
            					<button type='button' id='minus' class='btn btn-default btn-flat minus' data-id='".$row['productid']."'><i class='fa fa-minus'></i></button>
            				</span>
            				<input type='text' class='form-control' value='".$row['quantity']."' id='qty_".$row['productid']."' max='".$product['stock']."' data-stock='".$product['stock']."'>
				            <span class='input-group-btn'>
				                <button type='button' id='add' class='btn btn-default btn-flat add' data-id='".$row['productid']."'><i class='fa fa-plus'></i>
				                </button>
				            </span>
						</td>
						<td>&#36; ".number_format($subtotal, 2)."</td>
					</tr>
				";
				
			}

			$output .= "
				<tr>
					<td colspan='5' align='right'><b>Total</b></td>
					<td><b>&#36; ".number_format($total, 2)."</b></td>
				<tr>
			";
		}

		else{
			$output .= "
				<tr>
					<td colspan='6' align='center'>Carrito de compras vacío</td>
				<tr>
			";
		}
		
	}

	$pdo->close();
	echo json_encode($output);

?>

