<?php
// End-to-end CLI and Apache gate check with fictitious data only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/historical_integrity.php';
$control = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$source = $control->query('SELECT DATABASE()')->fetchColumn();
$suffix = bin2hex(random_bytes(8));
$database = 'pgcv_cli_test_' . $suffix;
$root = realpath(__DIR__ . '/..');
$workspace = $root . '/tests/fixtures/cli-' . $suffix;
$created = false; $checks = 0;
$check = function ($condition, $label) use (&$checks) {
    if (!$condition) throw new RuntimeException('Falló: ' . $label);
    $checks++;
};
$run = function ($tool, array $args = []) use ($workspace) {
    $pipes = [];
    $process = proc_open(array_merge([PHP_BINARY, $workspace . '/tools/' . $tool], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $workspace);
    if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la prueba CLI.');
    $out = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return [proc_close($process), $out, $error];
};
$http = function () use ($suffix) {
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]]);
    $body = file_get_contents('http://127.0.0.1/PGCV/tests/fixtures/cli-' . $suffix . '/probe.php', false, $context);
    preg_match('/\s(\d{3})\s/', $http_response_header[0] ?? '', $match);
    return [(int) ($match[1] ?? 0), $body];
};
try {
    $dsn = preg_replace('/dbname=[^;]+/', 'dbname=' . $database, DB_SERVER, 1, $replaced);
    if ($replaced !== 1 || $database === $source) throw new RuntimeException('Destino de prueba inválido.');
    $control->exec('CREATE DATABASE `' . $database . '`'); $created = true;
    $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec(file_get_contents($root . '/migrations/000_schema.sql'));
    foreach (historicalForeignKeys() as [$table, $column, $parent, $name]) $conn->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
    $conn->exec('ALTER TABLE sales DROP COLUMN legacy_user_id, MODIFY user_id INT NOT NULL');
    $conn->exec("INSERT INTO category(id,name,cat_slug) VALUES(1,'Test','test');
        INSERT INTO products(id,category_id,name,description,slug,price,stock,descuento) VALUES(1,1,'Test','','test',100,10,15);
        INSERT INTO cart(id,user_id,product_id,quantity,fecha_hora_inicio) VALUES(1,99,1,2,'2020-01-02');
        INSERT INTO sales(id,user_id,pay_id,nombre_facturacion,documento,direccion,telefono,ciudad,metodo_pago,total,sales_date) VALUES(1,99,'10','Test','test','test','test','test','efectivo',42.50,'2020-01-02');
        INSERT INTO details(id,sales_id,product_id,quantity) VALUES(1,1,1,1)");
    foreach (['tools', 'includes', 'storage/backups'] as $dir) if (!mkdir($workspace . '/' . $dir, 0700, true)) throw new RuntimeException('No se pudo preparar la prueba.');
    file_put_contents($workspace . '/includes/.htaccess', "Require all denied\n");
    file_put_contents($workspace . '/storage/.htaccess', "Require all denied\n");
    copy($root . '/storage/backups/.htaccess', $workspace . '/storage/backups/.htaccess');
    foreach (['maintenance.php', 'migrate_historical_integrity.php', 'backup_and_verify_restore.php'] as $file) copy(__DIR__ . '/' . $file, $workspace . '/tools/' . $file);
    copy($root . '/includes/historical_integrity.php', $workspace . '/includes/historical_integrity.php');
    file_put_contents($workspace . '/includes/config.php', '<?php define("DB_SERVER",' . var_export($dsn, true) . '); define("DB_USER",' . var_export(DB_USER, true) . '); define("DB_PASS",' . var_export(DB_PASS, true) . ');');
    $rules = str_replace('/PGCV/storage/maintenance.lock', '/PGCV/tests/fixtures/cli-' . $suffix . '/storage/maintenance.lock', file_get_contents($root . '/.htaccess'));
    file_put_contents($workspace . '/.htaccess', $rules);
    file_put_contents($workspace . '/probe.php', '<?php echo "probe";');
    $args = ['--apply', '--expected-cart=1', '--expected-sales=1', '--external-writers-paused'];
    $check($http() === [200, 'probe'], 'HTTP open before maintenance');
    $check($run('migrate_historical_integrity.php', $args)[0] !== 0, 'CLI refuses absent gate');
    $check($run('maintenance.php', ['on'])[0] === 0, 'pause enabled in fixture');
    $check($http()[0] === 503, 'Apache blocks PHP during pause');
    $check($run('migrate_historical_integrity.php', ['--apply', '--expected-cart=1', '--expected-sales=1'])[0] !== 0, 'external writer acknowledgement required');
    $check($run('migrate_historical_integrity.php', ['--apply', '--expected-cart=5', '--expected-sales=17', '--external-writers-paused'])[0] !== 0, 'unexpected counts refused');
    $guard = fopen($workspace . '/storage/backups/maintenance-control.lock', 'c'); flock($guard, LOCK_EX);
    $check($run('maintenance.php', ['off'])[0] !== 0 && is_file($workspace . '/storage/maintenance.lock'), 'operator cannot reopen during migration lock');
    flock($guard, LOCK_UN); fclose($guard);
    $result = $run('migrate_historical_integrity.php', $args);
    $check($result[0] === 0, 'complete CLI backup and migration: ' . $result[2]);
    $check(array_sum(historicalOrphanCounts($conn)) === 0 && validateHistoricalForeignKeys($conn) === [], 'constraints and references verified');
    $check((int) $conn->query('SELECT user_id FROM cart_orphan_archive WHERE id=1')->fetchColumn() === 99, 'original cart retained');
    $check($conn->query('SELECT user_id FROM sales WHERE id=1')->fetchColumn() === null && (int) $conn->query('SELECT legacy_user_id FROM sales WHERE id=1')->fetchColumn() === 99, 'historical owner retained');
    $check($http()[0] === 503, 'CLI preserves pause after success');
    $manifests = glob($workspace . '/storage/backups/*-migration.json');
    $evidence = json_decode(file_get_contents($manifests[0]), true);
    $check(isset($evidence['completed_at']) && array_map('intval', $evidence['cart_ids']) === [1] && count($evidence['sales']) === 1, 'protected evidence recorded');
    $retry = $run('migrate_historical_integrity.php', ['--apply', '--expected-cart=0', '--expected-sales=0', '--external-writers-paused']);
    $check($retry[0] === 0 && (int) $conn->query('SELECT COUNT(*) FROM cart_orphan_archive')->fetchColumn() === 1, 'verified retry retains one archive');
    $check($run('maintenance.php', ['off'])[0] === 0 && $http() === [200, 'probe'], 'operator reopens fixture');
    echo "Verificación CLI/Apache: $checks comprobaciones correctas.\n";
} finally {
    $conn = null;
    if ($created) {
        if (!preg_match('/\Apgcv_cli_test_[a-f0-9]{16}\z/', $database) || $database === $source) throw new RuntimeException('Limpieza de base inválida.');
        $control->exec('DROP DATABASE `' . $database . '`');
    }
    $resolved = realpath($workspace);
    $parent = realpath($root . '/tests/fixtures');
    if ($resolved !== false) {
        if (dirname($resolved) !== $parent || basename($resolved) !== 'cli-' . $suffix) throw new RuntimeException('Limpieza de carpeta inválida.');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) { if ($file->isDir() && !$file->isLink()) rmdir($file->getPathname()); else unlink($file->getPathname()); }
        rmdir($resolved);
    }
}
