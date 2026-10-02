<?php

function precioConDescuento($precio, $descuento)
{
    if ($descuento > 0) {
        return round(round($precio, 2) * (1 - round($descuento, 2) / 100), 2);
    }
    return round($precio, 2);
}
