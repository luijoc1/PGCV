<?php
require_once __DIR__ . '/cart_operations.php';

function productCategoryId($value)
{
    if ($value === null || $value === 0 || $value === '0') {
        return 0;
    }
    $id = cartPositiveInteger($value);
    if ($id === null) {
        throw new InvalidArgumentException('La categoría seleccionada es inválida.');
    }
    return $id;
}

function productListQuery(PDO $conn, $categoryId)
{
    $id = productCategoryId($categoryId);
    if ($id === 0) {
        $stmt = $conn->prepare('SELECT * FROM products');
        $stmt->execute();
    } else {
        $stmt = $conn->prepare('SELECT * FROM products WHERE category_id=:category_id');
        $stmt->execute(['category_id' => $id]);
    }
    return $stmt;
}
