<?php
use PHPUnit\Framework\TestCase;
if (!defined('MAIL_USER')) define('MAIL_USER', 'sender@example.com');
if (!defined('MAIL_PASS')) define('MAIL_PASS', 'not-used-by-tests');
require_once __DIR__ . '/../includes/stock_alert.php';
require_once __DIR__ . '/../includes/contact_mail.php';

final class MailDeliveryTest extends TestCase
{
    private function database($withStock = true): PDO
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec('CREATE TABLE mail_daily_delivery (kind TEXT,delivery_day TEXT,sent_at TEXT,PRIMARY KEY(kind,delivery_day))');
        $conn->exec('CREATE TABLE products (id INTEGER,name TEXT,stock INTEGER,stock_minimo INTEGER)');
        $conn->exec('CREATE TABLE login_attempts (bucket_key TEXT PRIMARY KEY,attempts INTEGER,window_started INTEGER)');
        if ($withStock) $conn->exec("INSERT INTO products VALUES (1,'<img src=x onerror=alert(1)>',1,5),(2,'Normal',10,5)");
        return $conn;
    }

    public function testMailerEnforcesTlsIdentityVerificationAndTimeouts(): void
    {
        $mail = configuredMailer();
        $this->assertSame('smtp', $mail->Mailer);
        $this->assertTrue($mail->SMTPAuth);
        $this->assertContains($mail->SMTPSecure, ['ssl', 'tls']);
        $this->assertTrue($mail->SMTPOptions['ssl']['verify_peer']);
        $this->assertTrue($mail->SMTPOptions['ssl']['verify_peer_name']);
        $this->assertFalse($mail->SMTPOptions['ssl']['allow_self_signed']);
        $this->assertSame(8, $mail->Timeout);
        $this->assertSame(15, $mail->getSMTPInstance()->Timelimit);
        $this->assertSame('UTF-8', $mail->CharSet);
        $this->assertSame('sender@example.com', $mail->From);
    }

    public function testMailLinksUseConfiguredBaseAndEncodeQuery(): void
    {
        $this->assertSame('https://example.com/store/password_restablecer.php?code=a%26b&user=1', applicationUrl('password_restablecer.php', ['code' => 'a&b', 'user' => 1], 'https://example.com/store/'));
        foreach (['javascript:alert(1)', 'https://user:pass@example.com', 'https://example.com/?redirect=x', "https://example.com\r\nInjected"] as $base) {
            try {
                applicationUrl('index.php', [], $base);
                $this->fail('Debe rechazarse la URL base inválida.');
            } catch (InvalidArgumentException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }

    public function testDailyAlertIsSharedAcrossSessionsAndEscapesProducts(): void
    {
        $conn = $this->database();
        $calls = 0;
        $transport = function ($recipient, $subject, $body) use (&$calls) {
            $calls++;
            $this->assertSame('sender@example.com', $recipient);
            $this->assertStringContainsString('&lt;img', $body);
            $this->assertStringNotContainsString('<img', $body);
            $this->assertStringNotContainsString('Normal', $body);
            return true;
        };
        $this->assertSame('sent', sendDailyStockAlert($conn, $transport, '2026-10-01'));
        $_SESSION = [];
        $this->assertSame('already_sent', sendDailyStockAlert($conn, $transport, '2026-10-01'));
        $this->assertSame(1, $calls);
        $this->assertSame('sent', sendDailyStockAlert($conn, $transport, '2026-10-02'));
        $this->assertSame(2, $calls);
    }

    public function testFailedTransportDoesNotMarkSentAndCanRetry(): void
    {
        $conn = $this->database();
        try {
            sendDailyStockAlert($conn, function () { return false; }, '2026-10-01');
            $this->fail('Debe reportarse el fallo de envío.');
        } catch (RuntimeException $e) {
            $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM mail_daily_delivery')->fetchColumn());
        }
        $this->assertSame('sent', sendDailyStockAlert($conn, function () { return true; }, '2026-10-01'));
        $this->assertNotNull($conn->query('SELECT sent_at FROM mail_daily_delivery')->fetchColumn());
    }

    public function testNormalStockSendsNothing(): void
    {
        $conn = $this->database(false);
        $this->assertSame('empty', sendDailyStockAlert($conn, function () { $this->fail('No debe enviarse correo.'); }, '2026-10-01'));
        $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM mail_daily_delivery')->fetchColumn());
    }

    public function testContactRejectsHeaderInjectionAndInvalidFields(): void
    {
        $input = ['name' => 'Test', 'mail' => 'test@example.com', 'subject' => 'Question', 'message' => "Hello\nWorld"];
        $this->assertSame($input, contactMessage($input));
        foreach ([['mail', "test@example.com\r\nBcc: victim@example.com"], ['subject', "Question\r\nInjected"], ['name', []], ['message', str_repeat('x', 5001)], ['message', ''], ['mail', 'invalid']] as $case) {
            $bad = $input;
            $bad[$case[0]] = $case[1];
            try { contactMessage($bad); $this->fail('Debe rechazarse el contacto inválido.'); }
            catch (InvalidArgumentException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }

    public function testContactLimitIsPersistentAndSeparateFromLogin(): void
    {
        $conn = $this->database();
        for ($i = 0; $i < 5; $i++) $this->assertSame(0, reserveContactAttempt($conn, '192.0.2.1', 1000));
        $this->assertSame(900, reserveContactAttempt($conn, '192.0.2.1', 1000));
        $this->assertSame(0, reserveLoginAttempt($conn, 'test@example.com', '192.0.2.1', 1000));
        $this->assertSame(0, reserveContactAttempt($conn, '192.0.2.1', 1900));
    }
}
