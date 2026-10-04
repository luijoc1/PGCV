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
<script src="bower_components/datatables.net/js/dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="dist/js/pgcv-tables.js?v=<?php echo filemtime(__DIR__ . '/../dist/js/pgcv-tables.js'); ?>"></script>
<!-- Distribución propia, conservando el tema habitual. -->
<script src="dist/js/pgcv-layout.js?v=<?php echo filemtime(__DIR__ . '/../dist/js/pgcv-layout.js'); ?>"></script>
<script>
  $(function () {
    // Datatable
    document.documentElement.classList.remove('pgcv-tables-pending');
    $('#example1').DataTable({
      scrollX: true,
      language: {emptyTable: 'Todavía no hay transacciones'}
    });
    $('.dt-scroll-body').attr({tabindex: '0', role: 'region', 'aria-label': 'Historial de transacciones, desplazamiento horizontal'});
  });
</script>
<!--Magnify -->
<script src="bower_components/magnify/dist/js/jquery.magnify.js?v=<?php echo filemtime(__DIR__ . '/../bower_components/magnify/dist/js/jquery.magnify.js'); ?>"></script>
<script>
$(function(){
	$('.zoom').magnify({timeout: 0});
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
