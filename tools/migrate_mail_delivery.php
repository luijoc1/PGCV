<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/006_mail_daily_delivery.sql'));
    echo "Registro diario de alertas disponible.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo preparar el registro de alertas.\n");
    exit(1);
}
