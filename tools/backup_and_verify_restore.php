<?php
// Read-only source snapshot; restore exclusively to a random, disposable DB.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__ . '/../includes/config.php';
date_default_timezone_set('America/Bogota');
function backupTableDigest(PDO $conn, string $quoted, string $order): array
{
    $hash = hash_init('sha256'); $count = 0;
    foreach ($conn->query('SELECT * FROM ' . $quoted . $order, PDO::FETCH_ASSOC) as $row) {
        foreach ($row as $value) hash_update($hash, $value === null ? 'N;' : 'S' . strlen($value) . ':' . $value . ';');
        hash_update($hash, "\n"); $count++;
    }
    return ['rows' => $count, 'sha256' => hash_final($hash)];
}
$source = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$source->exec('SET NAMES utf8mb4');
$sourceName = $source->query('SELECT DATABASE()')->fetchColumn();
$database = 'pgcv_restore_test_' . bin2hex(random_bytes(8));
$directory = __DIR__ . '/../storage/backups';
if (!is_file($directory . '/.htaccess') || !is_dir($directory)) throw new RuntimeException('Falta protección del directorio de respaldo.');
$basename = 'history-preflight-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
$path = $directory . '/' . $basename . '.sql';
$file = null; $restore = null; $created = false;
try {
    $tables = $source->query("SELECT TABLE_NAME,ENGINE,TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tables as $table) if ($table['ENGINE'] !== 'InnoDB' || $table['TABLE_TYPE'] !== 'BASE TABLE') throw new RuntimeException('El respaldo requiere tablas base InnoDB.');
    foreach (['TRIGGERS', 'ROUTINES', 'EVENTS'] as $object) {
        $column = $object === 'TRIGGERS' ? 'TRIGGER_SCHEMA' : ($object === 'ROUTINES' ? 'ROUTINE_SCHEMA' : 'EVENT_SCHEMA');
        if ((int) $source->query('SELECT COUNT(*) FROM information_schema.' . $object . ' WHERE ' . $column . '=DATABASE()')->fetchColumn()) throw new RuntimeException('Hay objetos adicionales que requieren respaldo específico.');
    }
    $source->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $source->exec('SET TRANSACTION READ ONLY');
    $source->beginTransaction();
    $file = fopen($path, 'xb');
    if (!$file) throw new RuntimeException('No se pudo crear el respaldo.');
    $write = function ($text) use ($file) {
        if (fwrite($file, $text) !== strlen($text)) throw new RuntimeException('Escritura de respaldo incompleta.');
    };
    $write("-- PGCV source snapshot. Restore only to a new empty database.\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';\n");
    $manifest = ['created_at' => date(DATE_ATOM), 'tables' => []];
    foreach ($tables as $table) {
        $name = $table['TABLE_NAME'];
        $quoted = '`' . str_replace('`', '``', $name) . '`';
        $create = $source->query('SHOW CREATE TABLE ' . $quoted)->fetch(PDO::FETCH_NUM)[1];
        $keys = $source->query('SHOW INDEX FROM ' . $quoted . " WHERE Key_name='PRIMARY'")->fetchAll(PDO::FETCH_ASSOC);
        if (!$keys) throw new RuntimeException('La comprobación requiere clave primaria en cada tabla.');
        usort($keys, function ($a, $b) { return $a['Seq_in_index'] <=> $b['Seq_in_index']; });
        $order = ' ORDER BY ' . implode(',', array_map(function ($k) { return '`' . str_replace('`', '``', $k['Column_name']) . '`'; }, $keys));
        $manifest['tables'][$name] = backupTableDigest($source, $quoted, $order) + ['schema_sha256' => hash('sha256', $create), 'order' => $order];
        $write($create . ";\n");
        foreach ($source->query('SELECT * FROM ' . $quoted . $order, PDO::FETCH_ASSOC) as $row) {
            $columns = array_map(function ($column) { return '`' . str_replace('`', '``', $column) . '`'; }, array_keys($row));
            // Hex avoids embedded newlines/quotes and preserves UTF-8 text on import.
            $values = array_map(function ($value) { return $value === null ? 'NULL' : "CONVERT(X'" . bin2hex($value) . "' USING utf8mb4)"; }, array_values($row));
            $write('INSERT INTO ' . $quoted . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
        }
    }
    $write("SET FOREIGN_KEY_CHECKS=1;\n");
    if (!fflush($file)) throw new RuntimeException('No se pudo finalizar el respaldo.');
    fclose($file); $file = null;
    $source->commit();
    $manifest['backup_sha256'] = hash_file('sha256', $path);
    // Separate connection. Never USE a test database on the source connection.
    $control = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if (!preg_match('/\Apgcv_restore_test_[a-f0-9]{16}\z/', $database) || $database === $sourceName) throw new RuntimeException('Destino de restauración inválido.');
    $dsn = preg_replace('/dbname=[^;]+/', 'dbname=' . $database, DB_SERVER, 1, $replaced);
    if ($replaced !== 1) throw new RuntimeException('El DSN requiere base explícita.');
    $control->exec('CREATE DATABASE `' . $database . '`'); $created = true;
    $restore = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if ($restore->query('SELECT DATABASE()')->fetchColumn() !== $database) throw new RuntimeException('Conexión de restauración incorrecta.');
    $input = fopen($path, 'rb'); $statement = '';
    if (!$input) throw new RuntimeException('No se pudo leer el archivo de respaldo.');
    try {
        while (($line = fgets($input)) !== false) {
            if ($statement === '' && (strpos($line, '-- ') === 0 || trim($line) === '')) continue;
            $statement .= $line;
            if (substr(rtrim($line), -1) === ';') { $restore->exec($statement); $statement = ''; }
        }
        if (!feof($input) || trim($statement) !== '') throw new RuntimeException('Archivo de respaldo incompleto.');
    } finally { fclose($input); }
    foreach ($manifest['tables'] as $name => $expected) {
        $quoted = '`' . str_replace('`', '``', $name) . '`';
        $actual = backupTableDigest($restore, $quoted, $expected['order']);
        $schema = $restore->query('SHOW CREATE TABLE ' . $quoted)->fetch(PDO::FETCH_NUM)[1];
        if ($actual['rows'] !== $expected['rows'] || !hash_equals($actual['sha256'], $expected['sha256']) || !hash_equals(hash('sha256', $schema), $expected['schema_sha256'])) throw new RuntimeException('La restauración no coincide: ' . $name);
    }
    $manifest['restore_verified'] = true;
    if (file_put_contents($directory . '/' . $basename . '.json', json_encode($manifest, JSON_PRETTY_PRINT) . "\n", LOCK_EX) === false) throw new RuntimeException('No se pudo guardar la evidencia.');
    echo 'Respaldo: ' . $basename . ".sql\n";
    echo 'Restauración verificada: ' . count($manifest['tables']) . " tablas, estructuras y contenido exactos.\n";
} finally {
    if ($source->inTransaction()) $source->rollBack();
    if (is_resource($file)) fclose($file);
    $restore = null;
    if ($created) {
        if (!preg_match('/\Apgcv_restore_test_[a-f0-9]{16}\z/', $database) || $database === $sourceName) throw new RuntimeException('Destino de limpieza inválido.');
        $control->exec('DROP DATABASE `' . $database . '`');
        echo "Base temporal eliminada.\n";
    }
}
