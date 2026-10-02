<?php

function passwordResetParameters($code, $userId)
{
    if (!is_string($code) || !preg_match('/\A[a-f0-9]{64}\z/', $code)) {
        return null;
    }
    if (!is_string($userId) || !preg_match('/\A[1-9][0-9]*\z/', $userId)) {
        return null;
    }
    $id = filter_var($userId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : ['code' => $code, 'user' => $id];
}

function issuePasswordReset(PDO $conn, $userId)
{
    $code = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("UPDATE users SET reset_token_hash=:hash, reset_expires_at=:expires, reset_code='' WHERE id=:id");
    $stmt->execute([
        'hash' => hash('sha256', $code),
        'expires' => gmdate('Y-m-d H:i:s', time() + 3600),
        'id' => $userId,
    ]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('No se pudo generar el enlace de recuperación.');
    }
    return $code;
}

function consumePasswordReset(PDO $conn, $code, $userId, $password)
{
    if (passwordResetParameters($code, (string) $userId) === null) {
        return false;
    }
    // Cambiar la contraseña y consumir el código en una única operación.
    $stmt = $conn->prepare("UPDATE users SET password=:password, reset_code='', reset_token_hash=NULL, reset_expires_at=NULL WHERE id=:id AND reset_token_hash=:hash AND reset_expires_at>:now");
    $stmt->execute([
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $userId,
        'hash' => hash('sha256', $code),
        'now' => gmdate('Y-m-d H:i:s'),
    ]);
    return $stmt->rowCount() === 1;
}
