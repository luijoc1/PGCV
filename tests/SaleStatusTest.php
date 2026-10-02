<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/sale_status.php';

final class SaleStatusTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE sales (id INTEGER PRIMARY KEY, estado TEXT, sales_date TEXT, total NUMERIC)');
        $conn->exec("INSERT INTO sales VALUES (1, 'pendiente', '2025-03-10 14:35:22', 15000), (2, 'pendiente', '2025-04-12 09:12:10', 8000)");
        return $conn;
    }

    public function testStatusChangesPreservePurchaseDateAmountAndOtherSales(): void
    {
        $conn = $this->database();
        $other = $conn->query('SELECT * FROM sales WHERE id=2')->fetch(PDO::FETCH_ASSOC);
        $originalTotal = $conn->query('SELECT total FROM sales WHERE id=1')->fetchColumn();
        foreach (['en_proceso', 'enviado', 'entregado', 'entregado', 'pendiente'] as $status) {
            updateSaleStatus($conn, '1', $status);
            $sale = $conn->query('SELECT * FROM sales WHERE id=1')->fetch(PDO::FETCH_ASSOC);
            $this->assertSame($status, $sale['estado']);
            $this->assertSame('2025-03-10 14:35:22', $sale['sales_date']);
            $this->assertSame($originalTotal, $sale['total']);
            $this->assertSame($other, $conn->query('SELECT * FROM sales WHERE id=2')->fetch(PDO::FETCH_ASSOC));
        }
        updateSaleStatus($conn, 999, 'enviado');
        $this->assertSame(2, (int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn());
    }

    public function testInvalidRequestsLeaveSalesUntouched(): void
    {
        $conn = $this->database();
        $before = $conn->query('SELECT * FROM sales ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        foreach ([[0, 'enviado'], [-1, 'enviado'], [[], 'enviado'], ['1 OR 1=1', 'enviado'], [1, []], [1, 'cancelado']] as $input) {
            try {
                updateSaleStatus($conn, $input[0], $input[1]);
                $this->fail('La solicitud debe rechazarse.');
            } catch (InvalidArgumentException $e) {
                $this->assertSame($before, $conn->query('SELECT * FROM sales ORDER BY id')->fetchAll(PDO::FETCH_ASSOC));
            }
        }
    }
}
