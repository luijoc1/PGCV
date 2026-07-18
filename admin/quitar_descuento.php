<?php
include 'includes/session.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $conn = $pdo->open();
    try {
        $stmt = $conn->prepare("UPDATE products SET descuento=0 WHERE id=:id");
        $stmt->execute(['id' => $_GET['id']]);
        $_SESSION['success'] = 'Descuento eliminado exitosamente';
    } catch (PDOException $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    $pdo->close();
}

header('location: ofertas.php');
