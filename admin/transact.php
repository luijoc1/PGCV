<?php
include 'includes/session.php';
require_once __DIR__ . '/../includes/sale_history.php';

$id = $_POST['id'];

$conn = $pdo->open();

$output = array('list' => '');

$saleStmt = $conn->prepare('SELECT pay_id, sales_date, total FROM sales WHERE id=:id');
$saleStmt->execute(['id' => $id]);
$sale = $saleStmt->fetch();
if (!$sale) {
    http_response_code(404);
    echo json_encode(['error' => true, 'message' => 'Venta no encontrada.']);
    $pdo->close();
    exit();
}
$output['transaction'] = $sale['pay_id'];
$output['date'] = date('M d, Y', strtotime($sale['sales_date']));

$stmt = $conn->prepare("SELECT " . saleDetailColumns() . ", sales.pay_id, sales.sales_date FROM details LEFT JOIN products ON products.id=details.product_id LEFT JOIN sales ON sales.id=details.sales_id WHERE details.sales_id=:id");
$stmt->execute(['id' => $id]);

$total = 0;
foreach ($stmt as $row) {
	$output['transaction'] = $row['pay_id'];
	$output['date'] = date('M d, Y', strtotime($row['sales_date']));
	$precio_final = saleDetailUnitPrice($row);
	$subtotal = $precio_final * $row['quantity'];
	$total += $subtotal;
	if (!isset($row['historical_unit_price'])) { $output['legacy_details'] = true; }

	$precio_html = ($row['descuento'] > 0)
		? "<small style='text-decoration:line-through; color:#999;'>&#36; " . number_format($row['price'], 2) . "</small>
			   <b style='color:#e74c3c;'>&#36; " . number_format($precio_final, 2) . "</b>
			   <span style='background:#e74c3c; color:#fff; font-size:10px; padding:1px 6px; border-radius:20px;'>-" . $row['descuento'] . "%</span>"
		: "&#36; " . number_format($row['price'], 2);

	$output['list'] .= "
			<tr class='prepend_items'>
				<td>" . escapeHtml($row['name']) . "</td>
				<td>" . $precio_html . "</td>
				<td>" . $row['quantity'] . "</td>
				<td>&#36; " . number_format($subtotal, 2) . "</td>
			</tr>
		";
}

if (!empty($output['legacy_details'])) {
    $output['list'] .= '<tr><td colspan="4">Venta antigua: precios de detalle estimados con el catálogo actual.</td></tr>';
}
$total = (float) ($sale['total'] ?? 0);
$output['total'] = '<b>&#36; ' . number_format($total, 2) . '</b>';
$pdo->close();
echo json_encode($output);
