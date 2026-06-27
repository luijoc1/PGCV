<link rel="stylesheet" href="wasathpp.css">
<footer class="main-footer" style="background: #1a2e4a; padding: 36px 0 0; color: rgba(255,255,255,0.85); margin-top: 30px;">
  <div class="container">
    <div class="row">

      <!-- Info empresa -->
      <div class="col-sm-4" style="margin-bottom: 20px;">
        <h4 style="color: #fff; font-weight: bold; margin-bottom: 14px;">
          <i class="fa fa-shopping-bag" style="color: #3a8eff; margin-right: 8px;"></i>Almacén Los Almendros
        </h4>
        <p style="font-size: 13px; color: rgba(255,255,255,0.65); line-height: 1.7;">
          Repuestos de segunda mano, confiables y a bajo costo.
        </p>
        <p style="font-size: 13px; color: rgba(255,255,255,0.65); margin-bottom: 6px;">
          <i class="fa fa-map-marker" style="color: #3a8eff; margin-right: 6px;"></i>Calle Principal, Fundación, Magdalena
        </p>
        <p style="font-size: 13px; color: rgba(255,255,255,0.65); margin-bottom: 6px;">
          <i class="fa fa-phone" style="color: #3a8eff; margin-right: 6px;"></i>+57 3045800522
        </p>
        <p style="font-size: 13px; color: rgba(255,255,255,0.65); margin-bottom: 0;">
          <i class="fa fa-envelope" style="color: #3a8eff; margin-right: 6px;"></i>contacto@almendros.com
        </p>
      </div>

      <!-- Enlaces útiles -->
      <div class="col-sm-4" style="margin-bottom: 20px;">
        <h4 style="color: #fff; font-weight: bold; margin-bottom: 14px;">Enlaces útiles</h4>
        <ul style="list-style: none; padding-left: 0; margin: 0;">
          <li style="margin-bottom: 8px;">
            <a href="index.php" style="color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none;">
              <i class="fa fa-angle-right" style="color: #3a8eff; margin-right: 6px;"></i>Inicio
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="sobrenosotros.php" style="color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none;">
              <i class="fa fa-angle-right" style="color: #3a8eff; margin-right: 6px;"></i>Sobre Nosotros
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="contacto.php" style="color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none;">
              <i class="fa fa-angle-right" style="color: #3a8eff; margin-right: 6px;"></i>Contacto
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="category.php" style="color: rgba(255,255,255,0.65); font-size: 13px; text-decoration: none;">
              <i class="fa fa-angle-right" style="color: #3a8eff; margin-right: 6px;"></i>Productos
            </a>
          </li>
        </ul>
      </div>

      <!-- Redes sociales -->
      <div class="col-sm-4" style="margin-bottom: 20px;">
        <h4 style="color: #fff; font-weight: bold; margin-bottom: 14px;">Síguenos</h4>
        <div style="display: flex; gap: 10px; margin-bottom: 16px;">
          <a href="" style="width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 6px;
                            display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;">
            <i class="fa fa-facebook"></i>
          </a>
          <a href="" style="width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 6px;
                            display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;">
            <i class="fa fa-instagram"></i>
          </a>
          <a href="https://api.whatsapp.com/send?phone=+573045800522&text=Hola%21%20necesito%20m%C3%A1s%20informaci%C3%B3n%20sobre%20Los%20Productos."
             style="width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 6px;
                    display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;">
            <i class="fa fa-whatsapp"></i>
          </a>
        </div>
      </div>

    </div>
  </div>

  <!-- Barra inferior -->
  <div style="background: rgba(0,0,0,0.2); margin-top: 10px; padding: 12px 0; text-align: center;">
    <p style="color: rgba(255,255,255,0.45); font-size: 12px; margin: 0;">
      © <?php echo date('Y'); ?> Almacén Los Almendros — Todos los derechos reservados
    </p>
  </div>
</footer>

<!-- Facebook chat -->
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({ xfbml: true, version: 'v8.0' });
  };
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = 'https://connect.facebook.net/es_LA/sdk/xfbml.customerchat.js';
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
</script>
<div class="fb-customerchat"
  attribution=setup_tool
  page_id="112976387041659"
  theme_color="#0084ff"
  greeting_dialog_display="fade"
  logged_in_greeting="Hola Mucho Gusto ¿Cuál es su consulta?"
  logged_out_greeting="Hola Mucho Gusto ¿Cuál es su consulta?">
</div>

<!-- WhatsApp flotante -->
<a href="https://api.whatsapp.com/send?phone=+573045800522&text=Hola%21%20necesito%20m%C3%A1s%20informaci%C3%B3n%20sobre%20Los%20Productos."
   class="float" target="_blank">
  <i class="fa fa-whatsapp my-float"></i>
</a>