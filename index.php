<!DOCTYPE html>
<html>

<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Contenido principal -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<?php
	        			if(isset($_SESSION['error'])){
	        				echo "
	        					<div class='alert alert-danger'>
	        						".$_SESSION['error']."
	        					</div>
	        				";
	        				unset($_SESSION['error']);
	        			}
	        		?>
	        		<div id="carousel-example-generic" class="carousel slide" data-ride="carousel"
     style="border-radius: 12px; overflow: hidden; margin-bottom: 24px;">

    <!-- Indicadores -->
    <ol class="carousel-indicators" style="margin-bottom: 12px;">
        <li data-target="#carousel-example-generic" data-slide-to="0" class="active"
            style="width: 24px; height: 4px; border-radius: 2px; background: #3a8eff; border: none;"></li>
        <li data-target="#carousel-example-generic" data-slide-to="1"
            style="width: 8px; height: 4px; border-radius: 2px; border: none;"></li>
        <li data-target="#carousel-example-generic" data-slide-to="2"
            style="width: 8px; height: 4px; border-radius: 2px; border: none;"></li>
        <li data-target="#carousel-example-generic" data-slide-to="3"
            style="width: 8px; height: 4px; border-radius: 2px; border: none;"></li>
    </ol>

    <div class="carousel-inner">

        <div class="item active">
            <img src="images/filtross.jpg" alt="Slide 1" style="width:100%; height:320px; object-fit:cover;">
            <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
            <div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
                <span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">DESTACADO</span>
                <h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; line-height:1.3; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
                <p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0 0 18px; max-width:280px;">Encuentra los mejores repuestos para tu vehículo en Almacén los Almendros.</p>
                
            </div>
        </div>

        <div class="item">
            <img src="images/motores.jpg" alt="Slide 2" style="width:100%; height:320px; object-fit:cover;">
            <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
            <div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
                <span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">DESTACADO</span>
                <h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; line-height:1.3; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
                <p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0 0 18px; max-width:280px;">Encuentra los mejores repuestos para tu vehículo en Almacén los Almendros.</p>
                
            </div>
        </div>

        <div class="item">
            <img src="images/v.jpg" alt="Slide 3" style="width:100%; height:320px; object-fit:cover;">
            <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
            <div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
                <span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">DESTACADO</span>
                <h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; line-height:1.3; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
                <p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0 0 18px; max-width:280px;">Encuentra los mejores repuestos para tu vehículo en Almacén los Almendros.</p>
                
            </div>
        </div>

        <div class="item">
            <img src="images/d.jpg" alt="Slide 4" style="width:100%; height:320px; object-fit:cover;">
            <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(26,46,74,0.90) 35%, rgba(26,46,74,0.15) 100%);"></div>
            <div style="position:absolute; left:36px; top:50%; transform:translateY(-50%);">
                <span style="background:#3a8eff; color:#fff; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:20px; letter-spacing:1px;">DESTACADO</span>
                <h2 style="color:#fff; font-size:24px; font-weight:bold; margin:10px 0 6px; line-height:1.3; max-width:320px;">Repuestos de calidad<br>al mejor precio</h2>
                <p style="color:rgba(255,255,255,0.70); font-size:13px; margin:0 0 18px; max-width:280px;">Encuentra los mejores repuestos para tu vehículo en Almacén los Almendros.</p>
                
            </div>
        </div>

    </div>

    <!-- Flechas -->
    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev"
       style="background:none; width:40px;">
        <div style="width:36px; height:36px; background:rgba(255,255,255,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-18px; position:absolute; top:50%; left:8px;">
            <span class="fa fa-angle-left" style="font-size:20px; color:#fff;"></span>
        </div>
    </a>
    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next"
       style="background:none; width:40px;">
        <div style="width:36px; height:36px; background:rgba(255,255,255,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-18px; position:absolute; top:50%; right:8px;">
            <span class="fa fa-angle-right" style="font-size:20px; color:#fff;"></span>
        </div>
    </a>

</div>
		            <!-- TÍTULO -->
<div style="margin: 24px 0 16px; display: flex; align-items: center; gap: 12px;">
    <div style="width: 4px; height: 28px; background: #1a2e4a; border-radius: 2px;"></div>
    <div>
        <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: #1a2e4a;">Productos más recomendados</h3>
        <p style="margin: 0; font-size: 13px; color: #888;">Los más vendidos este mes</p>
    </div>
</div>

<!-- PRODUCTOS -->
<?php
    $month = date('m');
    $conn = $pdo->open();

    try{
        $inc = 3;
        $stmt = $conn->prepare("SELECT *, SUM(quantity) AS total_qty FROM details LEFT JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id WHERE MONTH(sales_date) = '$month' AND products.stock > 0 GROUP BY details.product_id ORDER BY total_qty DESC LIMIT 6");
        $stmt->execute();
        foreach ($stmt as $row) {
            $image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/noimage.jpg';
            $inc = ($inc == 3) ? 1 : $inc + 1;
            if($inc == 1) echo "<div class='row'>";
            echo "
                <div class='col-sm-4' style='margin-bottom: 16px;'>
                    <div style='background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden;'>
                        <div style='width: 100%; height: 180px; overflow: hidden; background: #f5f5f5;'>
                            <img src='".$image."' style='width: 100%; height: 100%; object-fit: cover;'>
                        </div>
                        <div style='padding: 12px 14px;'>
                            <p style='font-size: 13px; color: #666; margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'
                               title='".$row['name']."'>
                                ".$row['name']."
                            </p>
                            <p style='font-size: 16px; font-weight: bold; color: #1a2e4a; margin: 0 0 12px;'>
                                &#36; ".number_format($row['price'], 2)."
                            </p>
                            <a href='producto.php?product=".$row['slug']."'
                               style='display: block; text-align: center; background: #1a2e4a; color: #fff;
                                      text-decoration: none; padding: 8px; border-radius: 6px; font-size: 13px;'>
                                <i class='fa fa-eye'></i> Ver producto
                            </a>
                        </div>
                    </div>
                </div>
            ";
            if($inc == 3) echo "</div>";
        }
        if($inc == 1) echo "<div class='col-sm-4'></div><div class='col-sm-4'></div></div>";
        if($inc == 2) echo "<div class='col-sm-4'></div></div>";
    }
    catch(PDOException $e){
        echo "Hay algún problema en la conexión: " . $e->getMessage();
    }

    $pdo->close();
?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
			
	      </section>
	     
	    </div>
	  </div>
  
  	<?php include 'includes/footer.php'; ?>
</div>

<style>
.carousel .item {
    height: 350px;
    overflow: hidden;
}
.carousel .item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
<?php include 'includes/scripts.php'; ?>

</body>
</html>