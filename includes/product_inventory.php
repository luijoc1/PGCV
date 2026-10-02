<?php

function productInventoryInput(array $input)
{
    $result = [];
    foreach (['stock' => 2147483647, 'stock_minimo' => 2147483647, 'descuento' => 100] as $field => $maximum) {
        $value = $input[$field] ?? ($field === 'descuento' ? '0' : null);
        if ((!is_string($value) && !is_int($value)) || !preg_match('/\A(?:0|[1-9][0-9]*)\z/', (string) $value)) throw new InvalidArgumentException('Stock, stock mínimo y descuento deben ser enteros no negativos.');
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $maximum]]);
        if ($number === false) throw new InvalidArgumentException('Revisa los límites de stock y descuento.');
        $result[$field] = $number;
    }
    $price = $input['price'] ?? null;
    if (!is_string($price) || !preg_match('/\A(?:0|[1-9][0-9]{0,13})(?:\.[0-9]{1,2})?\z/', $price)) throw new InvalidArgumentException('Ingresa un precio no negativo con hasta dos decimales.');
    $result['price'] = number_format((float) $price, 2, '.', '');
    return $result;
}

function insertCatalogProduct(PDO $conn, array $fields)
{
    $stmt = $conn->prepare('INSERT INTO products (category_id,name,description,slug,price,stock,stock_minimo,photo,descuento) VALUES (:category,:name,:description,:slug,:price,:stock,:stock_minimo,:photo,:descuento)');
    $stmt->execute($fields);
    return $conn->lastInsertId();
}

function updateCatalogProduct(PDO $conn, array $fields)
{
    $stmt = $conn->prepare('UPDATE products SET name=:name,slug=:slug,category_id=:category,price=:price,stock=:stock,stock_minimo=:stock_minimo,description=:description,descuento=:descuento WHERE id=:id');
    $stmt->execute($fields);
}
