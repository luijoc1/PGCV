<?php

require_once __DIR__ . '/../includes/csrf.php';

if (!function_exists('precioConDescuento')) {
    function precioConDescuento($precio, $descuento)
    {
        if ($descuento > 0) {
            return $precio - ($precio * $descuento / 100);
        }

        return $precio;
    }
}
