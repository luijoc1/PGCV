<?php include 'includes/session.php'; ?>
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
	        		<h1 class="page-header">Su carrito de compras</h1>
	        		<div class="box box-solid">
	        			<div class="box-body">
		        		<table class="table table-bordered">
		        			<thead>
		        				<th></th>
		        				<th>Foto</th>
		        				<th>Nombre</th>
		        				<th>Precio</th>
		        				<th width="20%">Cantidad</th>
		        				<th>Subtotal</th>
		        			</thead>
		        			<tbody id="tbody">
		        			</tbody>
		        		</table>
	        			</div>
	        		</div>
	        		<?php
	        			if(isset($_SESSION['user'])){
	        				echo "
	        					<div class='box box-solid' style='margin-top: 20px;'>
	        						<div class='box-body'>
	        							<div class='row'>
	        								<div class='col-sm-6'>
	        									<h3>Total: <span id='total-display'>$0.00</span></h3>
	        								</div>
	        								<div class='col-sm-6 text-right'>
	        									<button type='button' class='btn btn-success btn-lg' id='btn-pagar' style='padding: 15px 50px; font-size: 18px;'>
	        										<i class='fa fa-credit-card'></i> Pagar
	        									</button>
	        								</div>
	        							</div>
	        						</div>
	        					</div>
	        				";
	        			}
	        			else{
	        				echo "
	        					<h4>Necesitas <a href='login.php'>Iniciar sesión</a> para revisar.</h4>
	        				";
	        			}
	        		?>
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
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_eliminar.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
				else{
					alert(response.message);
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = parseInt($('#qty_'+id).val());
		if(qty > 1){
			qty--;
			$('#qty_'+id).val(qty);
			$.ajax({
				type: 'POST',
				url: 'cart_actualizar.php',
				data: {
					id: id,
					qty: qty,
				},
				dataType: 'json',
				success: function(response){
					if(!response.error){
						getDetails();
						getCart();
						getTotal();
					}
					else{
						alert(response.message);
						getDetails();
						getCart();
						getTotal();
					}
				}
			});
		}
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = parseInt($('#qty_'+id).val());
		var maxStock = parseInt($('#qty_'+id).data('stock')) || 999;
		if(qty < maxStock){
			qty++;
			$('#qty_'+id).val(qty);
			$.ajax({
				type: 'POST',
				url: 'cart_actualizar.php',
				data: {
					id: id,
					qty: qty,
				},
				dataType: 'json',
				success: function(response){
					if(!response.error){
						getDetails();
						getCart();
						getTotal();
					}
					else{
						alert(response.message);
						getDetails();
						getCart();
						getTotal();
					}
				}
			});
		}
		else{
			alert('No hay más stock disponible. Stock máximo: ' + maxStock);
		}
	});

	getDetails();
	getTotal();

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_detalles.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			total = parseFloat(response);
			// Actualizar el total mostrado
			$('#total-display').text('$' + total.toFixed(2));
		}
	});
}

// Botón de pago
$(document).on('click', '#btn-pagar', function(e){
	e.preventDefault();
	
	// Verificar que hay productos en el carrito
	if(total <= 0){
		alert('El carrito está vacío');
		return;
	}
	
	// Confirmar pago
	if(confirm('¿Confirmar el pago de $' + total.toFixed(2) + '?')){
		// Deshabilitar botón mientras se procesa
		$('#btn-pagar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
		
		// Redirigir a ventas.php para procesar el pago
		window.location = 'ventas.php';
	}
});
</script>
</body>
</html>