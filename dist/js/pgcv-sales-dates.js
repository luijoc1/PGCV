/* PGCV: conservar los campos y botones del selector de ventas con Daterangepicker 3.1. */
(function (window, $, moment) {
  'use strict';
  var format = 'MM/DD/YYYY'; // Contrato de includes/sales_report.php.

  function calendar(side, name, label) {
    return '<div class="drp-calendar ' + side + '">' +
      '<div class="daterangepicker_input"><input class="input-mini form-control" type="text" name="' + name +
      '" aria-label="' + label + '" autocomplete="off"><i class="fa fa-calendar" aria-hidden="true"></i></div>' +
      '<div class="calendar-table"></div><div class="calendar-time"></div></div>';
  }

  function init(selector) {
    var field = $(selector);
    if (!field.length || field.data('daterangepicker')) return;
    field.daterangepicker({
      locale: {format: format, separator: ' - '},
      applyButtonClasses: 'btn-success',
      cancelButtonClasses: 'btn-default',
      template: '<div class="daterangepicker dropdown-menu">' +
        calendar('left', 'daterangepicker_start', 'Fecha inicial del reporte') +
        calendar('right', 'daterangepicker_end', 'Fecha final del reporte') +
        '<div class="ranges drp-buttons"><button class="applyBtn" disabled type="button"></button> ' +
        '<button class="cancelBtn" type="button"></button></div></div>'
    });
    var picker = field.data('daterangepicker');
    var inputs = picker.container.find('.input-mini');
    var originalUpdate = picker.updateFormInputs;

    // 3.1 ya no actualiza campos internos; mantenerlos sincronizados al navegar o elegir días.
    picker.updateFormInputs = function () {
      originalUpdate.call(this);
      inputs.filter('[name=daterangepicker_start]').val(this.startDate.format(format));
      inputs.filter('[name=daterangepicker_end]').val(this.endDate ? this.endDate.format(format) : '');
      inputs.removeAttr('aria-invalid').removeClass('active');
      inputs.filter(this.endDate ? '[name=daterangepicker_start]' : '[name=daterangepicker_end]').addClass('active');
    };
    function readInputs() {
      var start = moment(inputs.eq(0).val(), format, true);
      var end = moment(inputs.eq(1).val(), format, true);
      var validStart = start.isValid() && start.year() >= 1000;
      var validEnd = end.isValid() && end.year() >= 1000 && validStart && !end.isBefore(start, 'day');
      inputs.eq(0).attr('aria-invalid', validStart ? 'false' : 'true');
      inputs.eq(1).attr('aria-invalid', validEnd ? 'false' : 'true');
      picker.container.find('.applyBtn').prop('disabled', !validEnd);
      return validEnd ? {start: start, end: end} : null;
    }
    function commitInputs() {
      var dates = readInputs();
      if (!dates) return false;
      picker.setStartDate(dates.start);
      picker.setEndDate(dates.end);
      picker.updateView();
      return true;
    }
    inputs.on('input.pgcvDates', readInputs)
      .on('change.pgcvDates blur.pgcvDates', commitInputs)
      .on('keydown.pgcvDates', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); commitInputs(); }
        if (e.key === 'Escape') {
          e.preventDefault();
          // Ocultar dispara blur: restaurar los campos antes de que ese evento los confirme.
          inputs.eq(0).val(picker.oldStartDate.format(format));
          inputs.eq(1).val(picker.oldEndDate.format(format));
          picker.clickCancel();
        }
      });
    // Ejecutar antes del manejador delegado de 3.1, incluso al aplicar sin salir del campo.
    picker.container.find('.applyBtn').on('click.pgcvDates', function (e) {
      if (!commitInputs()) { e.preventDefault(); e.stopImmediatePropagation(); }
    });
    picker.updateFormInputs();
  }
  window.PGCVSalesDates = {init: init};
}(window, window.jQuery, window.moment));
