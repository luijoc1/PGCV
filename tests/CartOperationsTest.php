<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/cart_operations.php';

final class CartOperationsTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY, stock INTEGER)');
        $conn->exec('CREATE TABLE cart (id INTEGER PRIMARY KEY, user_id INTEGER, product_id INTEGER, quantity INTEGER)');
        $conn->exec('INSERT INTO products VALUES (1, 10)');
        $conn->exec('INSERT INTO cart VALUES (1, 11, 1, 2), (2, 22, 1, 3)');
        return $conn;
    }

    private function rejects(callable $operation): void
    {
        try {
            $operation();
            $this->fail('La operación debía rechazar la solicitud.');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testQuantityMustBeAPositiveIntegerWithinDatabaseRange(): void
    {
        foreach ([null, '', 0, -1, '0', '-5', '1.5', 1.5, '1e2', ' 1', '01', true, [], '2147483648'] as $value) {
            $this->assertNull(cartPositiveInteger($value));
        }
        $this->assertSame(1, cartPositiveInteger('1'));
        $this->assertSame(2147483647, cartPositiveInteger(2147483647));
    }

    public function testCustomerCanUpdateOwnItemIncludingUnchangedQuantity(): void
    {
        $conn = $this->database();
        updateOwnedCart($conn, 11, 1, 5);
        updateOwnedCart($conn, 11, 1, 5);
        $this->assertSame(5, (int) $conn->query('SELECT quantity FROM cart WHERE id=1')->fetchColumn());
        $this->assertSame(3, (int) $conn->query('SELECT quantity FROM cart WHERE id=2')->fetchColumn());
    }

    public function testCustomerCannotUpdateAnotherCustomersCart(): void
    {
        $conn = $this->database();
        $this->rejects(function () use ($conn) { updateOwnedCart($conn, 11, 2, 5); });
        $this->assertSame(3, (int) $conn->query('SELECT quantity FROM cart WHERE id=2')->fetchColumn());
    }

    public function testRejectsInvalidQuantitiesAndInsufficientStockWithoutChangingData(): void
    {
        $conn = $this->database();
        foreach ([-5, 0, '2.5', [], 11] as $qty) {
            $this->rejects(function () use ($conn, $qty) { updateOwnedCart($conn, 11, 1, $qty); });
        }
        $this->assertSame(2, (int) $conn->query('SELECT quantity FROM cart WHERE id=1')->fetchColumn());
        $this->assertSame(10, (int) $conn->query('SELECT stock FROM products WHERE id=1')->fetchColumn());
    }

    public function testCustomerCannotDeleteAnotherCustomersCart(): void
    {
        $conn = $this->database();
        $this->rejects(function () use ($conn) { deleteOwnedCart($conn, 11, 2); });
        $this->assertSame(2, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
        deleteOwnedCart($conn, 11, 1);
        $this->assertSame(1, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
        $this->assertSame(22, (int) $conn->query('SELECT user_id FROM cart')->fetchColumn());
    }

    public function testMissingAndOrphanItemsCannotBeUpdated(): void
    {
        $conn = $this->database();
        $conn->exec('INSERT INTO cart VALUES (3, 11, 999, 1)');
        $this->rejects(function () use ($conn) { updateOwnedCart($conn, 11, 999, 2); });
        $this->rejects(function () use ($conn) { updateOwnedCart($conn, 11, 3, 2); });
        $this->rejects(function () use ($conn) { deleteOwnedCart($conn, 11, 999); });
        $this->assertSame(1, (int) $conn->query('SELECT quantity FROM cart WHERE id=3')->fetchColumn());
    }
}
