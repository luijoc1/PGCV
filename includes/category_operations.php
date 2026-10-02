<?php

function categoryName($value): string
{
    if (!is_string($value) || trim($value) === '' || mb_strlen(trim($value), 'UTF-8') > 100) {
        throw new InvalidArgumentException('El nombre de la categoría es obligatorio y debe tener hasta 100 caracteres.');
    }
    return trim($value);
}

function categoryId($value): int
{
    if ((!is_string($value) && !is_int($value)) || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false) {
        throw new InvalidArgumentException('Seleccione una categoría válida.');
    }
    return (int) $value;
}

function addCategory(PDO $conn, $rawName): int
{
    $name = categoryName($rawName);
    $stmt = $conn->prepare('SELECT id FROM category WHERE name=:name LIMIT 1');
    $stmt->execute(['name' => $name]);
    if ($stmt->fetchColumn() !== false) {
        throw new InvalidArgumentException('La categoría ya existe.');
    }
    $stmt = $conn->prepare('INSERT INTO category (name,cat_slug) VALUES (:name,:slug)');
    $stmt->execute(['name' => $name, 'slug' => $name]);
    return (int) $conn->lastInsertId();
}

function editCategory(PDO $conn, $rawId, $rawName): void
{
    $id = categoryId($rawId);
    $name = categoryName($rawName);
    $stmt = $conn->prepare('SELECT id FROM category WHERE id=:id');
    $stmt->execute(['id' => $id]);
    if ($stmt->fetchColumn() === false) {
        throw new InvalidArgumentException('La categoría no existe.');
    }
    $stmt = $conn->prepare('SELECT id FROM category WHERE name=:name AND id<>:id LIMIT 1');
    $stmt->execute(['name' => $name, 'id' => $id]);
    if ($stmt->fetchColumn() !== false) {
        throw new InvalidArgumentException('La categoría ya existe.');
    }
    $stmt = $conn->prepare('UPDATE category SET name=:name,cat_slug=:slug WHERE id=:id');
    $stmt->execute(['name' => $name, 'slug' => $name, 'id' => $id]);
}

function deleteEmptyCategory(PDO $conn, $rawId): void
{
    $id = categoryId($rawId);
    $stmt = $conn->prepare('DELETE FROM category WHERE id=:id AND NOT EXISTS (SELECT 1 FROM products WHERE category_id=:category)');
    $stmt->execute(['id' => $id, 'category' => $id]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException('La categoría no existe o todavía contiene productos.');
    }
}
