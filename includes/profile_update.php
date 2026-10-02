<?php
require_once __DIR__ . '/user_administration.php';
require_once __DIR__ . '/image_upload.php';

function updateOwnProfile(PDO $conn, array $account, array $input, $file): void
{
    $currentPassword = $input['curr_password'] ?? null;
    if (!is_string($currentPassword) || !password_verify($currentPassword, $account['password'])) {
        throw new InvalidArgumentException('Contraseña actual incorrecta.');
    }
    $input += ['address' => $account['address'], 'contact' => $account['contact_info']];
    if ((int) $account['type'] === 1) {
        $input['address'] = $account['address'];
        $input['contact'] = $account['contact_info'];
    }
    $fields = adminUserFields($input);
    $hash = editedPasswordHash($input['password'] ?? '', $account['password']);
    $filename = null;
    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare('SELECT * FROM users WHERE id=:id' . ($conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : ''));
        $stmt->execute(['id' => $account['id']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current || (int) $current['status'] !== 1 || (int) $current['type'] !== (int) $account['type'] || !hash_equals(accountSessionSignature($account), accountSessionSignature($current))) {
            throw new InvalidArgumentException('La cuenta cambió. Inicia sesión de nuevo.');
        }
        $stmt = $conn->prepare('SELECT id FROM users WHERE email=:email AND id<>:id LIMIT 1');
        $stmt->execute(['email' => $fields['email'], 'id' => $account['id']]);
        if ($stmt->fetchColumn() !== false) throw new InvalidArgumentException('El correo electrónico ya está registrado.');
        $filename = saveImageUpload($file);
        $stmt = $conn->prepare('UPDATE users SET email=:email,password=:password,firstname=:firstname,lastname=:lastname,address=:address,contact_info=:contact,photo=:photo WHERE id=:id');
        $stmt->execute($fields + ['password' => $hash, 'photo' => $filename ?? $current['photo'], 'id' => $account['id']]);
        $conn->commit();
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        removeNewImageUpload($filename);
        throw $e;
    }
}
