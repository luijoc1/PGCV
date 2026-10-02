<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/checkout.php';

final class CheckoutTest extends TestCase
{
    private function billing(): array
    {
        return ['nombre_facturacion' => 'María Pérez', 'documento' => '1098765432', 'direccion' => 'Calle 10 # 5-20', 'telefono' => '+57 3001234567', 'ciudad' => 'Santa Marta', 'metodo_pago' => 'efectivo'];
    }

    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE users (id INTEGER PRIMARY KEY,status INTEGER,type INTEGER)');
        $conn->exec('CREATE TABLE products (id INTEGER PRIMARY KEY,name TEXT,price NUMERIC,descuento NUMERIC,stock INTEGER)');
        $conn->exec('CREATE TABLE cart (id INTEGER PRIMARY KEY,user_id INTEGER,product_id INTEGER,quantity INTEGER,fecha_hora_inicio TEXT)');
        $conn->exec('CREATE TABLE sales (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,pay_id TEXT,sales_date TEXT,fecha_hora_inicio TEXT,nombre_facturacion TEXT,documento TEXT,direccion TEXT,telefono TEXT,ciudad TEXT,metodo_pago TEXT,total NUMERIC)');
        $conn->exec('CREATE TABLE details (id INTEGER PRIMARY KEY AUTOINCREMENT,sales_id INTEGER,product_id INTEGER,quantity INTEGER,product_name TEXT,original_price NUMERIC,discount_percent NUMERIC,unit_price NUMERIC)');
        $conn->exec('CREATE TABLE checkout_requests (request_key TEXT PRIMARY KEY,user_id INTEGER,sales_id INTEGER DEFAULT NULL)');
        $conn->exec('CREATE TABLE checkout_sequence (id INTEGER PRIMARY KEY,last_value INTEGER)');
        $conn->exec("INSERT INTO users VALUES (1,1,0),(2,1,0); INSERT INTO products VALUES (1,'Original',100,10,5); INSERT INTO checkout_sequence VALUES (1,100);
            INSERT INTO cart VALUES (1,1,1,1,'2026-10-01 10:00:00'),(2,1,1,1,'2026-10-01 10:05:00'),(3,2,1,1,'2026-10-01 11:00:00')");
        return $conn;
    }

    public function testReplayDoesNotCreateSaleReduceStockOrConsumeNewCart(): void
    {
        $conn = $this->database();
        $logs = 0;
        $logger = function () use (&$logs) { $logs++; };
        $sale = completeCheckout($conn, 1, $this->billing(), str_repeat('a', 64), $logger);
        $this->assertFalse($sale['replayed']);
        $this->assertSame(101, $sale['pay_id']);
        $this->assertSame(180.0, $sale['total']);
        $this->assertCount(1, $sale['products']);
        $this->assertSame(2, $sale['products'][0]['quantity']);
        $this->assertSame(3, (int) $conn->query('SELECT stock FROM products')->fetchColumn());
        $this->assertSame(1, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
        $conn->exec("INSERT INTO cart VALUES (4,1,1,1,'2026-10-01 12:00:00')");
        $replay = completeCheckout($conn, 1, $this->billing(), str_repeat('a', 64), $logger);
        $this->assertTrue($replay['replayed']);
        $this->assertSame((string) $sale['sales_id'], (string) $replay['sales_id']);
        $this->assertSame(1, $logs);
        $this->assertSame(1, (int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn());
        $this->assertSame(3, (int) $conn->query('SELECT stock FROM products')->fetchColumn());
        $this->assertSame(2, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
        $new = completeCheckout($conn, 1, $this->billing(), str_repeat('b', 64));
        $this->assertSame(102, $new['pay_id']);
        $this->assertSame(2, (int) $conn->query('SELECT stock FROM products')->fetchColumn());
    }

    public function testInvalidCartOrAccountRollsBackEntirePurchase(): void
    {
        foreach (['UPDATE products SET stock=1', 'DELETE FROM products', 'UPDATE cart SET quantity=-1 WHERE id=1', 'UPDATE users SET status=0 WHERE id=1'] as $mutation) {
            $conn = $this->database();
            $conn->exec($mutation);
            try {
                completeCheckout($conn, 1, $this->billing(), str_repeat('c', 64));
                $this->fail('Debe rechazarse el pedido.');
            } catch (InvalidArgumentException $e) {
                $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn());
                $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM details')->fetchColumn());
                $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM checkout_requests')->fetchColumn());
                $this->assertSame(3, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
                $this->assertSame(100, (int) $conn->query('SELECT last_value FROM checkout_sequence')->fetchColumn());
            }
        }
    }

    public function testLogFailureRestoresStockCartSequenceAndAllowsRetry(): void
    {
        $conn = $this->database();
        try {
            completeCheckout($conn, 1, $this->billing(), str_repeat('d', 64), function () { throw new RuntimeException('Test failure'); });
            $this->fail('La compra debe revertirse.');
        } catch (RuntimeException $e) {
            $this->assertSame(5, (int) $conn->query('SELECT stock FROM products')->fetchColumn());
            $this->assertSame(3, (int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn());
            $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn());
            $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM details')->fetchColumn());
        }
        $this->assertSame(101, completeCheckout($conn, 1, $this->billing(), str_repeat('d', 64))['pay_id']);
    }

    public function testRequestBelongsToOriginalCustomer(): void
    {
        $conn = $this->database();
        completeCheckout($conn, 1, $this->billing(), str_repeat('e', 64));
        $this->expectException(InvalidArgumentException::class);
        completeCheckout($conn, 2, $this->billing(), str_repeat('e', 64));
    }

    public function testBillingRejectsMissingArraysBadLengthsAndUnknownMethods(): void
    {
        $this->assertSame($this->billing(), checkoutBilling($this->billing()));
        foreach ([['ciudad', ''], ['nombre_facturacion', []], ['direccion', str_repeat('x', 201)], ['telefono', '123'], ['documento', '<script>'], ['metodo_pago', 'unknown'], ['ciudad', "Santa\nMarta"], ['telefono', []]] as $case) {
            $input = $this->billing();
            $input[$case[0]] = $case[1];
            try {
                checkoutBilling($input);
                $this->fail('Debe rechazarse la facturación inválida.');
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    public function testFormTokenMustHaveBeenIssuedAndSupportsMultipleTabs(): void
    {
        $_SESSION = [];
        $first = issueCheckoutToken();
        $second = issueCheckoutToken();
        $this->assertNotSame($first, $second);
        $this->assertTrue((bool) validCheckoutToken($first));
        $this->assertTrue((bool) validCheckoutToken($second));
        $this->assertFalse((bool) validCheckoutToken(str_repeat('f', 64)));
        $this->assertFalse((bool) validCheckoutToken([]));
    }
}
