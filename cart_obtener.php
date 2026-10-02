<?php
	include 'includes/session.php';
	$conn = $pdo->open();

	$output = array('list'=>'','count'=>0);

	if(isset($_SESSION['user'])){
		try{
			$stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname FROM cart LEFT JOIN products ON products.id=cart.product_id LEFT JOIN category ON category.id=products.category_id WHERE user_id=:user_id");
			$stmt->execute(['user_id'=>$user['id']]);
			foreach($stmt as $row){
				$output['count']++;
				$image = safeImageUrl($row['photo'], 'images/', 'noimage.jpg');
				$productname = (mb_strlen($row['prodname'] ?? '', 'UTF-8') > 30) ? mb_substr($row['prodname'], 0, 27, 'UTF-8') . '...' : ($row['prodname'] ?? 'Producto no disponible');
				$output['list'] .= "
					<li>
						<a href='producto.php?product=".rawurlencode((string) $row['slug'])."'>
							<div class='pull-left'>
								<img src='".escapeHtml($image)."' class='thumbnail' alt='User Image'>
							</div>
							<h4>
		                        <b>".escapeHtml($row['catname'])."</b>
		                        <small>&times; ".$row['quantity']."</small>
		                    </h4>
		                    <p>".escapeHtml($productname)."</p>
						</a>
					</li>
				";
			}
		}
		catch(PDOException $e){
			$output['message'] = $e->getMessage();
		}
	}
	else{
		if(!isset($_SESSION['cart'])){
			$_SESSION['cart'] = array();
		}

		if(empty($_SESSION['cart'])){
			$output['count'] = 0;
		}
		else{
			foreach($_SESSION['cart'] as $row){
				$output['count']++;
				$stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname FROM products LEFT JOIN category ON category.id=products.category_id WHERE products.id=:id");
				$stmt->execute(['id'=>$row['productid']]);
				$product = $stmt->fetch();
				$image = safeImageUrl($product['photo'], 'images/', 'noimage.jpg');
				$output['list'] .= "
					<li>
						<a href='producto.php?product=".rawurlencode((string) $product['slug'])."'>
							<div class='pull-left'>
								<img src='".escapeHtml($image)."' class='img-circle' alt='User Image'>
							</div>
							<h4>
		                        <b>".escapeHtml($product['catname'])."</b>
		                        <small>&times; ".$row['quantity']."</small>
		                    </h4>
		                    <p>".escapeHtml($product['prodname'])."</p>
						</a>
					</li>
				";
				
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>
