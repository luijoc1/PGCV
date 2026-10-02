<?php include 'includes/session.php'; ?>
<?php
  require_once __DIR__ . '/includes/password_reset.php';
  $parameters = passwordResetParameters($_GET['code'] ?? null, $_GET['user'] ?? null);
  if ($parameters === null) {
    $_SESSION['error'] = 'Enlace de recuperación inválido.';
    header('location: password_olvidada.php');
    exit(); 
  }
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
<div class="login-box">
  	<?php
      if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8')."</p>
          </div>
        ";
        unset($_SESSION['error']);
      }
    ?>
  	<div class="login-box-body">
    	<p class="login-box-msg">Introduzca nueva contraseña</p>

        <form action="<?php echo htmlspecialchars('password_nueva.php?' . http_build_query($parameters), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
      		<div class="form-group has-feedback">
              <label for="new-password">Nueva contraseña</label>
        		<input type="password" class="form-control" name="password" id="new-password" autocomplete="new-password" minlength="6" placeholder="Mínimo 6 caracteres" required>
        		<span class="glyphicon glyphicon-lock form-control-feedback"></span>
      		</div>
          <div class="form-group has-feedback">
            <label for="new-repassword">Repetir contraseña</label>
            <input type="password" class="form-control" name="repassword" id="new-repassword" autocomplete="new-password" minlength="6" placeholder="Vuelva a escribir la contraseña" required>
            <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
          </div>
      		<div class="row">
    			<div class="col-xs-12">
          			<button type="submit" class="btn btn-primary btn-block btn-flat" name="reset"><i class="fa fa-check-square-o"></i> Guardar contraseña</button>
        		</div>
      		</div>
    	</form>
  	</div>
</div>
	
<?php include 'includes/scripts.php' ?>
</body>
</html>
