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

						<?php if(isset($_SESSION['error'])){ ?>
							<div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
						<?php } ?>

						<!-- CARRUSEL -->
						<div id="carousel-nosotros" class="carousel slide" data-ride="carousel"
							 style="border-radius:12px; overflow:hidden; margin-bottom:24px;">
							<ol class="carousel-indicators" style="margin-bottom:12px;">
								<li data-target="#carousel-nosotros" data-slide-to="0" class="active"
									style="width:24px; height:4px; border-radius:2px; background:#3a8eff; border:none;"></li>
								<li data-target="#carousel-nosotros" data-slide-to="1"
									style="width:8px; height:4px; border-radius:2px; border:none;"></li>
								<li data-target="#carousel-nosotros" data-slide-to="2"
									style="width:8px; height:4px; border-radius:2px; border:none;"></li>
								<li data-target="#carousel-nosotros" data-slide-to="3"
									style="width:8px; height:4px; border-radius:2px; border:none;"></li>
							</ol>
							<div class="carousel-inner">
								<div class="item active">
									<img src="images/piston.jpg" alt="Slide 1" style="width:100%; height:320px; object-fit:cover;">
									<div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
									<div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
										<span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">NOSOTROS</span>
										<h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
										<p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0; max-width:280px;">Más de años sirviendo a nuestros clientes con honestidad.</p>
									</div>
								</div>
								<div class="item">
									<img src="images/amortiguador.jpg" alt="Slide 2" style="width:100%; height:320px; object-fit:cover;">
									<div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
									<div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
										<span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">NOSOTROS</span>
										<h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
										<p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0; max-width:280px;">Más de años sirviendo a nuestros clientes con honestidad.</p>
									</div>
								</div>
								<div class="item">
									<img src="images/freno.jpg" alt="Slide 3" style="width:100%; height:320px; object-fit:cover;">
									<div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
									<div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
										<span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">NOSOTROS</span>
										<h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
										<p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0; max-width:280px;">Más de años sirviendo a nuestros clientes con honestidad.</p>
									</div>
								</div>
								<div class="item">
									<img src="images/filtros-870x471.jpg" alt="Slide 4" style="width:100%; height:320px; object-fit:cover;">
									<div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
									<div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
										<span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">NOSOTROS</span>
										<h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
										<p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0; max-width:280px;">Más de años sirviendo a nuestros clientes con honestidad.</p>
									</div>
								</div>
							</div>
							<a class="left carousel-control" href="#carousel-nosotros" data-slide="prev" style="background:none; width:40px;">
								<div style="width:36px; height:36px; background:rgba(255,255,255,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-18px; position:absolute; top:50%; left:8px;">
									<span class="fa fa-angle-left" style="font-size:20px; color:#fff;"></span>
								</div>
							</a>
							<a class="right carousel-control" href="#carousel-nosotros" data-slide="next" style="background:none; width:40px;">
								<div style="width:36px; height:36px; background:rgba(255,255,255,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-18px; position:absolute; top:50%; right:8px;">
									<span class="fa fa-angle-right" style="font-size:20px; color:#fff;"></span>
								</div>
							</a>
						</div>

						<!-- SOBRE NOSOTROS -->
						<div style="background:#fff; border:1px solid #e0e0e0; border-radius:12px; padding:28px; margin-bottom:24px;">
							<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
								<div style="width:4px; height:28px; background:#1a2e4a; border-radius:2px;"></div>
								<h3 style="margin:0; font-size:18px; font-weight:bold; color:#1a2e4a;">Almacén de Repuestos Los Almendros</h3>
							</div>
							<p style="font-size:14px; color:#555; line-height:1.8; margin:0;">
								Somos un almacén especializado en repuestos de segunda mano, confiables y a bajo costo.
								Ofrecemos soluciones prácticas y económicas para quienes buscan mantener en funcionamiento
								sus vehículos, maquinaria o equipos sin gastar de más.
								Contamos con un amplio inventario de repuestos usados en buen estado, cuidadosamente
								seleccionados y probados para asegurar su funcionamiento.<br><br>
								Atendemos las necesidades de cada cliente, desde lo más básico hasta componentes más complejos,
								siempre con atención personalizada y asesoría honesta.
								<strong style="color:#1a2e4a;">Calidad garantizada a precio justo.</strong>
							</p>
						</div>

						<!-- PRODUCTOS MÁS VENDIDOS -->
						<div style="margin-bottom:16px; display:flex; align-items:center; gap:12px;">
							<div style="width:4px; height:28px; background:#1a2e4a; border-radius:2px;"></div>
							<div>
								<h3 style="margin:0; font-size:18px; font-weight:bold; color:#1a2e4a;">Productos más vendidos</h3>
								<p style="margin:0; font-size:13px; color:#888;">Los más vendidos este mes</p>
							</div>
						</div>

						<?php
							$month = date('m');
							$conn = $pdo->open();
							try{
								$inc = 3;
								$stmt = $conn->prepare("SELECT *, SUM(quantity) AS total_qty FROM details LEFT JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id WHERE MONTH(sales_date) = '$month' GROUP BY details.product_id ORDER BY total_qty DESC LIMIT 6");
								$stmt->execute();
								foreach($stmt as $row){
									$image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/noimage.jpg';
									$inc = ($inc == 3) ? 1 : $inc + 1;
									if($inc == 1) echo "<div class='row'>";
									echo "
										<div class='col-sm-4' style='margin-bottom:16px;'>
											<div style='background:#fff; border:1px solid #e0e0e0; border-radius:12px; overflow:hidden;'>
												<div style='width:100%; height:180px; overflow:hidden; background:#f5f5f5;'>
													<img src='".$image."' style='width:100%; height:100%; object-fit:cover;'>
												</div>
												<div style='padding:12px 14px;'>
													<p style='font-size:13px; color:#666; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'
													   title='".$row['name']."'>
														".$row['name']."
													</p>
													<p style='font-size:16px; font-weight:bold; color:#1a2e4a; margin:0 0 12px;'>
														&#36; ".number_format($row['price'], 2)."
													</p>
													<a href='producto.php?product=".$row['slug']."'
													   style='display:block; text-align:center; background:#1a2e4a; color:#fff;
													          text-decoration:none; padding:8px; border-radius:6px; font-size:13px;'>
														<i class='fa fa-eye'></i> Ver producto
													</a>
												</div>
											</div>
										</div>
									";
									if($inc == 3) echo "</div>";
								}
								if($inc == 1) echo "<div class='col-sm-4'></div><div class='col-sm-4'></div></div>";
								if($inc == 2) echo "<div class='col-sm-4'></div></div>";
							}
							catch(PDOException $e){
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