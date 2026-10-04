<?php include 'includes/session.php'; ?>
<?php
if (!isset($_SESSION['user'])) {
	header('location: index.php');
	exit();
}
?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
	<div class="wrapper">

		<?php include 'includes/navbar.php'; ?>

		<div class="content-wrapper">
			<div class="container">

				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-sm-9">
							<?php
							if (isset($_SESSION['error'])) {
								echo "
	        					<div class='callout callout-danger'>
	        						" . escapeHtml($_SESSION['error']) . "
	        					</div>
	        				";
								unset($_SESSION['error']);
							}

							if (isset($_SESSION['success'])) {
								echo "
	        					<div class='callout callout-success'>
	        						" . escapeHtml($_SESSION['success']) . "
	        					</div>
	        				";
								unset($_SESSION['success']);
							}
							?>
							<div class="box box-solid">
								<div class="box-body">
									<div class="row profile-summary">
									<div class="col-sm-3">
										<img src="<?php echo safeImageUrl($user['photo'], 'images/', 'profile.jpg'); ?>" class="profile-avatar" alt="Foto de perfil">
									</div>
									<div class="col-sm-9">
										<dl class="profile-details">
                                                <dt>Nombre</dt>
                                                <dd>
													<?php echo escapeHtml($user['firstname']) . ' ' . escapeHtml($user['lastname']); ?>
                                                </dd>
                                                <dt>Correo electrónico</dt>
                                                <dd><?php echo escapeHtml($user['email']); ?></dd>
                                                <dt>Información de contacto</dt>
                                                <dd><?php echo escapeHtml(!empty($user['contact_info']) ? $user['contact_info'] : 'Sin registrar'); ?></dd>
                                                <dt>Dirección</dt>
                                                <dd><?php echo escapeHtml(!empty($user['address']) ? $user['address'] : 'Sin registrar'); ?></dd>
                                                <dt>Miembro desde</dt>
                                                <dd><?php
													if (!empty($user['created_on'])) {
														$partes = explode('-', $user['created_on']);
														$meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
														echo $meses[(int)$partes[1]] . ' ' . $partes[2] . ', ' . $partes[0];
													} else {
														echo 'N/a';
													}
													?></dd>
                                            </dl>
                                            <a href="#edit" class="btn btn-success btn-flat btn-sm" data-bs-toggle="modal"><i class="fa fa-edit"></i> Editar perfil</a>
									</div>
									</div>
								</div>
							</div>
							<div class="box box-solid">
								<div class="box-header with-border">
									<h4 class="box-title"><i class="fa fa-calendar"></i> <b>Historial de transacciones</b></h4>
								</div>
								<div class="box-body">
									<table class="table table-bordered" id="example1" style="width:100%" data-column-defs='[{"targets":0,"visible":false,"searchable":false},{"targets":"_all","orderSequence":["asc","desc"]}]'>
										<thead>
											<tr>
											<th class="hidden"></th>
											<th>Fecha</th>
											<th>Transacción#</th>
											<th>Total</th>
											<th>Estado</th>
											<th>Detalles completos</th>
											<th>Factura</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$conn = $pdo->open();

											try {
												$stmt = $conn->prepare("SELECT * FROM sales WHERE user_id=:user_id ORDER BY sales_date DESC");
												$stmt->execute(['user_id' => $user['id']]);
												foreach ($stmt as $row) {
													$total = (float) $row['total'];
													$estados = [
														'pendiente'  => ['label' => 'Pendiente',  'color' => '#f39c12'],
														'en_proceso' => ['label' => 'En proceso', 'color' => '#3a8eff'],
														'enviado'    => ['label' => 'Enviado',    'color' => '#8e44ad'],
														'entregado'  => ['label' => 'Entregado',  'color' => '#27ae60'],
													];
													$estado_actual = $row['estado'] ?? 'pendiente';
													$estado_info = $estados[$estado_actual] ?? $estados['pendiente'];

													echo "
    <tr>
        <td class='hidden'></td>
        <td>" . date('M d, Y', strtotime($row['sales_date'])) . "</td>
        <td>" . escapeHtml($row['pay_id']) . "</td>
        <td>&#36; " . number_format($total, 2) . "</td>
        <td>
            <span style='background:" . $estado_info['color'] . "; color:#fff; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold;'>
                " . $estado_info['label'] . "
            </span>
        </td>
        <td><button class='btn btn-sm btn-flat btn-info transact' data-id='" . $row['id'] . "'><i class='fa fa-search'></i> Ver</button></td>
        <td><a href='factura_pdf.php?id=" . $row['id'] . "' class='btn btn-sm btn-flat btn-danger'><i class='fa fa-file-pdf-o'></i> PDF</a></td>
    </tr>
";
												}
											} catch (PDOException $e) {
												echo "Hay algún problema en la conexión.: " . $e->getMessage();
											}

											$pdo->close();
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-sm-3">
							<?php include 'includes/sidebar.php'; ?>
						</div>
					</div>
				</section>

			</div>
		</div>

		<?php include 'includes/footer.php'; ?>
		<?php include 'includes/profile_modal.php'; ?>
	</div>

	<?php include 'includes/scripts.php'; ?>
	<script>
		$(function() {
			$(document).on('click', '.transact', function(e) {
				e.preventDefault();
				bootstrap.Modal.getOrCreateInstance(document.getElementById('transaction')).show();
				$('.prepend_items').remove();
				$('#date, #transid').text('');
				$('#total').empty();
				var id = $(this).data('id');
				$.ajax({
					type: 'POST',
					url: 'transaccion.php',
					data: {
						id: id
					},
					dataType: 'json',
					success: function(response) {
						$('#date').text(response.date);
						$('#transid').text(response.transaction);
						$('#detail').prepend(response.list);
						$('#total').html(response.total);
					},
					error: function(xhr) {
						bootstrap.Modal.getOrCreateInstance(document.getElementById('transaction')).hide();
						alert((xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo consultar la transacción.');
					}
				});
			});

			$("#transaction").on("hidden.bs.modal", function() {
				$('.prepend_items').remove();
			});
		});
	</script>
</body>

</html>
