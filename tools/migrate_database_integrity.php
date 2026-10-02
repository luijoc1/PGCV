<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database_integrity.php';
try {
    $conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $counts = databaseIntegrityCounts($conn);
    echo json_encode($counts, JSON_PRETTY_PRINT) . "\n";
    if (in_array('--audit', $argv, true)) exit();
    foreach ($counts as $name => $count) {
        if ($count && !in_array($name, ['cart_orphans', 'sales_orphans'], true)) throw new RuntimeException('Hay datos que requieren revisión antes de migrar.');
    }
    $directory = __DIR__ . '/../storage/backups';
    if (!is_file($directory . '/.htaccess')) throw new RuntimeException('Falta la protección del directorio de respaldos.');
    $path = $directory . '/integrity-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.sql';
    $file = fopen($path, 'xb');
    if (!$file) throw new RuntimeException('No se pudo crear el respaldo.');
    $write = function ($text) use ($file) { if (fwrite($file, $text) !== strlen($text)) throw new RuntimeException('Respaldo incompleto.'); };
    try {
        $conn->beginTransaction();
        $write("-- Respaldo previo a cambios de integridad. Importar solo en una base vacía.\nSET FOREIGN_KEY_CHECKS=0;\n");
        $charset = $conn->query('SELECT @@character_set_connection')->fetchColumn();
        if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $charset)) throw new RuntimeException('Codificación de conexión desconocida.');
        $write('SET NAMES ' . $charset . ";\n");
        foreach ($conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            $write($conn->query('SHOW CREATE TABLE ' . $quoted)->fetch(PDO::FETCH_NUM)[1] . ";\n");
            foreach ($conn->query('SELECT * FROM ' . $quoted) as $row) {
                $row = array_filter($row, 'is_string', ARRAY_FILTER_USE_KEY);
                $values = array_map(function ($value) use ($conn) { return $value === null ? 'NULL' : $conn->quote($value); }, array_values($row));
                $columns = array_map(function ($name) { return '`' . str_replace('`', '``', $name) . '`'; }, array_keys($row));
                $write('INSERT INTO ' . $quoted . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
            }
        }
        $write("SET FOREIGN_KEY_CHECKS=1;\n");
        $conn->commit();
    } finally {
        fclose($file);
    }
    echo 'Respaldo creado: ' . basename($path) . "\n";
    applyDatabaseIntegrity($conn);
    echo "Cambios de integridad aplicados; no se eliminaron ni reasignaron registros huérfanos.\n";
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    fwrite(STDERR, 'Migración detenida: ' . $e->getMessage() . "\n");
    exit(1);
}
