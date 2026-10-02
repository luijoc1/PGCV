<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/product_deletion.php';

final class ProductDeletionTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT);
            CREATE TABLE details (product_id INTEGER, quantity INTEGER);
            CREATE TABLE cart (product_id INTEGER, quantity INTEGER);
            INSERT INTO products VALUES (1, "Producto vendido"), (2, "Producto en carrito"), (3, "Producto libre");
            INSERT INTO details VALUES (1, 2); INSERT INTO cart VALUES (2, 1)');
        return $conn;
    }

    public function testDeletionPreservesSalesAndCarts(): void
    {
        $conn = $this->database();
        foreach ([1, 2] as $id) {
            try { deleteUnreferencedProduct($conn, $id); $this->fail('Debe conservar el producto vinculado.'); }
            catch (InvalidArgumentException $e) { $this->assertStringContainsString('ventas o carritos', $e->getMessage()); }
        }
        $this->assertSame(3, (int) $conn->query('SELECT COUNT(*) FROM products')->fetchColumn());
        $this->assertSame(2, (int) $conn->query('SELECT quantity FROM details')->fetchColumn());
        $this->assertSame(1, (int) $conn->query('SELECT quantity FROM cart')->fetchColumn());
    }

    public function testUnreferencedProductCanBeDeleted(): void
    {
        $conn = $this->database();
        deleteUnreferencedProduct($conn, '3');
        $this->assertSame([1, 2], array_map('intval', $conn->query('SELECT id FROM products ORDER BY id')->fetchAll(PDO::FETCH_COLUMN)));
        $this->expectException(InvalidArgumentException::class);
        deleteUnreferencedProduct($conn, 3);
    }

    public function testInvalidIdentifiersCannotDeleteProducts(): void
    {
        $conn = $this->database();
        foreach ([null, [], '1 OR 1=1', '1.0', 0, -1, '2147483648'] as $id) {
            try { deleteUnreferencedProduct($conn, $id); $this->fail('Debe rechazar el identificador.'); }
            catch (InvalidArgumentException $e) { $this->assertStringContainsString('inválido', $e->getMessage()); }
        }
        $this->assertSame(3, (int) $conn->query('SELECT COUNT(*) FROM products')->fetchColumn());
    }
}
