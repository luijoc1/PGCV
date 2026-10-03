<header class="main-header">
  <nav class="navbar navbar-static-top" style="background: #1a2e4a; border-bottom: 3px solid #3a8eff;">
    <div class="container">
      <div class="navbar-header">
        <a href="index.php" class="navbar-brand" style="color: #fff; font-size: 18px; font-weight: bold;">
          <i class="fa fa-shopping-bag" style="color: #3a8eff; margin-right: 6px;"></i>Los Almendros
        </a>
        <button type="button" class="navbar-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#navbar-collapse" aria-controls="navbar-collapse" aria-expanded="false" aria-label="Abrir menú de navegación" style="border-color: #3a8eff;">
          <i class="fa fa-bars" style="color: #fff;"></i>
        </button>
      </div>

      <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
        <ul class="nav navbar-nav">
          <li><a href="index.php" style="color: rgba(255,255,255,0.85); font-size: 13px;">INICIO</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" style="color: rgba(255,255,255,0.85); font-size: 13px;">
              CATEGORÍAS <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" role="menu" style="background: #1a2e4a; border: 1px solid #3a8eff;">
              <?php
              $conn = $pdo->open();
              try {
                $stmt = $conn->prepare("SELECT * FROM category");
                $stmt->execute();
                foreach ($stmt as $row) {
                  echo "
                      <li><a class='dropdown-item' href='category.php?category=" . rawurlencode((string) $row['cat_slug']) . "'
                             style='color: rgba(255,255,255,0.85); font-size: 13px;'>" . escapeHtml($row['name']) . "</a></li>
                    ";
                }
              } catch (PDOException $e) {
                echo "Hay algún problema en la conexión.: " . $e->getMessage();
              }
              $pdo->close();
              ?>
            </ul>
          </li>
          <li><a href="sobrenosotros.php" style="color: rgba(255,255,255,0.85); font-size: 13px;">NOSOTROS</a></li>
          <li><a href="contacto.php" style="color: rgba(255,255,255,0.85); font-size: 13px;">CONTÁCTANOS</a></li>
          <li><a href="ofertas.php" style="color: #fff; background: #e74c3c; border-radius: 6px; margin: 8px 4px; padding: 6px 14px; font-size: 13px; display: inline-block;">
              <i class="fa fa-tag"></i> OFERTAS
            </a></li>
        </ul>
        <form method="POST" class="navbar-form navbar-left" action="buscar.php">
          <div class="input-group navbar-search-group">
            <input type="text" class="form-control" id="navbar-search-input" name="keyword"
              placeholder="Buscar producto" aria-label="Buscar producto" required
              style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
            <span class="input-group-btn" id="searchBtn">
              <button type="submit" class="btn btn-flat" aria-label="Buscar" style="background: #3a8eff; color: #fff;">
                <i class="fa fa-search"></i>
              </button>
            </span>
          </div>
        </form>
      </div>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" style="color: #fff;">
              <i class="fa fa-shopping-cart" style="font-size: 18px;"></i>
              <span class="label label-success cart_count"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Tienes <span class="cart_count"></span> artículo(s) en el carrito</li>
              <li>
                <ul class="menu" id="cart_menu"></ul>
              </li>
              <li class="footer"><a class="dropdown-item" href="cart_ver.php">Ir al carrito</a></li>
            </ul>
          </li>
          <?php
          if (isset($_SESSION['user'])) {
            $image = safeImageUrl($user['photo'], 'images/', 'profile.jpg');
            echo '
                <li class="dropdown user user-menu">
                  <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="' . escapeHtml($image) . '" class="user-image" alt="User Image">
                    <span class="hidden-xs" style="color:#fff;">' . escapeHtml($user['firstname']) . ' ' . escapeHtml($user['lastname']) . '</span>
                  </a>
                  <ul class="dropdown-menu">
                    <li class="user-header" style="background: #1a2e4a;">
                      <img src="' . escapeHtml($image) . '" class="img-circle" alt="User Image">
                      <p style="color:#fff;">
                        ' . escapeHtml($user['firstname']) . ' ' . escapeHtml($user['lastname']) . '
                        <small>Miembro desde ' . date('M. Y', strtotime($user['created_on'])) . '</small>
                      </p>
                    </li>
                    <li class="user-footer">
                      <div class="pull-left">
                        <a href="perfil.php" class="btn btn-default btn-flat">Perfil</a>
                      </div>
                      <div class="pull-right">
                        <form method="POST" action="cerrar_sesion.php" style="display:inline;">
                          <input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8') . '">
                          <button type="submit" class="btn btn-default btn-flat">Cerrar sesión</button>
                        </form>
                      </div>
                    </li>
                  </ul>
                </li>
              ';
          } else {
            echo "
                <li><a href='login.php' style='color: rgba(255,255,255,0.85); font-size: 13px;'>INICIAR SESIÓN</a></li>
                <li>
                  <a href='registrarse.php'
                     style='color: #fff; background: #3a8eff; border-radius: 6px; margin: 8px 4px; padding: 6px 14px; font-size: 13px; display: inline-block;'>
                    REGÍSTRATE
                  </a>
                </li>
              ";
          }
          ?>
        </ul>
      </div>
    </div>
  </nav>
</header>
