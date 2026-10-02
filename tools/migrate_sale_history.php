<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $columns = $conn->query('SHOW COLUMNS FROM details')->fetchAll(PDO::FETCH_COLUMN);
    $definitions = [
        'product_name' => 'TEXT CHARACTER SET utf8mb4 NULL DEFAULT NULL',
        'original_price' => 'DECIMAL(18,2) NULL DEFAULT NULL',
        'discount_percent' => 'DECIMAL(5,2) NULL DEFAULT NULL',
        'unit_price' => 'DECIMAL(18,2) NULL DEFAULT NULL',
    ];
    $additions = [];
    foreach ($definitions as $name => $definition) {
        if (!in_array($name, $columns, true)) {
            $additions[] = 'ADD COLUMN ' . $name . ' ' . $definition;
        }
    }
    if ($additions) {
        $conn->exec('ALTER TABLE details ' . implode(', ', $additions));
        echo "Columnas históricas añadidas; ventas anteriores conservadas sin reconstruir precios.\n";
    } else {
        echo "Las columnas históricas ya existen.\n";
    }
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo aplicar la migración de detalles.\n");
    exit(1);
}
