<?php
require_once __DIR__ . '/BaseControllerTestCase.php';

final class RegistrationTest extends BaseControllerTestCase
{
    private function signup(): array
    {
        return ['signup' => '1', 'csrf_token' => str_repeat('a', 64), 'firstname' => 'Ana', 'lastname' => 'Pérez', 'email' => 'ana@example.com', 'password' => 'secret123', 'repassword' => 'secret123', 'aceptar_datos' => 'on'];
    }

    public function testSignupCreatesInactiveCustomerAndSendsMatchingActivationLink(): void
    {
        $post = $this->signup();
        $post['type'] = 1;
        $post['status'] = 1;
        $before = time();
        $result = $this->request('registro.php', $post);
        $this->assertCount(1, $result['users']);
        $user = $result['users'][0];
        $this->assertTrue(password_verify('secret123', $user['password']));
        $this->assertSame(0, (int) $user['type']);
        $this->assertSame(0, (int) $user['status']);
        $this->assertNotEmpty($user['activate_code']);
        $expires = strtotime($user['activate_expires_at'] . ' UTC');
        $this->assertGreaterThanOrEqual($before + 86400, $expires);
        $this->assertLessThanOrEqual(time() + 86400, $expires);
        $this->assertCount(1, $result['deliveries']);
        $this->assertSame('ana@example.com', $result['deliveries'][0]['recipient']);
        $this->assertStringContainsString('code=' . $user['activate_code'] . '&amp;user=' . $user['id'], $result['deliveries'][0]['body']);
        $this->assertArrayHasKey('success', $result['session']);
    }

    /** @dataProvider invalidSignupFields */
    public function testInvalidSignupDoesNotCreateAccountOrSendMail(string $field, $value): void
    {
        $post = $this->signup();
        if ($value === null) { unset($post[$field]); } else { $post[$field] = $value; }
        $result = $this->request('registro.php', $post);
        $this->assertSame([], $result['users']);
        $this->assertSame([], $result['deliveries']);
        $this->assertArrayHasKey('error', $result['session']);
    }

    public function invalidSignupFields(): array
    {
        return [
            'csrf' => ['csrf_token', 'invalid'],
            'missing csrf' => ['csrf_token', null],
            'first name' => ['firstname', '  '],
            'last name' => ['lastname', ''],
            'email' => ['email', 'invalid'],
            'short password' => ['password', 'short'],
            'password mismatch' => ['repassword', 'different'],
            'consent' => ['aceptar_datos', null],
            'missing name' => ['firstname', null],
            'array email' => ['email', []],
            'array password' => ['password', []],
            'array name' => ['firstname', []],
            'missing password' => ['password', null],
            'array confirmation' => ['repassword', []],
            'array csrf' => ['csrf_token', []],
            'email must not be silently repaired' => ['email', 'ana<>@example.com'],
        ];
    }

    public function testDuplicateEmailDoesNotCreateAnotherAccountOrSendAnotherMail(): void
    {
        $first = $this->request('registro.php', $this->signup());
        $second = $this->request('registro.php', $this->signup());
        $this->assertSame($first['users'], $second['users']);
        $this->assertSame([], $second['deliveries']);
        $this->assertArrayHasKey('error', $second['session']);
    }

    public function testMailFailureKeepsAccountInactiveAndDoesNotReportSuccess(): void
    {
        $result = $this->request('registro.php', $this->signup(), [], true);
        $this->assertCount(1, $result['users']);
        $this->assertSame(0, (int) $result['users'][0]['status']);
        $this->assertSame([], $result['deliveries']);
        $this->assertArrayHasKey('error', $result['session']);
        $this->assertArrayNotHasKey('success', $result['session']);
    }

    public function testMatchingActivationEnablesOnlyItsAccountAndRepeatIsHarmless(): void
    {
        $signup = $this->request('registro.php', $this->signup());
        $user = $signup['users'][0];
        $post = $this->signup();
        $post['email'] = 'other@example.com';
        $this->request('registro.php', $post);
        $wrongOwner = $this->request('activate.php', [], ['code' => $user['activate_code'], 'user' => 2], false, 'GET');
        $this->assertSame(0, (int) $wrongOwner['users'][0]['status']);
        $this->assertSame(0, (int) $wrongOwner['users'][1]['status']);
        $this->assertStringContainsString('alert-danger', $wrongOwner['html']);
        $query = ['code' => $user['activate_code'], 'user' => $user['id']];
        $first = $this->request('activate.php', [], $query, false, 'GET');
        $this->assertSame(1, (int) $first['users'][0]['status']);
        $this->assertSame(0, (int) $first['users'][1]['status']);
        $this->assertStringContainsString('Cuenta activada', $first['html']);
        $repeat = $this->request('activate.php', [], $query, false, 'GET');
        $this->assertSame($first['users'], $repeat['users']);
        $this->assertStringContainsString('Cuenta ya activada', $repeat['html']);
    }

    /** @dataProvider invalidActivationQueries */
    public function testInvalidActivationLeavesAccountUntouched(array $query): void
    {
        $before = $this->request('registro.php', $this->signup());
        $result = $this->request('activate.php', [], $query, false, 'GET');
        $this->assertSame($before['users'], $result['users']);
        $this->assertSame([], $result['deliveries']);
        $this->assertStringContainsString('alert-danger', $result['html']);
    }

    public function invalidActivationQueries(): array
    {
        return [
            'missing parameters' => [[]],
            'wrong code' => [['code' => 'wrong', 'user' => 1]],
            'empty code' => [['code' => '', 'user' => 1]],
            'missing user' => [['code' => 'wrong']],
            'array code' => [['code' => [], 'user' => 1]],
            'array user' => [['code' => 'wrong', 'user' => []]],
            'zero user' => [['code' => 'wrong', 'user' => 0]],
            'malformed user' => [['code' => 'wrong', 'user' => '1 OR 1']],
        ];
    }

    public function testGetRequestCannotRegisterAccount(): void
    {
        $result = $this->request('registro.php', $this->signup(), [], false, 'GET');
        $this->assertSame([], $result['users']);
        $this->assertSame([], $result['deliveries']);
        $this->assertArrayHasKey('error', $result['session']);
    }

    /** @dataProvider expiredActivationDates */
    public function testExpiredOrUndatedActivationCannotEnableAccount(?string $expiry): void
    {
        $signup = $this->request('registro.php', $this->signup());
        $user = $signup['users'][0];
        $stmt = $this->connection->prepare('UPDATE users SET activate_expires_at=? WHERE id=?');
        $stmt->execute([$expiry === 'now' ? gmdate('Y-m-d H:i:s') : $expiry, $user['id']]);
        $before = $this->connection->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->request('activate.php', [], ['code' => $user['activate_code'], 'user' => $user['id']], false, 'GET');
        $this->assertSame($before, $result['users']);
        $this->assertSame(0, (int) $result['users'][0]['status']);
        $this->assertSame([], $result['deliveries']);
        $this->assertStringContainsString('El enlace de activación venció', $result['html']);
        $this->assertStringNotContainsString('Cuenta activada - Email', $result['html']);
    }

    public function expiredActivationDates(): array
    {
        return ['past' => ['2000-01-01 00:00:00'], 'legacy without expiry' => [null], 'exact expiry' => ['now']];
    }
}
