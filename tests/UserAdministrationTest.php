<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class UserAdministrationTest extends BaseHttpTestCase
{
    private $csrf;
    protected function setUp(): void
    {
        parent::setUp();
        $login = $this->http('/__session', ['id' => 2]);
        $this->csrf = json_decode($login['body'], true)['csrf_token'];
    }

    private function fields(): array
    {
        return ['firstname' => 'Ana', 'lastname' => 'Pérez', 'email' => 'ana@example.com', 'password' => 'secret123', 'address' => 'Calle 1', 'contact' => '3001234567'];
    }

    private function operation(string $action, array $fields): array
    {
        $response = $this->http('/admin/users_' . $action . '.php', array_replace($fields, [$action => 1, 'csrf_token' => $this->csrf]));
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: users.php', strtolower($response['headers']));
        return json_decode($this->http('/__state', [], 'GET')['body'], true);
    }

    private function logs(): array
    {
        return $this->connection->query('SELECT * FROM logs_usuarios ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testDeletionRejectsReferencedUsersWithoutChangingData(): void
    {
        foreach (['cart', 'sales', 'checkout_requests'] as $table) {
            if ($table === 'cart') $this->connection->exec('INSERT INTO cart VALUES (90,1,1,1)');
            elseif ($table === 'sales') $this->connection->exec('INSERT INTO sales VALUES (90,1)');
            else $this->connection->exec("INSERT INTO checkout_requests VALUES ('reference',1,NULL)");
            $before = $this->snapshot();
            $logs = $this->logs();
            $result = $this->operation('delete', ['id' => 1]);
            $this->assertArrayHasKey('error', $result);
            $this->assertStringContainsString('asociadas', $result['error']);
            $this->assertSame($before, $this->snapshot());
            $this->assertSame($logs, $this->logs());
            $this->connection->exec('DELETE FROM ' . $table);
        }
    }

    public function testCreateEditActivateDeleteUserAndRecordSafeAuditData(): void
    {
        $before = $this->snapshot();
        $result = $this->operation('add', array_replace($this->fields(), ['type' => 1]));
        $this->assertArrayHasKey('success', $result);
        $user = $this->connection->query("SELECT * FROM users WHERE email='ana@example.com'")->fetch(PDO::FETCH_ASSOC);
        $this->assertTrue(password_verify('secret123', $user['password']));
        $this->assertEquals(0, $user['type']);
        $this->assertEquals(1, $user['status']);
        $edited = $this->operation('edit', array_replace($this->fields(), ['id' => $user['id'], 'password' => '', 'firstname' => 'Andrea']));
        $this->assertArrayHasKey('success', $edited);
        $this->assertSame($user['password'], $this->connection->query('SELECT password FROM users WHERE id=' . (int) $user['id'])->fetchColumn());
        $this->operation('edit', array_replace($this->fields(), ['id' => $user['id'], 'password' => 'changed123']));
        $changed = $this->connection->query('SELECT password FROM users WHERE id=' . (int) $user['id'])->fetchColumn();
        $this->assertTrue(password_verify('changed123', $changed));
        $this->connection->exec('UPDATE users SET status=0 WHERE id=' . (int) $user['id']);
        $activated = $this->operation('activate', ['id' => $user['id']]);
        $this->assertArrayHasKey('success', $activated);
        $this->assertEquals(1, $this->connection->query('SELECT status FROM users WHERE id=' . (int) $user['id'])->fetchColumn());
        $logs = $this->logs();
        $this->operation('activate', ['id' => $user['id']]);
        $this->assertSame($logs, $this->logs());
        $deleted = $this->operation('delete', ['id' => $user['id']]);
        $this->assertArrayHasKey('success', $deleted);
        $this->assertSame($before, $this->snapshot());
        $logs = $this->logs();
        $this->assertSame(['INSERT', 'UPDATE', 'UPDATE', 'UPDATE', 'DELETE'], array_column($logs, 'tipo_operacion'));
        foreach ($logs as $log) {
            $this->assertSame('admin@example.com', $log['usuario_created']);
            foreach (['informacion_anterior', 'nueva_informacion'] as $field) {
                $data = json_decode($log[$field] ?? 'null', true);
                if ($data) {
                    $this->assertArrayNotHasKey('password', $data);
                    $this->assertArrayNotHasKey('reset_token_hash', $data);
                }
            }
        }
        $this->assertStringNotContainsString($changed, json_encode($logs));
    }

    /** @dataProvider invalidRequests */
    public function testInvalidRequestDoesNotChangeUsersOrWriteLogs(string $action, array $changes): void
    {
        $before = $this->snapshot();
        $result = $this->operation($action, array_replace($this->fields(), ['id' => 1], $changes));
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('success', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], $this->logs());
    }

    public function invalidRequests(): array
    {
        return [
            'missing name' => ['add', ['firstname' => null]],
            'array name' => ['add', ['firstname' => ['bad']]],
            'invalid email' => ['add', ['email' => 'invalid']],
            'duplicate email' => ['add', ['email' => 'client@example.com']],
            'short password' => ['add', ['password' => 'short']],
            'array password' => ['edit', ['password' => ['bad']]],
            'duplicate edited email' => ['edit', ['email' => 'admin@example.com']],
            'unknown edited user' => ['edit', ['id' => 999]],
            'unknown activation' => ['activate', ['id' => 999]],
            'unknown deletion' => ['delete', ['id' => 999]],
            'array identifier' => ['delete', ['id' => ['1']]],
        ];
    }

    /** @dataProvider writeActions */
    public function testAuditFailureRollsBackUserOperation(string $action): void
    {
        if ($action === 'activate') $this->connection->exec('UPDATE users SET status=0 WHERE id=1');
        $before = $this->snapshot();
        $this->connection->exec('DROP TABLE logs_usuarios');
        $result = $this->operation($action, array_replace($this->fields(), ['id' => 1]));
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
    }

    public function writeActions(): array
    {
        return ['add' => ['add'], 'edit' => ['edit'], 'activate' => ['activate'], 'delete' => ['delete']];
    }

    public function testAjaxUserResponseDoesNotExposePasswordOrRecoverySecrets(): void
    {
        $response = $this->http('/admin/users_row.php', ['id' => 1]);
        $this->assertSame(200, $response['status']);
        $account = json_decode($response['body'], true);
        $this->assertSame('client@example.com', $account['email']);
        foreach (['password', 'activate_code', 'activate_expires_at', 'reset_code', 'reset_token_hash', 'reset_expires_at'] as $field) $this->assertArrayNotHasKey($field, $account);
    }
}
