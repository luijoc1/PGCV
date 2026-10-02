<?php
// This router exists only in a temporary test server. Never load it in Apache.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__health') { echo 'ready'; return; }
if ($path === '/__state') {
    session_start();
    header('Content-Type: application/json');
    echo json_encode($_SESSION);
    return;
}
if ($path === '/__session') {
    session_start();
    require_once __DIR__ . '/includes/authentication.php';
    require_once __DIR__ . '/includes/csrf.php';
    $conn = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $stmt = $conn->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$_POST['id'] ?? 0]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$account) { http_response_code(400); return; }
    establishAccountSession($account);
    if (isset($_POST['forge_admin'])) {
        unset($_SESSION['user']);
        $_SESSION['admin'] = $account['id'];
    }
    if (isset($_POST['forge_signature'])) { $_SESSION['auth_signature'] = 'wrong'; }
    header('Content-Type: application/json');
    echo json_encode(['csrf_token' => generateCSRFToken()]);
    return;
}
$routes = [
    '/admin/products_add.php', '/admin/products_edit.php', '/admin/products_row.php', '/admin/products_delete.php',
    '/admin/users_delete.php', '/admin/users_add.php', '/admin/users_edit.php', '/admin/users_activate.php', '/admin/users_row.php', '/admin/category_add.php', '/admin/category_delete.php',
    '/admin/actualizar_estado.php', '/admin/category_edit.php', '/admin/quitar_descuento.php', '/admin/products_photo.php', '/admin/users_photo.php', '/perfil_editar.php', '/admin/profile_update.php',
];
if (!in_array($path, $routes, true)) { http_response_code(404); return; }
chdir(dirname(__DIR__ . $path));
require __DIR__ . $path;
