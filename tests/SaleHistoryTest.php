<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/customer_transaction.php';

final class SaleHistoryTest extends TestCase
{
    public function testPurchaseSurvivesCatalogChangesAndDeletion(): void
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE sales (id INTEGER, user_id INTEGER, pay_id TEXT, sales_date TEXT, total NUMERIC)');
        $conn->exec('CREATE TABLE products (id INTEGER, name TEXT, price NUMERIC, descuento NUMERIC)');
        $conn->exec('CREATE TABLE details (sales_id INTEGER, product_id INTEGER, quantity INTEGER, product_name TEXT, original_price NUMERIC, discount_percent NUMERIC, unit_price NUMERIC)');
        $conn->exec("INSERT INTO sales VALUES (1,11,'test','2026-10-01 10:00:00',179.98)");
        $conn->exec("INSERT INTO products VALUES (1,'Original',99.99,10)");
        insertSaleDetail($conn, 1, ['product_id' => 1, 'quantity' => 2, 'name' => 'Original', 'price' => '99.99', 'descuento' => 10]);
        $original = findCustomerTransaction($conn, 11, 1);
        $this->assertSame('Original', $original['details'][0]['name']);
        $this->assertEquals(99.99, $original['details'][0]['price']);
        $this->assertEquals(10, $original['details'][0]['descuento']);
        $this->assertSame(89.99, saleDetailUnitPrice($original['details'][0]));
        $this->assertEquals($original['sale']['total'], round(saleDetailUnitPrice($original['details'][0]) * 2, 2));
        $conn->exec("UPDATE products SET name='Changed', price=999, descuento=50");
        $this->assertSame($original, findCustomerTransaction($conn, 11, 1));
        $conn->exec('DELETE FROM products');
        $this->assertSame($original, findCustomerTransaction($conn, 11, 1));
        $this->assertNull(findCustomerTransaction($conn, 22, 1));
    }

    public function testRoundingFreeItemsAndLegacyEstimates(): void
    {
        foreach ([[99.99,10,'89.99'], [10,100,'0.00'], [0,0,'0.00'], [20,0,'20.00'], [100,12.5,'87.50']] as $case) {
            $snapshot = saleSnapshot(['name' => 'Test', 'price' => $case[0], 'descuento' => $case[1]]);
            $this->assertSame($case[2], $snapshot['unit_price']);
        }
        $this->assertSame(0.0, saleDetailUnitPrice(['historical_unit_price' => '0.00', 'price' => 100, 'descuento' => 0]));
        $this->assertSame(90.0, saleDetailUnitPrice(['historical_unit_price' => null, 'price' => 100, 'descuento' => 10]));
        foreach ([[-1,0],[10,-1],[10,101],[INF,0],[10,NAN]] as $case) {
            try {
                saleSnapshot(['name' => 'Test', 'price' => $case[0], 'descuento' => $case[1]]);
                $this->fail('Debe rechazarse el precio o descuento inválido.');
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }
}
