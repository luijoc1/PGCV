<?php
include 'includes/session.php';

function generateRow($from, $to, $conn)
{
	$contents = '';

	$stmt = $conn->prepare("SELECT *, sales.id AS salesid FROM sales LEFT JOIN users ON users.id=sales.user_id WHERE sales_date BETWEEN '$from' AND '$to' ORDER BY sales_date DESC");
	$stmt->execute();
	$total = 0;
	$i = 0;
	foreach ($stmt as $row) {
		$stmt2 = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id WHERE sales_id=:id");
		$stmt2->execute(['id' => $row['salesid']]);
		$amount = 0;
		foreach ($stmt2 as $details) {
			$precio_final = precioConDescuento($details['price'], $details['descuento'] ?? 0);
			$subtotal = $precio_final * $details['quantity'];
			$amount += $subtotal;
		}
		$total += $amount;
		$bg = ($i % 2 == 0) ? '#ffffff' : '#f5f7fa';
		$contents .= '
			<tr style="background-color:' . $bg . ';">
				<td>' . date('M d, Y', strtotime($row['sales_date'])) . '</td>
				<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>
				<td align="center">' . $row['pay_id'] . '</td>
				<td align="right">&#36; ' . number_format($amount, 2) . '</td>
			</tr>
			';
		$i++;
	}

	$contents .= '
			<tr style="background-color:#1a2e4a;">
				<td colspan="3" align="right" style="color:#ffffff;"><b>TOTAL</b></td>
				<td align="right" style="color:#ffffff;"><b>&#36; ' . number_format($total, 2) . '</b></td>
			</tr>
		';
	return $contents;
}

if (isset($_POST['print'])) {
	$ex = explode(' - ', $_POST['date_range']);
	$from = date('Y-m-d', strtotime($ex[0]));
	$to = date('Y-m-d', strtotime($ex[1]));
	$from_title = date('M d, Y', strtotime($ex[0]));
	$to_title = date('M d, Y', strtotime($ex[1]));

	$conn = $pdo->open();

	require_once('../tcpdf/tcpdf.php');
	$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator('Almacén los Almendros');
	$pdf->SetTitle('Reporte de Ventas: ' . $from_title . ' - ' . $to_title);
	$pdf->SetMargins(10, 10, 10);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->SetAutoPageBreak(TRUE, 10);
	$pdf->SetFont('helvetica', '', 10);
	$pdf->AddPage();

	$content = '
			<table cellspacing="0" cellpadding="0" style="width:100%;">
				<tr>
					<td style="background-color:#1a2e4a; padding:16px; text-align:center;">
						<span style="color:#ffffff; font-size:20px; font-weight:bold;">Almacén los Almendros</span><br>
						<span style="color:rgba(255,255,255,0.7); font-size:13px;">Reporte de Ventas</span>
					</td>
				</tr>
				<tr>
					<td style="background-color:#3a8eff; padding:8px 16px; text-align:center;">
						<span style="color:#ffffff; font-size:12px;">Período: ' . $from_title . ' — ' . $to_title . '</span>
					</td>
				</tr>
			</table>
			<br>
			<table border="0" cellspacing="0" cellpadding="6" style="width:100%;">
				<tr style="background-color:#1a2e4a;">
					<th width="20%" align="center" style="color:#ffffff; font-size:11px;">Fecha</th>
					<th width="35%" align="center" style="color:#ffffff; font-size:11px;">Comprador</th>
					<th width="20%" align="center" style="color:#ffffff; font-size:11px;">Transacción#</th>
					<th width="25%" align="center" style="color:#ffffff; font-size:11px;">Subtotal</th>
				</tr>
		';

	$content .= generateRow($from, $to, $conn);
	$content .= '</table>';

	$content .= '
			<br>
			<table cellspacing="0" cellpadding="4" style="width:100%;">
				<tr>
					<td style="font-size:9px; color:#999; text-align:center;">
						Documento generado el ' . date('M d, Y H:i') . ' — Almacén los Almendros
					</td>
				</tr>
			</table>
		';

	$pdf->writeHTML($content);
	$pdf->Output('reporte_ventas.pdf', 'I');

	$pdo->close();
} else {
	$_SESSION['error'] = 'Necesita rango de fechas para proporcionar impresión de ventas';
	header('location: sales.php');
}
