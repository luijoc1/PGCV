<?php
include 'includes/session.php';

$id = $_POST['id'];

$conn = $pdo->open();

$output = array('list' => '');

$stmt = $conn->prepare("SELECT details.*, products.name, products.price, products.descuento, sales.pay_id, sales.sales_date FROM details LEFT JOIN products ON products.id=details.product_id LEFT JOIN sales ON sales.id=details.sales_id WHERE details.sales_id=:id");
$stmt->execute(['id' => $id]);

$total = 0;
foreach ($stmt as $row) {
	$output['transaction'] = $row['pay_id'];
	$output['date'] = date('M d, Y', strtotime($row['sales_date']));
	$precio_final = precioConDescuento($row['price'], $row['descuento'] ?? 0);
	$subtotal = $precio_final * $row['quantity'];
	$total += $subtotal;

	$precio_html = ($row['descuento'] > 0)
		? "<small style='text-decoration:line-through; color:#999;'>&#36; " . number_format($row['price'], 2) . "</small>
			   <b style='color:#e74c3c;'>&#36; " . number_format($precio_final, 2) . "</b>
			   <span style='background:#e74c3c; color:#fff; font-size:10px; padding:1px 6px; border-radius:20px;'>-" . $row['descuento'] . "%</span>"
		: "&#36; " . number_format($row['price'], 2);

	$output['list'] .= "
			<tr class='prepend_items'>
				<td>" . $row['name'] . "</td>
				<td>" . $precio_html . "</td>
				<td>" . $row['quantity'] . "</td>
				<td>&#36; " . number_format($subtotal, 2) . "</td>
			</tr>
		";
}

$output['total'] = '<b>&#36; ' . number_format($total, 2) . '</b>';
$pdo->close();
echo json_encode($output);
