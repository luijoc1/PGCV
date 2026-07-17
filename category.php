<link rel="stylesheet" href="wasathpp.css">
<?php include 'includes/session.php'; ?>
<?php
if (!isset($_GET['category'])) {
	header('location: category.php?category=MOTOR');
	exit();
}
$slug = $_GET['category'];

$conn = $pdo->open();

try {
	$stmt = $conn->prepare("SELECT * FROM category WHERE cat_slug = :slug");
	$stmt->execute(['slug' => $slug]);
	$cat = $stmt->fetch();
	$catid = $cat['id'];
} catch (PDOException $e) {
	echo "Hay algún problema en la conexión: " . $e->getMessage();
}

$pdo->close();
?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
	<div class="wrapper">

		<?php include 'includes/navbar.php'; ?>

		<div class="content-wrapper">
			<div class="container">
				<section class="content">
					<div class="row">
						<div class="col-sm-9">

							<!-- TÍTULO CATEGORÍA -->
							<div style="margin: 20px 0 16px; display: flex; align-items: center; gap: 12px;">
								<div style="width: 4px; height: 28px; background: #1a2e4a; border-radius: 2px;"></div>
								<h3 style="margin: 0; font-size: 20px; font-weight: bold; color: #1a2e4a;">
									<?php echo $cat['name']; ?>
								</h3>
							</div>

							<?php
							$conn = $pdo->open();
							try {
								$inc = 3;
								$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :catid AND stock > 0");
								$stmt->execute(['catid' => $catid]);
								foreach ($stmt as $row) {
									$image = (!empty($row['photo'])) ? 'images/' . $row['photo'] : 'images/noimage.jpg';
									$inc = ($inc == 3) ? 1 : $inc + 1;
									if ($inc == 1) echo "<div class='row'>";
									echo "
										<div class='col-sm-4' style='margin-bottom: 16px;'>
											<div style='background:#fff; border:1px solid #e0e0e0; border-radius:12px; overflow:hidden;'>
												<div style='width:100%; height:180px; overflow:hidden; background:#f5f5f5;'>
													<img src='" . $image . "' style='width:100%; height:100%; object-fit:cover;'>
												</div>
												<div style='padding:12px 14px;'>
													<p style='font-size:13px; color:#666; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'
													   title='" . $row['name'] . "'>
														" . $row['name'] . "
													</p>
													" . ($row['descuento'] > 0 ? "
													<p style='font-size:12px; color:#999; margin:0; text-decoration:line-through;'>
														&#36; " . number_format($row['price'], 2) . "
													</p>
													<p style='font-size:16px; font-weight:bold; color:#e74c3c; margin:0 0 4px;'>
														&#36; " . number_format(precioConDescuento($row['price'], $row['descuento']), 2) . "
														<span style='background:#e74c3c; color:#fff; font-size:11px; padding:2px 7px; border-radius:20px; margin-left:4px;'>-" . $row['descuento'] . "%</span>
													</p>
													" : "
													<p style='font-size:16px; font-weight:bold; color:#1a2e4a; margin:0 0 12px;'>
														&#36; " . number_format($row['price'], 2) . "
													</p>
													") . "
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