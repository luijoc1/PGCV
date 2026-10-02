<?php
use PHPUnit\Framework\TestCase;

abstract class BaseControllerTestCase extends TestCase
{
    protected $sandbox;
    protected $connection;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/pgcv_registration_' . bin2hex(random_bytes(8));
        mkdir($this->sandbox);
        mkdir($this->sandbox . '/includes');
        mkdir($this->sandbox . '/vendor');
        foreach (['session', 'config', 'mailer', 'header', 'navbar', 'sidebar', 'footer', 'scripts'] as $name) {
            file_put_contents($this->sandbox . '/includes/' . $name . '.php', '<?php');
        }
        file_put_contents($this->sandbox . '/vendor/autoload.php', '<?php');
        // Execute exact copies of the real controllers; replace only their infrastructure.
        foreach (['registro.php', 'activate.php', 'restablecer.php', 'password_nueva.php'] as $route) {
            copy(__DIR__ . '/../' . $route, $this->sandbox . '/' . $route);
        }
        copy(__DIR__ . '/../includes/password_reset.php', $this->sandbox . '/includes/password_reset.php');
        $this->connection = new PDO('sqlite:' . $this->sandbox . '/database.sqlite');
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE, password TEXT, firstname TEXT, lastname TEXT, address TEXT DEFAULT "", contact_info TEXT DEFAULT "", photo TEXT DEFAULT "", activate_code TEXT, activate_expires_at TEXT, created_on TEXT, type INTEGER DEFAULT 0, status INTEGER DEFAULT 0, reset_code TEXT DEFAULT "", reset_token_hash TEXT, reset_expires_at TEXT)');
    }

    protected function tearDown(): void
    {
        $this->connection = null;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->sandbox, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) {
            if ($entry->isDir()) { rmdir($entry->getPathname()); }
            else { unlink($entry->getPathname()); }
        }
        rmdir($this->sandbox);
    }

    protected function request(string $route, array $post = [], array $get = [], bool $mailFailure = false, string $method = 'POST'): array
    {
        $input = $this->sandbox . '/input.json';
        $log = $this->sandbox . '/php-errors.log';
        file_put_contents($log, '');
        file_put_contents($input, json_encode(['post' => $post, 'get' => $get, 'method' => $method, 'mail_failure' => $mailFailure, 'database' => $this->sandbox . '/database.sqlite', 'sandbox' => $this->sandbox, 'project' => dirname(__DIR__), 'route' => $route]));
        $process = proc_open([PHP_BINARY, '-d', 'error_log=' . $log, __DIR__ . '/fixtures/registration_runtime.php', $input], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        $loggedErrors = file_get_contents($log);
        $this->assertSame(0, $exit, $errors . $loggedErrors);
        $this->assertSame('', $errors);
        $result = json_decode($output, true);
        $this->assertIsArray($result, $output);
        if (!$mailFailure) { $this->assertSame('', $loggedErrors); }
        $result['log'] = $loggedErrors;
        return $result;
    }

}
