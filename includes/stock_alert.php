<?php
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/output.php';

function sendDailyStockAlert(PDO $conn, callable $send = null, $day = null)
{
    $now = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
    $day = $day ?? $now->format('Y-m-d');
    if (!is_string($day) || !preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $day)) throw new InvalidArgumentException('Fecha inválida.');
    $sqlite = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare($sqlite
            ? "INSERT OR IGNORE INTO mail_daily_delivery (kind,delivery_day) VALUES ('stock',:day)"
            : "INSERT INTO mail_daily_delivery (kind,delivery_day) VALUES ('stock',:day) ON DUPLICATE KEY UPDATE kind=kind");
        $stmt->execute(['day' => $day]);
        $stmt = $conn->prepare("SELECT sent_at FROM mail_daily_delivery WHERE kind='stock' AND delivery_day=:day" . ($sqlite ? '' : ' FOR UPDATE'));
        $stmt->execute(['day' => $day]);
        if ($stmt->fetchColumn() !== null) {
            $conn->commit();
            return 'already_sent';
        }
        $products = $conn->query('SELECT name,stock,stock_minimo FROM products WHERE stock=0 OR (stock_minimo>0 AND stock<=stock_minimo) ORDER BY stock,id LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
        if (!$products) { $conn->rollBack(); return 'empty'; }
        $rows = '';
        foreach ($products as $product) {
            $rows .= '<tr><td>' . escapeHtml($product['name']) . '</td><td>' . (int) $product['stock'] . '</td><td>' . (int) $product['stock_minimo'] . '</td></tr>';
        }
        $body = '<html><body><h2>Alerta de inventario</h2><p>Productos sin stock o con stock bajo. Se muestran hasta diez.</p><table><tr><th>Producto</th><th>Stock</th><th>Mínimo</th></tr>' . $rows
            . '</table><p><a href="' . escapeHtml(applicationUrl('admin/inventario.php')) . '">Ver inventario completo</a></p></body></html>';
        $recipient = defined('MAIL_ALERT_TO') ? MAIL_ALERT_TO : MAIL_USER;
        if ($send) {
            $success = $send($recipient, 'Alerta de inventario - ' . $day, $body);
        } else {
            $mail = configuredMailer();
            $mail->addAddress($recipient);
            $mail->Subject = 'Alerta de inventario - ' . $day;
            $mail->Body = $body;
            $success = $mail->send();
        }
        if (!$success) throw new RuntimeException('No se pudo enviar la alerta de inventario.');
        $stmt = $conn->prepare("UPDATE mail_daily_delivery SET sent_at=:sent WHERE kind='stock' AND delivery_day=:day");
        $stmt->execute(['sent' => $now->setTimestamp(time())->format('Y-m-d H:i:s'), 'day' => $day]);
        $conn->commit();
        return 'sent';
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        throw $e;
    }
}
