<?php
include 'includes/session.php';
require_once __DIR__ . '/includes/password_reset.php';

$parameters = passwordResetParameters($_GET['code'] ?? null, $_GET['user'] ?? null);
if ($parameters === null) {
    $_SESSION['error'] = 'Enlace de recuperación inválido.';
    header('location: password_olvidada.php');
    exit();
}
$path = 'password_restablecer.php?' . http_build_query($parameters);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['reset'])) {
    $_SESSION['error'] = 'Ingrese la nueva contraseña primero';
    header('location: ' . $path);
    exit();
}
if (!is_string($_POST['csrf_token'] ?? null) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Solicitud inválida. Intenta de nuevo.';
    header('location: ' . $path);
    exit();
}
$password = $_POST['password'] ?? null;
$repassword = $_POST['repassword'] ?? null;
if (!is_string($password) || !is_string($repassword) || strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
    header('location: ' . $path);
    exit();
}
if ($password !== $repassword) {
    $_SESSION['error'] = 'Las contraseñas no coinciden';
    header('location: ' . $path);
    exit();
}
$conn = $pdo->open();
try {
    if (consumePasswordReset($conn, $parameters['code'], $parameters['user'], $password)) {
        $_SESSION['success'] = 'La contraseña se restableció correctamente';
        $path = 'login.php';
    } else {
        $_SESSION['error'] = 'El enlace de recuperación es inválido, venció o ya fue utilizado.';
        $path = 'password_olvidada.php';
    }
} catch (PDOException $e) {
    error_log('Error al restablecer contraseña: ' . $e->getMessage());
    $_SESSION['error'] = 'No se pudo restablecer la contraseña. Intenta de nuevo.';
}
$pdo->close();
header('location: ' . $path);
exit();
