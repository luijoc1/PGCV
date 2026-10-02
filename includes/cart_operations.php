<?php

function cartPositiveInteger($value)
{
    if ((!is_int($value) && !is_string($value)) || !preg_match('/\A[1-9][0-9]*\z/', (string) $value)) {
        return null;
    }
    $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
    return $number === false ? null : $number;
}

function updateOwnedCart(PDO $conn, $userId, $cartId, $quantity)
{
    $id = cartPositiveInteger($cartId);
    $qty = cartPositiveInteger($quantity);
    $owner = cartPositiveInteger($userId);
    if ($id === null || $qty === null || $owner === null) {
        throw new InvalidArgumentException('El artículo y la cantidad deben ser enteros positivos.');
    }
    $stmt = $conn->prepare('SELECT cart.quantity, products.stock FROM cart JOIN products ON products.id=cart.product_id WHERE cart.id=:id AND cart.user_id=:user_id');
    $stmt->execute(['id' => $id, 'user_id' => $owner]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        throw new InvalidArgumentException('Artículo no encontrado en tu carrito.');
    }
    if ($qty > $item['stock']) {
        throw new InvalidArgumentException('La cantidad solicitada excede el stock disponible.');
    }
    $stmt = $conn->prepare('UPDATE cart SET quantity=:quantity WHERE id=:id AND user_id=:user_id');
    $stmt->execute(['quantity' => $qty, 'id' => $id, 'user_id' => $owner]);
}

function deleteOwnedCart(PDO $conn, $userId, $cartId)
{
    $id = cartPositiveInteger($cartId);
    $owner = cartPositiveInteger($userId);
    if ($id === null || $owner === null) {
        throw new InvalidArgumentException('El artículo es inválido.');
    }
    $stmt = $conn->prepare('DELETE FROM cart WHERE id=:id AND user_id=:user_id');
    $stmt->execute(['id' => $id, 'user_id' => $owner]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException('Artículo no encontrado en tu carrito.');
    }
}
