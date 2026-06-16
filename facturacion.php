<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>

<div class="content-wrapper">
<div class="container">
<section class="content">

<?php
if(!isset($_SESSION['user'])){
    echo "<h4>Necesitas <a href='login.php'>Iniciar sesión</a> para continuar.</h4>";
}
else{
    $conn = $pdo->open();

    // Verificar que el carrito no esté vacío
    $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM cart WHERE user_id=:user_id");
    $stmt->execute(['user_id'=>$user['id']]);
    $cart_check = $stmt->fetch();

    if($cart_check['numrows'] == 0){
        echo "<h4>Tu carrito está vacío. <a href='index.php'>Volver a la tienda</a></h4>";
    }
    else{
?>

<div class="row">
    <div class="col-sm-7">
        <h1 class="page-header">Facturación y pago</h1>

        <form action="ventas.php" method="POST" id="form-facturacion">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Datos de facturación</h3>
                </div>
                <div class="box-body">

                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre_facturacion" class="form-control"
                               value="<?php echo htmlspecialchars($user['firstname'].' '.$user['lastname']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Documento (cédula / NIT)</label>
                        <input type="text" name="documento" class="form-control" placeholder="1098765432" required>
                    </div>

                    <div class="form-group">
                        <label>Dirección de entrega</label>
                        <input type="text" name="direccion" class="form-control"
                               value="<?php echo htmlspecialchars($user['address']); ?>"
                               placeholder="Calle 10 # 5-20" required>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="3001234567" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="ciudad" class="form-control" placeholder="Santa Marta" required>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Método de pago</h3>
                </div>
                <div class="box-body">

                    <div class="radio">
                        <label>
                            <input type="radio" name="metodo_pago" value="tarjeta" checked>
                            <i class="fa fa-credit-card"></i> Tarjeta de crédito / débito
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="metodo_pago" value="transferencia">
                            <i class="fa fa-bank"></i> Transferencia / PSE
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="metodo_pago" value="efectivo">
                            <i class="fa fa-money"></i> Efectivo (pago contraentrega)
                        </label>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div class="col-sm-5">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Resumen del pedido</h3>
            </div>
            <div class="box-body">
                <table class="table">
                    <tbody id="resumen-tbody"></tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th id="resumen-total">$0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <button type="submit" form="form-facturacion" class="btn btn-success btn-lg btn-block" style="padding: 15px;">
            <i class="fa fa-lock"></i> Confirmar y pagar
        </button>

        <p class="text-muted text-center" style="margin-top: 10px; font-size: 12px;">
            Tus datos se usarán solo para generar la factura
        </p>
    </div>
</div>

<?php
    }
    $pdo->close();
}
?>

</section>
</div>
</div>

<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
    // Cargar resumen del carrito
    $.ajax({
        type: 'POST',
        url: 'cart_detalles.php',
        dataType: 'html',
        success: function(response){
            var rows = $(response).filter('tr');
            var resumen = '';
            rows.each(function(){
                var nombre = $(this).find('td').eq(2).text().trim();
                var cantidad = $(this).find('input[type=text]').val();
                var subtotal = $(this).find('td').last().text().trim();
                resumen += '<tr><td>'+nombre+' x'+cantidad+'</td><td class="text-right">'+subtotal+'</td></tr>';
            });
            $('#resumen-tbody').html(resumen);
        }
    });

    // Cargar total
    $.ajax({
        type: 'POST',
        url: 'cart_total.php',
        dataType: 'json',
        success: function(response){
            var total = parseFloat(response);
            $('#resumen-total').text('$' + total.toFixed(2));
        }
    });
});
</script>

</body>
</html>