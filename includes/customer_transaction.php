<?php
require_once __DIR__ . '/cart_operations.php';
require_once __DIR__ . '/sale_history.php';

function findCustomerTransaction(PDO $conn, $userId, $saleId)
{
    $owner = cartPositiveInteger($userId);
    $id = cartPositiveInteger($saleId);
    if ($owner === null || $id === null) {
        return null;
    }
    $stmt = $conn->prepare('SELECT id, pay_id, sales_date, total FROM sales WHERE id=:id AND user_id=:user_id');
    $stmt->execute(['id' => $id, 'user_id' => $owner]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sale) {
        return null;
    }
    $stmt = $conn->prepare('SELECT ' . saleDetailColumns() . ' FROM details LEFT JOIN products ON products.id=details.product_id JOIN sales ON sales.id=details.sales_id WHERE details.sales_id=:id AND sales.user_id=:user_id');
    $stmt->execute(['id' => $id, 'user_id' => $owner]);
    return ['sale' => $sale, 'details' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}
