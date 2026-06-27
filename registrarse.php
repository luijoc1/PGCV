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

  <div style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.10);">

    <!-- HEADER -->
    <div style="background: #1a2e4a; padding: 24px; text-align: center;">
      <p style="color: #ffffff; font-size: 18px; font-weight: bold; margin: 0 0 4px;">Crear cuenta</p>
      <p style="color: rgba(255,255,255,0.55); font-size: 13px; margin: 0;">Almacén los Almendros</p>
    </div>

    <!-- FORMULARIO -->
    <div style="padding: 24px;">
      <form action="registro.php" method="POST">

        <div style="display: flex; gap: 12px;">
          <div class="form-group" style="flex: 1;">
            <label style="font-size: 13px; color: #666;">Nombres</label>
            <div style="position: relative;">
              <i class="fa fa-user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
              <input type="text" class="form-control" name="firstname" placeholder="Juan"
                     style="padding-left: 36px;"
                     value="<?php echo (isset($_SESSION['firstname'])) ? $_SESSION['firstname'] : '' ?>" required>
            </div>
          </div>
          <div class="form-group" style="flex: 1;">
            <label style="font-size: 13px; color: #666;">Apellidos</label>
            <div style="position: relative;">
              <i class="fa fa-user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
              <input type="text" class="form-control" name="lastname" placeholder="Pérez"
                     style="padding-left: 36px;"
                     value="<?php echo (isset($_SESSION['lastname'])) ? $_SESSION['lastname'] : '' ?>" required>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label style="font-size: 13px; color: #666;">Correo electrónico</label>
          <div style="position: relative;">
            <i class="fa fa-envelope" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
            <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com"
                   style="padding-left: 36px;"
                   value="<?php echo (isset($_SESSION['email'])) ? $_SESSION['email'] : '' ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label style="font-size: 13px; color: #666;">Contraseña</label>
          <div style="position: relative;">
            <i class="fa fa-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
            <input type="password" class="form-control" name="password" placeholder="••••••••"
                   style="padding-left: 36px;" required>
          </div>
        </div>

        <div class="form-group">
          <label style="font-size: 13px; color: #666;">Repetir contraseña</label>
          <div style="position: relative;">
            <i class="fa fa-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
            <input type="password" class="form-control" name="repassword" placeholder="••••••••"
                   style="padding-left: 36px;" required>
          </div>
        </div>

        <div class="form-group">
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="aceptar_datos" id="aceptar_datos" style="width: auto;" required>
            <label for="aceptar_datos" style="font-size: 13px; color: #666; margin: 0;">
              Acepto los términos y condiciones
            </label>
          </div>
        </div>

        <button type="submit" name="signup" id="btn-registrar" class="btn btn-block btn-flat"
                style="background: #1a2e4a; color: #fff; font-size: 15px; padding: 11px; border-radius: 8px;">
          <i class="fa fa-user-plus"></i> Crear cuenta
        </button>

      </form>

      <div style="margin-top: 16px; display: flex; justify-content: center; gap: 20px;">
        <a href="login.php" style="font-size: 13px; color: #3a8eff;">Ya tengo cuenta</a>
        <a href="index.php" style="font-size: 13px; color: #888;">
          <i class="fa fa-home"></i> Inicio
        </a>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/scripts.php' ?>
<script>
$(function(){
  $('form').submit(function(e){
    if(!$('#aceptar_datos').is(':checked')){
      e.preventDefault();
      alert('Debes aceptar los términos y condiciones para registrarte');
      return false;
    }
  });
});
</script>
</body>
</html>