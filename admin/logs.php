<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Registro de Actividad</h1>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-dashboard"></i> Casa</a></li>
                    <li class="active">Logs</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <ul class="nav nav-tabs" role="tablist" aria-label="Tipos de actividad">
                                    <li role="presentation"><a class="active" id="logs-login-tab" href="#tab-login" data-bs-toggle="tab" role="tab" aria-controls="tab-login" aria-selected="true"><i class="fa fa-sign-in"></i> Login</a></li>
                                    <li role="presentation"><a id="logs-productos-tab" href="#tab-productos" data-bs-toggle="tab" role="tab" aria-controls="tab-productos" aria-selected="false" tabindex="-1"><i class="fa fa-barcode"></i> Productos</a></li>
                                    <li role="presentation"><a id="logs-ventas-tab" href="#tab-ventas" data-bs-toggle="tab" role="tab" aria-controls="tab-ventas" aria-selected="false" tabindex="-1"><i class="fa fa-shopping-cart"></i> Ventas</a></li>
                                    <li role="presentation"><a id="logs-usuarios-tab" href="#tab-usuarios" data-bs-toggle="tab" role="tab" aria-controls="tab-usuarios" aria-selected="false" tabindex="-1"><i class="fa fa-users"></i> Usuarios</a></li>
                                </ul>
                            </div>
                            <div class="box-body">
                                <div class="tab-content">

                                    <!-- LOGS LOGIN -->
                                    <div class="tab-pane active" id="tab-login" role="tabpanel" aria-labelledby="logs-login-tab" tabindex="0">
                                        <table class="table table-bordered" id="table-login" style="width:100%">
                                            <thead>
                                                <th>ID</th>
                                                <th>Email</th>
                                                <th>Tipo</th>
                                                <th>IP</th>
                                                <th>Fecha y hora</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $conn = $pdo->open();
                                                $stmt = $conn->prepare("SELECT * FROM logs_login ORDER BY fecha_hora_accion DESC LIMIT 100");
                                                $stmt->execute();
                                                foreach ($stmt as $row) {
                                                    $colores = ['EXITOSO' => '#27ae60', 'FALLIDO' => '#e74c3c', 'BLOQUEADO' => '#e67e22'];
                                                    $color = $colores[$row['tipo_operacion']] ?? '#999';
                                                    echo "
                                                    <tr>
                                                        <td>" . $row['id_registro'] . "</td>
                                                        <td>" . escapeHtml($row['email']) . "</td>
                                                        <td><span style='background:" . $color . "; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>" . escapeHtml($row['tipo_operacion']) . "</span></td>
                                                        <td>" . escapeHtml($row['ip']) . "</td>
                                                        <td>" . escapeHtml($row['fecha_hora_accion']) . "</td>
                                                    </tr>
                                                ";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- LOGS PRODUCTOS -->
                                    <div class="tab-pane" id="tab-productos" role="tabpanel" aria-labelledby="logs-productos-tab" tabindex="0">
                                        <table class="table table-bordered" id="table-productos" style="width:100%">
                                            <thead>
                                                <th>ID</th>
                                                <th>Producto ID</th>
                                                <th>Operación</th>
                                                <th>Info anterior</th>
                                                <th>Info nueva</th>
                                                <th>IP</th>
                                                <th>Usuario</th>
                                                <th>Fecha y hora</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM logs_productos ORDER BY fecha_hora_accion DESC LIMIT 100");
                                                $stmt->execute();
                                                foreach ($stmt as $row) {
                                                    $colores = ['INSERT' => '#27ae60', 'UPDATE' => '#3a8eff', 'DELETE' => '#e74c3c'];
                                                    $color = $colores[$row['tipo_operacion']] ?? '#999';
                                                    $anterior = $row['informacion_anterior'] ? json_decode($row['informacion_anterior'], true) : [];
                                                    $nueva = $row['nueva_informacion'] ? json_decode($row['nueva_informacion'], true) : [];
                                                    $anterior_txt = '';
                                                    if ($anterior) {
                                                        foreach ($anterior as $k => $v) $anterior_txt .= "$k: $v, ";
                                                    }
                                                    $nueva_txt = '';
                                                    if ($nueva) {
                                                        foreach ($nueva as $k => $v) $nueva_txt .= "$k: $v, ";
                                                    }
                                                    echo "
                                                    <tr>
                                                        <td>" . $row['id_registro'] . "</td>
                                                        <td>" . $row['id_referencia'] . "</td>
                                                        <td><span style='background:" . $color . "; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>" . escapeHtml($row['tipo_operacion']) . "</span></td>
                                                        <td><small>" . escapeHtml(rtrim($anterior_txt, ', ')) . "</small></td>
                                                        <td><small>" . escapeHtml(rtrim($nueva_txt, ', ')) . "</small></td>
                                                        <td>" . escapeHtml($row['ip']) . "</td>
                                                        <td>" . escapeHtml($row['usuario_created']) . "</td>
                                                        <td>" . escapeHtml($row['fecha_hora_accion']) . "</td>
                                                    </tr>
                                                ";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- LOGS VENTAS -->
                                    <div class="tab-pane" id="tab-ventas" role="tabpanel" aria-labelledby="logs-ventas-tab" tabindex="0">
                                        <table class="table table-bordered" id="table-ventas" style="width:100%">
                                            <thead>
                                                <th>ID</th>
                                                <th>Venta ID</th>
                                                <th>Operación</th>
                                                <th>Info anterior</th>
                                                <th>Info nueva</th>
                                                <th>IP</th>
                                                <th>Usuario</th>
                                                <th>Fecha y hora</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM logs_ventas ORDER BY fecha_hora_accion DESC LIMIT 100");
                                                $stmt->execute();
                                                foreach ($stmt as $row) {
                                                    $colores = ['INSERT' => '#27ae60', 'UPDATE' => '#3a8eff', 'DELETE' => '#e74c3c'];
                                                    $color = $colores[$row['tipo_operacion']] ?? '#999';
                                                    $anterior = $row['informacion_anterior'] ? json_decode($row['informacion_anterior'], true) : [];
                                                    $nueva = $row['nueva_informacion'] ? json_decode($row['nueva_informacion'], true) : [];
                                                    $anterior_txt = '';
                                                    if ($anterior) {
                                                        foreach ($anterior as $k => $v) $anterior_txt .= "$k: $v, ";
                                                    }
                                                    $nueva_txt = '';
                                                    if ($nueva) {
                                                        foreach ($nueva as $k => $v) $nueva_txt .= "$k: $v, ";
                                                    }
                                                    echo "
                                                    <tr>
                                                        <td>" . $row['id_registro'] . "</td>
                                                        <td>" . $row['id_referencia'] . "</td>
                                                        <td><span style='background:" . $color . "; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>" . escapeHtml($row['tipo_operacion']) . "</span></td>
                                                        <td><small>" . escapeHtml(rtrim($anterior_txt, ', ')) . "</small></td>
                                                        <td><small>" . escapeHtml(rtrim($nueva_txt, ', ')) . "</small></td>
                                                        <td>" . escapeHtml($row['ip']) . "</td>
                                                        <td>" . escapeHtml($row['usuario_created']) . "</td>
                                                        <td>" . escapeHtml($row['fecha_hora_accion']) . "</td>
                                                    </tr>
                                                ";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- LOGS USUARIOS -->
                                    <div class="tab-pane" id="tab-usuarios" role="tabpanel" aria-labelledby="logs-usuarios-tab" tabindex="0">
                                        <table class="table table-bordered" id="table-usuarios" style="width:100%">
                                            <thead>
                                                <th>ID</th>
                                                <th>Usuario ID</th>
                                                <th>Operación</th>
                                                <th>Info anterior</th>
                                                <th>Info nueva</th>
                                                <th>IP</th>
                                                <th>Admin</th>
                                                <th>Fecha y hora</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM logs_usuarios ORDER BY fecha_hora_accion DESC LIMIT 100");
                                                $stmt->execute();
                                                foreach ($stmt as $row) {
                                                    $colores = ['INSERT' => '#27ae60', 'UPDATE' => '#3a8eff', 'DELETE' => '#e74c3c'];
                                                    $color = $colores[$row['tipo_operacion']] ?? '#999';
                                                    $anterior = $row['informacion_anterior'] ? json_decode($row['informacion_anterior'], true) : [];
                                                    $nueva = $row['nueva_informacion'] ? json_decode($row['nueva_informacion'], true) : [];
                                                    $anterior_txt = '';
                                                    if ($anterior) {
                                                        foreach ($anterior as $k => $v) $anterior_txt .= "$k: $v, ";
                                                    }
                                                    $nueva_txt = '';
                                                    if ($nueva) {
                                                        foreach ($nueva as $k => $v) $nueva_txt .= "$k: $v, ";
                                                    }
                                                    echo "
                                                    <tr>
                                                        <td>" . $row['id_registro'] . "</td>
                                                        <td>" . $row['id_referencia'] . "</td>
                                                        <td><span style='background:" . $color . "; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>" . escapeHtml($row['tipo_operacion']) . "</span></td>
                                                        <td><small>" . escapeHtml(rtrim($anterior_txt, ', ')) . "</small></td>
                                                        <td><small>" . escapeHtml(rtrim($nueva_txt, ', ')) . "</small></td>
                                                        <td>" . escapeHtml($row['ip']) . "</td>
                                                        <td>" . escapeHtml($row['usuario_created']) . "</td>
                                                        <td>" . escapeHtml($row['fecha_hora_accion']) . "</td>
                                                    </tr>
                                                ";
                                                }
                                                $pdo->close();
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <?php include 'includes/scripts.php'; ?>
    <script>
        $(function() {
            $('#table-login').DataTable({
                order: [
                    [4, 'desc']
                ],
                scrollX: true
            });
            $('#table-productos').DataTable({
                order: [
                    [7, 'desc']
                ],
                scrollX: true
            });
            $('#table-ventas').DataTable({
                order: [
                    [7, 'desc']
                ],
                scrollX: true
            });
            $('#table-usuarios').DataTable({
                order: [
                    [7, 'desc']
                ],
                scrollX: true
            });
            $('.dt-scroll-body').attr({tabindex: '0', role: 'region', 'aria-label': 'Tabla de registros, desplazamiento horizontal'});
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
                $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
            });
        });
    </script>
</body>

</html>
