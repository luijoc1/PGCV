<?php

function validateImageUpload($file)
{
    if ($file === null) {
        return null;
    }
    if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
        throw new InvalidArgumentException('La carga de la foto es inválida.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('No se pudo cargar la foto. Intenta con una imagen de hasta 5 MB.');
    }
    $path = $file['tmp_name'] ?? null;
    if (!is_string($path) || !is_file($path)) {
        throw new InvalidArgumentException('La carga de la foto es inválida.');
    }
    $size = filesize($path);
    if ($size === false || $size <= 0 || $size > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('La foto debe ocupar entre 1 byte y 5 MB.');
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $image = @getimagesize($path);
    if (!isset($types[$mime]) || !$image || ($image['mime'] ?? '') !== $mime) {
        throw new InvalidArgumentException('Selecciona una imagen JPG, PNG, GIF o WebP válida.');
    }
    if ($image[0] <= 0 || $image[1] <= 0 || $image[0] * $image[1] > 20000000) {
        throw new InvalidArgumentException('La foto no puede superar los 20 millones de píxeles.');
    }
    if (!function_exists('imagecreatefromstring')) {
        throw new RuntimeException('La extensión GD es necesaria para verificar las imágenes.');
    }
    $decoded = @imagecreatefromstring(file_get_contents($path));
    if ($decoded === false) {
        throw new InvalidArgumentException('La imagen está dañada o su formato no es válido.');
    }
    imagedestroy($decoded);
    return $types[$mime];
}

function saveImageUpload($file)
{
    $extension = validateImageUpload($file);
    if ($extension === null) {
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('La carga de la foto es inválida.');
    }
    // El nombre y el tipo declarados por el cliente nunca determinan el destino.
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/' . $filename)) {
        throw new RuntimeException('No se pudo guardar la foto. Intenta de nuevo.');
    }
    return $filename;
}

function photoUploadOrRedirect($file, $fallback, $redirect, $required = false)
{
    try {
        $filename = saveImageUpload($file);
        if ($filename === null && $required) {
            throw new InvalidArgumentException('Selecciona una foto para subir.');
        }
        return $filename ?? $fallback;
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
    } catch (RuntimeException $e) {
        error_log('Error al guardar foto: ' . $e->getMessage());
        $_SESSION['error'] = 'No se pudo guardar la foto. Intenta de nuevo.';
    }
    header('location: ' . $redirect);
    exit();
}
