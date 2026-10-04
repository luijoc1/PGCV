<!-- Delete -->
<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="delete-title" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="delete-title"><b>Eliminando...</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="products_delete.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" class="prodid" name="id">
          <div class="text-center">
            <p>BORRAR PRODUCTO</p>
            <h2 class="bold name"></h2>
            <p>Solo se pueden eliminar productos que no estén vinculados a ventas ni carritos.</p>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-bs-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
        <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit" tabindex="-1" aria-labelledby="edit-title" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="edit-title"><b>Editar Producto</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="products_edit.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" class="prodid" name="id">
          <div class="form-group">
            <label for="edit_name" class="col-sm-1 control-label">Nombre</label>

            <div class="col-sm-5">
              <input type="text" class="form-control" id="edit_name" name="name">
            </div>

            <label for="edit_category" class="col-sm-1 control-label">Categoría</label>

            <div class="col-sm-5">
              <select class="form-control" id="edit_category" name="category">
                <option selected id="catselected"></option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_price" class="col-sm-1 control-label">Precio</label>

            <div class="col-sm-5">
              <input type="text" class="form-control" id="edit_price" name="price">
            </div>

            <label for="edit_stock" class="col-sm-1 control-label">Stock</label>

            <div class="col-sm-5">
              <input type="number" class="form-control" id="edit_stock" name="stock" min="0" value="0" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_descuento" class="col-sm-1 control-label">Descuento</label>
            <div class="col-sm-5">
              <div class="input-group">
                <input type="number" class="form-control" id="edit_descuento" name="descuento" min="0" max="100" value="0">
                <span class="input-group-addon">%</span>
              </div>
            </div>
            <label for="edit_stock_minimo" class="col-sm-2 control-label">Stock mínimo</label>
            <div class="col-sm-4">
              <input type="number" class="form-control" id="edit_stock_minimo" name="stock_minimo" min="0" max="2147483647" required>
            </div>
          </div>
          <p><b>Descripción</b></p>
          <div class="form-group">
            <div class="col-sm-12">
              <textarea id="editor2" name="description" rows="10" cols="80"></textarea>
            </div>

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-bs-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
        <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>
