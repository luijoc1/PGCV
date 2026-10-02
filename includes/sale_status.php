<?php

function updateSaleStatus(PDO $conn, $id, $status)
{
    $saleId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
    if ($saleId === false || !in_array($status, ['pendiente', 'en_proceso', 'enviado', 'entregado'], true)) {
        throw new InvalidArgumentException('Venta o estado inválido.');
    }

    // sales_date representa la compra; el cambio de estado tiene su fecha en logs_ventas.
    $stmt = $conn->prepare('UPDATE sales SET estado=:estado WHERE id=:id');
    $stmt->execute(['estado' => $status, 'id' => $saleId]);
}
