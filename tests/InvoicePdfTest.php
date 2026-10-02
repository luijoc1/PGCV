<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/sale_history.php';

final class InvoicePdfTest extends TestCase
{
    /** @dataProvider invoiceTemplates */
    public function testInvoiceKeepsHistoricalPricesAndStoredTotal(string $template, bool $historical): void
    {
        $sale = [
            'pay_id' => 'PDF-TEST', 'sales_date' => '2026-10-02 12:34:56',
            'nombre_facturacion' => 'Test Client', 'documento' => '12345678',
            'email' => 'test@example.com', 'direccion' => 'Test Street',
            'ciudad' => 'Test City', 'telefono' => '3001234567',
            'metodo_pago' => 'efectivo', 'total' => 137.25,
        ];
        $productos = [[
            'name' => 'Test Product', 'quantity' => 2, 'price' => 999,
            'descuento' => 10, 'historical_unit_price' => $historical ? 90 : null,
        ]];

        // Run the actual rendering section with fixtures, excluding authentication,
        // database access and the download response. Keep the PDF entirely in memory.
        $source = file_get_contents(__DIR__ . '/../' . $template);
        $start = strpos($source, '$pdf = new TCPDF(');
        $end = strrpos($source, '$pdf->Output(');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $render = substr($source, $start, $end - $start);
        $render = str_replace('$pdf->AddPage();', '$pdf->setCompression(false); $pdf->AddPage();', $render);
        eval($render);

        $this->assertSame(1, $pdf->getNumPages());
        $result = $pdf->Output('invoice-test.pdf', 'S');
        $this->assertStringStartsWith('%PDF-', $result);
        foreach (['PDF-TEST', 'Test Client', 'Test Product', '$137.25'] as $text) {
            $this->assertStringContainsString($text, $result);
        }
        if ($historical) {
            $this->assertStringContainsString('$90.00', $result);
            $this->assertStringContainsString('$180.00', $result);
            $this->assertStringNotContainsString('$899.10', $result);
            $this->assertStringNotContainsString('Venta antigua:', $result);
        } else {
            $this->assertStringContainsString('$899.10', $result);
            $this->assertStringContainsString('$1,798.20', $result);
            $this->assertStringContainsString('Venta antigua:', $result);
        }
    }

    public function invoiceTemplates(): array
    {
        return [
            'customer snapshot' => ['factura_pdf.php', true],
            'admin snapshot' => ['admin/factura_pdf.php', true],
            'customer legacy' => ['factura_pdf.php', false],
            'admin legacy' => ['admin/factura_pdf.php', false],
        ];
    }
}
