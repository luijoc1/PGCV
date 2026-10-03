<?php
// Exercise the actual profile modal markup with fictitious data. No session,
// configuration, database connection or mail transport is loaded.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    exit();
}
require_once __DIR__ . '/../../includes/output.php';
require_once __DIR__ . '/../../includes/csrf.php';
$_SESSION = [];
$user = ['firstname' => 'Cliente', 'lastname' => 'de ejemplo', 'email' => 'cliente@example.invalid',
    'contact_info' => '', 'address' => 'Dirección ficticia'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comprobación local de modales públicos</title>
  <link rel="stylesheet" href="../../bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../../dist/css/bootstrap5-public-compat.css">
</head>
<body>
  <div class="container">
    <h1>Modales públicos con datos ficticios</h1>
    <p>Se utiliza la plantilla real de perfil. Esta página no guarda formularios.</p>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#edit">Editar cuenta de ejemplo</button>
    <button class="btn btn-default" data-bs-toggle="modal" data-bs-target="#transaction">Ver transacción ficticia</button>
    <p id="fixture-result" role="status"></p>
  </div>
  <?php require __DIR__ . '/../../includes/profile_modal.php'; ?>
  <script src="../../bower_components/jquery/dist/jquery.min.js"></script>
  <script src="../../bower_components/bootstrap5/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Intercept every submission before it can reach an application endpoint.
    document.addEventListener('submit', function (event) {
      event.preventDefault();
      document.getElementById('fixture-result').textContent = 'Formulario interceptado: no se guardaron datos.';
    }, true);
    document.getElementById('edit').addEventListener('hidden.bs.modal', function () {
      document.getElementById('fixture-result').textContent = 'Modal de cuenta cerrado.';
    });
  </script>
</body>
</html>
