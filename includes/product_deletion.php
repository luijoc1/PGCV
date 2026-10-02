<?php

require_once __DIR__ . '/cart_operations.php';

function deleteUnreferencedProduct(PDO $conn, $value)
{
    $id = cartPositiveInteger($value);
    if ($id === null) {
        throw new InvalidArgumentException('El producto seleccionado es inválido.');
    }

    // La comprobación forma parte del DELETE: no borrar primero sus referencias.
    $stmt = $conn->prepare('DELETE FROM products WHERE id = :id
        AND NOT EXISTS (SELECT 1 FROM details WHERE product_id = products.id)
        AND NOT EXISTS (SELECT 1 FROM cart WHERE product_id = products.id)');
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException('No se eliminó el producto: no existe o está vinculado a ventas o carritos.');
    }
}
