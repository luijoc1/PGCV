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
            79.99,
            $resultado,
            0.001
        );
    }

    /** @dataProvider roundingCases */
    public function testCurrencyRounding($price, $discount, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, precioConDescuento($price, $discount), 0.000001);
    }

    public function roundingCases(): array
    {
        return [
            'price rounded without discount' => [19.995, 0, 20.0],
            'price rounded before discount' => [99.995, 20, 80.0],
            'discount rounded to two decimals' => [100, 12.345, 87.65],
            'fractional discount' => [99.99, 12.5, 87.49],
            'half cent rounded up' => [0.05, 10, 0.05],
            'zero price' => [0, 25, 0.0],
            'database numeric strings' => ['230000.00', '10.00', 207000.0],
        ];
    }
}
