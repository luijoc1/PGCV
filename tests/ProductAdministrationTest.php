<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class ProductAdministrationTest extends BaseHttpTestCase
{
    private $csrf;

    protected function setUp(): void
    {
        parent::setUp();
        $login = $this->http('/__session', ['id' => 2]);
        $this->assertSame(200, $login['status']);
        $this->csrf = json_decode($login['body'], true)['csrf_token'];
    }

    private function product(): array
    {
        return ['name' => 'Motor nuevo', 'category' => '1', 'price' => '25.50', 'stock' => '7', 'stock_minimo' => '3', 'descuento' => '10', 'description' => '<p onclick="alert(1)">Motor <strong>nuevo</strong></p><script>alert(1)</script>'];
    }

    private function operation(string $action, array $post): array
    {
        $response = $this->http('/admin/products_' . $action . '.php', array_replace($post, [$action => 1, 'csrf_token' => $this->csrf]));
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: products.php', strtolower($response['headers']));
        return json_decode($this->http('/__state', [], 'GET')['body'], true);
    }

    private function logs(): array
    {
        return $this->connection->query('SELECT * FROM logs_productos ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testCreateEditDeleteProductAndKeepAuditInformation(): void
    {
        $before = $this->snapshot();
        $created = $this->operation('add', $this->product());
        $this->assertArrayHasKey('success', $created);
        $row = $this->connection->query("SELECT * FROM products WHERE slug='motor-nuevo'")->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($row);
        $this->assertSame('<p>Motor <strong>nuevo</strong></p>', $row['description']);
        $this->assertSame('', $row['photo']);
        $this->assertEquals(25.5, $row['price']);
        $this->assertEquals(7, $row['stock']);
        $this->assertEquals(3, $row['stock_minimo']);
        $logs = $this->logs();
        $this->assertCount(1, $logs);
        $this->assertSame('INSERT', $logs[0]['tipo_operacion']);
        $this->assertSame('admin@example.com', $logs[0]['usuario_created']);
        $this->assertSame('Motor nuevo', json_decode($logs[0]['nueva_informacion'], true)['name']);
        $edited = $this->operation('edit', array_replace($this->product(), ['id' => $row['id'], 'name' => 'Motor revisado', 'price' => '40.00', 'stock' => '4']));
        $this->assertArrayHasKey('success', $edited);
        $logs = $this->logs();
        $this->assertCount(2, $logs);
        $this->assertSame('UPDATE', $logs[1]['tipo_operacion']);
        $this->assertSame('Motor nuevo', json_decode($logs[1]['informacion_anterior'], true)['name']);
        $this->assertSame('Motor revisado', json_decode($logs[1]['nueva_informacion'], true)['name']);
        $this->assertEquals(40, $this->connection->query('SELECT price FROM products WHERE id=' . (int) $row['id'])->fetchColumn());
        $deleted = $this->operation('delete', ['id' => $row['id']]);
        $this->assertArrayHasKey('success', $deleted);
        $this->assertSame($before, $this->snapshot());
        $afterDeletion = $this->logs();
        $this->assertSame($logs, array_slice($afterDeletion, 0, 2));
        $this->assertCount(3, $afterDeletion);
        $this->assertSame('DELETE', $afterDeletion[2]['tipo_operacion']);
        $this->assertSame('admin@example.com', $afterDeletion[2]['usuario_created']);
        $previous = json_decode($afterDeletion[2]['informacion_anterior'], true);
        $this->assertSame('Motor revisado', $previous['name']);
        $this->assertEquals(40, $previous['price']);
        $this->assertNull($afterDeletion[2]['nueva_informacion']);
    }

    /** @dataProvider invalidProducts */
    public function testInvalidProductDoesNotChangeCatalogOrCreateLog(string $action, array $changes): void
    {
        $before = $this->snapshot();
        $post = array_replace($this->product(), ['id' => 1], $changes);
        $result = $this->operation($action, $post);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], $this->logs());
    }

    public function invalidProducts(): array
    {
        return [
            'empty name' => ['add', ['name' => '']],
            'array name' => ['add', ['name' => ['bad']]],
            'missing category' => ['add', ['category' => null]],
            'unknown category' => ['add', ['category' => 999]],
            'invalid stock' => ['add', ['stock' => '-1']],
            'invalid price' => ['edit', ['price' => '1.234']],
            'unknown product' => ['edit', ['id' => 999]],
            'array identifier' => ['edit', ['id' => ['1']]],
            'duplicate slug' => ['add', ['name' => 'Original product']],
        ];
    }

    /** @dataProvider productReferences */
    public function testReferencedProductCannotBeDeleted(string $table): void
    {
        if ($table === 'cart') { $this->connection->exec('INSERT INTO cart VALUES (1,1,1,1)'); }
        else { $this->connection->exec('INSERT INTO details VALUES (1,1,1,1)'); }
        $before = $this->snapshot();
        $result = $this->operation('delete', ['id' => 1]);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertEquals(1, $this->connection->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn());
        $this->assertSame([], $this->logs());
    }

    public function productReferences(): array
    {
        return ['cart' => ['cart'], 'sale' => ['details']];
    }

    /** @dataProvider writeActions */
    public function testLogFailureRollsBackProductChange(string $action): void
    {
        $before = $this->snapshot();
        $this->connection->exec('DROP TABLE logs_productos');
        $result = $this->operation($action, array_replace($this->product(), ['id' => 1]));
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        if ($action === 'delete') {
            $logFile = $this->sandbox . '/php-errors.log';
            $log = file_get_contents($logFile);
            $this->assertMatchesRegularExpression('/\A\[[^\r\n]+\] Error al eliminar producto: SQLSTATE\[HY000\]: General error: 1 no such table: logs_productos\R\z/', $log);
            // The expected operational error is checked explicitly, not suppressed.
            file_put_contents($logFile, '');
        }
    }

    public function writeActions(): array
    {
        return ['add' => ['add'], 'edit' => ['edit'], 'delete' => ['delete']];
    }
}
