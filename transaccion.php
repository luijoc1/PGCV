<?php
include 'includes/session.php';
require_once __DIR__ . '/includes/sale_history.php';
require_once __DIR__ . '/includes/customer_transaction.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user'], $user['id']) || empty($user['status']) || (int) $user['type'] !== 0) {
	http_response_code(401);
	echo json_encode(['error' => true, 'message' => 'Debes iniciar sesión para consultar tus transacciones.']);
	exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	header('Allow: POST');
	echo json_encode(['error' => true, 'message' => 'Usa POST para consultar la transacción.']);
	exit();
}
$id = cartPositiveInteger($_POST['id'] ?? null);
if ($id === null) {
	http_response_code(400);
	echo json_encode(['error' => true, 'message' => 'El identificador de la transacción es inválido.']);
	exit();
}

$conn = $pdo->open();
try {
	$transaction = findCustomerTransaction($conn, $user['id'], $id);
} catch (PDOException $e) {
	error_log('Error al consultar transacción: ' . $e->getMessage());
	$pdo->close();
	http_response_code(500);
	echo json_encode(['error' => true, 'message' => 'No se pudo consultar la transacción.']);
	exit();
}
$pdo->close();
if ($transaction === null) {
	http_response_code(404);
	echo json_encode(['error' => true, 'message' => 'Transacción no encontrada.']);
	exit();
}

$output = [
	'error' => false,
	'list' => '',
	'transaction' => $transaction['sale']['pay_id'],
	'date' => date('M d, Y', strtotime($transaction['sale']['sales_date'])),
];

$total = 0;
foreach ($transaction['details'] as $row) {
	$row['price'] = (float) ($row['price'] ?? 0);
	$row['descuento'] = (float) ($row['descuento'] ?? 0);
	$row['quantity'] = (int) $row['quantity'];
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
				<td>" . htmlspecialchars($row['name'] ?? 'Producto no disponible', ENT_QUOTES, 'UTF-8') . "</td>
				<td>" . $precio_html . "</td>
				<td>" . $row['quantity'] . "</td>
				<td>&#36; " . number_format($subtotal, 2) . "</td>
			</tr>
		";
}

if (!empty($output['legacy_details'])) {
    $output['list'] .= '<tr><td colspan="4">Venta antigua: precios de detalle estimados con el catálogo actual.</td></tr>';
}
$total = (float) $transaction['sale']['total'];
$output['total'] = '<b>&#36; ' . number_format($total, 2) . '</b>';
echo json_encode($output);
