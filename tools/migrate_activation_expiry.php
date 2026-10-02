<?php
// Migración aditiva e idempotente; no cambia usuarios ni contraseñas.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/activation_migration.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if (ensureActivationExpiryColumn($conn)) {
        echo "Columna de vencimiento de activación agregada.\n";
    } else {
        echo "La columna de vencimiento de activación ya existe.\n";
    }
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo preparar el vencimiento de activación. Comprueba la conexión y los permisos de MySQL.\n");
    exit(1);
}
