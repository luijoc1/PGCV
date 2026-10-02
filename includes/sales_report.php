<?php
require_once __DIR__ . '/output.php';

function salesReportRange($value)
{
    if (!is_string($value) || !preg_match('/\A\s*(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2}) - (\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})\s*\z/', $value, $matches)) {
        throw new InvalidArgumentException('Selecciona un rango de fechas válido.');
    }
    $dates = [];
    foreach ([$matches[1], $matches[2]] as $text) {
        $format = strpos($text, '/') !== false ? 'm/d/Y' : 'Y-m-d';
        $date = DateTimeImmutable::createFromFormat('!' . $format, $text, new DateTimeZone('America/Bogota'));
        if (!$date || $date->format($format) !== $text || (int) $date->format('Y') < 1000) throw new InvalidArgumentException('El rango contiene una fecha inexistente.');
        $dates[] = $date;
    }
    if ($dates[0] > $dates[1] || $dates[1]->format('Y-m-d') === '9999-12-31') throw new InvalidArgumentException('Revisa el orden y los límites de las fechas.');
    return ['from' => $dates[0]->format('Y-m-d H:i:s'), 'until' => $dates[1]->modify('+1 day')->format('Y-m-d H:i:s'), 'from_title' => $dates[0]->format('M d, Y'), 'to_title' => $dates[1]->format('M d, Y')];
}

function salesReportRows(PDO $conn, array $range)
{
    $stmt = $conn->prepare('SELECT sales.*, users.firstname, users.lastname FROM sales LEFT JOIN users ON users.id=sales.user_id WHERE sales.sales_date>=:from AND sales.sales_date<:until ORDER BY sales.sales_date DESC,sales.id DESC');
    $stmt->execute(['from' => $range['from'], 'until' => $range['until']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function salesReportHtml(array $rows)
{
    $html = '';
    $total = 0;
    foreach ($rows as $index => $row) {
        $amount = (float) $row['total'];
        $total += $amount;
        $name = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
        if ($name === '') $name = $row['nombre_facturacion'] ?: 'Cliente no disponible';
        $html .= '<tr style="background-color:' . ($index % 2 === 0 ? '#ffffff' : '#f5f7fa') . ';">'
            . '<td>' . escapeHtml(date('M d, Y', strtotime($row['sales_date']))) . '</td><td>' . escapeHtml($name) . '</td>'
            . '<td align="center">' . escapeHtml($row['pay_id']) . '</td><td align="right">&#36; ' . number_format($amount, 2) . '</td></tr>';
    }
    return $html . '<tr style="background-color:#1a2e4a;"><td colspan="3" align="right" style="color:#ffffff;"><b>TOTAL</b></td><td align="right" style="color:#ffffff;"><b>&#36; ' . number_format($total, 2) . '</b></td></tr>';
}
