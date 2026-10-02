<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/includes/profile_update.php';
if (!isset($_SESSION['user'], $user['id'])) {
    header('location: login.php');
    exit();
}
$return = 'perfil.php';

unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['edit'])) {
    $conn = $pdo->open();
    try {
        updateOwnProfile($conn, $user, $_POST, $_FILES['photo'] ?? null);
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
