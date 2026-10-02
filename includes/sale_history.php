<?php

function saleDetailColumns()
{
    return "details.*, COALESCE(details.product_name, products.name, 'Producto no disponible') AS name,
        COALESCE(details.original_price, products.price) AS price,
        COALESCE(details.discount_percent, products.descuento, 0) AS descuento,
        details.unit_price AS historical_unit_price";
}

function saleDetailUnitPrice(array $detail)
{
    if (isset($detail['historical_unit_price'])) {
        return (float) $detail['historical_unit_price'];
    }
    // Solo para ventas anteriores a la migración: estimación con el catálogo actual.
    return (float) ($detail['price'] ?? 0) * (1 - (float) ($detail['descuento'] ?? 0) / 100);
}

function saleSnapshot(array $product)
{
    $price = (float) $product['price'];
    $discount = (float) ($product['descuento'] ?? 0);
    if (!is_finite($price) || $price < 0 || $price >= 100000000000000 || !is_finite($discount) || $discount < 0 || $discount > 100) {
        throw new InvalidArgumentException('Precio o descuento inválido en el catálogo.');
    }
    $price = round($price, 2);
    $discount = round($discount, 2);
    return [
        'product_name' => $product['name'],
        'original_price' => number_format($price, 2, '.', ''),
        'discount_percent' => number_format($discount, 2, '.', ''),
        'unit_price' => number_format(round($price * (1 - $discount / 100), 2), 2, '.', ''),
    ];
}

function insertSaleDetail(PDO $conn, $saleId, array $product)
{
    $stmt = $conn->prepare('INSERT INTO details (sales_id, product_id, quantity, product_name, original_price, discount_percent, unit_price)
        VALUES (:sales_id, :product_id, :quantity, :product_name, :original_price, :discount_percent, :unit_price)');
    $stmt->execute(array_merge([
        'sales_id' => $saleId, 'product_id' => $product['product_id'], 'quantity' => $product['quantity'],
    ], saleSnapshot($product)));
}
