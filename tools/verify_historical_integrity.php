<?php
// Creates only fictitious data in a randomly named test DB. Never migrates live data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/historical_integrity.php';
require_once __DIR__ . '/../includes/user_administration.php';
require_once __DIR__ . '/../includes/authentication.php';
require_once __DIR__ . '/../includes/checkout.php';
require_once __DIR__ . '/../includes/customer_transaction.php';
require_once __DIR__ . '/../includes/sales_report.php';
$control = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$source = $control->query('SELECT DATABASE()')->fetchColumn();
$database = 'pgcv_history_test_' . bin2hex(random_bytes(8));
$checks = 0;
$check = function ($ok, $label) use (&$checks) {
    if (!$ok) throw new RuntimeException('Falló: ' . $label);
    $checks++;
};
try {
    $control->exec('CREATE DATABASE `' . $database . '`');
    $dsn = preg_replace('/dbname=[^;]+/', 'dbname=' . $database, DB_SERVER, 1, $replaced);
    if ($replaced !== 1 || $database === $source) throw new RuntimeException('DSN aislado inválido.');
    $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,ONLY_FULL_GROUP_BY,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/000_schema.sql'));
    $check(validateHistoricalForeignKeys($conn) === [], 'clean schema constraints');
    foreach (historicalForeignKeys() as [$table, $column, $parent, $name]) $conn->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
    $conn->exec('ALTER TABLE sales DROP COLUMN legacy_user_id, MODIFY user_id INT NOT NULL');
    $conn->exec("INSERT INTO users (id,email,password,firstname,lastname,status,created_on) VALUES (1,'history@example.com','test-hash','Test','Client',1,'2026-10-02'),(2,'unlinked@example.com','test-hash','Test','Unlinked',1,'2026-10-02');
        INSERT INTO category (id,name,cat_slug) VALUES (1,'Test','test');
        INSERT INTO products (id,category_id,name,description,slug,price,stock,descuento) VALUES (1,1,'Discounted product','','test',100,10,15);
        INSERT INTO cart (id,user_id,product_id,quantity,fecha_hora_inicio) VALUES (1,1,1,1,'2020-01-01 00:00:00'),(2,99,1,2,'2020-01-02 00:00:00');
        INSERT INTO sales (id,user_id,pay_id,nombre_facturacion,documento,direccion,telefono,ciudad,metodo_pago,total,sales_date) VALUES (1,99,'10','Historical customer','test','test','test','test','efectivo',42.50,'2020-01-02');
        INSERT INTO details (id,sales_id,product_id,quantity) VALUES (1,1,1,1)");
    $originalSale = $conn->query('SELECT * FROM sales WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $originalDetail = $conn->query('SELECT * FROM details WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $originalCart = $conn->query('SELECT * FROM cart WHERE id=2')->fetch(PDO::FETCH_ASSOC);
    $rejected = false;
    try { applyHistoricalIntegrity($conn, 5, 17); } catch (RuntimeException $e) { $rejected = true; }
    $check($rejected, 'unexpected counts rejected');
    $conn->exec('ALTER TABLE checkout_requests ADD CONSTRAINT fk_checkout_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    $rejected = false;
    try { applyHistoricalIntegrity($conn, 1, 1); } catch (RuntimeException $e) { $rejected = true; }
    $check($rejected, 'existing incompatible FK rejected');
    $conn->exec('ALTER TABLE checkout_requests DROP FOREIGN KEY fk_checkout_user');
    $conn->exec("INSERT INTO cart_orphan_archive (id,user_id,product_id,quantity,fecha_hora_inicio) VALUES (2,99,1,7,'2020-01-02 00:00:00')");
    $rejected = false;
    try { applyHistoricalIntegrity($conn, 1, 1); } catch (RuntimeException $e) { $rejected = true; }
    $check($rejected && (int) $conn->query('SELECT COUNT(*) FROM cart WHERE id=2')->fetchColumn() === 1, 'archive mismatch preserves cart');
    $check((int) $conn->query('SELECT user_id FROM sales WHERE id=1')->fetchColumn() === 99, 'archive failure preserves owner');
    $conn->exec('DELETE FROM cart_orphan_archive WHERE id=2');
    $conn->exec('UPDATE sales SET legacy_user_id=88 WHERE id=1');
    $rejected = false;
    try { applyHistoricalIntegrity($conn, 1, 1); } catch (RuntimeException $e) { $rejected = true; }
    $check($rejected && (int) $conn->query('SELECT COUNT(*) FROM cart WHERE id=2')->fetchColumn() === 1, 'owner conflict rolls back cart removal');
    $check((int) $conn->query('SELECT COUNT(*) FROM cart_orphan_archive')->fetchColumn() === 0, 'owner conflict rolls back archive insertion');
    $conn->exec('UPDATE sales SET legacy_user_id=NULL WHERE id=1');
    applyHistoricalIntegrity($conn, 1, 1);
    $archived = $conn->query('SELECT id,user_id,product_id,quantity,fecha_hora_inicio FROM cart_orphan_archive WHERE id=2')->fetch(PDO::FETCH_ASSOC);
    $check($archived === $originalCart, 'cart archived exactly');
    $check((int) $conn->query('SELECT COUNT(*) FROM cart')->fetchColumn() === 1, 'active cart kept');
    $sale = $conn->query('SELECT * FROM sales WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $check($sale['user_id'] === null && (int) $sale['legacy_user_id'] === 99, 'original owner retained');
    unset($sale['legacy_user_id']); $sale['user_id'] = $originalSale['user_id'];
    $check($sale === $originalSale, 'all other sale fields preserved');
    $check($conn->query('SELECT * FROM details WHERE id=1')->fetch(PDO::FETCH_ASSOC) === $originalDetail, 'historical detail unchanged');
    applyHistoricalIntegrity($conn, 0, 0);
    $check((int) $conn->query('SELECT COUNT(*) FROM cart_orphan_archive')->fetchColumn() === 1, 'retry does not duplicate archive');
    $check(array_sum(historicalOrphanCounts($conn)) === 0 && validateHistoricalForeignKeys($conn) === [], 'all references enforced');
    foreach ([
        'UPDATE cart SET user_id=99 WHERE id=1', 'UPDATE cart SET product_id=99 WHERE id=1',
        'UPDATE sales SET user_id=99 WHERE id=1', 'UPDATE details SET product_id=99 WHERE id=1',
        "INSERT INTO checkout_requests (request_key,user_id) VALUES ('invalid',99)",
        "INSERT INTO checkout_requests (request_key,user_id,sales_id) VALUES ('invalid',1,99)",
        'DELETE FROM products WHERE id=1', 'DELETE FROM users WHERE id=1',
    ] as $sql) {
        $rejected = false;
        try { $conn->exec($sql); } catch (PDOException $e) { if (substr($e->getCode(), 0, 2) !== '23') throw $e; $rejected = true; }
        $check($rejected, 'invalid write rejected: ' . $sql);
    }
    $logs = 0;
    $logger = function () use (&$logs) { $logs++; };
    $rejected = false;
    try { administerUser($conn, 'delete', ['id' => 1], $logger); } catch (InvalidArgumentException $e) { $rejected = true; }
    $check($rejected && $logs === 0, 'referenced account deletion controlled');
    administerUser($conn, 'delete', ['id' => 2], $logger);
    $check($logs === 1 && (int) $conn->query('SELECT COUNT(*) FROM users WHERE id=2')->fetchColumn() === 0, 'unrelated account deletion allowed');
    $check(findCustomerTransaction($conn, 1, 1) === null, 'historical NULL owner inaccessible to customer');
    $range = salesReportRange('2020-01-02 - 2020-01-02');
    $rows = salesReportRows($conn, $range);
    $check(count($rows) === 1 && (float) $rows[0]['total'] === 42.5, 'historical admin report preserved');
    require_once __DIR__ . '/../vendor/autoload.php';
    $pdf = new TCPDF(); $pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->AddPage();
    $pdf->writeHTML('<table>' . salesReportHtml($rows) . '</table>');
    $check(strpos($pdf->Output('history.pdf', 'S'), '%PDF-') === 0, 'historical report PDF in memory');
    $billing = ['nombre_facturacion'=>'Test Client','documento'=>'12345678','direccion'=>'Calle 1','telefono'=>'3001234567','ciudad'=>'Test','metodo_pago'=>'efectivo'];
    $checkout = completeCheckout($conn, 1, $billing, str_repeat('a', 64));
    $check($checkout['total'] === 85.0, 'checkout with new FKs');
    $check(completeCheckout($conn, 1, $billing, str_repeat('a', 64))['replayed'], 'checkout replay with new FKs');
    echo 'Historial: ' . $checks . " comprobaciones pasan en MariaDB aislada; sin correo real.\n";
} finally {
    $conn = null;
    if (!preg_match('/\Apgcv_history_test_[a-f0-9]{16}\z/', $database) || $database === $source) throw new RuntimeException('Destino de limpieza inválido.');
    $control->exec('DROP DATABASE IF EXISTS `' . $database . '`');
}
