<?php
require_once __DIR__ . '/BaseControllerTestCase.php';

final class PasswordRecoveryFlowTest extends BaseControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $stmt = $this->connection->prepare('INSERT INTO users (id,email,password,status,type) VALUES (1,?, ?,1,0),(2,?, ?,1,0)');
        $hash = password_hash('original123', PASSWORD_DEFAULT);
        $stmt->execute(['ana@example.com', $hash, 'other@example.com', $hash]);
    }

    private function requestLink(): array
    {
        return ['reset' => '1', 'csrf_token' => str_repeat('a', 64), 'email' => 'ana@example.com'];
    }

    private function changePassword(): array
    {
        return ['reset' => '1', 'csrf_token' => str_repeat('a', 64), 'password' => 'new-secret123', 'repassword' => 'new-secret123'];
    }

    private function link(array $result): array
    {
        $body = html_entity_decode($result['deliveries'][0]['body'], ENT_QUOTES, 'UTF-8');
        $this->assertSame(1, preg_match('~href="(https://example.com/password_restablecer.php\?[^"]+)"~', $body, $matches));
        parse_str(parse_url($matches[1], PHP_URL_QUERY), $query);
        return $query;
    }

    public function testRecoveryEmailContainsUsableLinkAndOnlyItsHashIsStored(): void
    {
        $before = $this->connection->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->request('restablecer.php', $this->requestLink());
        $this->assertCount(1, $result['deliveries']);
        $this->assertSame('ana@example.com', $result['deliveries'][0]['recipient']);
        $link = $this->link($result);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $link['code']);
        $this->assertSame('1', $link['user']);
        $this->assertSame(hash('sha256', $link['code']), $result['users'][0]['reset_token_hash']);
        $this->assertSame('', $result['users'][0]['reset_code']);
        $this->assertSame($before[0]['password'], $result['users'][0]['password']);
        $this->assertSame($before[1], $result['users'][1]);
        $this->assertArrayHasKey('success', $result['session']);
    }

    /** @dataProvider invalidLinkRequests */
    public function testInvalidLinkRequestLeavesUsersUntouchedAndSendsNothing(array $changes, string $method): void
    {
        $before = $this->connection->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $post = array_replace($this->requestLink(), $changes);
        $result = $this->request('restablecer.php', $post, [], false, $method);
        $this->assertSame($before, $result['users']);
        $this->assertSame([], $result['deliveries']);
        $this->assertArrayHasKey('error', $result['session']);
        $this->assertArrayNotHasKey('success', $result['session']);
    }

    public function invalidLinkRequests(): array
    {
        return [
            'invalid csrf' => [['csrf_token' => 'wrong'], 'POST'],
            'missing csrf' => [['csrf_token' => null], 'POST'],
            'array csrf' => [['csrf_token' => []], 'POST'],
            'invalid email' => [['email' => 'invalid'], 'POST'],
            'missing email' => [['email' => null], 'POST'],
            'array email' => [['email' => []], 'POST'],
            'unknown email' => [['email' => 'missing@example.com'], 'POST'],
            'get' => [[], 'GET'],
            'missing action' => [['reset' => null], 'POST'],
        ];
    }

    public function testMailFailureDoesNotChangePasswordOrReportSuccessAndRetryWorks(): void
    {
        $before = $this->connection->query('SELECT password FROM users WHERE id=1')->fetchColumn();
        $failed = $this->request('restablecer.php', $this->requestLink(), [], true);
        $this->assertSame($before, $failed['users'][0]['password']);
        $this->assertSame([], $failed['deliveries']);
        $this->assertArrayHasKey('error', $failed['session']);
        $this->assertArrayNotHasKey('success', $failed['session']);
        $this->assertStringContainsString('Simulated mail failure', $failed['log']);
        $retry = $this->request('restablecer.php', $this->requestLink());
        $this->assertCount(1, $retry['deliveries']);
        $this->assertNotSame($failed['users'][0]['reset_token_hash'], $retry['users'][0]['reset_token_hash']);
        $changed = $this->request('password_nueva.php', $this->changePassword(), $this->link($retry));
        $this->assertTrue(password_verify('new-secret123', $changed['users'][0]['password']));
    }

    /** @dataProvider invalidPasswordSubmissions */
    public function testInvalidPasswordSubmissionPreservesPasswordAndLink(array $changes, string $method): void
    {
        $issued = $this->request('restablecer.php', $this->requestLink());
        $query = $this->link($issued);
        $post = array_replace($this->changePassword(), $changes);
        $rejected = $this->request('password_nueva.php', $post, $query, false, $method);
        $this->assertSame($issued['users'], $rejected['users']);
        $this->assertSame([], $rejected['deliveries']);
        $this->assertArrayHasKey('error', $rejected['session']);
        $this->assertArrayNotHasKey('success', $rejected['session']);
        $valid = $this->request('password_nueva.php', $this->changePassword(), $query);
        $this->assertTrue(password_verify('new-secret123', $valid['users'][0]['password']));
    }

    public function invalidPasswordSubmissions(): array
    {
        return [
            'csrf' => [['csrf_token' => 'wrong'], 'POST'],
            'missing csrf' => [['csrf_token' => null], 'POST'],
            'array csrf' => [['csrf_token' => []], 'POST'],
            'short password' => [['password' => 'short', 'repassword' => 'short'], 'POST'],
            'missing password' => [['password' => null], 'POST'],
            'array password' => [['password' => []], 'POST'],
            'array confirmation' => [['repassword' => []], 'POST'],
            'missing confirmation' => [['repassword' => null], 'POST'],
            'mismatch' => [['repassword' => 'different123'], 'POST'],
            'get' => [[], 'GET'],
            'missing action' => [['reset' => null], 'POST'],
        ];
    }

    public function testSuccessfulChangeConsumesLinkAndCannotBeReplayed(): void
    {
        $issued = $this->request('restablecer.php', $this->requestLink());
        $query = $this->link($issued);
        $changed = $this->request('password_nueva.php', $this->changePassword(), $query);
        $this->assertArrayHasKey('success', $changed['session']);
        $this->assertTrue(password_verify('new-secret123', $changed['users'][0]['password']));
        $this->assertNull($changed['users'][0]['reset_token_hash']);
        $this->assertNull($changed['users'][0]['reset_expires_at']);
        $this->assertSame($issued['users'][1], $changed['users'][1]);
        $replayed = $this->request('password_nueva.php', array_replace($this->changePassword(), ['password' => 'another123', 'repassword' => 'another123']), $query);
        $this->assertSame($changed['users'], $replayed['users']);
        $this->assertArrayHasKey('error', $replayed['session']);
    }

    public function testExpiredAndSupersededLinksCannotChangePassword(): void
    {
        $first = $this->request('restablecer.php', $this->requestLink());
        $firstQuery = $this->link($first);
        $second = $this->request('restablecer.php', $this->requestLink());
        $secondQuery = $this->link($second);
        $superseded = $this->request('password_nueva.php', $this->changePassword(), $firstQuery);
        $this->assertSame($second['users'], $superseded['users']);
        $this->assertArrayHasKey('error', $superseded['session']);
        $this->connection->exec("UPDATE users SET reset_expires_at='2000-01-01 00:00:00' WHERE id=1");
        $before = $this->connection->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $expired = $this->request('password_nueva.php', $this->changePassword(), $secondQuery);
        $this->assertSame($before, $expired['users']);
        $this->assertArrayHasKey('error', $expired['session']);
    }

    /** @dataProvider invalidLinkQueries */
    public function testMalformedOrWrongOwnerLinkCannotChangePassword(array $changes): void
    {
        $issued = $this->request('restablecer.php', $this->requestLink());
        $query = array_replace($this->link($issued), $changes);
        $result = $this->request('password_nueva.php', $this->changePassword(), $query);
        $this->assertSame($issued['users'], $result['users']);
        $this->assertArrayHasKey('error', $result['session']);
        $this->assertArrayNotHasKey('success', $result['session']);
    }

    public function invalidLinkQueries(): array
    {
        return [
            'wrong owner' => [['user' => '2']],
            'wrong token' => [['code' => str_repeat('b', 64)]],
            'missing code' => [['code' => null]],
            'array code' => [['code' => []]],
            'array user' => [['user' => []]],
            'missing user' => [['user' => null]],
        ];
    }
}
