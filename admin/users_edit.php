<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/user_administration.php';
require_once __DIR__ . '/../includes/image_upload.php';
unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['edit'])) {
    $conn = $pdo->open();
    try {
        $photo = '';
        if ('edit' === 'add') {
            adminUserFields($_POST);
            $photo = photoUploadOrRedirect($_FILES['photo'] ?? null, '', 'users.php');
        }
        administerUser($conn, 'edit', $_POST, function ($id, $previous, $current, $operation) use ($conn, $admin) {
            registrarLog($conn, 'logs_usuarios', $id, $previous, $current, $operation, $admin['email']);
        }, $photo);
        $_SESSION['success'] = 'Operación de usuario completada correctamente.';
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'No se pudo guardar el cambio del usuario. Revisa los datos e intenta de nuevo.';
    }
    $pdo->close();
} else {
    $_SESSION['error'] = 'Complete el formulario de usuario primero.';
}
header('location: users.php');
exit();
