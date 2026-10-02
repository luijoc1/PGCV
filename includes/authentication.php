<?php

function accountSessionSignature(array $account)
{
    return hash('sha256', (string) $account['password']);
}

function authenticatedAccount(PDO $conn, array $session, $role)
{
    $key = $role === 1 ? 'admin' : 'user';
    $id = filter_var($session[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || !is_string($session['auth_signature'] ?? null)) {
        return null;
    }
    $stmt = $conn->prepare('SELECT * FROM users WHERE id=:id');
    $stmt->execute(['id' => $id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$account || (int) $account['status'] !== 1 || (int) $account['type'] !== $role
        || !hash_equals(accountSessionSignature($account), $session['auth_signature'])) {
        return null;
    }
    return $account;
}

function clearAccountSession()
{
    unset($_SESSION['user'], $_SESSION['admin'], $_SESSION['auth_signature']);
}

function establishAccountSession(array $account)
{
    if ((int) $account['status'] !== 1 || !in_array((int) $account['type'], [0, 1], true)) {
        throw new InvalidArgumentException('Cuenta no habilitada.');
    }
    if (!session_regenerate_id(true)) {
        throw new RuntimeException('No se pudo renovar la sesión.');
    }
    clearAccountSession();
    unset($_SESSION['csrf_token']);
    $_SESSION[(int) $account['type'] === 1 ? 'admin' : 'user'] = $account['id'];
    $_SESSION['auth_signature'] = accountSessionSignature($account);
}

function editedPasswordHash($password, $currentHash)
{
    if (!is_string($password) || ($password !== '' && strlen($password) < 6)) {
        throw new InvalidArgumentException('La nueva contraseña debe tener al menos 6 caracteres.');
    }
    return $password === '' ? $currentHash : password_hash($password, PASSWORD_DEFAULT);
}

function publicAccountFields(array $account)
{
    return array_intersect_key($account, array_flip(['id', 'email', 'firstname', 'lastname', 'address', 'contact_info', 'photo', 'type', 'status', 'created_on']));
}

// Reserva el intento antes de comprobar credenciales, compartido por todas las sesiones.
function reserveLoginAttempt(PDO $conn, $email, $ip, $now = null)
{
    $now = $now ?? time();
    $buckets = [hash('sha256', 'email:' . strtolower($email)) => 5, hash('sha256', 'ip:' . $ip) => 30];
    ksort($buckets);
    $sqlite = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $conn->beginTransaction();
    try {
        $records = [];
        foreach ($buckets as $key => $limit) {
            $sql = $sqlite
                ? 'INSERT OR IGNORE INTO login_attempts (bucket_key, attempts, window_started) VALUES (:key, 0, :now)'
                : 'INSERT INTO login_attempts (bucket_key, attempts, window_started) VALUES (:key, 0, :now) ON DUPLICATE KEY UPDATE bucket_key=bucket_key';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['key' => $key, 'now' => $now]);
            $stmt = $conn->prepare('SELECT attempts, window_started FROM login_attempts WHERE bucket_key=:key' . ($sqlite ? '' : ' FOR UPDATE'));
            $stmt->execute(['key' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($now - (int) $row['window_started'] >= 900) {
                $row = ['attempts' => 0, 'window_started' => $now];
            }
            $records[$key] = $row;
        }
        $retry = 0;
        foreach ($buckets as $key => $limit) {
            if ((int) $records[$key]['attempts'] >= $limit) {
                $retry = max($retry, 900 - ($now - (int) $records[$key]['window_started']));
            }
        }
        if (!$retry) {
            $stmt = $conn->prepare('UPDATE login_attempts SET attempts=:attempts, window_started=:started WHERE bucket_key=:key');
            foreach ($records as $key => $row) {
                $stmt->execute(['attempts' => (int) $row['attempts'] + 1, 'started' => $row['window_started'], 'key' => $key]);
            }
        }
        $conn->commit();
        return $retry;
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        throw $e;
    }
}

function clearSuccessfulLoginAttempts(PDO $conn, $email)
{
    $stmt = $conn->prepare('DELETE FROM login_attempts WHERE bucket_key=:key');
    $stmt->execute(['key' => hash('sha256', 'email:' . strtolower($email))]);
}

function createAdministrator(PDO $conn, $email, $password)
{
    if (!is_string($email) || strlen($email) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 12) {
        throw new InvalidArgumentException('Correo inválido o contraseña administrativa de menos de 12 caracteres.');
    }
    $stmt = $conn->prepare("INSERT INTO users (email,password,firstname,lastname,type,status,created_on) VALUES (:email,:password,'Admin','Local',1,1,:created)");
    $stmt->execute(['email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'created' => (new DateTimeImmutable('now', new DateTimeZone('America/Bogota')))->format('Y-m-d')]);
    return $conn->lastInsertId();
}
