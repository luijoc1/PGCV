<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Gestión de Descuentos</h1>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-dashboard"></i> Casa</a></li>
                    <li class="active">Descuentos</li>
                </ol>
            </section>

            <section class="content">
                <?php
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger alert-dismissible'>
                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                            " . $_SESSION['error'] . "
                          </div>";
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo "<div class='alert alert-success alert-dismissible'>
                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                            " . $_SESSION['success'] . "
                          </div>";
                    unset($_SESSION['success']);
                }
                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Productos con descuento activo</h3>
                            </div>
                            <div class="box-body">
                                <table id="example1" class="table table-bordered">
                                    <thead>
                                        <th>Producto</th>
                                        <th>Precio original</th>
                                        <th>Descuento</th>
                                        <th>Precio final</th>
                                        <th>Stock</th>
                                        <th>Acción</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $conn = $pdo->open();
                                        try {
                                            $stmt = $conn->prepare("SELECT * FROM products WHERE descuento > 0 AND stock > 0 ORDER BY descuento DESC");
                                            $stmt->execute();
                                            foreach ($stmt as $row) {
                                                $precio_final = precioConDescuento($row['price'], $row['descuento']);
                                                echo "
                                                <tr>
                                                    <td>" . $row['name'] . "</td>
                                                    <td>&#36; " . number_format($row['price'], 2) . "</td>
                                                    <td>
                                                        <span style='background:#e74c3c; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold;'>
                                                            -" . $row['descuento'] . "%
                                                        </span>
                                                    </td>
                                                    <td style='color:#e74c3c; font-weight:bold;'>&#36; " . number_format($precio_final, 2) . "</td>
                                                    <td><span class='label label-success'>" . $row['stock'] . "</span></td>
                                                    <td>
                                                        <button class='btn btn-warning btn-sm btn-flat quitar-desc' data-id='" . $row['id'] . "' data-nombre='" . $row['name'] . "'>
                                                        <i class='fa fa-times'></i> Quitar descuento
                                                        </button>
                                                    </td>
                                                </tr>
                                            ";
                                            }
                                        } catch (PDOException $e) {
                                            echo $e->getMessage();
                                        }
                                        $pdo->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
    <!-- Modal confirmación -->
    <div class="modal fade" id="modalQuitar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background:#1a2e4a;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title" style="color:#fff;"><i class="fa fa-tag"></i> Quitar descuento</h4>
                </div>
                <div class="modal-body text-center" style="padding:24px;">
                    <i class="fa fa-exclamation-triangle" style="font-size:40px; color:#f39c12; margin-bottom:12px;"></i>
                    <h4>¿Quitar el descuento a este producto?</h4>
                    <p style="color:#888;" id="nombre-producto"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <a href="#" id="btn-confirmar-quitar" class="btn btn-warning btn-flat">
                        <i class="fa fa-check"></i> Sí, quitar descuento
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/scripts.php'; ?>

    <script>
        $(function() {
            $(document).on('click', '.quitar-desc', function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                $('#nombre-producto').text(nombre);
                $('#btn-confirmar-quitar').attr('href', 'quitar_descuento.php?id=' + id);
                $('#modalQuitar').modal('show');
            });
        });
    </script>

</body>

</html>