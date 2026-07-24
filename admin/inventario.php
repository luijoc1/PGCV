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
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-sm-3">
                        <div style="background:#e74c3c; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-times-circle" style="font-size:32px; margin-bottom:8px;"></i>
                            <h2 style="margin:0; font-size:32px; font-weight:bold;"><?php echo $resumen['sin_stock']; ?></h2>
                            <p style="margin:0; font-size:13px; opacity:0.85;">Sin stock</p>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div style="background:#e67e22; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-exclamation-triangle" style="font-size:32px; margin-bottom:8px;"></i>
                            <h2 style="margin:0; font-size:32px; font-weight:bold;"><?php echo $resumen['critico']; ?></h2>
                            <p style="margin:0; font-size:13px; opacity:0.85;">Stock crítico</p>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div style="background:#f39c12; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-warning" style="font-size:32px; margin-bottom:8px;"></i>
                            <h2 style="margin:0; font-size:32px; font-weight:bold;"><?php echo $resumen['bajo']; ?></h2>
                            <p style="margin:0; font-size:13px; opacity:0.85;">Stock bajo</p>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div style="background:#27ae60; border-radius:12px; padding:20px; text-align:center; color:#fff;">
                            <i class="fa fa-check-circle" style="font-size:32px; margin-bottom:8px;"></i>
                            <h2 style="margin:0; font-size:32px; font-weight:bold;"><?php echo $resumen['normal']; ?></h2>
                            <p style="margin:0; font-size:13px; opacity:0.85;">Stock normal</p>
                        </div>
                    </div>
                </div>

                <!-- TABLA -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Estado del inventario</h3>
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
                                                <td>" . $row['name'] . "</td>
                                                <td>" . $row['catname'] . "</td>
                                                <td><b>" . $row['stock'] . "</b></td>
                                                <td>" . $row['stock_minimo'] . "</td>
                                                <td>" . $estado . "</td>
                                                <td>
                                                    <a href='products.php' class='btn btn-sm btn-flat btn-primary'>
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
</body>

</html>