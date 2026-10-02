<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/004_checkout_requests.sql'));
    echo "Protección de pedidos y secuencia disponibles.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo preparar la protección de compras.\n");
    exit(1);
}
