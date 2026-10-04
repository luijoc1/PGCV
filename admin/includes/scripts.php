<!-- jQuery 3 -->
<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<script>
$.ajaxPrefilter(function(options, originalOptions, xhr) {
  if (!options.crossDomain && /^(POST|PUT|PATCH|DELETE)$/i.test(options.type)) {
    xhr.setRequestHeader('X-CSRF-Token', <?php echo json_encode(generateCSRFToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
  }
});
</script>
<!-- Bootstrap 5: conservar el diseño administrativo durante la transición CSS. -->
<script src="../bower_components/bootstrap5/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="../bower_components/select2-v4/dist/js/select2.full.min.js?v=<?php echo filemtime(__DIR__ . '/../../bower_components/select2-v4/dist/js/select2.full.min.js'); ?>"></script>
<script src="../bower_components/select2-v4/dist/js/i18n/es.js?v=<?php echo filemtime(__DIR__ . '/../../bower_components/select2-v4/dist/js/i18n/es.js'); ?>"></script>
<!-- DataTables -->
<script src="../bower_components/datatables.net/js/dataTables.min.js"></script>
<script src="../bower_components/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="../dist/js/pgcv-tables.js?v=<?php echo filemtime(__DIR__ . '/../../dist/js/pgcv-tables.js'); ?>"></script>
<!-- daterangepicker -->
<script src="../bower_components/moment/min/moment.min.js"></script>
<script src="../bower_components/daterangepicker/daterangepicker.js?v=<?php echo filemtime(__DIR__ . '/../../bower_components/daterangepicker/daterangepicker.js'); ?>"></script>
<!-- Distribución y navegación propias, conservando el tema habitual. -->
<script src="../dist/js/pgcv-layout.js?v=<?php echo filemtime(__DIR__ . '/../../dist/js/pgcv-layout.js'); ?>"></script>
<!-- Product description editor -->
<script src="../bower_components/jodit/es2021/jodit.min.js"></script>
<script src="../dist/js/product-editor.js"></script>
<!-- Data Table Initialize -->
<script>
  $(function() {
    document.documentElement.classList.remove('pgcv-tables-pending');
    $('#example1').DataTable({
      scrollX: true
    })
    $('#example2').DataTable({
      'paging': true,
      'lengthChange': false,
      'searching': false,
      'ordering': true,
      'info': true,
      'autoWidth': false,
      'scrollX': true
    })
    $('.dt-scroll-body').attr({tabindex: '0', role: 'region', 'aria-label': 'Tabla de registros, desplazamiento horizontal'});
    $('body').on('shown.bs.modal', '.modal', function() {
      $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });
  })
</script>
<script>
  $(function() {
    //Initialize Select2 Elements
    $('.select2').each(function() {
      var modal = $(this).closest('.modal');
      var options = {language: 'es'};
      if (modal.length) options.dropdownParent = modal;
      $(this).select2(options);
      if (modal.length) {
        // Escape cierra primero la lista; no debe llegar también al modal Bootstrap.
        $(this).on('select2:open', function() {
          modal.find('.select2-dropdown')
            .off('keydown.pgcvSelect2Escape')
            .on('keydown.pgcvSelect2Escape', function(e) {
              if (e.key === 'Escape' || e.which === 27) e.stopPropagation();
            });
        });
      }
    });

    PGCVProductEditors.init();
  });
</script>
<!-- Avisos de ventas: versión exacta local, con los estilos incluidos en el mismo bundle. -->
<script src="../bower_components/sweetalert2/dist/sweetalert2.all.min.js?v=<?php echo filemtime(__DIR__ . '/../../bower_components/sweetalert2/dist/sweetalert2.all.min.js'); ?>"></script>
