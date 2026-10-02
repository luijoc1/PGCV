<?php
// Prueba integral CLI. Solo inserta datos ficticios en una base con nombre aleatorio.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/authentication.php';
require_once __DIR__ . '/../includes/password_reset.php';
require_once __DIR__ . '/../includes/activation_migration.php';
require_once __DIR__ . '/../includes/customer_transaction.php';
require_once __DIR__ . '/../includes/checkout.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/sale_status.php';
require_once __DIR__ . '/../includes/sales_report.php';
require_once __DIR__ . '/../includes/stock_alert.php';
require_once __DIR__ . '/../includes/product_deletion.php';
require_once __DIR__ . '/../includes/category_operations.php';
require_once __DIR__ . '/../includes/user_administration.php';
require_once __DIR__ . '/../includes/profile_update.php';
date_default_timezone_set('America/Bogota');

function projectTestConnection($database)
{
    if (!is_string($database) || !preg_match('/\Apgcv_integration_test_[a-f0-9]{16}\z/', $database)) throw new RuntimeException('Base de prueba inválida.');
    $dsn = preg_replace('/dbname=[^;]+/', 'dbname=' . $database, DB_SERVER, 1, $replaced);
    if ($replaced !== 1) throw new RuntimeException('La configuración requiere un nombre de base explícito.');
    $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec("SET SESSION innodb_lock_wait_timeout=5, sql_mode='STRICT_ALL_TABLES,ONLY_FULL_GROUP_BY,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
    return $conn;
}

function projectTestBilling()
{
    return ['nombre_facturacion' => 'Test Client', 'documento' => '12345678', 'direccion' => 'Calle 1', 'telefono' => '3001234567', 'ciudad' => 'Test', 'metodo_pago' => 'efectivo'];
}

if (($argv[1] ?? '') === '--worker') {
    try {
        $conn = projectTestConnection($argv[2] ?? null);
        $owner = cartPositiveInteger($argv[3] ?? null);
        $token = $argv[4] ?? null;
        $worker = cartPositiveInteger($argv[5] ?? null);
        if (!$owner || !$worker) throw new RuntimeException('Trabajador de prueba inválido.');
        $stmt = $conn->prepare('INSERT INTO integration_workers (id) VALUES (:id)');
        $stmt->execute(['id' => $worker]);
        $deadline = microtime(true) + 10;
        while (!(int) $conn->query('SELECT ready FROM integration_gate WHERE id=1')->fetchColumn()) {
            if (microtime(true) > $deadline) throw new RuntimeException('Tiempo de sincronización excedido.');
            usleep(10000);
        }
        $result = completeCheckout($conn, $owner, projectTestBilling(), $token);
        echo json_encode(['ok' => true, 'replayed' => $result['replayed'], 'id' => $result['sales_id'], 'pay_id' => $result['pay_id']]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'validation' => $e instanceof InvalidArgumentException]);
    }
    exit();
}

function concurrentProjectPurchases(PDO $conn, $database, array $requests)
{
    $conn->exec('DELETE FROM integration_workers; UPDATE integration_gate SET ready=0 WHERE id=1');
    $processes = [];
    try {
        foreach ($requests as $index => $request) {
            $process = proc_open([PHP_BINARY, __FILE__, '--worker', $database, (string) $request[0], $request[1], (string) ($index + 1)], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la prueba simultánea.');
            fclose($pipes[0]);
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((int) $conn->query('SELECT COUNT(*) FROM integration_workers')->fetchColumn() !== count($requests)) {
            if (microtime(true) > $deadline) throw new RuntimeException('No se sincronizaron los procesos de prueba.');
            usleep(10000);
        }
        $conn->exec('UPDATE integration_gate SET ready=1 WHERE id=1');
        $results = [];
        foreach ($processes as $index => [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $exit = proc_close($process);
            unset($processes[$index]);
            $result = json_decode($output, true);
            if ($exit !== 0 || $error !== '' || !is_array($result)) throw new RuntimeException('Un proceso de prueba no terminó correctamente.');
            $results[] = $result;
        }
        return $results;
    } finally {
        foreach ($processes as [$process, $pipes]) {
            proc_terminate($process);
            foreach ([$pipes[1], $pipes[2]] as $pipe) if (is_resource($pipe)) fclose($pipe);
            proc_close($process);
        }
    }
}

$control = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$source = $control->query('SELECT DATABASE()')->fetchColumn();
$database = 'pgcv_integration_test_' . bin2hex(random_bytes(8));
$checks = 0;
$check = function ($condition) use (&$checks) {
    if (!$condition) throw new RuntimeException('Falló la comprobación integral ' . ($checks + 1) . '.');
    $checks++;
};
try {
    $control->exec('CREATE DATABASE `' . $database . '`');
    $conn = projectTestConnection($database);
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/000_schema.sql'));
    // Exercise migration from the previous schema, only in the guarded random test DB.
    $conn->exec('ALTER TABLE users DROP COLUMN activate_expires_at');
    $check(ensureActivationExpiryColumn($conn));
    $check(!ensureActivationExpiryColumn($conn));
    $conn->exec('CREATE TABLE integration_gate (id INT PRIMARY KEY,ready INT); CREATE TABLE integration_workers (id INT PRIMARY KEY); INSERT INTO integration_gate VALUES (1,0)');
    $stmt = $conn->prepare("INSERT INTO users (id,email,password,firstname,lastname,type,status,created_on) VALUES (:id,:email,:password,'Test','Client',:type,:status,'2026-10-01')");
    foreach ([[1,0,0],[2,0,1]] as [$id,$type,$status]) {
        $stmt->execute(['id' => $id, 'email' => 'test' . $id . '@example.com', 'password' => password_hash('test-password', PASSWORD_DEFAULT), 'type' => $type, 'status' => $status]);
    }
    $adminId = createAdministrator($conn, 'test-admin@example.com', 'test-admin-password');
    $admin = $conn->query('SELECT * FROM users WHERE id=' . (int) $adminId)->fetch(PDO::FETCH_ASSOC);
    $check(password_verify('test-admin-password', $admin['password']));
    $check(authenticatedAccount($conn, ['admin' => $adminId, 'auth_signature' => accountSessionSignature($admin)], 1) !== null);
    $userLogger = function ($id, $previous, $current, $operation) use ($conn) {
        $stmt = $conn->prepare('INSERT INTO logs_usuarios (id_referencia,informacion_anterior,nueva_informacion,tipo_operacion,ip,usuario_created) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$id, $previous ? json_encode($previous) : null, $current ? json_encode($current) : null, $operation, '127.0.0.1', 'test-admin@example.com']);
    };
    $userInput = ['firstname' => 'Test', 'lastname' => 'User', 'email' => 'managed-test@example.com', 'password' => 'test-password', 'address' => '', 'contact' => ''];
    administerUser($conn, 'add', $userInput, $userLogger);
    $managed = $conn->query("SELECT * FROM users WHERE email='managed-test@example.com'")->fetch(PDO::FETCH_ASSOC);
    $check(password_verify('test-password', $managed['password']) && (int) $managed['type'] === 0);
    $userInput['id'] = $managed['id'];
    $userInput['password'] = '';
    $userInput['firstname'] = 'Edited';
    administerUser($conn, 'edit', $userInput, $userLogger);
    $check($conn->query('SELECT password FROM users WHERE id=' . (int) $managed['id'])->fetchColumn() === $managed['password']);
    $conn->exec('UPDATE users SET status=0 WHERE id=' . (int) $managed['id']);
    administerUser($conn, 'activate', $userInput, $userLogger);
    $check((int) $conn->query('SELECT status FROM users WHERE id=' . (int) $managed['id'])->fetchColumn() === 1);
    updateOwnProfile($conn, $managed, ['curr_password' => 'test-password', 'password' => '', 'firstname' => 'Profile', 'lastname' => 'User', 'email' => 'managed-test@example.com'], null);
    $check($conn->query('SELECT firstname FROM users WHERE id=' . (int) $managed['id'])->fetchColumn() === 'Profile');
    $check($conn->query('SELECT password FROM users WHERE id=' . (int) $managed['id'])->fetchColumn() === $managed['password']);
    administerUser($conn, 'delete', $userInput, $userLogger);
    $check((int) $conn->query('SELECT COUNT(*) FROM users WHERE id=' . (int) $managed['id'])->fetchColumn() === 0);
    $check($conn->query('SELECT tipo_operacion FROM logs_usuarios ORDER BY id_registro')->fetchAll(PDO::FETCH_COLUMN) === ['INSERT', 'UPDATE', 'UPDATE', 'DELETE']);
    $account = $conn->query('SELECT * FROM users WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $session = ['user' => 1, 'auth_signature' => accountSessionSignature($account)];
    $check(authenticatedAccount($conn, $session, 0) === null);
    $conn->exec('UPDATE users SET status=1 WHERE id=1');
    $check(authenticatedAccount($conn, $session, 0) !== null);
    $check(authenticatedAccount($conn, ['admin' => 1, 'auth_signature' => $session['auth_signature']], 1) === null);
    $code = issuePasswordReset($conn, 1);
    $check(consumePasswordReset($conn, $code, 1, 'new-test-password'));
    $check(!consumePasswordReset($conn, $code, 1, 'reuse-password'));
    $check(authenticatedAccount($conn, $session, 0) === null);
    $_SESSION = [];
    $csrf = generateCSRFToken();
    $check(validateCSRFToken($csrf) && !validateCSRFToken('invalid'));
    $conn->exec("INSERT INTO category (id,name,cat_slug) VALUES (1,'Test','test'); INSERT INTO products (id,category_id,name,description,slug,price,stock,descuento) VALUES (1,1,'Original','','original',100,5,10),(2,1,'Last Unit','','last-unit',20,1,0);
        INSERT INTO cart (id,user_id,product_id,quantity) VALUES (1,1,1,1)");
    $extraCategory = addCategory($conn, 'Extra test category');
    $check($extraCategory > 1);
    editCategory($conn, $extraCategory, 'Edited test category');
    $check($conn->query('SELECT name FROM category WHERE id=' . $extraCategory)->fetchColumn() === 'Edited test category');
    deleteEmptyCategory($conn, $extraCategory);
    $check((int) $conn->query('SELECT COUNT(*) FROM category WHERE id=' . $extraCategory)->fetchColumn() === 0);
    try { deleteEmptyCategory($conn, 1); $check(false); } catch (InvalidArgumentException $e) { $check(true); }
    try { addCategory($conn, 'Test'); $check(false); } catch (InvalidArgumentException $e) { $check(true); }
    $check((int) $conn->query('SELECT COUNT(*) FROM products WHERE category_id=1')->fetchColumn() === 2);
    updateOwnedCart($conn, 1, 1, 2);
    try { deleteUnreferencedProduct($conn, 1); $check(false); } catch (InvalidArgumentException $e) { $check(true); }
    $check((int) $conn->query('SELECT quantity FROM cart WHERE id=1')->fetchColumn() === 2);
    try { updateOwnedCart($conn, 2, 1, 1); $check(false); } catch (InvalidArgumentException $e) { $check(true); }
    $token = str_repeat('a',64);
    $results = concurrentProjectPurchases($conn, $database, [[1,$token],[1,$token]]);
    $check($results[0]['ok'] && $results[1]['ok']);
    $check((string) $results[0]['id'] === (string) $results[1]['id']);
    $check((int) $results[0]['replayed'] + (int) $results[1]['replayed'] === 1);
    $check((int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn() === 1);
    $check((int) $conn->query('SELECT stock FROM products WHERE id=1')->fetchColumn() === 3);
    $saleId = $results[0]['id'];
    $transaction = findCustomerTransaction($conn, 1, $saleId);
    try { deleteUnreferencedProduct($conn, 1); $check(false); } catch (InvalidArgumentException $e) { $check(true); }
    $check(findCustomerTransaction($conn, 1, $saleId) === $transaction);
    $conn->exec("INSERT INTO products (id,category_id,name,description,slug,price,stock,descuento) VALUES (3,1,'Unused','','unused',20,1,0)");
    deleteUnreferencedProduct($conn, 3);
    $check((int) $conn->query('SELECT COUNT(*) FROM products WHERE id=3')->fetchColumn() === 0);
    $check((float) $transaction['sale']['total'] === 180.0);
    $check(findCustomerTransaction($conn, 2, $saleId) === null);
    $conn->exec("UPDATE products SET name='Edited',price=500,descuento=50 WHERE id=1");
    $check(findCustomerTransaction($conn, 1, $saleId) === $transaction);
    updateSaleStatus($conn, $saleId, 'enviado');
    $check(findCustomerTransaction($conn, 1, $saleId)['sale']['sales_date'] === $transaction['sale']['sales_date']);
    $range = salesReportRange(date('m/d/Y') . ' - ' . date('m/d/Y'));
    $check(count(salesReportRows($conn, $range)) === 1);
    require_once __DIR__ . '/../vendor/autoload.php';
    $pdf = new TCPDF(); $pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->AddPage();
    $pdf->writeHTML('<table>' . salesReportHtml(salesReportRows($conn, $range)) . '</table>');
    $check(strpos($pdf->Output('integration.pdf','S'), '%PDF-') === 0);
    $conn->exec('INSERT INTO cart (user_id,product_id,quantity) VALUES (1,2,1),(2,2,1)');
    $results = concurrentProjectPurchases($conn, $database, [[1,str_repeat('b',64)],[2,str_repeat('c',64)]]);
    $check((int) $results[0]['ok'] + (int) $results[1]['ok'] === 1);
    $loser = $results[0]['ok'] ? $results[1] : $results[0];
    $check($loser['validation']);
    $check((int) $conn->query('SELECT stock FROM products WHERE id=2')->fetchColumn() === 0);
    $check((int) $conn->query('SELECT COUNT(*) FROM sales')->fetchColumn() === 2);
    $check((int) $conn->query('SELECT COUNT(DISTINCT pay_id) FROM sales')->fetchColumn() === 2);
    $check((int) $conn->query('SELECT COUNT(*) FROM cart WHERE product_id=2')->fetchColumn() === 1);
    $sent = 0;
    $fake = function () use (&$sent) { $sent++; return true; };
    $check(sendDailyStockAlert($conn, $fake) === 'sent');
    $check(sendDailyStockAlert($conn, $fake) === 'already_sent' && $sent === 1);
    echo 'Prueba integral: ' . $checks . " comprobaciones pasan; dos escenarios simultáneos con conexiones independientes, PDF en memoria y correo simulado.\n";
} finally {
    $conn = null;
    if (!preg_match('/\Apgcv_integration_test_[a-f0-9]{16}\z/', $database) || $database === $source) throw new RuntimeException('Destino de limpieza inválido.');
    $control->exec('DROP DATABASE IF EXISTS `' . $database . '`');
}
