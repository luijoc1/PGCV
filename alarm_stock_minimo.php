<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(405);
    exit('Esta tarea se ejecuta desde consola.');
}
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/stock_alert.php';
$conn = $pdo->open();
try {
    echo 'Estado de alerta: ' . sendDailyStockAlert($conn) . PHP_EOL;
} catch (Throwable $e) {
    error_log('Error de alerta CLI: ' . $e->getMessage());
    fwrite(STDERR, "No se pudo enviar la alerta. Se podrá intentar nuevamente.\n");
    exit(1);
} finally {
    $pdo->close();
}