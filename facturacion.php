<?php include 'includes/session.php'; ?>
<?php require_once __DIR__ . '/includes/checkout.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>

        <div class="content-wrapper">
            <div class="container">
                <section class="content">

                    <?php foreach (['error' => 'danger', 'success' => 'success'] as $messageKey => $messageClass): ?>
                        <?php if (isset($_SESSION[$messageKey])): ?>
                            <div class="alert alert-<?php echo $messageClass; ?>"><?php echo escapeHtml($_SESSION[$messageKey]); ?></div>
                            <?php unset($_SESSION[$messageKey]); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php
                    if (!isset($_SESSION['user'])) {
                        echo "<h4>Necesitas <a href='login.php'>Iniciar sesión</a> para continuar.</h4>";
                    } else {
                        $conn = $pdo->open();

                        // Verificar que el carrito no esté vacío
                        $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM cart WHERE user_id=:user_id");
                        $stmt->execute(['user_id' => $user['id']]);
                        $cart_check = $stmt->fetch();

                        if ($cart_check['numrows'] == 0) {
                            echo "<h4>Tu carrito está vacío. <a href='index.php'>Volver a la tienda</a></h4>";
                        } else {
                    ?>

                            <div class="row">
                                <div class="col-sm-7">
                                    <h1 class="page-header">Facturación y pago</h1>

                                    <form action="ventas.php" method="POST" id="form-facturacion">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="checkout_token" value="<?php echo issueCheckoutToken(); ?>">
                                        <div class="box box-solid">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Datos de facturación</h3>
                                            </div>
                                            <div class="box-body">

                                                <div class="form-group">
                                                    <label for="billing-name">Nombre completo</label>
                                                    <input type="text" name="nombre_facturacion" id="billing-name" autocomplete="name" class="form-control"
                                                        value="<?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>" maxlength="100" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="billing-document">Documento (cédula / NIT)</label>
                                                    <input type="text" name="documento" id="billing-document" class="form-control" placeholder="1098765432" maxlength="30" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="billing-address">Dirección de entrega</label>
                                                    <input type="text" name="direccion" id="billing-address" autocomplete="street-address" class="form-control"
                                                        value="<?php echo htmlspecialchars($user['address']); ?>"
                                                        placeholder="Calle 10 # 5-20" maxlength="200" required>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="billing-phone">Teléfono</label>
                                                            <input type="tel" name="telefono" id="billing-phone" autocomplete="tel" class="form-control" placeholder="3001234567" maxlength="20" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="billing-city">Ciudad</label>
                                                            <input type="text" name="ciudad" id="billing-city" autocomplete="address-level2" class="form-control" placeholder="Santa Marta" maxlength="100" required>
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

                                                <p class="text-muted">El pedido se registra para coordinar el pago. Este formulario no realiza cobros en línea.</p>
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
                                        <i class="fa fa-lock"></i> Confirmar pedido
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

    <script src="dist/js/billing-summary.js"></script>
    <script>
        $(function() {
            PGCVBillingSummary.init($);
        });
    </script>

</body>

</html>
