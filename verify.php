<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login']) || !validateCSRFToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Solicitud inválida. Recarga la página e intenta de nuevo.';
    header('location: login.php');
    exit();
}
$email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
$password = $_POST['password'] ?? null;
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || $password === '') {
    $_SESSION['error'] = 'Ingresa un correo válido y tu contraseña.';
    header('location: login.php');
    exit();
}

$conn = $pdo->open();
try {
    // REMOTE_ADDR procede de la conexión; no se confía en encabezados del navegador.
    $retry = reserveLoginAttempt($conn, $email, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if ($retry > 0) {
        $_SESSION['error'] = 'Demasiados intentos. Intenta de nuevo en ' . (int) ceil($retry / 60) . ' minuto(s).';
        registrarLog($conn, 'logs_login', 0, null, null, 'BLOQUEADO', $email);
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        $hash = $account['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
        $valid = password_verify($password, $hash);
        if ($account && $valid && (int) $account['status'] === 1 && in_array((int) $account['type'], [0, 1], true)) {
            registrarLog($conn, 'logs_login', $account['id'], null, null, 'EXITOSO', $email);
            clearSuccessfulLoginAttempts($conn, $email);
            establishAccountSession($account);
        } else {
            $_SESSION['error'] = 'Correo o contraseña incorrectos, o cuenta no habilitada.';
            registrarLog($conn, 'logs_login', 0, null, null, 'FALLIDO', $email);
        }
    }
} catch (Throwable $e) {
    clearAccountSession();
    error_log('Error al iniciar sesión: ' . $e->getMessage());
    $_SESSION['error'] = 'No se pudo iniciar sesión. Intenta de nuevo.';
}
$pdo->close();
header('location: login.php');
exit();