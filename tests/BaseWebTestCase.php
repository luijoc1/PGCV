<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class BaseWebTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            //    session_start();
        }
    }

    protected function loginAs(int $userId): void
    {
        $_SESSION['user'] = $userId;
    }

    protected function logout(): void
    {
        unset($_SESSION['user']);
    }
}
