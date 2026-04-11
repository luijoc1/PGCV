<?php include 'includes/session.php'; ?>
<?php
  if(isset($_SESSION['user'])){
    header('location: cart_ver.php');
  }

  if(isset($_SESSION['captcha'])){
    $now = time();
    if($now >= $_SESSION['captcha']){
      unset($_SESSION['captcha']);
    }
  }

?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition register-page">
<div class="register-box">
  	<?php
      if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        ";
        unset($_SESSION['error']);
      }

      if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        ";
        unset($_SESSION['success']);
      }
    ?>
  	<div class="register-box-body">
    	<p class="login-box-msg">Registrar una nueva cuenta</p>

    	<form action="registro.php" method="POST">
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="firstname" placeholder="Nombres" value="<?php echo (isset($_SESSION['firstname'])) ? $_SESSION['firstname'] : '' ?>" required>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="lastname" placeholder="Apellidos" value="<?php echo (isset($_SESSION['lastname'])) ? $_SESSION['lastname'] : '' ?>"  required>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
      		<div class="form-group has-feedback">
        		<input type="email" class="form-control" name="email" placeholder="Correo electrónico" value="<?php echo (isset($_SESSION['email'])) ? $_SESSION['email'] : '' ?>" required>
        		<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      		</div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="repassword" placeholder="Vuelva a escribir la contraseña" required>
            <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
          </div>
          <?php
          /*
            if(!isset($_SESSION['captcha'])){
              echo '
                <di class="form-group" style="width:100%;">
                  <div class="g-recaptcha" data-sitekey="6LevO1IUAAAAAFX5PpmtEoCxwae-I8cCQrbhTfM6"></div>
                </di>
              ';
            } 
            */
          ?>
          <div class="form-group">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="aceptar_datos" id="aceptar_datos" required>
                Aceptar terminos y condiciones
              </label>
            </div>
          </div>
          <hr>
      		<div class="row">
    			<div class="col-xs-5">
          			<button type="submit" class="btn btn-primary btn-block btn-flat" name="signup" id="btn-registrar"><i class="fa fa-pencil"></i> Regístrate</button>
        		</div>
      		</div>
    	</form>
      <br>
      <a href="login.php">Ya tengo cuenta</a><br>
      <a href="index.php"><i class="fa fa-home"></i> Casa</a>
  	</div>
</div>
	
<?php include 'includes/scripts.php' ?>
<script>
$(function(){
	// Validar que el checkbox esté marcado antes de enviar
	$('form').submit(function(e){
		if(!$('#aceptar_datos').is(':checked')){
			e.preventDefault();
			alert('Debes aceptar los terminos y condiciones para registrarte');
			return false;
		}
	});
});
</script>
</body>
</html>