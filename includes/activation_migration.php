<?php

function ensureActivationExpiryColumn(PDO $conn): bool
{
    $columns = $conn->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('activate_expires_at', $columns, true)) {
        return false;
    }
    $conn->exec(file_get_contents(__DIR__ . '/../migrations/007_activation_expiry.sql'));
    return true;
}
