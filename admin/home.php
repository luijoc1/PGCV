<?php 
  include 'includes/session.php';
  include 'includes/format.php';
  include '../includes/config.php'; 
?>
<?php 
  $today = date('Y-m-d');
  $year = date('Y');
  if(isset($_GET['year'])){
    $year = $_GET['year'];
  }

  $conn = $pdo->open();
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Envoltorio de contenido. Contiene contenido de la página -->
  <div class="content-wrapper">
    <!-- Encabezado de contenido (encabezado de página) -->
    <section class="content-header">
      <h1>
        Escritorio
      </h1>
      <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-dashboard"></i> Casa</a></li>
        <li class="active">Tablero</li>
      </ol>
    </section>

    <!-- Contenido principal -->
    <section class="content">
      <?php
// Verificar stock crítico
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE stock > 0 AND stock <= stock_minimo");
$stmt->execute();
$stock_critico = $stmt->fetch();

$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE stock = 0");
$stmt2->execute();
$sin_stock = $stmt2->fetch();

if($stock_critico['total'] > 0 || $sin_stock['total'] > 0){
    echo "
        <div class='alert alert-warning alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4><i class='fa fa-exclamation-triangle'></i> Alerta de inventario</h4>
            <p>
                ".($sin_stock['total'] > 0 ? "<strong style='color:#e74c3c;'>".$sin_stock['total']." producto(s) sin stock.</strong> " : "")."
                ".($stock_critico['total'] > 0 ? "<strong style='color:#e67e22;'>".$stock_critico['total']." producto(s) con stock crítico.</strong>" : "")."
                <a href='inventario.php' class='btn btn-sm btn-flat btn-warning' style='margin-left:10px;'>
                    <i class='fa fa-cubes'></i> Ver inventario
                </a>
            </p>
        </div>
    ";

    // Enviar correo de alerta solo una vez por día
    if(!isset($_SESSION['alerta_stock_enviada']) || $_SESSION['alerta_stock_enviada'] != date('Y-m-d')){
        $_SESSION['alerta_stock_enviada'] = date('Y-m-d');

        // Obtener productos críticos para el correo
        $stmt3 = $conn->prepare("SELECT name, stock, stock_minimo FROM products WHERE stock <= stock_minimo ORDER BY stock ASC LIMIT 10");
        $stmt3->execute();
        $productos_criticos = $stmt3->fetchAll();

        $lista_productos = '';
        foreach($productos_criticos as $pc){
            $color = ($pc['stock'] == 0) ? '#e74c3c' : '#e67e22';
            $estado = ($pc['stock'] == 0) ? 'Sin stock' : 'Stock crítico';
            $lista_productos .= "
                <tr>
                    <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0;'>".$pc['name']."</td>
                    <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>".$pc['stock']."</td>
                    <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>".$pc['stock_minimo']."</td>
                    <td style='padding:8px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>
                        <span style='background:".$color."; color:#fff; padding:2px 8px; border-radius:20px; font-size:11px;'>".$estado."</span>
                    </td>
                </tr>
            ";
        }

        $correo_alerta = '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e0e0e0;">
        <tr>
          <td style="background:#1a2e4a; padding:28px 36px; text-align:center;">
            <span style="font-size:18px; font-weight:bold; color:#ffffff;">Almacén los Almendros</span><br><br>
            <div style="font-size:32px;">⚠️</div>
            <h1 style="color:#ffffff; font-size:20px; margin:8px 0 4px;">Alerta de Inventario</h1>
            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0;">'.date('d/m/Y').'</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px;">
            <p style="color:#444; font-size:15px; margin:0 0 20px;">
              Se han detectado <strong>'.$stock_critico['total'].' producto(s) con stock crítico</strong> y 
              <strong>'.$sin_stock['total'].' producto(s) sin stock</strong>.
            </p>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:8px; overflow:hidden; margin-bottom:20px;">
              <tr style="background:#1a2e4a;">
                <th style="padding:10px 12px; color:#fff; text-align:left; font-size:13px;">Producto</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Stock</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Mínimo</th>
                <th style="padding:10px 12px; color:#fff; text-align:center; font-size:13px;">Estado</th>
              </tr>
              '.$lista_productos.'
            </table>
            <div style="text-align:center;">
              <a href="http://localhost/PGCV/admin/inventario.php"
                 style="display:inline-block; background:#1a2e4a; color:#ffffff; text-decoration:none;
                        padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold;">
                Ver reporte de inventario
              </a>
            </div>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e0e0e0; padding:16px 36px; text-align:center;">
            <p style="font-size:12px; color:#bbb; margin:0;">© 2026 Almacén los Almendros — Correo automático</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

        // Enviar correo
        require_once '../vendor/autoload.php';
        use PHPMailer\PHPMailer\PHPMailer;
        $mail = new PHPMailer(true);
        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USER;
            $mail->Password = MAIL_PASS;
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->setFrom(MAIL_USER);
            $mail->addAddress(MAIL_USER);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '⚠️ Alerta de inventario - '.date('d/m/Y');
            $mail->Body = $correo_alerta;
            $mail->send();
        } catch(Exception $e){
            // Si falla el correo no interrumpimos
        }
    }
}
?>
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <!-- Cajas pequeñas (caja de estadísticas)-->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- caja pequeña -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id");
                $stmt->execute();

                $total = 0;
                foreach($stmt as $srow){
                  $subtotal = $srow['price']*$srow['quantity'];
                  $total += $subtotal;
                }

                echo "<h3>&#36; ".number_format_short($total, 2)."</h3>";
              ?>
              <p>Ventas totales</p>
            </div>
            <div class="icon">
              <i class="fa fa-shopping-cart"></i>
            </div>
            <a href="#" class="small-box-footer">Mas informaciòn <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM products");
                $stmt->execute();
                $prow =  $stmt->fetch();

                echo "<h3>".$prow['numrows']."</h3>";
              ?>
          
              <p>Número de productos</p>
            </div>
            <div class="icon">
              <i class="fa fa-barcode"></i>
            </div>
            <a href="products.php" class="small-box-footer">Mas informaciòn <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users");
                $stmt->execute();
                $urow =  $stmt->fetch();

                echo "<h3>".$urow['numrows']."</h3>";
              ?>
             
              <p>Número de usuarios</p>
            </div>
            <div class="icon">
              <i class="fa fa-users"></i>
            </div>
            <a href="users.php" class="small-box-footer">Mas informaciòn <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT * FROM details LEFT JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id WHERE sales_date=:sales_date");
                $stmt->execute(['sales_date'=>$today]);

                $total = 0;
                foreach($stmt as $trow){
                  $subtotal = $trow['price']*$trow['quantity'];
                  $total += $subtotal;
                }

                echo "<h3>&#36; ".number_format_short($total, 2)."</h3>";
                
              ?>

              <p>Ventas hoy</p>
            </div>
            <div class="icon">
              <i class="fa fa-money"></i>
            </div>
            <a href="sales.php" class="small-box-footer">Mas informaciòn <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Informe mensual de ventas</h3>
              <div class="box-tools pull-right">
                <form class="form-inline">
                  <div class="form-group">
                    <label>Seleccione el año: </label>
                    <select class="form-control input-sm" id="select_year">
                      <?php
                        for($i=2015; $i<=2065; $i++){
                          $selected = ($i==$year)?'selected':'';
                          echo "
                            <option value='".$i."' ".$selected.">".$i."</option>
                          ";
                        }
                      ?>
                    </select>
                  </div>
                </form>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <br>
                <div id="legend" class="text-center"></div>
                <canvas id="barChart" style="height:350px"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      </section>
      <!-- columna derecha -->
    </div>
  	<?php include 'includes/footer.php'; ?>

</div>
<!-- ./envoltura -->

<!-- Datos del gráfico
-->
<?php
  $months = array();
  $sales = array();
  for( $m = 1; $m <= 12; $m++ ) {
    try{
      $stmt = $conn->prepare("SELECT * FROM details LEFT JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id WHERE MONTH(sales_date)=:month AND YEAR(sales_date)=:year");
      $stmt->execute(['month'=>$m, 'year'=>$year]);
      $total = 0;
      foreach($stmt as $srow){
        $subtotal = $srow['price']*$srow['quantity'];
        $total += $subtotal;    
      }
      array_push($sales, round($total, 2));
    }
    catch(PDOException $e){
      echo $e->getMessage();
    }

    $num = str_pad( $m, 2, 0, STR_PAD_LEFT );
    $month =  date('M', mktime(0, 0, 0, $m, 1));
    array_push($months, $month);
  }

  $months = json_encode($months);
  $sales = json_encode($sales);

?>
<!-- Datos de gráfico final -->

<?php $pdo->close(); ?>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  var barChartCanvas = $('#barChart').get(0).getContext('2d')
  var barChart = new Chart(barChartCanvas)
  var barChartData = {
    labels  : <?php echo $months; ?>,
    datasets: [
      {
        label               : 'VENTAS',
        fillColor           : 'rgba(60,141,188,0.9)',
        strokeColor         : 'rgba(60,141,188,0.8)',
        pointColor          : '#3b8bba',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : <?php echo $sales; ?>
      }
    ]
  }
  //barChartData.datasets[1].fillColor   = '#00a65a'
  //barChartData.datasets[1].strokeColor = '#00a65a'
  //barChartData.datasets[1].pointColor  = '#00a65a'
  var barChartOptions                  = {
    //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
    scaleBeginAtZero        : true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines      : true,
    //String - Colour of the grid lines
    scaleGridLineColor      : 'rgba(0,0,0,.05)',
    //Number - Width of the grid lines
    scaleGridLineWidth      : 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines  : true,
    //Boolean - If there is a stroke on each bar
    barShowStroke           : true,
    //Number - Pixel width of the bar stroke
    barStrokeWidth          : 2,
    //Number - Spacing between each of the X value sets
    barValueSpacing         : 5,
    //Number - Spacing between data sets within X values
    barDatasetSpacing       : 1,
    //String - A legend template
    legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
    //Boolean - whether to make the chart responsive
    responsive              : true,
    maintainAspectRatio     : true
  }

  barChartOptions.datasetFill = false
  var myChart = barChart.Bar(barChartData, barChartOptions)
  document.getElementById('legend').innerHTML = myChart.generateLegend();
});
</script>
<script>
$(function(){
  $('#select_year').change(function(){
    window.location.href = 'home.php?year='+$(this).val();
  });
});
</script>
</body>
</html>
