<?php

class StockValidator
{
    /**
     * Separa los productos entre alerta y normales.
     *
     * @param array $productos
     * @return array
     */
    public function validar(array $productos)
    {
        $productos_alerta = [];
        $productos_normal = [];

        foreach ($productos as $producto) {

            if ($producto['stock'] <= $producto['stock_minimo']) {

                $productos_alerta[] = [
                    'id' => $producto['id'],
                    'name' => $producto['name'],
                    'stock' => $producto['stock'],
                    'stock_minimo' => $producto['stock_minimo']
                ];

            } else {

                $productos_normal[] = [
                    'id' => $producto['id'],
                    'name' => $producto['name'],
                    'stock' => $producto['stock'],
                    'stock_minimo' => $producto['stock_minimo']
                ];
            }
        }

        return [
            'alerta' => $productos_alerta,
            'normal' => $productos_normal
        ];
    }
}
