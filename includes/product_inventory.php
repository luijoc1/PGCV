<?php

function productCatalogIdentity(array $input, bool $editing = false): array
{
    $name = $input['name'] ?? null;
    if (!is_string($name) || trim($name) === '' || mb_strlen(trim($name), 'UTF-8') > 200) {
        throw new InvalidArgumentException('Ingresa un nombre de producto de hasta 200 caracteres.');
    }
    $result = ['name' => trim($name)];
    foreach ($editing ? ['category', 'id'] : ['category'] as $field) {
        $value = $input[$field] ?? null;
        if ((!is_string($value) && !is_int($value)) || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false) {
            throw new InvalidArgumentException('Selecciona un producto y una categoría válidos.');
        }
        $result[$field] = (int) $value;
    }
    if (isset($input['description']) && !is_string($input['description'])) {
        throw new InvalidArgumentException('La descripción del producto debe ser texto.');
    }
    return $result;
}

function requireProductCategory(PDO $conn, int $category): void
{
    $stmt = $conn->prepare('SELECT id FROM category WHERE id=:id');
    $stmt->execute(['id' => $category]);
    if ($stmt->fetchColumn() === false) {
        throw new InvalidArgumentException('La categoría seleccionada no existe.');
    }
}

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
