<?php include 'includes/session.php'; ?>
<?php
$conn = $pdo->open();

$slug = $_GET['product'];

try {
	$stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname, products.id AS prodid FROM products LEFT JOIN category ON category.id=products.category_id WHERE slug = :slug AND stock > 0");
	$stmt->execute(['slug' => $slug]);
	$product = $stmt->fetch();

	if (!$product) {
		$_SESSION['error'] = 'Producto no disponible o sin stock';
		header('location: index.php');
		exit();
	}
} catch (PDOException $e) {
	echo "There is some problem in connection: " . $e->getMessage();
}

$now = date('Y-m-d');
if ($product['date_view'] == $now) {
	$stmt = $conn->prepare("UPDATE products SET counter=counter+1 WHERE id=:id");
	$stmt->execute(['id' => $product['prodid']]);
} else {
	$stmt = $conn->prepare("UPDATE products SET counter=1, date_view=:now WHERE id=:id");
	$stmt->execute(['id' => $product['prodid'], 'now' => $now]);
}
?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
	<script>
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s);
			js.id = id;
			js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	</script>
	<div class="wrapper">

		<?php include 'includes/navbar.php'; ?>

		<div class="content-wrapper">
			<div class="container">
				<section class="content">
					<div class="row">
						<div class="col-sm-9">

							<div class="callout" id="callout" style="display:none">
								<button type="button" class="close"><span aria-hidden="true">&times;</span></button>
								<span class="message"></span>
							</div>

							<div style="background:#fff; border:1px solid #e0e0e0; border-radius:12px; padding:24px; margin-bottom:20px;">
								<div class="row">

									<!-- IMAGEN -->
									<div class="col-sm-5">
										<div style="border:1px solid #e0e0e0; border-radius:8px; background:#f5f7fa; display:flex; align-items:center; justify-content:center; height:280px; overflow:hidden;">
											<img src="<?php echo (!empty($product['photo'])) ? 'images/' . $product['photo'] : 'images/noimage.jpg'; ?>"
												style="max-width:100%; max-height:100%; object-fit:contain;"
												class="zoom" data-magnify-src="images/large-<?php echo $product['photo']; ?>">
										</div>
									</div>

									<!-- DETALLES -->
									<div class="col-sm-7">
										<h2 style="color:#1a2e4a; font-size:20px; font-weight:bold; margin:0 0 10px;">
											<?php echo $product['prodname']; ?>
										</h2>

										<?php if ($product['descuento'] > 0): ?>
											<p style="font-size:14px; color:#999; margin:0; text-decoration:line-through;">
												&#36; <?php echo number_format($product['price'], 2); ?>
											</p>
											<h3 style="color:#e74c3c; font-size:26px; font-weight:bold; margin:0 0 8px;">
												&#36; <?php echo number_format(precioConDescuento($product['price'], $product['descuento']), 2); ?>
												<span style="background:#e74c3c; color:#fff; font-size:13px; padding:3px 10px; border-radius:20px; margin-left:8px;">
													-<?php echo $product['descuento']; ?>%
												</span>
											</h3>
										<?php else: ?>
											<h3 style="color:#3a8eff; font-size:26px; font-weight:bold; margin:0 0 16px;">
												&#36; <?php echo number_format($product['price'], 2); ?>
											</h3>
										<?php endif; ?>

										<div style="border-top:1px solid #f0f0f0; padding-top:14px; margin-bottom:14px;">
											<p style="font-size:13px; color:#666; margin-bottom:8px;">
												<b style="color:#1a2e4a;">Categoría:</b>
												<a href="category.php?category=<?php echo $product['cat_slug']; ?>"
													style="color:#3a8eff; text-decoration:none;">
													<?php echo $product['catname']; ?>
												</a>
											</p>
											<p style="font-size:13px; color:#666; margin-bottom:8px;">
												<b style="color:#1a2e4a;">Stock disponible:</b>
												<span style="background:<?php echo ($product['stock'] > 0) ? '#28a745' : '#dc3545'; ?>; color:#fff; padding:2px 10px; border-radius:20px; font-size:12px;">
													<?php echo $product['stock']; ?> unidades
												</span>
											</p>
											<?php if (!empty($product['description'])): ?>
												<p style="font-size:13px; color:#666; margin-bottom:0;">
													<b style="color:#1a2e4a;">Descripción:</b><br>
													<span style="line-height:1.7;"><?php echo $product['description']; ?></span>
												</p>
											<?php endif; ?>
										</div>

										<!-- CANTIDAD Y CARRITO -->
										<form class="form-inline" id="productForm">
											<div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
												<div class="input-group" style="width:130px;">
													<span class="input-group-btn">
														<button type="button" id="minus" class="btn btn-flat"
															style="background:#f0f2f5; border:1px solid #e0e0e0; padding:8px 12px;">
															<i class="fa fa-minus"></i>
														</button>
													</span>
													<input type="text" name="quantity" id="quantity" class="form-control"
														value="1" max="<?php echo $product['stock']; ?>" min="1"
														style="text-align:center; font-weight:bold;">
													<span class="input-group-btn">
														<button type="button" id="add" class="btn btn-flat"
															style="background:#f0f2f5; border:1px solid #e0e0e0; padding:8px 12px;">
															<i class="fa fa-plus"></i>
														</button>
													</span>
												</div>
												<input type="hidden" value="<?php echo $product['prodid']; ?>" name="id">
												<button type="submit" class="btn btn-lg btn-flat"
													style="background:#1a2e4a; color:#fff; border-radius:8px; padding:8px 24px; font-size:14px;">
													<i class="fa fa-shopping-cart"></i> Añadir al carrito
												</button>
											</div>
										</form>
									</div>

								</div>
							</div>

							<div class="fb-comments" data-href="http://localhost/ecommerce/producto.php?product=<?php echo $slug; ?>" data-numposts="10" width="100%"></div>

						</div>
						<div class="col-sm-3">
							<?php include 'includes/sidebar.php'; ?>
						</div>
					</div>
				</section>
			</div>
		</div>

		<?php $pdo->close(); ?>
		<?php include 'includes/footer.php'; ?>
	</div>

	<?php include 'includes/scripts.php'; ?>
	<script>
		$(function() {
			var maxStock = <?php echo $product['stock']; ?>;

			$('#add').click(function(e) {
				e.preventDefault();
				var quantity = parseInt($('#quantity').val());
				if (quantity < maxStock) {
					quantity++;
					$('#quantity').val(quantity);
				} else {
					alert('No hay más stock disponible. Stock máximo: ' + maxStock);
				}
			});

			$('#minus').click(function(e) {
				e.preventDefault();
				var quantity = parseInt($('#quantity').val());
				if (quantity > 1) {
					quantity--;
				}
				$('#quantity').val(quantity);
			});

			$('#productForm').submit(function(e) {
				var quantity = parseInt($('#quantity').val());
				if (quantity > maxStock) {
					e.preventDefault();
					alert('La cantidad solicitada excede el stock disponible. Stock disponible: ' + maxStock);
					$('#quantity').val(maxStock);
					return false;
				}
				if (quantity < 1) {
					e.preventDefault();
					alert('La cantidad debe ser al menos 1');
					$('#quantity').val(1);
					return false;
				}
			});
		});
	</script>
</body>

</html>