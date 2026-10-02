<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/product_inventory.php';

final class ProductInventoryTest extends TestCase
{
    public function testMinimumIsPersistedAndChangesCriticalStockThreshold(): void
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT,category_id INTEGER,name TEXT,description TEXT,slug TEXT,price NUMERIC,stock INTEGER,stock_minimo INTEGER,photo TEXT,descuento INTEGER)');
        $inventory = productInventoryInput(['stock' => '3', 'stock_minimo' => '5', 'descuento' => '10', 'price' => '20.50']);
        $fields = array_merge($inventory, ['category' => 1, 'name' => 'Test', 'description' => '', 'slug' => 'test', 'photo' => '']);
        $id = insertCatalogProduct($conn, $fields);
        $this->assertSame(5, (int) $conn->query('SELECT stock_minimo FROM products')->fetchColumn());
        $this->assertSame(1, (int) $conn->query('SELECT COUNT(*) FROM products WHERE stock<=stock_minimo')->fetchColumn());
        unset($fields['photo']);
        $fields['id'] = $id;
        $fields['stock_minimo'] = 2;
        updateCatalogProduct($conn, $fields);
        $this->assertSame(2, (int) $conn->query('SELECT stock_minimo FROM products')->fetchColumn());
        $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM products WHERE stock<=stock_minimo')->fetchColumn());
    }

    public function testInventoryValidationRejectsMalformedAndOutOfRangeValues(): void
    {
        $valid = ['stock' => '0', 'stock_minimo' => '0', 'descuento' => '100', 'price' => '0'];
        $this->assertSame(['stock' => 0, 'stock_minimo' => 0, 'descuento' => 100, 'price' => '0.00'], productInventoryInput($valid));
        foreach ([['stock_minimo', '-1'], ['stock_minimo', '1.5'], ['stock_minimo', []], ['stock_minimo', '2147483648'], ['stock', '-1'], ['descuento', '101'], ['descuento', '10.5'], ['price', '1.234'], ['price', '-1'], ['price', []]] as $case) {
            $input = $valid;
            $input[$case[0]] = $case[1];
            try { productInventoryInput($input); $this->fail('Debe rechazarse el inventario inválido.'); }
            catch (InvalidArgumentException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
}
