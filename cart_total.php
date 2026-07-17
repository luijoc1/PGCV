<?php
include 'includes/session.php';

if (isset($_SESSION['user'])) {
	$conn = $pdo->open();

	$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
	$stmt->execute(['user_id' => $user['id']]);

	$total = 0;
	foreach ($stmt as $row) {
		$precio_final = precioConDescuento($row['price'], $row['descuento']);
		$subtotal = $precio_final * $row['quantity'];
		$total += $subtotal;
	}

	$pdo->close();

	echo json_encode($total);
}
