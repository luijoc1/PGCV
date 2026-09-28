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

        $database = new Database();

        $this->conn = $database->open();
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
