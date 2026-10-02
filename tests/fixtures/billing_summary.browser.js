(function () {
  'use strict';
  var $ = window.jQuery;
  var api = window.PGCVBillingSummary;
  var passed = 0, failed = 0;
  function assert(condition, message) { if (!condition) throw new Error(message); }
  function equal(actual, expected) { assert(actual === expected, actual + ' !== ' + expected); }
  function fixture() {
    $('#fixture').html('<form id="form-facturacion"></form>' +
      '<button type="submit" form="form-facturacion">Confirmar pedido</button>' +
      '<table><tbody id="resumen-tbody"></tbody><tfoot><tr><td id="resumen-total">$0.00</td></tr></tfoot></table>');
  }
  function product(name, quantity, subtotal) {
    return '<tr><td>Eliminar</td><td>Foto</td><td>' + name + '</td>' +
      '<td><small>$ 250,000.00</small><b>$ 230,000.00</b></td>' +
      '<td><input type="text" value="' + quantity + '"></td><td>' + subtotal + '</td></tr>';
  }
  var total = '<tr><td colspan="5">Total</td><td>$ 230,000.00</td></tr>';
  var empty = '<tr><td colspan="6">Carrito de compras vacío</td></tr>';
  function render(html) { api.renderRows($, html, '#resumen-tbody'); }
  function test(name, body) {
    fixture();
    var originalAjax = $.ajax;
    try {
      body(); passed++;
      $('<li>').text('PASS: ' + name).appendTo('#results');
    } catch (error) {
      failed++;
      $('<li>').text('FAIL: ' + name + ': ' + error.message).appendTo('#results');
    } finally { $.ajax = originalAjax; }
  }

  test('Regresión xundefined: omite total y mensajes', function () {
    render(product('Motor', '1', '$ 230,000.00') + total + empty);
    equal($('#resumen-tbody tr').length, 1);
    equal($('#resumen-tbody td').first().text(), 'Motor x1');
    assert(!$('#resumen-tbody').text().includes('undefined'), 'Cantidad indefinida');
  });
  test('Quita el aviso de stock del nombre', function () {
    render(product('Motor<br><small class="text-warning">Stock bajo: 1</small>', '1', '$ 230,000.00'));
    equal($('#resumen-tbody td').first().text(), 'Motor x1');
  });
  test('Conserva orden, cantidades y subtotales con descuento', function () {
    render(product('Motor', '2', '$ 460,000.00') + product('Filtro', '3', '$ 60,000.00') + total);
    equal($('#resumen-tbody tr').length, 2);
    equal($('#resumen-tbody tr').first().text(), 'Motor x2$ 460,000.00');
    equal($('#resumen-tbody tr').last().text(), 'Filtro x3$ 60,000.00');
  });
  test('Una recarga reemplaza el contenido anterior', function () {
    render(product('Anterior', '1', '$ 1.00'));
    render(product('Nuevo', '2', '$ 2.00'));
    equal($('#resumen-tbody tr').length, 1);
    equal($('#resumen-tbody td').first().text(), 'Nuevo x2');
  });
  test('Carrito vacío retira productos anteriores', function () {
    render(product('Anterior', '1', '$ 1.00'));
    render(empty + total);
    equal($('#resumen-tbody tr').length, 0);
  });
  test('Nombres escapados se insertan como texto', function () {
    render(product('&lt;img src=x onerror=alert(1)&gt; &amp; Motor', '1', '$ 1.00'));
    equal($('#resumen-tbody td').first().text(), '<img src=x onerror=alert(1)> & Motor x1');
    equal($('#resumen-tbody img').length, 0);
  });
  test('Ignora scripts de la respuesta', function () {
    window.billingScriptExecuted = false;
    render('<script>window.billingScriptExecuted=true;</script>' + product('Motor', '1', '$ 1.00'));
    equal(window.billingScriptExecuted, false);
    equal($('#resumen-tbody script').length, 0);
  });
  test('Una respuesta de texto no se interpreta como selector DOM', function () {
    render('#form-facturacion');
    equal($('#resumen-tbody tr').length, 0);
    equal($('#form-facturacion').length, 1);
  });
  test('Ignora respuestas no textuales', function () {
    [null, undefined, 42, {}, []].forEach(function (value) {
      render(value); equal($('#resumen-tbody tr').length, 0);
    });
  });
  test('Ignora cantidades vacías, negativas, cero o fraccionarias', function () {
    ['', '0', '-1', '1.5', 'undefined', ' 1', '01'].forEach(function (value) {
      render(product('Motor', value, '$ 1.00'));
      equal($('#resumen-tbody tr').length, 0);
    });
  });
  test('Ignora filas incompletas o sin nombre/subtotal', function () {
    render('<tr><td><input type="text" value="1"></td></tr>' +
      product('', '1', '$ 1.00') + product('Motor', '1', ''));
    equal($('#resumen-tbody tr').length, 0);
  });
  test('Carga el resumen y total mediante POST JSON', function () {
    var requests = [];
    $.ajax = function (options) { requests.push(options); };
    api.init($);
    equal(requests.length, 2);
    requests.forEach(function (request) { equal(request.type, 'POST'); equal(request.dataType, 'json'); });
    equal(requests[0].url, 'cart_detalles.php'); equal(requests[1].url, 'cart_total.php');
    requests[0].success(product('Motor', '1', '$ 230,000.00') + total);
    requests[1].success(230000);
    equal($('#resumen-tbody tr').length, 1);
    equal($('#resumen-total').text(), '$ 230,000.00');
  });
  test('Indica fallos de red y retira el resumen anterior', function () {
    var requests = [];
    $.ajax = function (options) { requests.push(options); };
    api.init($); render(product('Anterior', '1', '$ 1.00'));
    requests[0].error(); requests[1].error();
    equal($('#resumen-tbody').text(), 'No se pudo cargar el resumen del pedido.');
    equal($('#resumen-total').text(), 'Total no disponible');
  });
  test('Un total inválido nunca muestra NaN', function () {
    var requests = [];
    $.ajax = function (options) { requests.push(options); };
    api.init($); requests[1].success('incorrecto');
    equal($('#resumen-total').text(), 'Total no disponible');
  });
  test('Deshabilita confirmar al enviar el formulario', function () {
    $.ajax = function () {};
    api.init($);
    $('#form-facturacion').triggerHandler('submit');
    equal($('button[form="form-facturacion"]').prop('disabled'), true);
    equal($('button[form="form-facturacion"]').text(), 'Registrando pedido...');
  });
  $('#fixture').empty();
  $('#result').text(passed + ' pruebas correctas; ' + failed + ' fallidas.');
}());
