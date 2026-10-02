<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class ProtectedRoutesTest extends BaseHttpTestCase
{
    /** @dataProvider routes */
    public function testVisitorCannotReadOrModifyAdministrativeData(string $route, array $post): void
    {
        $before = $this->snapshot();
        $response = $this->http('/' . $route, $post);
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: ../index.php', strtolower($response['headers']));
        $this->assertSame($before, $this->snapshot());
        $this->assertStringNotContainsString('Original product', $response['body']);
    }

    /** @dataProvider routes */
    public function testCustomerWithValidCsrfStillCannotAccessAdministrativeRoutes(string $route, array $post): void
    {
        $session = $this->http('/__session', ['id' => 1]);
        $this->assertSame(200, $session['status']);
        $csrf = json_decode($session['body'], true)['csrf_token'];
        $before = $this->snapshot();
        $response = $this->http('/' . $route, array_replace($post, ['csrf_token' => $csrf]));
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: ../index.php', strtolower($response['headers']));
        $this->assertSame($before, $this->snapshot());
        $this->assertStringNotContainsString('Original product', $response['body']);
    }

    /** @dataProvider invalidAdministratorSessions */
    public function testInvalidAdministratorSessionIsRejectedAndCannotDeleteUsers(array $session, string $mutation): void
    {
        $login = $this->http('/__session', $session);
        $this->assertSame(200, $login['status']);
        if ($mutation !== '') { $this->connection->exec($mutation); }
        $before = $this->snapshot();
        $response = $this->http('/admin/users_delete.php', ['delete' => 1, 'id' => 1]);
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: ../login.php', strtolower($response['headers']));
        $this->assertSame($before, $this->snapshot());
        $again = $this->http('/admin/users_delete.php', ['delete' => 1, 'id' => 1]);
        $this->assertSame(302, $again['status']);
        $this->assertStringContainsString('location: ../index.php', strtolower($again['headers']));
    }

    public function invalidAdministratorSessions(): array
    {
        return [
            'customer forged as administrator' => [['id' => 1, 'forge_admin' => 1], ''],
            'wrong signature' => [['id' => 2, 'forge_signature' => 1], ''],
            'disabled administrator' => [['id' => 2], 'UPDATE users SET status=0 WHERE id=2'],
            'changed role' => [['id' => 2], 'UPDATE users SET type=0 WHERE id=2'],
            'changed password' => [['id' => 2], "UPDATE users SET password='new-hash' WHERE id=2"],
            'deleted administrator' => [['id' => 2], 'DELETE FROM users WHERE id=2'],
        ];
    }

    public function testValidAdministratorCanReadProductAndMustUseCsrfForWrites(): void
    {
        $session = $this->http('/__session', ['id' => 2]);
        $this->assertSame(200, $session['status']);
        $before = $this->snapshot();
        $read = $this->http('/admin/products_row.php', ['id' => 1]);
        $this->assertSame(200, $read['status']);
        $this->assertSame('Original product', json_decode($read['body'], true)['prodname']);
        $missing = $this->http('/admin/category_delete.php', ['delete' => 1, 'id' => 1]);
        $this->assertSame(403, $missing['status']);
        $wrong = $this->http('/admin/category_delete.php', ['delete' => 1, 'id' => 1, 'csrf_token' => 'wrong']);
        $this->assertSame(403, $wrong['status']);
        $get = $this->http('/admin/category_delete.php', [], 'GET');
        $this->assertSame(405, $get['status']);
        $this->assertStringContainsString('Allow: POST', $get['headers']);
        $this->assertSame($before, $this->snapshot());
    }
}
