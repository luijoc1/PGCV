<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class AdministrativeOperationsTest extends BaseHttpTestCase
{
    private $csrf;

    protected function setUp(): void
    {
        parent::setUp();
        $login = $this->http('/__session', ['id' => 2]);
        $this->assertSame(200, $login['status']);
        $this->csrf = json_decode($login['body'], true)['csrf_token'];
    }

    private function operation(string $route, array $post): array
    {
        $response = $this->http('/admin/' . $route . '.php', array_replace($post, ['csrf_token' => $this->csrf]));
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: ' . ($route === 'quitar_descuento' ? 'ofertas.php' : 'category.php'), strtolower($response['headers']));
        return json_decode($this->http('/__state', [], 'GET')['body'], true);
    }

    public function testAdministratorCanCreateEditAndDeleteUnusedCategory(): void
    {
        $before = $this->snapshot();
        $created = $this->operation('category_add', ['add' => 1, 'name' => 'Repuestos']);
        $this->assertArrayHasKey('success', $created);
        $row = $this->connection->query("SELECT * FROM category WHERE name='Repuestos'")->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($row);
        $this->assertSame('Repuestos', $row['cat_slug']);
        $edited = $this->operation('category_edit', ['edit' => 1, 'id' => $row['id'], 'name' => 'Accesorios']);
        $this->assertArrayHasKey('success', $edited);
        $updated = $this->connection->query('SELECT * FROM category WHERE id=' . (int) $row['id'])->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('Accesorios', $updated['name']);
        $this->assertSame('Accesorios', $updated['cat_slug']);
        $deleted = $this->operation('category_delete', ['delete' => 1, 'id' => $row['id']]);
        $this->assertArrayHasKey('success', $deleted);
        $this->assertSame($before, $this->snapshot());
    }

    public function testDuplicateCategoryNameDoesNotCreateAnotherCategory(): void
    {
        $before = $this->snapshot();
        $result = $this->operation('category_add', ['add' => 1, 'name' => 'Original category']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
    }

    public function testCategoryContainingProductsCannotBeDeleted(): void
    {
        $before = $this->snapshot();
        $result = $this->operation('category_delete', ['delete' => 1, 'id' => 1]);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
    }

    public function testEditingCategoryCannotUseAnotherCategorysName(): void
    {
        $this->connection->exec("INSERT INTO category VALUES (2,'Other category','other')");
        $before = $this->snapshot();
        $result = $this->operation('category_edit', ['edit' => 1, 'id' => 1, 'name' => 'Other category']);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('success', $result);
        $this->assertSame($before, $this->snapshot());
    }

    public function testUnchangedCategoryEditSucceedsAndPreservesOtherData(): void
    {
        $this->operation('category_edit', ['edit' => 1, 'id' => 1, 'name' => 'Updated category']);
        $before = $this->snapshot();
        $result = $this->operation('category_edit', ['edit' => 1, 'id' => 1, 'name' => 'Updated category']);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
    }

    /** @dataProvider invalidCategoryRequests */
    public function testInvalidCategoryRequestLeavesAllDataUntouched(string $route, array $post): void
    {
        $before = $this->snapshot();
        $result = $this->operation($route, $post);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
    }

    public function invalidCategoryRequests(): array
    {
        return [
            'blank new name' => ['category_add', ['add' => 1, 'name' => '   ']],
            'missing new name' => ['category_add', ['add' => 1]],
            'array new name' => ['category_add', ['add' => 1, 'name' => ['invalid']]],
            'long new name' => ['category_add', ['add' => 1, 'name' => str_repeat('x', 101)]],
            'blank edited name' => ['category_edit', ['edit' => 1, 'id' => 1, 'name' => '']],
            'missing edited id' => ['category_edit', ['edit' => 1, 'name' => 'Changed']],
            'array edited id' => ['category_edit', ['edit' => 1, 'id' => ['1'], 'name' => 'Changed']],
            'unknown edited id' => ['category_edit', ['edit' => 1, 'id' => 999, 'name' => 'Changed']],
            'missing deleted id' => ['category_delete', ['delete' => 1]],
            'array deleted id' => ['category_delete', ['delete' => 1, 'id' => ['1']]],
            'zero deleted id' => ['category_delete', ['delete' => 1, 'id' => 0]],
            'unknown deleted id' => ['category_delete', ['delete' => 1, 'id' => 999]],
        ];
    }

    public function testRemovingDiscountChangesOnlySelectedProductsDiscount(): void
    {
        $before = $this->snapshot();
        $result = $this->operation('quitar_descuento', ['id' => 1]);
        $this->assertArrayHasKey('success', $result);
        $expected = $before;
        $expected['products'][0]['descuento'] = '0';
        $this->assertEquals($expected, $this->snapshot());
        $this->operation('quitar_descuento', ['id' => 1]);
        $this->assertEquals($expected, $this->snapshot());
    }

    /** @dataProvider invalidDiscountIdentifiers */
    public function testInvalidDiscountIdentifierCannotChangeProducts(array $post): void
    {
        $before = $this->snapshot();
        $this->operation('quitar_descuento', $post);
        $this->assertSame($before, $this->snapshot());
    }

    public function invalidDiscountIdentifiers(): array
    {
        return ['missing' => [[]], 'array' => [['id' => ['1']]], 'zero' => [['id' => 0]], 'unknown' => [['id' => 999]]];
    }
}
