<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/profile_update.php';

$return = 'home.php';
if (is_string($_GET['return'] ?? null) && preg_match('/\A[a-zA-Z0-9_-]+\.php\z/', $_GET['return'])) {
    $return = $_GET['return'];
}
unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['save'])) {
    $conn = $pdo->open();
    try {
        updateOwnProfile($conn, $admin, $_POST, $_FILES['photo'] ?? null);
        $_SESSION['success'] = 'Cuenta actualizada con éxito';
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'No se pudo actualizar el perfil. Intenta de nuevo.';
    } catch (RuntimeException $e) {
        $_SESSION['error'] = 'No se pudo guardar la foto. Intenta de nuevo.';
    }
    $pdo->close();
} else {
    $_SESSION['error'] = 'Complete el formulario de edición primero.';
}
header('location: ' . $return);
exit();
