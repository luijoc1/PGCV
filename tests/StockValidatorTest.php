<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../StockValidator.php';

class StockValidatorTest extends TestCase
{
    public function testProductoConStockMenorAlMinimoGeneraAlerta()
    {
        $productos = [
            [
                'id' => 1,
                'name' => 'Arroz',
                'stock' => 5,
                'stock_minimo' => 10
            ]
        ];

        $validator = new StockValidator();

        $resultado = $validator->validar($productos);

        $this->assertCount(1, $resultado['alerta']);
        $this->assertCount(0, $resultado['normal']);

        $this->assertEquals('Arroz', $resultado['alerta'][0]['name']);
        $this->assertEquals(5, $resultado['alerta'][0]['stock']);
        $this->assertEquals(10, $resultado['alerta'][0]['stock_minimo']);
    }


    public function testProductoConStockMayorAlMinimoEsNormal()
    {
        $productos = [
            [
                'id' => 2,
                'name' => 'Aceite',
                'stock' => 20,
                'stock_minimo' => 10
            ]
        ];

        $validator = new StockValidator();

        $resultado = $validator->validar($productos);

        $this->assertCount(0, $resultado['alerta']);
        $this->assertCount(1, $resultado['normal']);

        $this->assertEquals('Aceite', $resultado['normal'][0]['name']);
    }


    public function testStockIgualAlMinimoGeneraAlerta()
    {
        $productos = [
            [
                'id' => 3,
                'name' => 'Leche',
                'stock' => 10,
                'stock_minimo' => 10
            ]
        ];

        $validator = new StockValidator();

        $resultado = $validator->validar($productos);

        $this->assertCount(1, $resultado['alerta']);
        $this->assertCount(0, $resultado['normal']);
    }


    public function testVariosProductos()
    {
        $productos = [
            [
                'id' => 1,
                'name' => 'Arroz',
                'stock' => 5,
                'stock_minimo' => 10
            ],
            [
                'id' => 2,
                'name' => 'Aceite',
                'stock' => 20,
                'stock_minimo' => 10
            ],
            [
                'id' => 3,
                'name' => 'Leche',
                'stock' => 10,
                'stock_minimo' => 10
            ]
        ];

        $validator = new StockValidator();

        $resultado = $validator->validar($productos);

        $this->assertCount(2, $resultado['alerta']);
        $this->assertCount(1, $resultado['normal']);
    }


    public function testSinProductos()
    {
        $productos = [];

        $validator = new StockValidator();

        $resultado = $validator->validar($productos);

        $this->assertCount(0, $resultado['alerta']);
        $this->assertCount(0, $resultado['normal']);
    }
}