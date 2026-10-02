<header class="main-header">
  <!-- Logo -->
  <a href="#" class="logo bg-green">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b>TC</b></span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b>Los Almendros</b></span>
  </a>
  <!-- Barra de navegación de encabezado: el estilo se puede encontrar en header.less -->
  <nav class="navbar navbar-static-top bg-green">
    <!-- Botón de alternancia de la barra lateral-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Navegación de palanca</span>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- Cuenta de usuario: el estilo se puede encontrar en el menú desplegable. -->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?php echo safeImageUrl($admin['photo'], '../images/', 'profile.jpg'); ?>" class="user-image" alt="User Image">
            <span class="hidden-xs"><?php echo escapeHtml($admin['firstname']).' '.escapeHtml($admin['lastname']); ?></span>
          </a>
          <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
              <img src="<?php echo safeImageUrl($admin['photo'], '../images/', 'profile.jpg'); ?>" class="img-circle" alt="User Image">

              <p>
                <?php echo escapeHtml($admin['firstname']).' '.escapeHtml($admin['lastname']); ?>
                <small>Miembro desde <?php echo date('M. Y', strtotime($admin['created_on'])); ?></small>
              </p>
            </li>
            <li class="user-footer">
              <div class="pull-left">
                <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" id="admin_profile">Perfil</a>
              </div>
              <div class="pull-right">
                <form method="POST" action="../cerrar_sesion.php" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
                  <button type="submit" class="btn btn-default btn-flat">Cerrar Sesión</button>
                </form>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
<?php include 'includes/profile_modal.php'; ?>
