<?php
include 'includes/session.php';
requireValidCSRFRequest();

if (isset($_POST['id']) && is_scalar($_POST['id']) && filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
    $conn = $pdo->open();
    try {
        $stmt = $conn->prepare("UPDATE products SET descuento=0 WHERE id=:id");
        $stmt->execute(['id' => $_POST['id']]);
        $_SESSION['success'] = 'Descuento eliminado exitosamente';
    } catch (PDOException $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    $pdo->close();
}

header('location: ofertas.php');
