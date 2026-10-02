<?php
require_once __DIR__ . '/authentication.php';

function adminUserId($value): int
{
    if ((!is_string($value) && !is_int($value)) || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false) {
        throw new InvalidArgumentException('Seleccione un usuario válido.');
    }
    return (int) $value;
}

function adminUserFields(array $input): array
{
    $result = [];
    foreach (['firstname' => 50, 'lastname' => 50, 'email' => 200, 'address' => 2000, 'contact' => 100] as $field => $limit) {
        $value = $input[$field] ?? (in_array($field, ['address', 'contact'], true) ? '' : null);
        if (!is_string($value) || mb_strlen(trim($value), 'UTF-8') > $limit || (in_array($field, ['firstname', 'lastname', 'email'], true) && trim($value) === '')) {
            throw new InvalidArgumentException('Revisa los campos obligatorios y la longitud de los datos del usuario.');
        }
        $result[$field] = trim($value);
    }
    if (!filter_var($result['email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Ingresa un correo electrónico válido.');
    return $result;
}

function administerUser(PDO $conn, string $action, array $input, callable $logger, string $photo = ''): void
{
    if (!in_array($action, ['add', 'edit', 'activate', 'delete'], true)) throw new InvalidArgumentException('Operación inválida.');
    $id = $action === 'add' ? null : adminUserId($input['id'] ?? null);
    $fields = in_array($action, ['add', 'edit'], true) ? adminUserFields($input) : [];
    $conn->beginTransaction();
    try {
        $previous = null;
        if ($id !== null) {
            $stmt = $conn->prepare('SELECT * FROM users WHERE id=:id');
            $stmt->execute(['id' => $id]);
            $previous = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$previous) throw new InvalidArgumentException('El usuario no existe.');
        }
        if ($fields) {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email=:email AND id<>:id LIMIT 1');
            $stmt->execute(['email' => $fields['email'], 'id' => $id ?? 0]);
            if ($stmt->fetchColumn() !== false) throw new InvalidArgumentException('El correo electrónico ya está registrado.');
            $password = $input['password'] ?? '';
            if ($action === 'add' && (!is_string($password) || strlen($password) < 6)) throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
            $hash = editedPasswordHash($password, $previous['password'] ?? '');
            $parameters = array_merge($fields, ['password' => $hash]);
            if ($action === 'add') {
                $stmt = $conn->prepare('INSERT INTO users (email,password,firstname,lastname,address,contact_info,photo,type,status,created_on) VALUES (:email,:password,:firstname,:lastname,:address,:contact,:photo,0,1,:created)');
                $parameters += ['photo' => $photo, 'created' => date('Y-m-d')];
            } else {
                $stmt = $conn->prepare('UPDATE users SET email=:email,password=:password,firstname=:firstname,lastname=:lastname,address=:address,contact_info=:contact WHERE id=:id');
                $parameters['id'] = $id;
            }
            $stmt->execute($parameters);
            if ($action === 'add') $id = (int) $conn->lastInsertId();
        } elseif ($action === 'delete') {
            foreach (['cart', 'sales', 'checkout_requests'] as $table) {
                $stmt = $conn->prepare('SELECT 1 FROM ' . $table . ' WHERE user_id=:id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) throw new InvalidArgumentException('No se puede eliminar el usuario porque tiene carritos, ventas o solicitudes de compra asociadas.');
            }
            $stmt = $conn->prepare('DELETE FROM users WHERE id=:id');
            $stmt->execute(['id' => $id]);
        } elseif ((int) $previous['status'] !== 1) {
            $stmt = $conn->prepare('UPDATE users SET status=1 WHERE id=:id');
            $stmt->execute(['id' => $id]);
        } else {
            $conn->commit();
            return;
        }
        $current = null;
        if ($action !== 'delete') {
            $stmt = $conn->prepare('SELECT * FROM users WHERE id=:id');
            $stmt->execute(['id' => $id]);
            $current = publicAccountFields($stmt->fetch(PDO::FETCH_ASSOC));
        }
        $logger($id, $previous ? publicAccountFields($previous) : null, $current, ['add' => 'INSERT', 'edit' => 'UPDATE', 'activate' => 'UPDATE', 'delete' => 'DELETE'][$action]);
        $conn->commit();
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        throw $e;
    }
}
