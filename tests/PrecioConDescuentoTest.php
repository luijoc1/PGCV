<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class PrecioConDescuentoTest extends TestCase
{
    public function test_precio_sin_descuento(): void
    {
        $resultado = precioConDescuento(100, 0);

        $this->assertEquals(100, $resultado);
    }

    public function test_descuento_10_por_ciento(): void
    {
        $resultado = precioConDescuento(100, 10);

        $this->assertEquals(90, $resultado);
    }

    public function test_descuento_50_por_ciento(): void
    {
        $resultado = precioConDescuento(200, 50);

        $this->assertEquals(100, $resultado);
    }

    public function test_descuento_100_por_ciento(): void
    {
        $resultado = precioConDescuento(500, 100);

        $this->assertEquals(0, $resultado);
    }

    public function test_precio_decimal(): void
    {
        $resultado = precioConDescuento(99.99, 20);

        $this->assertEqualsWithDelta(
            79.992,
            $resultado,
            0.001
        );
    }
}