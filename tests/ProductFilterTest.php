<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/product_filter.php';

final class ProductFilterTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY, category_id INTEGER)');
        $conn->exec('INSERT INTO products VALUES (1,10),(2,20),(3,10)');
        return $conn;
    }

    public function testAllProductsAndSelectedCategory(): void
    {
        $conn = $this->database();
        foreach ([null, 0, '0'] as $value) {
            $this->assertSame(0, productCategoryId($value));
            $this->assertCount(3, productListQuery($conn, $value)->fetchAll());
        }
        $this->assertSame([1,3], array_map('intval', productListQuery($conn, '10')->fetchAll(PDO::FETCH_COLUMN)));
        $this->assertSame([2], array_map('intval', productListQuery($conn, 20)->fetchAll(PDO::FETCH_COLUMN)));
        $this->assertSame([], productListQuery($conn, 999)->fetchAll());
    }

    public function testMaliciousAndMalformedCategoriesAreRejected(): void
    {
        $conn = $this->database();
        foreach (['10 OR 1=1', '10; DROP TABLE products', '0 UNION SELECT 1,1', [], -1, '', '1.5', true] as $value) {
            try {
                productListQuery($conn, $value);
                $this->fail('La categoría debía rechazarse.');
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
        $this->assertCount(3, productListQuery($conn, 0)->fetchAll());
    }
}
