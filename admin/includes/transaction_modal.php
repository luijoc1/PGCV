<!-- Detalles de venta: Bootstrap 5 conserva el diseño administrativo habitual. -->
<div class="modal fade" id="transaction" tabindex="-1" role="dialog" aria-labelledby="transaction-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="transaction-title"><b>Detalles completos de la transacción</b></h4>
            </div>
            <div class="modal-body">
                <p>
                    Fecha: <span id="date"></span>
                    <span class="pull-right">Transacción: <span id="transid"></span></span>
                </p>
                <div class="table-responsive" role="region" aria-label="Detalles de la transacción" tabindex="0">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody id="detail">
                            <tr>
                                <td colspan="3" align="right"><b>Total</b></td>
                                <td class="text-nowrap"><span id="total"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-bs-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>
