<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/category_operations.php';

unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['add'])) {
    $conn = $pdo->open();
    try {
        addCategory($conn, $_POST['name'] ?? null);
        $_SESSION['success'] = 'Categoría añadida con éxito';
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (PDOException $e) {
        error_log('Error en operación de categoría: ' . $e->getMessage());
        $_SESSION['error'] = 'No se pudo guardar el cambio de categoría. Revisa los datos e intenta de nuevo.';
    }
    $pdo->close();
} else {
    $_SESSION['error'] = 'Complete el formulario de categoría primero.';
}
header('location: category.php');
