<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script>
$.ajaxPrefilter(function(options, originalOptions, xhr) {
  if (!options.crossDomain && /^(POST|PUT|PATCH|DELETE)$/i.test(options.type)) {
    xhr.setRequestHeader('X-CSRF-Token', <?php echo json_encode(generateCSRFToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
  }
});
</script>
<!-- Bootstrap 5 JS; existing visual theme is preserved during CSS migration. -->
<script src="bower_components/bootstrap5/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
  $(function () {
    // Datatable
    document.documentElement.classList.remove('pgcv-tables-pending');
    $('#example1').DataTable({
      scrollX: true,
      language: {
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Sin registros',
        infoFiltered: '(de _MAX_ registros en total)',
        zeroRecords: 'No se encontraron registros',
        emptyTable: 'Todavía no hay transacciones',
        paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior'},
        aria: {sortAscending: ': ordenar de menor a mayor', sortDescending: ': ordenar de mayor a menor'}
      }
    });
  });
</script>
<!--Magnify -->
<script src="magnify/magnify.min.js"></script>
<script>
$(function(){
	$('.zoom').magnify();
});
</script>
<!-- Custom Scripts -->
<script>
$(function(){
  getCart();

  $('#productForm').submit(function(e){
  	e.preventDefault();
  	var product = $(this).serialize();
  	$.ajax({
  		type: 'POST',
  		url: 'cart_agregar.php',
  		data: product,
  		dataType: 'json',
  		success: function(response){
  			$('#callout').show();
  			$('.message').text(response.message);
  			if(response.error){
  				$('#callout').removeClass('callout-success').addClass('callout-danger');
  			}
  			else{
				$('#callout').removeClass('callout-danger').addClass('callout-success');
				getCart();
  			}
  		}
  	});
  });

  $(document).on('click', '.close', function(){
  	$('#callout').hide();
  });

});

function getCart(){
	$.ajax({
		type: 'POST',
		url: 'cart_obtener.php',
		dataType: 'json',
		success: function(response){
			$('#cart_menu').html(response.list);
			$('.cart_count').text(response.count);
		}
	});
}
</script>
