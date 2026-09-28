<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class SessionFunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            //  session_start();
        }

        $_SESSION = [];
    }

    public function testGeneraTokenCSRF()
    {
        $token = generateCSRFToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));
    }

    public function testGenerateCSRFTokenDevuelveMismoTokenSiYaExiste()
    {
        $_SESSION['csrf_token'] = 'token123';

        $token = generateCSRFToken();

        $this->assertEquals('token123', $token);
    }

    public function testValidaTokenCorrecto()
    {
        $_SESSION['csrf_token'] = 'abc123';

        $this->assertTrue(
            validateCSRFToken('abc123')
        );
    }

    public function testValidaTokenIncorrecto()
    {
        $_SESSION['csrf_token'] = 'abc123';

        $this->assertFalse(
            validateCSRFToken('otro')
        );
    }

    public function testValidaTokenSinSesion()
    {
        unset($_SESSION['csrf_token']);

        $this->assertFalse(
            validateCSRFToken('abc123')
        );
    }

    public function testPrecioSinDescuento()
    {
        $this->assertEquals(
            100,
            precioConDescuento(100, 0)
        );
    }

    public function testPrecioCon10Porciento()
    {
        $this->assertEquals(
            90,
            precioConDescuento(100, 10)
        );
    }

    public function testPrecioCon50Porciento()
    {
        $this->assertEquals(
            50,
            precioConDescuento(100, 50)
        );
    }

    public function testPrecioCon100Porciento()
    {
        $this->assertEquals(
            0,
            precioConDescuento(100, 100)
        );
    }
}
