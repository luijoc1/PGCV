<?php
include 'includes/session.php';

if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
    header('location: login.php');
    exit();
}
if (!isset($user)) $user = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: perfil.php');
    exit();
}

$sale_id = $_GET['id'];
$conn = $pdo->open();

if (isset($_SESSION['admin'])) {
    $stmt = $conn->prepare("SELECT sales.*, users.email, users.firstname, users.lastname 
                            FROM sales 
                            LEFT JOIN users ON users.id = sales.user_id 
                            WHERE sales.id = :id");
    $stmt->execute(['id' => $sale_id]);
} else {
    $stmt = $conn->prepare("SELECT sales.*, users.email, users.firstname, users.lastname 
                            FROM sales 
                            LEFT JOIN users ON users.id = sales.user_id 
                            WHERE sales.id = :id AND sales.user_id = :user_id");
    $stmt->execute(['id' => $sale_id, 'user_id' => $user['id']]);
}
$sale = $stmt->fetch();

if (!$sale) {
    $_SESSION['error'] = 'Factura no encontrada';
    header('location: perfil.php');
    exit();
}

// Obtener productos de la venta
$stmt = $conn->prepare("SELECT details.*, products.name, products.price, products.descuento 
                        FROM details 
                        LEFT JOIN products ON products.id = details.product_id 
                        WHERE details.sales_id = :sales_id");
$stmt->execute(['sales_id' => $sale_id]);
$productos = $stmt->fetchAll();

$pdo->close();

// Generar PDF con TCPDF
require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator('Almacén los Almendros');
$pdf->SetAuthor('Almacén los Almendros');
$pdf->SetTitle('Factura #' . $sale['pay_id']);
$pdf->SetSubject('Factura de compra');

// Sin header/footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// ─── HEADER ───────────────────────────────────────────────
$pdf->SetFillColor(26, 46, 74);   // #1a2e4a
$pdf->Rect(0, 0, 210, 38, 'F');

$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(15, 10);
$pdf->Cell(0, 10, 'Almacén los Almendros', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 11);
$pdf->SetXY(15, 22);
$pdf->Cell(0, 8, 'Factura de compra', 0, 1, 'L');

// Número de factura (derecha)
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetXY(0, 12);
$pdf->Cell(195, 8, 'N° ' . $sale['pay_id'], 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(0, 22);
$pdf->Cell(195, 8, 'Fecha: ' . $sale['sales_date'], 0, 1, 'R');

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(18);

// ─── DATOS DEL CLIENTE Y ENTREGA ──────────────────────────
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(240, 242, 245);
$pdf->Cell(0, 8, 'Datos de facturación', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 10);
$col1 = [
    'Cliente'    => $sale['nombre_facturacion'],
    'Documento'  => $sale['documento'],
    'Correo'     => $sale['email'],
];
$col2 = [
    'Dirección'  => $sale['direccion'],
    'Ciudad'     => $sale['ciudad'],
    'Teléfono'   => $sale['telefono'],
];

$y_start = $pdf->GetY();
$pdf->SetX(15);
foreach ($col1 as $label => $valor) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(30, 7, $label . ':', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(65, 7, $valor, 0, 1, 'L');
}

$pdf->SetXY(110, $y_start);
foreach ($col2 as $label => $valor) {
    $pdf->SetX(110);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(30, 7, $label . ':', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(65, 7, $valor, 0, 1, 'L');
}

$pdf->Ln(4);

// ─── MÉTODO DE PAGO ───────────────────────────────────────
$metodos = [
    'tarjeta'       => 'Tarjeta de crédito / débito',
    'transferencia' => 'Transferencia / PSE',
    'efectivo'      => 'Efectivo (pago contraentrega)',
];
$metodo_texto = $metodos[$sale['metodo_pago']] ?? $sale['metodo_pago'];

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(35, 7, 'Método de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, $metodo_texto, 0, 1, 'L');
$pdf->Ln(4);

// ─── TABLA DE PRODUCTOS ───────────────────────────────────
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(240, 242, 245);
$pdf->Cell(0, 8, 'Detalle de productos', 0, 1, 'L', true);
$pdf->Ln(2);

// Cabecera tabla
$pdf->SetFillColor(26, 46, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 8, 'Producto',   1, 0, 'C', true);
$pdf->Cell(30, 8, 'Cantidad',   1, 0, 'C', true);
$pdf->Cell(35, 8, 'Precio unit.', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Subtotal',   1, 1, 'C', true);

// Filas
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);
$fill = false;
foreach ($productos as $p) {
    $precio_final = precioConDescuento($p['price'], $p['descuento'] ?? 0);
    $subtotal = $precio_final * $p['quantity'];
    $pdf->SetFillColor(249, 249, 249);
    $pdf->Cell(90, 7, $p['name'],                          1, 0, 'L', $fill);
    $pdf->Cell(30, 7, $p['quantity'],                      1, 0, 'C', $fill);
    $pdf->Cell(35, 7, '$' . number_format($precio_final, 2), 1, 0, 'R', $fill);
    $pdf->Cell(35, 7, '$' . number_format($subtotal, 2),     1, 1, 'R', $fill);
    $fill = !$fill;
}

// Total
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(26, 46, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(155, 8, 'TOTAL',                                    1, 0, 'R', true);
$pdf->Cell(35,  8, '$' . number_format($sale['total'], 2),       1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);

// ─── FOOTER ───────────────────────────────────────────────
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, 'Gracias por tu compra. Este documento es una factura válida de Almacén los Almendros.', 0, 1, 'C');

// Descargar PDF
$pdf->Output('Factura_' . $sale['pay_id'] . '.pdf', 'D');
