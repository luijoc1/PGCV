<?php

function databaseIntegrityCounts(PDO $conn)
{
    $queries = [
        'cart_orphans' => 'SELECT COUNT(*) FROM cart c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN products p ON p.id=c.product_id WHERE u.id IS NULL OR p.id IS NULL',
        'sales_orphans' => 'SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id=s.user_id WHERE u.id IS NULL',
        'details_sale_orphans' => 'SELECT COUNT(*) FROM details d LEFT JOIN sales s ON s.id=d.sales_id WHERE s.id IS NULL',
        'category_orphans' => 'SELECT COUNT(*) FROM products p LEFT JOIN category c ON c.id=p.category_id WHERE c.id IS NULL',
        'invalid_products' => 'SELECT COUNT(*) FROM products WHERE price<0 OR stock<0 OR stock_minimo<0 OR descuento<0 OR descuento>100 OR price>=100000000000000',
        'invalid_sales' => 'SELECT COUNT(*) FROM sales WHERE total<0 OR total>=100000000000000',
        'invalid_cart' => 'SELECT COUNT(*) FROM cart WHERE quantity<=0',
        'invalid_details' => 'SELECT COUNT(*) FROM details WHERE quantity<=0',
        'invalid_snapshots' => 'SELECT COUNT(*) FROM details WHERE NOT ((product_name IS NULL AND original_price IS NULL AND discount_percent IS NULL AND unit_price IS NULL) OR (product_name IS NOT NULL AND original_price IS NOT NULL AND discount_percent IS NOT NULL AND unit_price IS NOT NULL AND original_price>=0 AND discount_percent BETWEEN 0 AND 100 AND unit_price>=0 AND unit_price<=original_price))',
        'price_rounding' => 'SELECT COUNT(*) FROM products WHERE ABS(price-ROUND(price,2))>0.000001',
        'sales_rounding' => 'SELECT COUNT(*) FROM sales WHERE ABS(total-ROUND(total,2))>0.000001',
    ];
    foreach (['users' => ['email'], 'products' => ['slug'], 'category' => ['cat_slug'], 'sales' => ['pay_id'], 'cart' => ['user_id', 'product_id']] as $table => $columns) {
        $group = implode(',', $columns);
        $queries[$table . '_duplicates'] = "SELECT COUNT(*) FROM (SELECT $group FROM $table GROUP BY $group HAVING COUNT(*)>1) duplicates";
    }
    $counts = [];
    foreach ($queries as $name => $sql) $counts[$name] = (int) $conn->query($sql)->fetchColumn();
    return $counts;
}

function integrityAlterations()
{
    return [
        'users' => [
            'columns' => "MODIFY password VARCHAR(255) NOT NULL, ALTER type SET DEFAULT 0, ALTER status SET DEFAULT 0, ALTER address SET DEFAULT '', ALTER contact_info SET DEFAULT '', ALTER photo SET DEFAULT '', ALTER activate_code SET DEFAULT '', ALTER reset_code SET DEFAULT ''",
            'indexes' => ['uq_users_email' => 'UNIQUE (email)'],
        ],
        'category' => ['indexes' => ['uq_category_slug' => 'UNIQUE (cat_slug)']],
        'products' => [
            'columns' => "MODIFY price DECIMAL(18,2) NOT NULL, ALTER stock SET DEFAULT 0, ALTER photo SET DEFAULT '', ALTER counter SET DEFAULT 0, MODIFY date_view DATE NOT NULL DEFAULT (CURRENT_DATE)",
            'indexes' => ['uq_products_slug' => 'UNIQUE (slug)', 'ix_products_category' => '(category_id)'],
            'constraints' => ['ck_products_values' => 'CHECK (price>=0 AND stock>=0 AND stock_minimo>=0 AND descuento BETWEEN 0 AND 100)', 'fk_products_category' => 'FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE RESTRICT ON UPDATE RESTRICT'],
        ],
        'cart' => [
            'columns' => 'MODIFY fecha_hora_inicio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'indexes' => ['uq_cart_user_product' => 'UNIQUE (user_id,product_id)', 'ix_cart_product' => '(product_id)'],
            'constraints' => ['ck_cart_quantity' => 'CHECK (quantity>0)'],
        ],
        'sales' => [
            'columns' => 'MODIFY total DECIMAL(18,2) NOT NULL',
            'indexes' => ['uq_sales_pay_id' => 'UNIQUE (pay_id)', 'ix_sales_user_date' => '(user_id,sales_date)', 'ix_sales_date' => '(sales_date)'],
            'constraints' => ['ck_sales_total' => 'CHECK (total>=0)'],
        ],
        'details' => [
            'indexes' => ['ix_details_sale' => '(sales_id)', 'ix_details_product' => '(product_id)'],
            'constraints' => [
                'ck_details_quantity' => 'CHECK (quantity>0)',
                'ck_details_snapshot' => 'CHECK ((product_name IS NULL AND original_price IS NULL AND discount_percent IS NULL AND unit_price IS NULL) OR (product_name IS NOT NULL AND original_price IS NOT NULL AND discount_percent IS NOT NULL AND unit_price IS NOT NULL AND original_price>=0 AND discount_percent BETWEEN 0 AND 100 AND unit_price>=0 AND unit_price<=original_price))',
                'fk_details_sale' => 'FOREIGN KEY (sales_id) REFERENCES sales(id) ON DELETE RESTRICT ON UPDATE RESTRICT',
            ],
        ],
    ];
}

function applyDatabaseIntegrity(PDO $conn)
{
    $counts = databaseIntegrityCounts($conn);
    foreach ($counts as $name => $count) {
        if ($count && !in_array($name, ['cart_orphans', 'sales_orphans'], true)) {
            throw new RuntimeException('La migración requiere revisar: ' . $name . ' (' . $count . ').');
        }
    }
    foreach (integrityAlterations() as $table => $definition) {
        $operations = isset($definition['columns']) ? [$definition['columns']] : [];
        $indexes = $conn->query('SHOW INDEX FROM ' . $table)->fetchAll(PDO::FETCH_ASSOC);
        $indexNames = array_column($indexes, 'Key_name');
        foreach ($definition['indexes'] ?? [] as $name => $sql) {
            if (!in_array($name, $indexNames, true)) {
                $operations[] = strpos($sql, 'UNIQUE') === 0 ? 'ADD UNIQUE INDEX ' . $name . substr($sql, 6) : 'ADD INDEX ' . $name . ' ' . $sql;
            }
        }
        $create = $conn->query('SHOW CREATE TABLE ' . $table)->fetch(PDO::FETCH_NUM)[1];
        foreach ($definition['constraints'] ?? [] as $name => $sql) {
            if (strpos($create, '`' . $name . '`') === false) $operations[] = 'ADD CONSTRAINT ' . $name . ' ' . $sql;
        }
        if ($operations) $conn->exec('ALTER TABLE ' . $table . ' ' . implode(', ', $operations));
    }
    return $counts;
}
