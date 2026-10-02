
<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">

<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	  <div class="content-wrapper">
	   <div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<h2>Envíanos un mensaje</h2>
<?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
  <?php if (isset($_SESSION[$key])): ?>
    <div class="alert alert-<?php echo $class; ?>"><?php echo escapeHtml($_SESSION[$key]); ?></div>
    <?php unset($_SESSION[$key]); ?>
  <?php endif; ?>
<?php endforeach; ?>
<form action="enviar.php" method="post">
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
  <div class="form-group">
  <label for="name">Nombre</label>
    <input type="text" name="name" class="form-control" id="name" autocomplete="name" placeholder="Tu nombre" maxlength="100" required>
  </div>
  <label for="mail">Correo electrónico</label>
  <div class="form-group">
  <input type="email" name="mail" class="form-control" id="mail" autocomplete="email" placeholder="Tu email" maxlength="200" required>
  </div>
  <label for="subject">Asunto</label>
  <div class="form-group">
  <input type="text" name="subject" class="form-control" id="subject" placeholder="Asunto del Mensaje" maxlength="150" required>
  </div>
  <div class="form-group">
    <label for="message">Mensaje</label>
    <textarea name="message" rows="3" id="message" placeholder="Escribe tu mensaje" maxlength="5000" required class="form-control"></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Enviar mensaje</button>
</form>


					</div>
					</div>
					</div>
					</div>
					<?php include 'includes/footer.php'; ?>
	        	</div>
				
				<!-- Me jala todos las categorias -->
					<?php include 'includes/scripts.php'; ?>
</body>
</html>
