<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<style>
    .cambiar-estado {
        min-width: 130px;
        font-weight: 600;
        border-radius: 14px;
    }

    .est-pendiente {
        background: #fff4e0;
        color: #8a5a00;
        border-color: #f39c12;
    }

    .est-en_proceso {
        background: #e3f1fa;
        color: #1b5e86;
        border-color: #3c8dbc;
    }

    .est-enviado {
        background: #e0f7fc;
        color: #0a6f85;
        border-color: #00c0ef;
    }

    .est-entregado {
        background: #e1f5ea;
        color: #0b6b3a;
        border-color: #00a65a;
    }
</style>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>
        <!-- Contenedor de contenido. Contiene contenido de la página -->
        <div class="content-wrapper">
            <!-- Encabezado de contenido (encabezado de página) -->
            <section class="content-header">
                <h1>
                    Historial de ventas
                </h1>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-dashboard"></i> Casa</a></li>
                    <li class="active">Ventas</li>
                </ol>
            </section>

            <!-- Contenido principal -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <div class="pull-right">
                                    <form method="POST" class="form-inline" action="sales_print.php" target="_blank">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right col-sm-8" id="reservation"
                                                name="date_range" aria-label="Rango de fechas del reporte">
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm btn-flat"
                                            name="print"><span class="fa fa-print" aria-hidden="true"></span>
                                            Impresión</button>
                                    </form>
                                </div>
                            </div>
                            <div class="box-body">
                                <table id="example1" class="table table-bordered">
                                    <thead>
                                        <th class="hidden"></th>
                                        <th>Fecha</th>
                                        <th>Nombre del comprador</th>
                                        <th>Transacción#</th>
                                        <th>Total</th>
                                        <th>Facturación</th>
                                        <th>Método de pago</th>
                                        <th>Detalles completos</th>
                                        <th>Estado</th>
                                        <th>Factura PDF</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $conn = $pdo->open();

                                        $estado_labels = [
                                            'pendiente'  => 'Pendiente',
                                            'en_proceso' => 'En proceso',
                                            'enviado'    => 'Enviado',
                                            'entregado'  => 'Entregado',
                                        ];

                                        if (!function_exists('sx_e')) {
                                            function sx_e($s)
                                            {
                                                return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                            }
                                        }

                                        try {
                                            // El importe corresponde al total guardado al realizar la compra.
                                            $stmt = $conn->prepare("SELECT sales.*, sales.id AS salesid, users.firstname, users.lastname,
                                                    TIMEDIFF(sales.sales_date, sales.fecha_hora_inicio) AS tiempo_transcurrido,
                                                    sales.total AS total_calc
                                                FROM sales LEFT JOIN users ON users.id = sales.user_id
                                                ORDER BY sales.sales_date DESC");
                                            $stmt->execute();
                                            foreach ($stmt as $row) {
                                                $estado = isset($estado_labels[$row['estado']]) ? $row['estado'] : 'pendiente';
                                                $opciones = '';
                                                foreach ($estado_labels as $valor => $texto) {
                                                    $opciones .= "<option value='" . $valor . "'" . ($estado == $valor ? ' selected' : '') . ">" . $texto . "</option>";
                                                }
                                                $ts = strtotime($row['sales_date']);
                                                echo "
                          <tr>
                            <td class='hidden'></td>
                            <td data-order='" . (int)$ts . "'>" . date('d/m/Y H:i', $ts) . "</td>
                            <td>" . sx_e($row['firstname'] . ' ' . $row['lastname']) . "</td>
                            <td>" . sx_e($row['pay_id']) . "</td>
                            <td data-order='" . (float)$row['total_calc'] . "'>&#36; " . number_format($row['total_calc'], 2) . "</td>
                            <td>
                                <small><strong>Nombre:</strong> " . sx_e($row['nombre_facturacion']) . "</small><br>
                                <small><strong>Doc:</strong> " . sx_e($row['documento']) . "</small><br>
                                <small><strong>Dir:</strong> " . sx_e($row['direccion']) . ", " . sx_e($row['ciudad']) . "</small><br>
                                <small><strong>Tel:</strong> " . sx_e($row['telefono']) . "</small><br>
                                <small><strong>Tiempo Fac:</strong> " . sx_e($row['tiempo_transcurrido']) . "</small>
                            </td>
                            <td>" . sx_e(ucfirst($row['metodo_pago'])) . "</td>
                            <td>
                                <button type='button' class='btn btn-info btn-sm btn-flat transact' data-id='" . (int)$row['salesid'] . "'><i class='fa fa-search'></i> Ver</button>
                            </td>
                            <td data-search='" . $estado_labels[$estado] . "' data-order='" . $estado . "'>
                                <select class='form-control input-sm cambiar-estado est-" . $estado . "' data-id='" . (int)$row['salesid'] . "'>" . $opciones . "</select>
                            </td>
                            <td>
                                <a href='factura_pdf.php?id=" . (int)$row['salesid'] . "' class='btn btn-danger btn-sm btn-flat'><i class='fa fa-file-pdf-o'></i> PDF</a>
                            </td>
                          </tr>
                        ";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<tr><td colspan='10'>" . sx_e($e->getMessage()) . "</td></tr>";
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
        <?php include 'includes/transaction_modal.php'; ?>

    </div>
    <!-- ./envoltura -->

    <?php include 'includes/scripts.php'; ?>
    <!-- Selector de fechas -->
    <script src="../dist/js/pgcv-sales-dates.js?v=<?php echo filemtime(__DIR__ . '/../dist/js/pgcv-sales-dates.js'); ?>"></script>
    <script>
        $(function() {
            // Rango de fechas utilizado por el formulario de reporte.
            PGCVSalesDates.init('#reservation');
        });
    </script>
    <script>
        $(function() {
            $(document).on('click', '.transact', function(e) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance(document.querySelector('#transaction')).show();
                var id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: 'transact.php',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#date').text(response.date);
                        $('#transid').text(response.transaction);
                        $('#detail').prepend(response.list);
                        $('#total').html(response.total);
                    }
                });
            });

            $("#transaction").on("hidden.bs.modal", function() {
                $('.prepend_items').remove();
            });
        });
    </script>
    <script>
        $(function() {
            function pintar($sel) {
                $sel.removeClass('est-pendiente est-en_proceso est-enviado est-entregado')
                    .addClass('est-' + $sel.val());
            }

            // Recordar el estado anterior por si el servidor rechaza el cambio
            $(document).on('focus', '.cambiar-estado', function() {
                $(this).data('anterior', $(this).val());
            });

            $(document).on('change', '.cambiar-estado', function() {
                var $sel = $(this);
                var id = $sel.data('id');
                var estado = $sel.val();
                var anterior = $sel.data('anterior');

                function revertir() {
                    if (anterior) {
                        $sel.val(anterior);
                    }
                    pintar($sel);
                }

                pintar($sel);

                $.ajax({
                    type: 'POST',
                    url: 'actualizar_estado.php',
                    data: {
                        id: id,
                        estado: estado
                    },
                    dataType: 'json',

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Actualizando estado...',
                            text: 'Por favor espera un momento.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },

                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            $sel.data('anterior', estado);
                            Swal.fire({
                                icon: 'success',
                                title: '¡Estado actualizado!',
                                text: 'El estado del pedido se actualizó correctamente.',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#3c8dbc'
                            });
                        } else {
                            revertir();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo actualizar el estado.'
                            });
                        }
                    },

                    error: function() {
                        Swal.close();
                        revertir();

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No fue posible comunicarse con el servidor.'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
