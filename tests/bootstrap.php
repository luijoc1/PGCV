<?php

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
}

if (!function_exists('precioConDescuento')) {
    function precioConDescuento($precio, $descuento)
    {
        if ($descuento > 0) {
            return $precio - ($precio * $descuento / 100);
        }

        return $precio;
    }
} 