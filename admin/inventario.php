<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Reporte de Inventario</h1>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-dashboard"></i> Casa</a></li>
                    <li class="active">Inventario</li>
                </ol>
            </section>

            <section class="content">
                <?php
                $conn = $pdo->open();

                // Contar productos por estado
                $stmt = $conn->prepare("SELECT 
                    COUNT(CASE WHEN stock = 0 THEN 1 END) AS sin_stock,
                    COUNT(CASE WHEN stock > 0 AND stock <= stock_minimo THEN 1 END) AS critico,
                    COUNT(CASE WHEN stock > stock_minimo AND stock <= stock_minimo * 2 THEN 1 END) AS bajo,
                    COUNT(CASE WHEN stock > stock_minimo * 2 THEN 1 END) AS normal
                    FROM products");
                $stmt->execute();
                $resumen = $stmt->fetch();
                ?>

                <!-- RESUMEN -->
                <style>
                    .inventory-filter {
                        display:block; width:100%; border:0; font:inherit; cursor:pointer;
                    }
                    .inventory-filter-count {
                        display:block; margin:0; font-size:32px; font-weight:bold; line-height:1.1;
                    }
                    .inventory-filter-label {
                        display:block; margin:0; font-size:13px; opacity:0.85;
                    }
                    .inventory-filter[aria-pressed="true"] {
                        box-shadow:inset 0 0 0 3px rgba(255,255,255,0.8);
                    }
                    .inventory-filter:focus-visible {
                        outline:2px solid #1a2e4a; outline-offset:3px;
                    }
                </style>
                <p class="sr-only" id="inventory-filter-help">Selecciona un estado para ver sus productos. Vuelve a pulsar el estado seleccionado para mostrar todos.</p>
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-sm-3">
                        <button type="button" class="inventory-filter" data-stock-filter="Sin stock" aria-label="Mostrar productos sin stock" aria-controls="example1" aria-describedby="inventory-filter-help" aria-pressed="false" style="background:#e74c3c; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-times-circle" aria-hidden="true" style="font-size:32px; margin-bottom:8px;"></i>
                            <span class="inventory-filter-count"><?php echo $resumen['sin_stock']; ?></span>
                            <span class="inventory-filter-label">Sin stock</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="inventory-filter" data-stock-filter="Crítico" aria-label="Mostrar productos con stock crítico" aria-controls="example1" aria-describedby="inventory-filter-help" aria-pressed="false" style="background:#e67e22; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true" style="font-size:32px; margin-bottom:8px;"></i>
                            <span class="inventory-filter-count"><?php echo $resumen['critico']; ?></span>
                            <span class="inventory-filter-label">Stock crítico</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="inventory-filter" data-stock-filter="Bajo" aria-label="Mostrar productos con stock bajo" aria-controls="example1" aria-describedby="inventory-filter-help" aria-pressed="false" style="background:#f39c12; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-warning" aria-hidden="true" style="font-size:32px; margin-bottom:8px;"></i>
                            <span class="inventory-filter-count"><?php echo $resumen['bajo']; ?></span>
                            <span class="inventory-filter-label">Stock bajo</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="inventory-filter" data-stock-filter="Normal" aria-label="Mostrar productos con stock normal" aria-controls="example1" aria-describedby="inventory-filter-help" aria-pressed="false" style="background:#27ae60; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-check-circle" aria-hidden="true" style="font-size:32px; margin-bottom:8px;"></i>
                            <span class="inventory-filter-count"><?php echo $resumen['normal']; ?></span>
                            <span class="inventory-filter-label">Stock normal</span>
                        </button>
                    </div>
                </div>

                <!-- TABLA -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title" id="inventory-table-title" aria-live="polite">Estado del inventario</h3>
                            </div>
                            <div class="box-body">
                                <table id="example1" class="table table-bordered">
                                    <thead>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock actual</th>
                                        <th>Stock mínimo</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("SELECT products.*, category.name AS catname 
                                                            FROM products 
                                                            LEFT JOIN category ON category.id = products.category_id 
                                                            ORDER BY stock ASC");
                                        $stmt->execute();
                                        foreach ($stmt as $row) {
                                            if ($row['stock'] == 0) {
                                                $estado = "<span style='background:#e74c3c; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>Sin stock</span>";
                                            } elseif ($row['stock'] <= $row['stock_minimo']) {
                                                $estado = "<span style='background:#e67e22; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>Crítico</span>";
                                            } elseif ($row['stock'] <= $row['stock_minimo'] * 2) {
                                                $estado = "<span style='background:#f39c12; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>Bajo</span>";
                                            } else {
                                                $estado = "<span style='background:#27ae60; color:#fff; padding:3px 10px; border-radius:20px; font-size:12px;'>Normal</span>";
                                            }

                                            echo "
                                            <tr>
                                                <td>" . escapeHtml($row['name']) . "</td>
                                                <td>" . escapeHtml($row['catname']) . "</td>
                                                <td><b>" . $row['stock'] . "</b></td>
                                                <td>" . $row['stock_minimo'] . "</td>
                                                <td>" . $estado . "</td>
                                                <td>
                                                    <a href='products.php?edit=" . $row['id'] . "' class='btn btn-sm btn-flat btn-primary'>
                                                    <i class='fa fa-edit'></i> Editar
                                                </a>
                                                </td>
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
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <?php include 'includes/scripts.php'; ?>
    <script>
        $(function() {
            var table = $('#example1').DataTable();
            var buttons = $('.inventory-filter');
            var activeStatus = '';

            buttons.on('click', function() {
                var status = this.dataset.stockFilter;
                activeStatus = activeStatus === status ? '' : status;
                table.search('').column(4).search(activeStatus, {exact: true}).draw();
                buttons.attr('aria-pressed', 'false');
                if (activeStatus) this.setAttribute('aria-pressed', 'true');
                $('#inventory-table-title').text(activeStatus
                    ? 'Estado del inventario: ' + $(this).find('.inventory-filter-label').text()
                    : 'Estado del inventario');
            });
        });
    </script>
</body>

</html>
