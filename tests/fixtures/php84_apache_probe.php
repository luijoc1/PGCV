<?php
// Only mapped by the isolated Apache verifier. No orders or real email.
$project = getenv('PGCV_TEST_PROJECT');
if (!$project || !is_file($project . '/includes/config.php')) { http_response_code(404); exit(); }
require_once $project . '/includes/config.php';
require_once $project . '/includes/historical_integrity.php';
require_once $project . '/includes/mailer.php';
session_start();
$_SESSION['probe_count'] = ($_SESSION['probe_count'] ?? 0) + 1;
$conn = new PDO(DB_SERVER, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$conn->exec('SET TRANSACTION READ ONLY');
$conn->beginTransaction();
$counts = historicalOrphanCounts($conn);
$missing = validateHistoricalForeignKeys($conn);
$conn->commit();
$image = imagecreatetruecolor(10, 10);
ob_start(); imagepng($image); $png = ob_get_clean();
$pdf = new TCPDF();
$pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->AddPage();
$pdf->writeHTML('<p>Fictitious Apache compatibility probe</p>');
$bytes = $pdf->Output('probe.pdf', 'S');
$mail = configuredMailer();
header('Content-Type: application/json');
echo json_encode([
    'php_version' => PHP_VERSION, 'sapi' => PHP_SAPI, 'thread_safe' => (bool) PHP_ZTS,
    'ini' => php_ini_loaded_file(), 'session_count' => $_SESSION['probe_count'],
    'required_extensions' => array_map('extension_loaded', ['pdo_mysql', 'openssl', 'dom', 'mbstring', 'fileinfo', 'gd', 'curl']),
    'orphan_count' => array_sum($counts), 'missing_constraints' => count($missing),
    'png_ok' => substr($png, 0, 8) === "\x89PNG\r\n\x1a\n",
    'pdf_ok' => substr($bytes, 0, 5) === '%PDF-',
    'smtp_time_limit' => $mail->getSMTPInstance()->Timelimit,
    'tls_identity_required' => $mail->SMTPOptions['ssl']['verify_peer'] && $mail->SMTPOptions['ssl']['verify_peer_name'],
]);
