<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/sales_report.php';

final class SalesReportTest extends TestCase
{
    public function testRangeIncludesWholeFinalDayAndHandlesLeapDay(): void
    {
        $range = salesReportRange('10/01/2026 - 10/01/2026');
        $this->assertSame('2026-10-01 00:00:00', $range['from']);
        $this->assertSame('2026-10-02 00:00:00', $range['until']);
        $this->assertSame('2024-03-01 00:00:00', salesReportRange('2024-02-29 - 2024-02-29')['until']);
        foreach ([null, [], '02/29/2025 - 03/01/2025', '10/02/2026 - 10/01/2026', '13/01/2026 - 13/01/2026', '2026-10-01 OR 1=1', '9999-12-31 - 9999-12-31'] as $invalid) {
            try { salesReportRange($invalid); $this->fail('Debe rechazarse el rango inválido.'); }
            catch (InvalidArgumentException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }

    public function testReportUsesStoredTotalsAndPreservesOrphanSales(): void
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE users (id INTEGER,firstname TEXT,lastname TEXT)');
        $conn->exec('CREATE TABLE sales (id INTEGER,user_id INTEGER,sales_date TEXT,total NUMERIC,pay_id TEXT,nombre_facturacion TEXT)');
        $conn->exec("INSERT INTO users VALUES (1,'<img src=x>','Test');
            INSERT INTO sales VALUES (1,1,'2026-10-01 00:00:00',5,'first',''),(2,1,'2026-10-01 12:00:00',10,'middle',''),(3,99,'2026-10-01 23:59:59',15,'<script>x</script>','Historical Client'),(4,1,'2026-10-02 00:00:00',100,'next',''),(5,1,'2026-09-30 23:59:59',100,'previous','')");
        $rows = salesReportRows($conn, salesReportRange('10/01/2026 - 10/01/2026'));
        $this->assertSame([3,2,1], array_map('intval', array_column($rows, 'id')));
        $html = salesReportHtml($rows);
        $this->assertStringContainsString('Historical Client', $html);
        $this->assertStringContainsString('30.00', $html);
        $this->assertStringContainsString('&lt;img', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('100.00', $html);
        $this->assertStringContainsString('0.00', salesReportHtml([]));
    }

    public function testPdfEngineAcceptsEscapedReportHtml(): void
    {
        require_once __DIR__ . '/../tcpdf/tcpdf.php';
        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTML('<table>' . salesReportHtml([['firstname' => 'Test', 'lastname' => 'Client', 'nombre_facturacion' => '', 'sales_date' => '2026-10-01 23:59:59', 'pay_id' => '1', 'total' => 10]]) . '</table>');
        $this->assertStringStartsWith('%PDF-', $pdf->Output('report-test.pdf', 'S'));
    }
}
