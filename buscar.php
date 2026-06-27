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
					<?php
						// Aceptar GET o POST
						$keyword = '';
						if(isset($_POST['keyword'])) $keyword = $_POST['keyword'];
						elseif(isset($_GET['keyword'])) $keyword = $_GET['keyword'];

						$conn = $pdo->open();

						$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM products WHERE name LIKE :keyword AND stock > 0");
						$stmt->execute(['keyword' => '%'.$keyword.'%']);
						$row = $stmt->fetch();

						if($row['numrows'] < 1){
							echo '<h3 style="color:#1a2e4a; margin: 20px 0;">No se encontraron resultados para <i>"'.$keyword.'"</i></h3>';
						}
						else{
							if(!empty($keyword)){
								echo '<h3 style="color:#1a2e4a; margin: 20px 0;">Resultados para <i>"'.$keyword.'"</i></h3>';
							} else {
								echo '<h3 style="color:#1a2e4a; margin: 20px 0;">Todos los productos</h3>';
							}

							try{
								$inc = 3;
								$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :keyword AND stock > 0");
								$stmt->execute(['keyword' => '%'.$keyword.'%']);

								foreach($stmt as $row){
									$highlighted = (!empty($keyword)) 
										? preg_filter('/'.preg_quote($keyword, '/').'/i', '<b>$0</b>', $row['name'])
										: $row['name'];
									$highlighted = $highlighted ?? $row['name'];
									$image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/noimage.jpg';
									$inc = ($inc == 3) ? 1 : $inc + 1;
									if($inc == 1) echo "<div class='row'>";
									echo "
										<div class='col-sm-4' style='margin-bottom: 16px;'>
											<div style='background:#fff; border:1px solid #e0e0e0; border-radius:12px; overflow:hidden;'>
												<div style='width:100%; height:180px; overflow:hidden; background:#f5f5f5;'>
													<img src='".$image."' style='width:100%; height:100%; object-fit:cover;'>
												</div>
												<div style='padding:12px 14px;'>
													<p style='font-size:13px; color:#666; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'
													   title='".$row['name']."'>
														".$highlighted."
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