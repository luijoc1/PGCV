<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/historical_integrity.php';
$options = getopt('', ['apply', 'expected-cart:', 'expected-sales:', 'external-writers-paused', 'help']);
if (isset($options['help'])) {
    echo "Uso: php tools/migrate_historical_integrity.php --apply --expected-cart=5 --expected-sales=17 --external-writers-paused\n";
    echo "Requiere pausa HTTP comprobada, tareas externas detenidas y peticiones previas finalizadas. Crea y restaura un respaldo antes de aplicar. No retira la pausa.\n";
    exit();
}
$guard = null; $conn = null;
try {
    foreach (['expected-cart', 'expected-sales'] as $key) {
        if (!isset($options[$key]) || !is_string($options[$key]) || !preg_match('/\A(?:0|[1-9][0-9]{0,8})\z/', $options[$key])) throw new RuntimeException('Se requieren conteos explícitos válidos. Usa --help.');
    }
    if (!isset($options['apply'], $options['external-writers-paused'])) throw new RuntimeException('Falta aplicación explícita o confirmación de pausa de escritores externos.');
    $guard = fopen(__DIR__ . '/../storage/backups/maintenance-control.lock', 'c');
    if (!$guard || !flock($guard, LOCK_EX | LOCK_NB)) throw new RuntimeException('Otra operación de mantenimiento está en curso.');
    $gate = __DIR__ . '/../storage/maintenance.lock';
    if (!is_file($gate)) throw new RuntimeException('Activa y comprueba la pausa HTTP antes de continuar.');
    $gateHash = hash_file('sha256', $gate);
    $state = json_decode(file_get_contents($gate), true);
    if (!is_array($state) || !preg_match('/\A[a-f0-9]{32}\z/', $state['token'] ?? '')) throw new RuntimeException('Marcador de pausa inválido.');
    require_once __DIR__ . '/../includes/config.php';
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->exec('SET NAMES utf8mb4');
    $expected = [(int) $options['expected-cart'], (int) $options['expected-sales']];
    $before = historicalOrphanCounts($conn);
    if ($before['cart'] !== $expected[0] || $before['sales'] !== $expected[1] || array_sum(array_slice($before, 2)) !== 0) throw new RuntimeException('Conteos distintos a los aprobados; vuelve a auditar.');
    validateHistoricalForeignKeys($conn);
    // Function scope prevents the backup utility from replacing this connection.
    $backup = (function () {
        require __DIR__ . '/backup_and_verify_restore.php';
        return ['path' => $path, 'manifest' => $manifest];
    })();
    if (($backup['manifest']['restore_verified'] ?? false) !== true || !hash_equals($backup['manifest']['backup_sha256'], hash_file('sha256', $backup['path']))) throw new RuntimeException('Respaldo sin verificación válida.');
    clearstatcache(true, $gate);
    if (!is_file($gate) || !hash_equals($gateHash, hash_file('sha256', $gate))) throw new RuntimeException('La pausa cambió durante el respaldo.');
    foreach ($backup['manifest']['tables'] as $table => $snapshot) {
        $quoted = '`' . str_replace('`', '``', $table) . '`';
        $current = backupTableDigest($conn, $quoted, $snapshot['order']);
        $schema = $conn->query('SHOW CREATE TABLE ' . $quoted)->fetch(PDO::FETCH_NUM)[1];
        if ($current['rows'] !== $snapshot['rows'] || !hash_equals($current['sha256'], $snapshot['sha256']) || !hash_equals(hash('sha256', $schema), $snapshot['schema_sha256'])) throw new RuntimeException('Datos o estructura cambiaron desde el respaldo: ' . $table);
    }
    // Record original IDs only in protected local evidence, before any DDL.
    $evidence = [
        'created_at' => date(DATE_ATOM), 'backup' => basename($backup['path']),
        'counts' => $before,
        'cart_ids' => $conn->query('SELECT c.id FROM cart c LEFT JOIN users u ON u.id=c.user_id WHERE u.id IS NULL ORDER BY c.id')->fetchAll(PDO::FETCH_COLUMN),
        'sales' => $conn->query('SELECT s.id,s.user_id FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE s.user_id IS NOT NULL AND u.id IS NULL ORDER BY s.id')->fetchAll(PDO::FETCH_ASSOC),
    ];
    $evidencePath = substr($backup['path'], 0, -4) . '-migration.json';
    if (file_put_contents($evidencePath, json_encode($evidence, JSON_PRETTY_PRINT) . "\n", LOCK_EX) === false) throw new RuntimeException('No se pudo registrar la evidencia previa.');
    applyHistoricalIntegrity($conn, $expected[0], $expected[1]);
    $after = historicalOrphanCounts($conn);
    if (array_sum($after) !== 0 || validateHistoricalForeignKeys($conn)) throw new RuntimeException('Revisión final incompleta.');
    $evidence['completed_at'] = date(DATE_ATOM); $evidence['after'] = $after;
    if (file_put_contents($evidencePath, json_encode($evidence, JSON_PRETTY_PRINT) . "\n", LOCK_EX) === false) throw new RuntimeException('Migración aplicada; no se pudo finalizar la evidencia.');
    echo "Migración verificada. La pausa sigue activa: revisa el resultado antes de reabrir.\n";
} catch (Throwable $e) {
    if ($conn && $conn->inTransaction()) $conn->rollBack();
    fwrite(STDERR, 'Migración detenida: ' . $e->getMessage() . "\nLa pausa no se retira automáticamente. El DDL puede haberse aplicado parcialmente; audita antes de reintentar.\n");
    exit(1);
} finally {
    if (is_resource($guard)) { flock($guard, LOCK_UN); fclose($guard); }
}
