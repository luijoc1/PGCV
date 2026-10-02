(function (root) {
  'use strict';

  function formatTotal(value) {
    if ((typeof value !== 'number' && typeof value !== 'string') ||
        (typeof value === 'string' && !value.trim())) return null;
    var total = Number(value);
    if (!Number.isFinite(total) || total < 0) return null;
    return '$ ' + total.toLocaleString('en-US', {
      minimumFractionDigits: 2, maximumFractionDigits: 2
    });
  }

  function renderRows($, response, target) {
    var resumen = $('<tbody>');
    // Parse as HTML rather than allowing a response to act as a selector.
    var rows = typeof response === 'string'
      ? $($.parseHTML(response, document, false)).filter('tr') : $();
    rows.each(function () {
      var cells = $(this).children('td');
      var quantityInputs = $(this).find('input[type=text]');
      if (cells.length !== 6 || quantityInputs.length !== 1) return;
      var quantity = quantityInputs.val();
      if (!/^[1-9]\d*$/.test(quantity)) return;
      var nameCell = cells.eq(2).clone();
      nameCell.find('small, br').remove();
      var name = nameCell.text().trim();
      var subtotal = cells.last().text().trim();
      if (!name || !subtotal) return;
      var row = $('<tr>');
      $('<td>').text(name + ' x' + quantity).appendTo(row);
      $('<td>').addClass('text-right').text(subtotal).appendTo(row);
      resumen.append(row);
    });
    $(target).empty().append(resumen.children());
  }

  function init($) {
    $('#form-facturacion').on('submit', function () {
      $('button[form="form-facturacion"]').prop('disabled', true).text('Registrando pedido...');
    });
    $.ajax({
      type: 'POST', url: 'cart_detalles.php', dataType: 'json',
      success: function (response) { renderRows($, response, '#resumen-tbody'); },
      error: function () {
        var row = $('<tr>');
        $('<td>').attr('colspan', 2).text('No se pudo cargar el resumen del pedido.').appendTo(row);
        $('#resumen-tbody').empty().append(row);
      }
    });
    $.ajax({
      type: 'POST', url: 'cart_total.php', dataType: 'json',
      success: function (response) {
        var formatted = formatTotal(response);
        $('#resumen-total').text(formatted === null ? 'Total no disponible' : formatted);
      },
      error: function () { $('#resumen-total').text('Total no disponible'); }
    });
  }

  var api = { formatTotal: formatTotal, renderRows: renderRows, init: init };
  if (typeof module === 'object' && module.exports) module.exports = api;
  else root.PGCVBillingSummary = api;
}(typeof window === 'undefined' ? globalThis : window));
