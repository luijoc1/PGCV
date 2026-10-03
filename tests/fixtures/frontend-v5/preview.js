// Static fixture only: no fetch, AJAX, database, account or mail transport.
'use strict';
const currency = new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0});
const rows = Array.from({length: 12}, (_, index) => [
  index === 0 ? 'Amortiguador de muestra' : `Producto de ejemplo ${index + 1}`,
  index % 2 ? 'Motor' : 'Amortiguador',
  currency.format(150000), '15%', currency.format(127500)
]);
const table = new DataTable('#products-table', {
  data: rows, pageLength: 5, lengthMenu: [5, 10, 25], scrollX: true,
  language: {
    aria: {
      orderable: ': Activar para ordenar',
      orderableReverse: ': Activar para invertir el orden',
      orderableRemove: ': Activar para quitar el orden',
      paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior', number: 'Página '}
    },
    emptyTable: 'Sin registros',
    search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
    infoEmpty: 'Sin registros', infoFiltered: '(de _MAX_ registros en total)',
    zeroRecords: 'No se encontraron registros',
    paginate: {first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior'}
  }
});
document.querySelector('.dt-scroll-body').setAttribute('tabindex', '0');
document.querySelector('.dt-scroll-body').setAttribute('aria-label', 'Tabla de ejemplo con desplazamiento horizontal');
document.querySelector('#product-form').addEventListener('submit', event => {
  event.preventDefault();
  document.querySelector('#example-result').textContent = `Ejemplo: ${document.querySelector('#sample-name').value}, descuento ${document.querySelector('#sample-discount').value}%.`;
  bootstrap.Modal.getOrCreateInstance(document.querySelector('#product-dialog')).hide();
});
document.querySelector('#range-form').addEventListener('submit', event => {
  event.preventDefault();
  const from = document.querySelector('#from-date').value;
  const to = document.querySelector('#to-date').value;
  const result = document.querySelector('#range-result');
  if (from > to) { result.textContent = 'La fecha inicial debe ser anterior o igual a la final.'; return; }
  const reportDate = value => { const [year, month, day] = value.split('-'); return `${month}/${day}/${year}`; };
  result.textContent = `Rango de ejemplo: ${reportDate(from)} - ${reportDate(to)}`;
});
// Allow column widths to follow a sidebar transition or viewport resize.
document.addEventListener('collapsed.lte.push-menu', () => table.columns.adjust());
document.addEventListener('opened.lte.push-menu', () => table.columns.adjust());
