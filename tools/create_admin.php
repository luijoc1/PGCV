<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/authentication.php';
$email = $argv[1] ?? null;
$password = getenv('PGCV_ADMIN_PASSWORD');
if (!is_string($email) || strlen($email) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 12) {
    fwrite(STDERR, "Uso: php tools/create_admin.php correo@example.com; definir PGCV_ADMIN_PASSWORD con al menos 12 caracteres.\n");
    exit(1);
}
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    createAdministrator($conn, $email, $password);
    echo "Administrador nuevo creado. No se enviaron correos.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo crear el administrador. Revisa la conexión o si el correo ya existe.\n");
    exit(1);
}
