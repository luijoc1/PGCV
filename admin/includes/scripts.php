<!-- jQuery 3 -->
<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<script>
$.ajaxPrefilter(function(options, originalOptions, xhr) {
  if (!options.crossDomain && /^(POST|PUT|PATCH|DELETE)$/i.test(options.type)) {
    xhr.setRequestHeader('X-CSRF-Token', <?php echo json_encode(generateCSRFToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
  }
});
</script>
<!-- Bootstrap 3.4.1: PGCV runtime without Button, Tooltip or Popover -->
<script src="../dist/js/bootstrap-pgcv.js"></script>
<!-- Select2 -->
<script src="../bower_components/select2/dist/js/select2.full.min.js"></script>
<!-- DataTables -->
<script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- daterangepicker -->
<script src="../bower_components/moment/min/moment.min.js"></script>
<script src="../bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="../plugins/timepicker/bootstrap-timepicker.min.js"></script>
<!-- Slimscroll -->
<script src="../bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="../bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- Product description editor -->
<script src="../bower_components/jodit/es2021/jodit.min.js"></script>
<script src="../dist/js/product-editor.js"></script>
<!-- Active Script -->
<script>
  $(function() {
    /** agregar una clase activa y permanecer abierto cuando se selecciona */
    var url = window.location;

    // for sidebar menu entirely but not cover treeview
    $('ul.sidebar-menu a').filter(function() {
      return this.href == url;
    }).parent().addClass('active');

    // for treeview
    $('ul.treeview-menu a').filter(function() {
      return this.href == url;
    }).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');

  });
</script>
<!-- Data Table Initialize -->
<script>
  $(function() {
    var tableLanguage = {
      search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      infoEmpty: 'Sin registros', infoFiltered: '(de _MAX_ registros en total)',
      zeroRecords: 'No se encontraron registros', emptyTable: 'No hay registros',
      paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior'},
      aria: {sortAscending: ': ordenar de menor a mayor', sortDescending: ': ordenar de mayor a menor'}
    };
    document.documentElement.classList.remove('pgcv-tables-pending');
    $('#example1').DataTable({
      scrollX: true,
      language: tableLanguage
    })
    $('#example2').DataTable({
      'paging': true,
      'lengthChange': false,
      'searching': false,
      'ordering': true,
      'info': true,
      'autoWidth': false,
      'scrollX': true,
      'language': tableLanguage
    })
    $('.dataTables_scrollBody').attr({tabindex: '0', role: 'region', 'aria-label': 'Tabla de registros, desplazamiento horizontal'});
    $('body').on('shown.bs.modal', '.modal', function() {
      $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });
  })
</script>
<script>
  $(function() {
    //Initialize Select2 Elements
    $('.select2').select2()

    PGCVProductEditors.init();
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
