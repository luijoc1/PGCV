<?php
// Usa una base de prueba aislada y la elimina al terminar. Nunca copia filas reales.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database_integrity.php';
require_once __DIR__ . '/../includes/checkout.php';
$conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$source = $conn->query('SELECT DATABASE()')->fetchColumn();
$database = 'pgcv_integrity_test_' . bin2hex(random_bytes(8));
$checkCount = 0;
$check = function ($condition) use (&$checkCount) {
    if (!$condition) throw new RuntimeException('Falló una comprobación de integridad.');
    $checkCount++;
};
try {
    $conn->exec('CREATE DATABASE `' . $database . '`');
    $definitions = [];
    foreach (['users', 'category', 'products', 'sales', 'cart', 'details', 'checkout_requests', 'checkout_sequence'] as $table) {
        $definitions[] = $conn->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_NUM)[1];
    }
    $conn->exec('USE `' . $database . '`');
    foreach ($definitions as $definition) $conn->exec($definition);
    $conn->exec("INSERT INTO users (id,email,password,type,firstname,lastname,address,contact_info,photo,status,activate_code,reset_code,created_on) VALUES (1,'test@example.com','test-hash',0,'Test','Only','','','',1,'','','2026-10-01')");
    $conn->exec("INSERT INTO category VALUES (1,'Test','test');
        INSERT INTO products (id,category_id,name,description,slug,price,descuento,stock,photo,date_view,counter) VALUES (1,1,'Test','','test',10.25,0,5,'','2026-10-01',0);
        INSERT INTO sales (id,user_id,pay_id,nombre_facturacion,documento,direccion,telefono,ciudad,metodo_pago,total,sales_date) VALUES (1,1,'100','','','','','','',20.50,'2026-10-01'),(2,99,'101','','','','','','',10.25,'2026-10-01');
        INSERT INTO cart (id,user_id,product_id,quantity,fecha_hora_inicio) VALUES (1,1,1,1,'2000-01-01 00:00:00'),(2,99,1,1,'2000-01-01 00:00:00');
        INSERT INTO details (sales_id,product_id,quantity) VALUES (1,1,2)");
    applyDatabaseIntegrity($conn);
    applyDatabaseIntegrity($conn);
    $conn->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,ONLY_FULL_GROUP_BY,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
    $counts = databaseIntegrityCounts($conn);
    $check($counts['cart_orphans'] === 1 && $counts['sales_orphans'] === 1);
    $check((float) $conn->query('SELECT price FROM products WHERE id=1')->fetchColumn() === 10.25);
    $check((float) $conn->query('SELECT total FROM sales WHERE id=1')->fetchColumn() === 20.5);
    foreach ([
        "INSERT INTO users (email,password,firstname,lastname,created_on) VALUES ('test@example.com','test','Test','Only','2026-10-01')",
        'UPDATE products SET stock=-1 WHERE id=1',
        'UPDATE products SET price=-1 WHERE id=1',
        'UPDATE products SET descuento=101 WHERE id=1',
        'UPDATE cart SET quantity=0 WHERE id=1',
        'UPDATE details SET quantity=0',
        'UPDATE sales SET total=-1 WHERE id=1',
        "UPDATE details SET product_name='Partial'",
        'UPDATE products SET category_id=99 WHERE id=1',
        'DELETE FROM sales WHERE id=1',
        "INSERT INTO cart (user_id,product_id,quantity) VALUES (1,1,1)",
        "UPDATE sales SET pay_id='100' WHERE id=2",
    ] as $invalid) {
        $rejected = false;
        try { $conn->exec($invalid); } catch (PDOException $e) {
            if (substr($e->getCode(), 0, 2) !== '23') throw $e;
            $rejected = true;
        }
        $check($rejected);
    }
    $conn->exec("INSERT INTO users (email,password,firstname,lastname,created_on) VALUES ('new@example.com','test','New','Only','2026-10-01')");
    $check((int) $conn->query("SELECT type FROM users WHERE email='new@example.com'")->fetchColumn() === 0);
    $check((int) $conn->query("SELECT status FROM users WHERE email='new@example.com'")->fetchColumn() === 0);
    $conn->exec("INSERT INTO products (category_id,name,description,slug,price,stock,photo,descuento) VALUES (1,'New','','new',5,0,'',0)");
    $check((int) $conn->query("SELECT counter FROM products WHERE slug='new'")->fetchColumn() === 0);
    $conn->exec('UPDATE cart SET quantity=2 WHERE id=1');
    $check($conn->query('SELECT fecha_hora_inicio FROM cart WHERE id=1')->fetchColumn() === '2000-01-01 00:00:00');
    $conn->exec('INSERT INTO checkout_sequence VALUES (1,101)');
    $billing = ['nombre_facturacion' => 'Test Only', 'documento' => '12345678', 'direccion' => 'Calle 1', 'telefono' => '3001234567', 'ciudad' => 'Test', 'metodo_pago' => 'efectivo'];
    $sale = completeCheckout($conn, 1, $billing, str_repeat('a', 64));
    $check((int) $sale['pay_id'] === 102);
    $check($sale['total'] === 20.5);
    $check((int) $conn->query('SELECT stock FROM products WHERE id=1')->fetchColumn() === 3);
    $replay = completeCheckout($conn, 1, $billing, str_repeat('a', 64));
    $check($replay['replayed'] && (string) $replay['sales_id'] === (string) $sale['sales_id']);
    echo 'MariaDB: ' . $checkCount . " comprobaciones pasan en una base aislada, incluyendo modo estricto y migración repetida.\n";
} finally {
    if (!preg_match('/\Apgcv_integrity_test_[a-f0-9]{16}\z/', $database) || $database === $source) throw new RuntimeException('Destino de limpieza inválido.');
    $conn->exec('DROP DATABASE IF EXISTS `' . $database . '`');
}
