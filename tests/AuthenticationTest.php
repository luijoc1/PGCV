<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/authentication.php';
require_once __DIR__ . '/../includes/password_reset.php';

final class AuthenticationTest extends TestCase
{
    private function database(): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, password TEXT, status INTEGER, type INTEGER, reset_code TEXT, reset_token_hash TEXT, reset_expires_at TEXT)');
        $conn->exec('CREATE TABLE login_attempts (bucket_key TEXT PRIMARY KEY, attempts INTEGER, window_started INTEGER)');
        $stmt = $conn->prepare("INSERT INTO users VALUES (1,:hash,1,0,'',NULL,NULL),(2,:hash,1,1,'',NULL,NULL)");
        $stmt->execute(['hash' => password_hash('secret123', PASSWORD_DEFAULT)]);
        return $conn;
    }

    public function testGuardsRequireActiveAccountCorrectRoleAndPasswordSignature(): void
    {
        $conn = $this->database();
        $account = $conn->query('SELECT * FROM users WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        $session = ['user' => 1, 'auth_signature' => accountSessionSignature($account)];
        $this->assertSame($account, authenticatedAccount($conn, $session, 0));
        $this->assertNull(authenticatedAccount($conn, ['user' => 1], 0));
        $this->assertNull(authenticatedAccount($conn, ['admin' => 1, 'auth_signature' => $session['auth_signature']], 1));
        $this->assertNull(authenticatedAccount($conn, ['user' => [], 'auth_signature' => $session['auth_signature']], 0));
        $conn->exec('UPDATE users SET status=0 WHERE id=1');
        $this->assertNull(authenticatedAccount($conn, $session, 0));
        $conn->exec('UPDATE users SET status=1, type=1 WHERE id=1');
        $this->assertNull(authenticatedAccount($conn, $session, 0));
        $conn->exec('UPDATE users SET type=0 WHERE id=1');
        $code = issuePasswordReset($conn, 1);
        $this->assertTrue(consumePasswordReset($conn, $code, 1, 'changed123'));
        $this->assertNull(authenticatedAccount($conn, $session, 0));
        $admin = $conn->query('SELECT * FROM users WHERE id=2')->fetch(PDO::FETCH_ASSOC);
        $adminSession = ['admin' => 2, 'auth_signature' => accountSessionSignature($admin)];
        $this->assertSame($admin, authenticatedAccount($conn, $adminSession, 1));
        $conn->exec('DELETE FROM users WHERE id=2');
        $this->assertNull(authenticatedAccount($conn, $adminSession, 1));
    }

    public function testEmptyPasswordPreservesHashAndNewPasswordRevokesOldSignature(): void
    {
        $hash = password_hash('original123', PASSWORD_DEFAULT);
        $this->assertSame($hash, editedPasswordHash('', $hash));
        $updated = editedPasswordHash('newpass123', $hash);
        $this->assertTrue(password_verify('newpass123', $updated));
        $this->assertNotSame($hash, $updated);
        foreach ([[], null, 'short'] as $value) {
            try {
                editedPasswordHash($value, $hash);
                $this->fail('Debe rechazarse la nueva contraseña inválida.');
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    public function testPublicFieldsExcludePasswordAndRecoverySecrets(): void
    {
        $this->assertSame(['id' => 1, 'email' => 'test@example.com'], publicAccountFields([
            'id' => 1, 'email' => 'test@example.com', 'password' => 'secret', 'reset_code' => 'secret',
            'reset_token_hash' => 'secret', 'reset_expires_at' => 'secret', 'activate_code' => 'secret',
        ]));
    }

    public function testEmailLimitIsSharedAcrossBrowsersAndIpsAndExpires(): void
    {
        $conn = $this->database();
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame(0, reserveLoginAttempt($conn, 'Test@example.com', '192.0.2.' . $i, 1000));
        }
        $this->assertSame(900, reserveLoginAttempt($conn, 'test@example.com', '192.0.2.99', 1000));
        $this->assertSame(1, reserveLoginAttempt($conn, 'test@example.com', '192.0.2.99', 1899));
        $this->assertSame(0, reserveLoginAttempt($conn, 'test@example.com', '192.0.2.99', 1900));
        clearSuccessfulLoginAttempts($conn, 'test@example.com');
        $this->assertSame(0, reserveLoginAttempt($conn, 'test@example.com', '192.0.2.99', 1901));
    }

    public function testIpLimitSurvivesChangingEmailsAndSuccessfulAccountReset(): void
    {
        $conn = $this->database();
        for ($i = 0; $i < 30; $i++) {
            $email = 'test' . $i . '@example.com';
            $this->assertSame(0, reserveLoginAttempt($conn, $email, '192.0.2.1', 1000));
            clearSuccessfulLoginAttempts($conn, $email);
        }
        $this->assertSame(900, reserveLoginAttempt($conn, 'another@example.com', '192.0.2.1', 1000));
        $this->assertSame(0, reserveLoginAttempt($conn, 'another@example.com', '192.0.2.2', 1000));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoginRenewsSessionIdAndRemovesPreviousRoleAndCsrf(): void
    {
        $sessionDirectory = __DIR__ . '/session-test-' . bin2hex(random_bytes(8));
        mkdir($sessionDirectory);
        session_save_path($sessionDirectory);
        session_start();
        $old = session_id();
        $_SESSION = ['admin' => 99, 'csrf_token' => 'old', 'cart' => [['productid' => 1, 'quantity' => 2]]];
        establishAccountSession(['id' => 1, 'status' => 1, 'type' => 0, 'password' => 'test-hash']);
        $this->assertNotSame($old, session_id());
        $this->assertSame(1, $_SESSION['user']);
        $this->assertArrayNotHasKey('admin', $_SESSION);
        $this->assertArrayNotHasKey('csrf_token', $_SESSION);
        $this->assertCount(1, $_SESSION['cart']);
        $this->assertSame(hash('sha256', 'test-hash'), $_SESSION['auth_signature']);
        session_destroy();
        rmdir($sessionDirectory);
    }
}
