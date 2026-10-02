<?php
require_once __DIR__ . '/cart_operations.php';
require_once __DIR__ . '/sale_history.php';

function checkoutBilling(array $input)
{
    $limits = ['nombre_facturacion' => 100, 'documento' => 30, 'direccion' => 200, 'telefono' => 20, 'ciudad' => 100];
    $result = [];
    foreach ($limits as $field => $limit) {
        $value = $input[$field] ?? null;
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
            throw new InvalidArgumentException('Revisa los datos de facturación.');
        }
        $value = trim($value);
        if ($value === '' || mb_strlen($value, 'UTF-8') > $limit || preg_match('/[\x00-\x1f\x7f]/u', $value)) {
            throw new InvalidArgumentException('Completa los datos de facturación sin exceder la longitud permitida.');
        }
        $result[$field] = $value;
    }
    if (!preg_match('/\A[0-9A-Za-z .-]+\z/', $result['documento'])) {
        throw new InvalidArgumentException('El documento contiene caracteres inválidos.');
    }
    if (!preg_match('/\A\+?[0-9 ()-]+\z/', $result['telefono']) || strlen(preg_replace('/\D/', '', $result['telefono'])) < 7) {
        throw new InvalidArgumentException('Ingresa un teléfono válido de al menos siete dígitos.');
    }
    $method = $input['metodo_pago'] ?? null;
    if (!in_array($method, ['tarjeta', 'transferencia', 'efectivo'], true)) {
        throw new InvalidArgumentException('Selecciona un método de pago válido.');
    }
    $result['metodo_pago'] = $method;
    return $result;
}

function issueCheckoutToken()
{
    $token = bin2hex(random_bytes(32));
    $tokens = is_array($_SESSION['checkout_tokens'] ?? null) ? $_SESSION['checkout_tokens'] : [];
    $tokens[$token] = time();
    $_SESSION['checkout_tokens'] = array_slice($tokens, -20, null, true);
    return $token;
}

function validCheckoutToken($token)
{
    return is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token)
        && isset($_SESSION['checkout_tokens'][$token]);
}

function completeCheckout(PDO $conn, $userId, array $input, $token, callable $log = null)
{
    $billing = checkoutBilling($input);
    $owner = cartPositiveInteger($userId);
    if ($owner === null || !is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
        throw new InvalidArgumentException('Solicitud de compra inválida.');
    }
    $key = hash('sha256', $token);
    $sqlite = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $lock = $sqlite ? '' : ' FOR UPDATE';
    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare($sqlite
            ? 'INSERT OR IGNORE INTO checkout_requests (request_key,user_id) VALUES (:key,:owner)'
            : 'INSERT INTO checkout_requests (request_key,user_id) VALUES (:key,:owner) ON DUPLICATE KEY UPDATE request_key=request_key');
        $stmt->execute(['key' => $key, 'owner' => $owner]);
        $stmt = $conn->prepare('SELECT user_id,sales_id FROM checkout_requests WHERE request_key=:key' . $lock);
        $stmt->execute(['key' => $key]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int) $request['user_id'] !== $owner) throw new InvalidArgumentException('Solicitud de compra inválida.');
        if ($request['sales_id'] !== null) {
            $stmt = $conn->prepare('SELECT id,pay_id,total FROM sales WHERE id=:id AND user_id=:owner');
            $stmt->execute(['id' => $request['sales_id'], 'owner' => $owner]);
            $sale = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sale) throw new InvalidArgumentException('El pedido anterior ya no está disponible.');
            $conn->commit();
            return ['sales_id' => $sale['id'], 'pay_id' => $sale['pay_id'], 'total' => $sale['total'], 'replayed' => true, 'products' => []];
        }
        $stmt = $conn->prepare('SELECT id,status,type FROM users WHERE id=:id' . $lock);
        $stmt->execute(['id' => $owner]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$account || (int) $account['status'] !== 1 || (int) $account['type'] !== 0) throw new InvalidArgumentException('La cuenta no está habilitada para comprar.');

        $stmt = $conn->prepare('SELECT id,product_id,quantity,fecha_hora_inicio FROM cart WHERE user_id=:owner ORDER BY product_id,id' . $lock);
        $stmt->execute(['owner' => $owner]);
        $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$cart) throw new InvalidArgumentException('El carrito está vacío.');
        $products = [];
        $started = null;
        foreach ($cart as $item) {
            $id = cartPositiveInteger($item['product_id']);
            $quantity = cartPositiveInteger($item['quantity']);
            if ($id === null || $quantity === null) throw new InvalidArgumentException('El carrito contiene una cantidad o artículo inválidos.');
            if ($started === null || ($item['fecha_hora_inicio'] !== null && $item['fecha_hora_inicio'] < $started)) $started = $item['fecha_hora_inicio'];
            if (!isset($products[$id])) {
                $stmt = $conn->prepare('SELECT name,price,descuento,stock FROM products WHERE id=:id' . $lock);
                $stmt->execute(['id' => $id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$product) throw new InvalidArgumentException('Un producto del carrito ya no está disponible.');
                $products[$id] = array_merge($product, ['product_id' => $id, 'quantity' => 0]);
            }
            $products[$id]['quantity'] += $quantity;
            if ($products[$id]['quantity'] > 2147483647 || $products[$id]['quantity'] > (int) $products[$id]['stock']) throw new InvalidArgumentException('No hay stock suficiente para completar el pedido.');
        }
        $total = 0;
        foreach ($products as $product) $total += (float) saleSnapshot($product)['unit_price'] * $product['quantity'];
        $total = round($total, 2);
        if (!is_finite($total) || $total >= 100000000000000) throw new InvalidArgumentException('El importe del pedido supera el límite permitido.');

        $value = $conn->query('SELECT last_value FROM checkout_sequence WHERE id=1' . $lock)->fetchColumn();
        if ($value === false || !ctype_digit((string) $value) || (float) $value >= PHP_INT_MAX - 1) throw new RuntimeException('Secuencia de pedidos no disponible.');
        $payId = (int) $value + 1;
        $stmt = $conn->prepare('UPDATE checkout_sequence SET last_value=:value WHERE id=1');
        $stmt->execute(['value' => $payId]);
        $stmt = $conn->prepare('INSERT INTO sales (user_id,pay_id,sales_date,fecha_hora_inicio,nombre_facturacion,documento,direccion,telefono,ciudad,metodo_pago,total)
            VALUES (:user_id,:pay_id,:sales_date,:fecha_hora_inicio,:nombre_facturacion,:documento,:direccion,:telefono,:ciudad,:metodo_pago,:total)');
        $stmt->execute(array_merge($billing, ['user_id' => $owner, 'pay_id' => $payId, 'sales_date' => date('Y-m-d H:i:s'), 'fecha_hora_inicio' => $started, 'total' => $total]));
        $saleId = $conn->lastInsertId();
        $stmt = $conn->prepare('UPDATE products SET stock=stock-:quantity WHERE id=:id AND stock>=:minimum');
        foreach ($products as $product) {
            insertSaleDetail($conn, $saleId, $product);
            $stmt->execute(['quantity' => $product['quantity'], 'id' => $product['product_id'], 'minimum' => $product['quantity']]);
            if ($stmt->rowCount() !== 1) throw new InvalidArgumentException('El stock cambió. Revisa tu carrito e intenta de nuevo.');
        }
        $stmt = $conn->prepare('DELETE FROM cart WHERE id=:id AND user_id=:owner');
        foreach ($cart as $item) $stmt->execute(['id' => $item['id'], 'owner' => $owner]);
        $stmt = $conn->prepare('UPDATE checkout_requests SET sales_id=:sale WHERE request_key=:key');
        $stmt->execute(['sale' => $saleId, 'key' => $key]);
        $result = ['sales_id' => $saleId, 'pay_id' => $payId, 'total' => $total, 'replayed' => false, 'products' => array_values($products)];
        if ($log) $log($conn, $result);
        $conn->commit();
        return $result;
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        throw $e;
    }
}
