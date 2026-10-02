<?php
// Migración local e idempotente. No cambia contraseñas ni elimina datos.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';

try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $columns = $conn->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    $additions = [];
    if (!in_array('reset_token_hash', $columns, true)) {
        $additions[] = 'ADD COLUMN reset_token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL';
    }
    if (!in_array('reset_expires_at', $columns, true)) {
        $additions[] = 'ADD COLUMN reset_expires_at DATETIME NULL DEFAULT NULL';
    }
    if ($additions) {
        $conn->exec('ALTER TABLE users ' . implode(', ', $additions));
        echo "Columnas de recuperación agregadas.\n";
    } else {
        echo "Las columnas de recuperación ya existen.\n";
    }
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo aplicar la migración. Comprueba la conexión y los permisos de MySQL.\n");
    exit(1);
}
