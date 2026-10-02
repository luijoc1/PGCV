<?php

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) !== 64) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return is_string($token) && $token !== ''
        && isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function validCSRFRequest($method, $formToken, $headerToken)
{
    return $method === 'POST' && validateCSRFToken($formToken ?? $headerToken);
}

function requireValidCSRFRequest($json = false)
{
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (validCSRFRequest($method, $_POST['csrf_token'] ?? null, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        return;
    }
    http_response_code($method === 'POST' ? 403 : 405);
    if ($method !== 'POST') {
        header('Allow: POST');
    }
    $message = 'Solicitud inválida. Recarga la página e intenta de nuevo.';
    if ($json) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => true, 'success' => false, 'message' => $message]);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
    exit();
}
