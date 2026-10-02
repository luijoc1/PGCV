<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/003_login_attempts.sql'));
    echo "Tabla de límites de inicio de sesión disponible.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo preparar la tabla de límites de acceso.\n");
    exit(1);
}
