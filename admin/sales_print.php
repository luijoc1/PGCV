<?php
include 'includes/session.php';
require_once __DIR__ . '/../includes/sales_report.php';
date_default_timezone_set('America/Bogota');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['print'])) {
    $_SESSION['error'] = 'Selecciona un rango de fechas para imprimir el reporte.';
    header('location: sales.php');
    exit();
}
try {
    $range = salesReportRange($_POST['date_range'] ?? null);
} catch (InvalidArgumentException $e) {
    $_SESSION['error'] = $e->getMessage();
    header('location: sales.php');
    exit();
}
$from_title = $range['from_title'];
$to_title = $range['to_title'];

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

	$content .= salesReportHtml(salesReportRows($conn, $range));
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
