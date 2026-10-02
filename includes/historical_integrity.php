<?php

function historicalForeignKeys(): array
{
    return [
        ['cart', 'user_id', 'users', 'fk_cart_user'],
        ['cart', 'product_id', 'products', 'fk_cart_product'],
        ['sales', 'user_id', 'users', 'fk_sales_user'],
        ['details', 'product_id', 'products', 'fk_details_product'],
        ['checkout_requests', 'user_id', 'users', 'fk_checkout_user'],
        ['checkout_requests', 'sales_id', 'sales', 'fk_checkout_sale'],
    ];
}

function historicalOrphanCounts(PDO $conn): array
{
    $queries = [
        'cart' => 'SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id WHERE u.id IS NULL',
        'sales' => 'SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE s.user_id IS NOT NULL AND u.id IS NULL',
        'products' => 'SELECT COUNT(*) FROM cart c LEFT JOIN products p ON p.id=c.product_id WHERE p.id IS NULL',
        'details' => 'SELECT COUNT(*) FROM details d LEFT JOIN products p ON p.id=d.product_id WHERE p.id IS NULL',
        'checkout_users' => 'SELECT COUNT(*) FROM checkout_requests c LEFT JOIN users u ON u.id=c.user_id WHERE u.id IS NULL',
        'checkout_sales' => 'SELECT COUNT(*) FROM checkout_requests c LEFT JOIN sales s ON s.id=c.sales_id WHERE c.sales_id IS NOT NULL AND s.id IS NULL',
    ];
    $counts = [];
    foreach ($queries as $key => $sql) $counts[$key] = (int) $conn->query($sql)->fetchColumn();
    return $counts;
}

function validateHistoricalForeignKeys(PDO $conn): array
{
    $missing = [];
    foreach (historicalForeignKeys() as $definition) {
        [$table, $column, $parent, $name] = $definition;
        $stmt = $conn->prepare('SELECT k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,r.DELETE_RULE,r.UPDATE_RULE FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME WHERE k.TABLE_SCHEMA=DATABASE() AND k.TABLE_NAME=:table AND (k.COLUMN_NAME=:column OR k.CONSTRAINT_NAME=:name) AND k.REFERENCED_TABLE_NAME IS NOT NULL');
        $stmt->execute(['table' => $table, 'column' => $column, 'name' => $name]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) { $missing[] = $definition; continue; }
        $size = $conn->prepare('SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table AND CONSTRAINT_NAME=:name');
        $size->execute(['table' => $table, 'name' => $rows[0]['CONSTRAINT_NAME']]);
        if ((int) $size->fetchColumn() !== 1) throw new RuntimeException('Clave foránea compuesta incompatible en ' . $table);
        if (count($rows) !== 1 || $rows[0]['COLUMN_NAME'] !== $column || $rows[0]['REFERENCED_TABLE_NAME'] !== $parent || $rows[0]['REFERENCED_COLUMN_NAME'] !== 'id' || !in_array($rows[0]['DELETE_RULE'], ['RESTRICT', 'NO ACTION'], true) || !in_array($rows[0]['UPDATE_RULE'], ['RESTRICT', 'NO ACTION'], true)) {
            throw new RuntimeException('Restricción incompatible en ' . $table . '.' . $column);
        }
    }
    return $missing;
}

// Caller must pause writes and validate a backup before applying to a live DB.
// DDL commits implicitly. Cleanup is transactional; constraints are retryable.
function applyHistoricalIntegrity(PDO $conn, int $expectedCart, int $expectedSales): array
{
    if ($conn->inTransaction()) throw new RuntimeException('La migración requiere una conexión sin transacción activa.');
    $counts = historicalOrphanCounts($conn);
    if ($counts['cart'] !== $expectedCart || $counts['sales'] !== $expectedSales || array_sum(array_slice($counts, 2)) !== 0) throw new RuntimeException('Conteos inesperados; vuelve a auditar antes de migrar.');
    $missing = validateHistoricalForeignKeys($conn);
    $tables = ['users', 'products', 'sales', 'cart', 'details', 'checkout_requests'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
        $stmt->execute(['table' => $table]);
        if ($stmt->fetchColumn() !== 'InnoDB') throw new RuntimeException('Se requiere InnoDB: ' . $table);
    }
    foreach (historicalForeignKeys() as [$table, $column, $parent]) {
        foreach ([[$table, $column], [$parent, 'id']] as [$target, $field]) {
            $stmt = $conn->prepare('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table AND COLUMN_NAME=:column');
            $stmt->execute(['table' => $target, 'column' => $field]);
            if (!preg_match('/\Aint(?:\(\d+\))?\z/', (string) $stmt->fetchColumn())) throw new RuntimeException('Tipo de referencia incompatible: ' . $target . '.' . $field);
        }
    }
    $columns = $conn->query('SHOW COLUMNS FROM sales')->fetchAll(PDO::FETCH_ASSOC);
    $legacy = array_values(array_filter($columns, function ($c) { return $c['Field'] === 'legacy_user_id'; }));
    if (!$legacy) $conn->exec('ALTER TABLE sales ADD legacy_user_id INT NULL');
    elseif (!preg_match('/\Aint(?:\(\d+\))?\z/', $legacy[0]['Type']) || $legacy[0]['Null'] !== 'YES') throw new RuntimeException('Columna legacy_user_id incompatible.');
    $owner = array_values(array_filter($columns, function ($c) { return $c['Field'] === 'user_id'; }))[0];
    if ($owner['Null'] !== 'YES') $conn->exec('ALTER TABLE sales MODIFY user_id INT NULL');
    $conn->exec("CREATE TABLE IF NOT EXISTS cart_orphan_archive (
        id INT NOT NULL PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL,
        quantity INT NOT NULL, fecha_hora_inicio TIMESTAMP NOT NULL,
        archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        reason VARCHAR(40) NOT NULL DEFAULT 'missing_user'
    ) ENGINE=InnoDB");
    $archiveColumns = $conn->query('SHOW COLUMNS FROM cart_orphan_archive')->fetchAll(PDO::FETCH_ASSOC);
    $archiveTypes = array_column($archiveColumns, 'Type', 'Field');
    foreach (['id', 'user_id', 'product_id', 'quantity'] as $field) {
        if (!preg_match('/\Aint(?:\(\d+\))?\z/', $archiveTypes[$field] ?? '')) throw new RuntimeException('Archivo incompatible: ' . $field);
    }
    if (($archiveTypes['fecha_hora_inicio'] ?? '') !== 'timestamp' || ($archiveTypes['archived_at'] ?? '') !== 'datetime' || ($archiveTypes['reason'] ?? '') !== 'varchar(40)') throw new RuntimeException('Columnas de archivo incompatibles.');
    foreach ($archiveColumns as $column) {
        if (in_array($column['Field'], ['id', 'user_id', 'product_id', 'quantity', 'fecha_hora_inicio', 'archived_at', 'reason'], true) && $column['Null'] !== 'NO') throw new RuntimeException('El archivo no admite campos originales nulos.');
    }
    $archiveIndexes = $conn->query("SHOW INDEX FROM cart_orphan_archive WHERE Key_name='PRIMARY'")->fetchAll(PDO::FETCH_ASSOC);
    if (count($archiveIndexes) !== 1 || $archiveIndexes[0]['Column_name'] !== 'id') throw new RuntimeException('El archivo requiere una clave primaria única por ID original.');
    $archiveEngine = $conn->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='cart_orphan_archive'")->fetchColumn();
    if ($archiveEngine !== 'InnoDB') throw new RuntimeException('El archivo requiere InnoDB para preservar la transacción.');
    $conn->beginTransaction();
    try {
        $now = historicalOrphanCounts($conn);
        if ($now !== $counts) throw new RuntimeException('Los datos cambiaron durante la preparación.');
        $conn->exec("INSERT INTO cart_orphan_archive (id,user_id,product_id,quantity,fecha_hora_inicio)
            SELECT c.id,c.user_id,c.product_id,c.quantity,c.fecha_hora_inicio FROM cart c LEFT JOIN users u ON u.id=c.user_id
            WHERE u.id IS NULL AND NOT EXISTS (SELECT 1 FROM cart_orphan_archive a WHERE a.id=c.id)");
        $mismatches = (int) $conn->query("SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN cart_orphan_archive a ON a.id=c.id
            WHERE u.id IS NULL AND (a.id IS NULL OR NOT (a.user_id <=> c.user_id) OR NOT (a.product_id <=> c.product_id)
            OR NOT (a.quantity <=> c.quantity) OR NOT (a.fecha_hora_inicio <=> c.fecha_hora_inicio) OR NOT (a.reason <=> 'missing_user'))")->fetchColumn();
        if ($mismatches) throw new RuntimeException('El archivo no coincide con las filas originales.');
        $conn->exec('DELETE c FROM cart c LEFT JOIN users u ON u.id=c.user_id WHERE u.id IS NULL');
        $conflicts = (int) $conn->query('SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE s.user_id IS NOT NULL AND u.id IS NULL AND s.legacy_user_id IS NOT NULL AND s.legacy_user_id<>s.user_id')->fetchColumn();
        if ($conflicts) throw new RuntimeException('Referencia histórica incompatible.');
        $conn->exec('UPDATE sales s LEFT JOIN users u ON u.id=s.user_id SET s.legacy_user_id=s.user_id,s.user_id=NULL WHERE s.user_id IS NOT NULL AND u.id IS NULL');
        if (array_sum(historicalOrphanCounts($conn)) !== 0) throw new RuntimeException('Persisten referencias ausentes.');
        $conn->commit();
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        throw $e;
    }
    foreach ($missing as [$table, $column, $parent, $name]) {
        $conn->exec("ALTER TABLE `$table` ADD CONSTRAINT `$name` FOREIGN KEY (`$column`) REFERENCES `$parent` (id) ON DELETE RESTRICT ON UPDATE RESTRICT");
    }
    if (validateHistoricalForeignKeys($conn)) throw new RuntimeException('Faltan restricciones.');
    return $counts;
}
