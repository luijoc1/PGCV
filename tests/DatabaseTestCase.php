<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/conn.php';

abstract class DatabaseTestCase extends TestCase
{
    protected ?PDO $conn = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            //   session_start();
        }

        $dsn = getenv('PGCV_TEST_DSN');
        if (!$dsn) {
            $this->markTestSkipped('Esta prueba requiere PGCV_TEST_DSN para una base aislada.');
        }
        $this->conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $name = $this->conn->query('SELECT DATABASE()')->fetchColumn();
        if (!is_string($name) || !preg_match('/\Apgcv_(?:integration|integrity)_test_[a-f0-9]{16}\z/', $name)) {
            $this->fail('La conexión no apunta a una base de prueba autorizada.');
        }
    }

    protected function tearDown(): void
    {
        $this->conn = null;

        parent::tearDown();
    }

    public function test_database_object_exists(): void
    {
        $database = new Database();

        $this->assertInstanceOf(
            Database::class,
            $database
        );
    }
}
