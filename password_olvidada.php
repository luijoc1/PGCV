<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
<div class="login-box">

  <?php
    if(isset($_SESSION['error'])){
      echo "<div class='callout callout-danger text-center'><p>".$_SESSION['error']."</p></div>";
      unset($_SESSION['error']);
    }
    if(isset($_SESSION['success'])){
      echo "<div class='callout callout-success text-center'><p>".$_SESSION['success']."</p></div>";
      unset($_SESSION['success']);
    }
  ?>

  <div style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.10);">

    <!-- HEADER -->
    <div style="background:#1a2e4a; padding:32px 24px; text-align:center;">
      <div style="width:56px; height:56px; background:rgba(58,142,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
        <i class="fa fa-lock" style="font-size:26px; color:#3a8eff;"></i>
      </div>
      <p style="color:#ffffff; font-size:18px; font-weight:bold; margin:0 0 4px;">¿Olvidaste tu contraseña?</p>
      <p style="color:rgba(255,255,255,0.55); font-size:13px; margin:0;">Almacén los Almendros</p>
    </div>

    <!-- FORMULARIO -->
    <div style="padding:28px 24px;">
      <p style="font-size:13px; color:#666; margin-bottom:20px; text-align:center; line-height:1.6;">
        Ingresa el correo electrónico asociado a tu cuenta y te enviaremos un enlace para restablecer tu contraseña.
      </p>

      <form action="restablecer.php" method="POST">
        <div class="form-group">
          <label style="font-size:13px; color:#666;">Correo electrónico</label>
          <div style="position:relative;">
            <i class="fa fa-envelope" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa;"></i>
            <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com"
                   style="padding-left:36px;" required>
          </div>
        </div>

        <button type="submit" name="reset" class="btn btn-block btn-flat"
                style="background:#1a2e4a; color:#fff; font-size:15px; padding:11px; border-radius:8px; margin-top:4px;">
          <i class="fa fa-paper-plane"></i> Enviar enlace
        </button>
      </form>

      <div style="margin-top:18px; display:flex; flex-direction:column; align-items:center; gap:8px;">
        <a href="login.php" style="font-size:13px; color:#3a8eff;">Recuerdo mi contraseña</a>
        <a href="index.php" style="font-size:13px; color:#888;">
          <i class="fa fa-home"></i> Volver al inicio
        </a>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/scripts.php' ?>
</body>
</html>