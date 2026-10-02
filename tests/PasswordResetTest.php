<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/password_reset.php';

final class PasswordResetTest extends TestCase
{
    public function testRejectsInvalidLinkParameters(): void
    {
        foreach ([null, '', ' ', [], 'code"', 'validCode', str_repeat('a', 63), str_repeat('g', 64)] as $code) {
            $this->assertNull(passwordResetParameters($code, '1'));
        }
        foreach ([null, '', '0', '-1', '1 OR 1', [], '99999999999999999999999'] as $id) {
            $this->assertNull(passwordResetParameters(str_repeat('a', 64), $id));
        }
        $this->assertSame(['code' => str_repeat('a', 64), 'user' => 1], passwordResetParameters(str_repeat('a', 64), '1'));
    }

    public function testTokenMustMatchAndCanOnlyBeUsedOnce(): void
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, password TEXT, reset_code TEXT, reset_token_hash TEXT, reset_expires_at TEXT)');
        $stmt = $conn->prepare('INSERT INTO users (id, password, reset_code) VALUES (?, ?, ?)');
        $original = password_hash('originalPassword', PASSWORD_DEFAULT);
        $stmt->execute([1, $original, '']);
        $stmt->execute([2, $original, 'validCode']);
        $code = issuePasswordReset($conn, 2);
        $this->assertFalse(consumePasswordReset($conn, '', 1, 'newPassword'));
        $this->assertFalse(consumePasswordReset($conn, $code, 1, 'newPassword'));
        $this->assertFalse(consumePasswordReset($conn, str_repeat('b', 64), 2, 'newPassword'));
        $this->assertSame($original, $conn->query('SELECT password FROM users WHERE id=1')->fetchColumn());
        $this->assertSame($original, $conn->query('SELECT password FROM users WHERE id=2')->fetchColumn());
        $this->assertTrue(consumePasswordReset($conn, $code, 2, 'newPassword'));
        $changed = $conn->query('SELECT password FROM users WHERE id=2')->fetchColumn();
        $this->assertTrue(password_verify('newPassword', $changed));
        $this->assertSame('', $conn->query('SELECT reset_code FROM users WHERE id=2')->fetchColumn());
        $this->assertNull($conn->query('SELECT reset_token_hash FROM users WHERE id=2')->fetchColumn());
        $this->assertNull($conn->query('SELECT reset_expires_at FROM users WHERE id=2')->fetchColumn());
        $this->assertFalse(consumePasswordReset($conn, $code, 2, 'anotherPassword'));
        $this->assertSame($changed, $conn->query('SELECT password FROM users WHERE id=2')->fetchColumn());
    }

    public function testStoresOnlyHashAndNewLinkInvalidatesPreviousLink(): void
    {
        $conn = $this->createDatabase();
        $before = time();
        $first = issuePasswordReset($conn, 1);
        $record = $conn->query('SELECT * FROM users WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $first);
        $this->assertSame(hash('sha256', $first), $record['reset_token_hash']);
        $this->assertNotSame($first, $record['reset_token_hash']);
        $this->assertSame('', $record['reset_code']);
        $expires = strtotime($record['reset_expires_at'] . ' UTC');
        $this->assertGreaterThanOrEqual($before + 3600, $expires);
        $this->assertLessThanOrEqual(time() + 3600, $expires);
        $second = issuePasswordReset($conn, 1);
        $this->assertNotSame($first, $second);
        $this->assertFalse(consumePasswordReset($conn, $first, 1, 'newPassword'));
        $this->assertTrue(consumePasswordReset($conn, $second, 1, 'newPassword'));
    }

    public function testExpiredMissingAndLegacyTokensCannotChangePassword(): void
    {
        $conn = $this->createDatabase();
        $original = $conn->query('SELECT password FROM users WHERE id=1')->fetchColumn();
        $this->assertFalse(consumePasswordReset($conn, 'legacyCode', 1, 'newPassword'));
        $this->assertFalse(consumePasswordReset($conn, str_repeat('a', 64), 1, 'newPassword'));
        $token = issuePasswordReset($conn, 1);
        $conn->exec("UPDATE users SET reset_expires_at='2000-01-01 00:00:00' WHERE id=1");
        $this->assertFalse(consumePasswordReset($conn, $token, 1, 'newPassword'));
        $conn->exec('UPDATE users SET reset_expires_at=NULL WHERE id=1');
        $this->assertFalse(consumePasswordReset($conn, $token, 1, 'newPassword'));
        $this->assertSame($original, $conn->query('SELECT password FROM users WHERE id=1')->fetchColumn());
    }

    private function createDatabase(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, password TEXT, reset_code TEXT, reset_token_hash TEXT, reset_expires_at TEXT)');
        $stmt = $conn->prepare('INSERT INTO users (id,password,reset_code) VALUES (1,?,?)');
        $stmt->execute([password_hash('originalPassword', PASSWORD_DEFAULT), 'legacyCode']);
        return $conn;
    }
}
