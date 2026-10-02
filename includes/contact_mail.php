<?php
require_once __DIR__ . '/authentication.php';

function contactMessage(array $input)
{
    $result = [];
    foreach (['name' => 100, 'mail' => 200, 'subject' => 150, 'message' => 5000] as $field => $limit) {
        $value = $input[$field] ?? null;
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || trim($value) === '' || mb_strlen($value, 'UTF-8') > $limit
            || ($field !== 'message' && preg_match('/[\x00-\x1f\x7f]/', $value)) || strpos($value, "\0") !== false) {
            throw new InvalidArgumentException('Completa los datos de contacto sin exceder la longitud permitida.');
        }
        $result[$field] = trim($value);
    }
    if (!filter_var($result['mail'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Ingresa un correo válido.');
    return $result;
}

function reserveContactAttempt(PDO $conn, $ip, $now = null)
{
    // Espacio separado del login; cinco solicitudes por IP cada quince minutos.
    return reserveLoginAttempt($conn, 'contact-ip:' . hash('sha256', $ip), 'contact:' . $ip, $now);
}
