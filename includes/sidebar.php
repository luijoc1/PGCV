<!-- Más vistos -->
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
    <div style="background: #1a2e4a; padding: 12px 16px;">
        <h4 style="color: #fff; margin: 0; font-size: 14px; font-weight: bold;">
            <i class="fa fa-fire" style="color: #3a8eff; margin-right: 6px;"></i>Los más vistos hoy
        </h4>
    </div>
    <div style="padding: 12px 16px;">
        <ul style="list-style: none; padding: 0; margin: 0;">
        <?php
            $now = date('Y-m-d');
            $conn = $pdo->open();
            $stmt = $conn->prepare("SELECT * FROM products WHERE date_view=:now AND stock > 0 ORDER BY counter DESC LIMIT 10");
            $stmt->execute(['now'=>$now]);
            foreach($stmt as $row){
                echo "
                    <li style='padding: 6px 0; border-bottom: 1px solid #f0f0f0;'>
                        <i class='fa fa-angle-right' style='color: #3a8eff; margin-right: 6px;'></i>
                        <a href='producto.php?product=".$row['slug']."'
                           style='font-size: 13px; color: #1a2e4a; text-decoration: none;'>
                            ".$row['name']."
                        </a>
                    </li>
                ";
            }
            $pdo->close();
        ?>
        </ul>
    </div>
</div>

<!-- Suscríbete -->
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
    <div style="background: #1a2e4a; padding: 12px 16px;">
        <h4 style="color: #fff; margin: 0; font-size: 14px; font-weight: bold;">
            <i class="fa fa-envelope" style="color: #3a8eff; margin-right: 6px;"></i>Suscríbete
        </h4>
    </div>
    <div style="padding: 14px 16px;">
        <p style="font-size: 13px; color: #666; margin-bottom: 12px; line-height: 1.6;">
            Recibe actualizaciones sobre los últimos productos y descuentos.
        </p>
        <form method="POST" action="">
            <div class="input-group">
                <input type="email" class="form-control" placeholder="correo@ejemplo.com"
                       style="border-color: #e0e0e0; font-size: 13px;">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-flat"
                            style="background: #1a2e4a; color: #fff; border-color: #1a2e4a;">
                        <i class="fa fa-envelope"></i>
                    </button>
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Redes sociales -->
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
    <div style="background: #1a2e4a; padding: 12px 16px;">
        <h4 style="color: #fff; margin: 0; font-size: 14px; font-weight: bold;">
            <i class="fa fa-share-alt" style="color: #3a8eff; margin-right: 6px;"></i>Síguenos
        </h4>
    </div>
    <div style="padding: 14px 16px;">
        <div style="display: flex; gap: 10px;">
            <a href="" style="width: 38px; height: 38px; background: #1a2e4a; border-radius: 6px;
                              display: flex; align-items: center; justify-content: center;
                              color: #fff; text-decoration: none; font-size: 16px;">
                <i class="fa fa-facebook"></i>
            </a>
            <a href="" style="width: 38px; height: 38px; background: #1a2e4a; border-radius: 6px;
                              display: flex; align-items: center; justify-content: center;
                              color: #fff; text-decoration: none; font-size: 16px;">
                <i class="fa fa-instagram"></i>
            </a>
            <a href="https://api.whatsapp.com/send?phone=+573045800522&text=Hola%21%20necesito%20m%C3%A1s%20informaci%C3%B3n%20sobre%20Los%20Productos."
               style="width: 38px; height: 38px; background: #25d366; border-radius: 6px;
                      display: flex; align-items: center; justify-content: center;
                      color: #fff; text-decoration: none; font-size: 16px;">
                <i class="fa fa-whatsapp"></i>
            </a>
        </div>
    </div>
</div>