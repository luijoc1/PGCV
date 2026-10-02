<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/customer_transaction.php';

final class CustomerTransactionTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE sales (id INTEGER PRIMARY KEY, user_id INTEGER, pay_id TEXT, sales_date TEXT, total NUMERIC DEFAULT 0)');
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT, price REAL, descuento INTEGER)');
        $conn->exec('CREATE TABLE details (sales_id INTEGER, product_id INTEGER, quantity INTEGER, product_name TEXT, original_price NUMERIC, discount_percent NUMERIC, unit_price NUMERIC)');
        $conn->exec("INSERT INTO sales (id,user_id,pay_id,sales_date) VALUES (1,11,'own','2026-10-01 10:00:00'),(2,22,'private','2026-10-01 11:00:00'),(3,11,'empty','2026-10-01 12:00:00')");
        $conn->exec("INSERT INTO products VALUES (1,'Example',100,10)");
        $conn->exec('INSERT INTO details (sales_id,product_id,quantity) VALUES (1,1,2),(2,1,3)');
        return $conn;
    }

    public function testCustomerCanReadOnlyOwnTransaction(): void
    {
        $conn = $this->database();
        $own = findCustomerTransaction($conn, 11, 1);
        $this->assertSame('own', $own['sale']['pay_id']);
        $this->assertCount(1, $own['details']);
        $this->assertSame(2, (int) $own['details'][0]['quantity']);
        $this->assertNull(findCustomerTransaction($conn, 11, 2));
        $this->assertNull(findCustomerTransaction($conn, 22, 1));
        $this->assertSame('private', findCustomerTransaction($conn, 22, 2)['sale']['pay_id']);
    }

    public function testInvalidOwnerMissingSaleAndMalformedIdentifiersReturnNoData(): void
    {
        $conn = $this->database();
        foreach ([null, 0, -1, [], '1 OR 1=1'] as $value) {
            $this->assertNull(findCustomerTransaction($conn, $value, 1));
            $this->assertNull(findCustomerTransaction($conn, 11, $value));
        }
        $this->assertNull(findCustomerTransaction($conn, 11, 999));
        $this->assertNull(findCustomerTransaction($conn, 999, 1));
    }

    public function testOwnedSaleWithoutDetailsStillHasTransactionMetadata(): void
    {
        $own = findCustomerTransaction($this->database(), 11, 3);
        $this->assertSame('empty', $own['sale']['pay_id']);
        $this->assertSame([], $own['details']);
    }
}
