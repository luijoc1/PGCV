<?php
include 'includes/session.php';
requireValidCSRFRequest(true);
require_once __DIR__ . '/../includes/stock_alert.php';
header('Content-Type: application/json; charset=UTF-8');
$conn = $pdo->open();
try {
    $status = sendDailyStockAlert($conn);
    echo json_encode(['success' => true, 'status' => $status]);
} catch (Throwable $e) {
    error_log('Error de alerta de stock: ' . $e->getMessage());
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'No se pudo enviar la alerta. Se podrá intentar nuevamente.']);
}
$pdo->close();