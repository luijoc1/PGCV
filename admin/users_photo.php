<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/image_upload.php';
unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['upload'])) {
    $conn = $pdo->open();
    try {
        replaceEntityPhoto($conn, 'users', $_POST['id'] ?? null, $_FILES['photo'] ?? null);
        $_SESSION['success'] = 'Foto actualizada correctamente.';
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'No se pudo guardar la foto. Intenta de nuevo.';
    } catch (RuntimeException $e) {
        $_SESSION['error'] = 'No se pudo guardar la foto. Intenta de nuevo.';
    }
    $pdo->close();
} else {
    $_SESSION['error'] = 'Seleccione el registro para actualizar la foto primero.';
}
header('location: users.php');
exit();
