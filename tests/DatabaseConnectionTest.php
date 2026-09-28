<?php

declare(strict_types=1);
 
require_once __DIR__ . '/DatabaseTestCase.php';

final class DatabaseConnectionTest extends DatabaseTestCase
{
    public function test_database_connection_is_available(): void
    {
        $this->assertInstanceOf(
            PDO::class,
            $this->conn
        );
    }
}