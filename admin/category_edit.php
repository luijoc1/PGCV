<?php
include 'includes/session.php';
requireValidCSRFRequest();
require_once __DIR__ . '/../includes/category_operations.php';

unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['edit'])) {
    $conn = $pdo->open();
    try {
        editCategory($conn, $_POST['id'] ?? null, $_POST['name'] ?? null);
        $_SESSION['success'] = 'Categoría actualizada con éxito';
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
