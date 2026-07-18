<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>

        <div class="content-wrapper">
            <div class="container">
                <section class="content">
                    <div class="row">
                        <div class="col-sm-9">

                            <!-- BANNER OFERTAS -->
                            <div style="background:#1a2e4a; border-radius:12px; padding:28px 32px; margin:20px 0 24px; display:flex; align-items:center; gap:20px;">
                                <div style="width:56px; height:56px; background:rgba(58,142,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="fa fa-tag" style="font-size:26px; color:#3a8eff;"></i>
                                </div>
                                <div>
                                    <h2 style="color:#fff; font-size:22px; font-weight:bold; margin:0 0 4px;">Ofertas y Descuentos</h2>
                                    <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">Encuentra los mejores precios en repuestos seleccionados</p>
                                </div>
                            </div>

                            <?php
                            $conn = $pdo->open();
                            try {
                                $inc = 3;
                                $stmt = $conn->prepare("SELECT * FROM products WHERE descuento > 0 AND stock > 0 ORDER BY descuento DESC");
                                $stmt->execute();
                                $count = $stmt->rowCount();

                                if ($count == 0) {
                                    echo "
										<div style='text-align:center; padding:40px; background:#fff; border-radius:12px; border:1px solid #e0e0e0;'>
											<i class='fa fa-tag' style='font-size:48px; color:#e0e0e0;'></i>
											<h4 style='color:#999; margin-top:16px;'>No hay ofertas disponibles por el momento</h4>
										</div>
									";
                                } else {
                                    foreach ($stmt as $row) {
                                        $image = (!empty($row['photo'])) ? 'images/' . $row['photo'] : 'images/noimage.jpg';
                                        $precio_final = precioConDescuento($row['price'], $row['descuento']);
                                        $inc = ($inc == 3) ? 1 : $inc + 1;
                                        if ($inc == 1) echo "<div class='row'>";
                                        echo "
											<div class='col-sm-4' style='margin-bottom:16px;'>
												<div style='background:#fff; border:1px solid #e0e0e0; border-radius:12px; overflow:hidden; position:relative;'>
													<div style='position:absolute; top:10px; right:10px; background:#e74c3c; color:#fff; font-size:12px; font-weight:bold; padding:4px 10px; border-radius:20px; z-index:1;'>
														-" . $row['descuento'] . "%
													</div>
													<div style='width:100%; height:180px; overflow:hidden; background:#f5f5f5;'>
														<img src='" . $image . "' style='width:100%; height:100%; object-fit:cover;'>
													</div>
													<div style='padding:12px 14px;'>
														<p style='font-size:13px; color:#666; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;' title='" . $row['name'] . "'>
															" . $row['name'] . "
														</p>
														<p style='font-size:12px; color:#999; margin:0; text-decoration:line-through;'>
															&#36; " . number_format($row['price'], 2) . "
														</p>
														<p style='font-size:18px; font-weight:bold; color:#e74c3c; margin:0 0 12px;'>
															&#36; " . number_format($precio_final, 2) . "
														</p>
														<a href='producto.php?product=" . $row['slug'] . "'
														   style='display:block; text-align:center; background:#1a2e4a; color:#fff;
														          text-decoration:none; padding:8px; border-radius:6px; font-size:13px;'>
															<i class='fa fa-eye'></i> Ver producto
														</a>
													</div>
												</div>
											</div>
										";
                                        if ($inc == 3) echo "</div>";
                                    }
                                    if ($inc == 1) echo "<div class='col-sm-4'></div><div class='col-sm-4'></div></div>";
                                    if ($inc == 2) echo "<div class='col-sm-4'></div></div>";
                                }
                            } catch (PDOException $e) {
                                echo "Hay algún problema en la conexión: " . $e->getMessage();
                            }
                            $pdo->close();
                            ?>

                        </div>
                        <div class="col-sm-3">
                            <?php include 'includes/sidebar.php'; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <?php include 'includes/scripts.php'; ?>
</body>

</html>