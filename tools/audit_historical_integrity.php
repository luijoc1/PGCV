<?php
// Read-only audit: aggregate counts and schema metadata, no personal data.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec('SET TRANSACTION READ ONLY');
    $conn->beginTransaction();
    $queries = [
        'cart_missing_user_only' => 'SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN products p ON p.id=c.product_id WHERE u.id IS NULL AND p.id IS NOT NULL',
        'cart_missing_product_only' => 'SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN products p ON p.id=c.product_id WHERE u.id IS NOT NULL AND p.id IS NULL',
        'cart_missing_both' => 'SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN products p ON p.id=c.product_id WHERE u.id IS NULL AND p.id IS NULL',
        'sales_missing_user' => 'SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE s.user_id IS NOT NULL AND u.id IS NULL',
        'sales_missing_user_distinct_ids' => 'SELECT COUNT(DISTINCT s.user_id) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE s.user_id IS NOT NULL AND u.id IS NULL',
        'sales_without_account' => 'SELECT COUNT(*) FROM sales WHERE user_id IS NULL',
        'details_missing_product' => 'SELECT COUNT(*) FROM details d LEFT JOIN products p ON p.id=d.product_id WHERE p.id IS NULL',
        'details_missing_product_without_snapshot' => 'SELECT COUNT(*) FROM details d LEFT JOIN products p ON p.id=d.product_id WHERE p.id IS NULL AND d.unit_price IS NULL',
        'details_without_snapshot' => 'SELECT COUNT(*) FROM details WHERE unit_price IS NULL',
        'details_with_snapshot' => 'SELECT COUNT(*) FROM details WHERE unit_price IS NOT NULL',
        'orphan_sales_without_details' => 'SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE u.id IS NULL AND NOT EXISTS (SELECT 1 FROM details d WHERE d.sales_id=s.id)',
        'checkout_missing_user' => 'SELECT COUNT(*) FROM checkout_requests c LEFT JOIN users u ON u.id=c.user_id WHERE u.id IS NULL',
        'checkout_missing_sale' => 'SELECT COUNT(*) FROM checkout_requests c LEFT JOIN sales s ON s.id=c.sales_id WHERE c.sales_id IS NOT NULL AND s.id IS NULL',
    ];
    $result = [];
    foreach ($queries as $label => $query) $result[$label] = (int) $conn->query($query)->fetchColumn();
    $result['foreign_keys'] = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME, COLUMN_NAME")->fetchAll(PDO::FETCH_ASSOC);
    $conn->commit();
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    fwrite(STDERR, 'Auditoría detenida: ' . $e->getMessage() . "\n");
    exit(1);
}
