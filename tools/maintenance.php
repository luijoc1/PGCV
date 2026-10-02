<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
$path = __DIR__ . '/../storage/maintenance.lock';
$action = $argv[1] ?? 'status';
if (!in_array($action, ['on', 'off', 'status'], true)) {
    fwrite(STDERR, "Uso: php tools/maintenance.php on|off|status\n"); exit(1);
}
// Serializes operator actions with the migration; never remove a running gate.
$guard = fopen(__DIR__ . '/../storage/backups/maintenance-control.lock', 'c');
if (!$guard || !flock($guard, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Hay otra operación de mantenimiento en curso.\n"); exit(1);
}
try {
    if ($action === 'on' && !is_file($path)) {
        $file = fopen($path, 'xb');
        if (!$file) throw new RuntimeException('No se pudo activar la pausa.');
        try {
            $token = json_encode(['started_at' => date(DATE_ATOM), 'token' => bin2hex(random_bytes(16))]) . "\n";
            if (fwrite($file, $token) !== strlen($token)) throw new RuntimeException('Pausa incompleta.');
        } finally { fclose($file); }
    }
    if ($action === 'off' && is_file($path) && !unlink($path)) throw new RuntimeException('No se pudo retirar la pausa.');
    echo is_file($path) ? "Pausa HTTP activa. Detén también cron, CLI y escrituras externas; espera a que terminen las peticiones previas.\n" : "Pausa HTTP inactiva.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n"); exit(1);
} finally { flock($guard, LOCK_UN); fclose($guard); }
