/* Configuración compartida: conservar los controles habituales sobre DataTables 3. */
(function () {
  'use strict';

  // Mantener el ancho natural cuando la plantilla no declara uno explícito.
  document.querySelectorAll('#example1, #example2').forEach(function (table) {
    if (!table.style.width && !table.hasAttribute('width')) table.style.width = 'auto';
  });

  DataTable.util.object.assignDeep(DataTable.ext.classes.layout, {
    row: 'row pgcv-dt-row',
    cell: '',
    tableCell: 'col-sm-12',
    start: 'dt-layout-start col-sm-6',
    end: 'dt-layout-end col-sm-6',
    full: 'dt-layout-full col-sm-12'
  });
  DataTable.util.object.assignDeep(DataTable.defaults, {
    columnDefs: [{targets: '_all', orderSequence: ['asc', 'desc']}],
    layout: {
      topStart: 'pageLength', topEnd: 'search',
      bottomStart: 'info', bottomEnd: {paging: {firstLast: false}}
    },
    language: {
      search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      infoEmpty: 'Sin registros', infoFiltered: '(de _MAX_ registros en total)',
      zeroRecords: 'No se encontraron registros', emptyTable: 'No hay registros',
      paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior'},
      aria: {
        orderable: ': ordenar de menor a mayor',
        orderableReverse: ': invertir ordenación',
        orderableRemove: ': invertir ordenación',
        paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior'}
      }
    }
  });
})();
