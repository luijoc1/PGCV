<?php
include 'includes/session.php';
include 'includes/format.php';
include '../includes/config.php';
?>
<?php
date_default_timezone_set('America/Bogota');
$today = date('Y-m-d');
$year = (int) date('Y');
if (isset($_GET['year'])) {
  $year = (int) $_GET['year'];
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

        if ($stock_critico['total'] > 0 || $sin_stock['total'] > 0) {
          echo "
        <div class='alert alert-warning alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4><i class='fa fa-exclamation-triangle'></i> Alerta de inventario</h4>
            <p>
                " . ($sin_stock['total'] > 0 ? "<strong style='color:#e74c3c;'>" . $sin_stock['total'] . " producto(s) sin stock.</strong> " : "") . "
                " . ($stock_critico['total'] > 0 ? "<strong style='color:#e67e22;'>" . $stock_critico['total'] . " producto(s) con stock crítico.</strong>" : "") . "
                <a href='inventario.php' class='btn btn-sm btn-flat btn-warning' style='margin-left:10px;'>
                    <i class='fa fa-cubes'></i> Ver inventario
                </a>
            </p>
        </div>
    ";

        }
        ?>
        <?php
        if (isset($_SESSION['error'])) {
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              " . $_SESSION['error'] . "
            </div>
          ";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              " . $_SESSION['success'] . "
            </div>
          ";
          unset($_SESSION['success']);
        }
        ?>
        <?php
        // ───────── Datos del panel (una consulta por indicador) ─────────
        function dx_e($s)
        {
          return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // Totales guardados en la compra; no dependen de los precios actuales.
        $sql_total = "COALESCE(SUM(sales.total), 0)";
        $sql_base  = "FROM sales";

        $ayer = date('Y-m-d', strtotime('-1 day'));
        $mes  = (int) date('n');

        // Ventas de hoy y de ayer
        $stmt = $conn->prepare("SELECT $sql_total AS t $sql_base WHERE DATE(sales.sales_date) = :d");
        $stmt->execute(['d' => $today]);
        $ventas_hoy = (float) $stmt->fetch()['t'];
        $stmt->execute(['d' => $ayer]);
        $ventas_ayer = (float) $stmt->fetch()['t'];

        if ($ventas_ayer > 0) {
          $pct = round(($ventas_hoy - $ventas_ayer) / $ventas_ayer * 100);
          $cmp_texto = ($pct >= 0 ? '+' : '') . $pct . '% vs ayer';
          $cmp_clase = ($pct >= 0) ? 'dx-ok' : 'dx-bad';
        } else {
          $cmp_texto = 'Sin ventas ayer';
          $cmp_clase = 'dx-muted';
        }

        // Pedidos pendientes y cuántos llevan más de 24 h
        $stmt = $conn->prepare("SELECT COUNT(*) AS n, SUM(sales_date < :limite) AS viejos FROM sales WHERE estado = 'pendiente'");
        $stmt->execute(['limite' => date('Y-m-d H:i:s', strtotime('-24 hours'))]);
        $pend = $stmt->fetch();
        $pendientes = (int) $pend['n'];
        $pend_viejos = (int) $pend['viejos'];

        // Stock en riesgo (usa los conteos calculados arriba)
        $en_riesgo = (int) $stock_critico['total'] + (int) $sin_stock['total'];

        // Ticket promedio del mes
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT sales.id) AS n, $sql_total AS t $sql_base WHERE YEAR(sales.sales_date) = :y AND MONTH(sales.sales_date) = :m");
        $stmt->execute(['y' => (int) date('Y'), 'm' => $mes]);
        $tk = $stmt->fetch();
        $ticket = ((int) $tk['n'] > 0) ? ((float) $tk['t'] / (int) $tk['n']) : 0;

        // Ventas por mes del año elegido (una sola consulta)
        $stmt = $conn->prepare("SELECT MONTH(sales.sales_date) AS m, $sql_total AS t $sql_base WHERE YEAR(sales.sales_date) = :y GROUP BY MONTH(sales.sales_date)");
        $stmt->execute(['y' => $year]);
        $por_mes = array_fill(1, 12, 0);
        foreach ($stmt as $r) {
          $por_mes[(int) $r['m']] = round((float) $r['t'], 2);
        }
        $total_anio = array_sum($por_mes);
        $nombres_meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $months = json_encode($nombres_meses);
        $sales  = json_encode(array_values($por_mes));

        // Pedidos por estado
        $estados = [
          'pendiente'  => ['Pendiente',  0, '#f39c12'],
          'en_proceso' => ['En proceso', 0, '#3c8dbc'],
          'enviado'    => ['Enviado',    0, '#00c0ef'],
          'entregado'  => ['Entregado',  0, '#00a65a'],
        ];
        $stmt = $conn->prepare("SELECT estado, COUNT(*) AS n FROM sales GROUP BY estado");
        $stmt->execute();
        foreach ($stmt as $r) {
          if (isset($estados[$r['estado']])) {
            $estados[$r['estado']][1] = (int) $r['n'];
          }
        }
        $max_estado = 1;
        foreach ($estados as $e) {
          $max_estado = max($max_estado, $e[1]);
        }

        // Más vendidos del mes
        $stmt = $conn->prepare("SELECT products.name, SUM(details.quantity) AS q
                                FROM details JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id
                                WHERE YEAR(sales.sales_date) = :y AND MONTH(sales.sales_date) = :m AND products.id IS NOT NULL
                                GROUP BY products.id, products.name
                                ORDER BY q DESC LIMIT 5");
        $stmt->execute(['y' => (int) date('Y'), 'm' => $mes]);
        $mas_vendidos = $stmt->fetchAll();

        // Productos con stock crítico
        $stmt = $conn->prepare("SELECT name, stock, stock_minimo FROM products WHERE stock <= stock_minimo ORDER BY stock ASC, name ASC LIMIT 5");
        $stmt->execute();
        $lista_critica = $stmt->fetchAll();
        ?>

        <style>
          .dx-card {
            background: #fff;
            border: 1px solid #e3e6ea;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 20px;
          }

          .dx-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            margin: 0 0 12px;
          }

          .dx-metric {
            background: #fff;
            border: 1px solid #e3e6ea;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: block;
            color: inherit;
          }

          .dx-metric:hover {
            border-color: #c5cad1;
            color: inherit;
            text-decoration: none;
          }

          .dx-metric small {
            display: block;
            font-size: 13px;
            color: #6b7280;
          }

          .dx-metric b {
            display: block;
            font-size: 28px;
            font-weight: 600;
            line-height: 1.3;
          }

          .dx-metric span {
            font-size: 12px;
          }

          .dx-ok {
            color: #00a65a;
          }

          .dx-bad {
            color: #dd4b39;
          }

          .dx-warn {
            color: #d68a00;
          }

          .dx-muted {
            color: #6b7280;
          }

          .dx-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            margin: 10px 0;
          }

          .dx-row .dx-name {
            width: 84px;
          }

          .dx-row .dx-bar {
            flex: 1;
            height: 10px;
            background: #f1f3f5;
            border-radius: 5px;
            overflow: hidden;
          }

          .dx-row .dx-bar i {
            display: block;
            height: 100%;
            border-radius: 5px;
          }

          .dx-row .dx-n {
            width: 28px;
            text-align: right;
            font-weight: 600;
          }

          .dx-list {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            padding: 8px 0;
            border-top: 1px solid #eef0f2;
          }

          .dx-list:first-of-type {
            border-top: none;
          }

          .dx-list .dx-t {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

          .dx-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 6px;
          }
        </style>

        <!-- Indicadores -->
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <a href="sales.php" class="dx-metric">
              <small>Ventas hoy</small>
              <b>&#36;<?php echo number_format_short($ventas_hoy, 2); ?></b>
              <span class="<?php echo $cmp_clase; ?>"><?php echo $cmp_texto; ?></span>
            </a>
          </div>
          <div class="col-lg-3 col-xs-6">
            <a href="sales.php" class="dx-metric">
              <small>Pedidos por atender</small>
              <b><?php echo $pendientes; ?></b>
              <span class="<?php echo $pend_viejos > 0 ? 'dx-warn' : 'dx-muted'; ?>">
                <?php echo $pend_viejos > 0 ? $pend_viejos . ' llevan más de 24 h' : 'Ninguno con más de 24 h'; ?>
              </span>
            </a>
          </div>
          <div class="col-lg-3 col-xs-6">
            <a href="inventario.php" class="dx-metric">
              <small>Stock crítico</small>
              <b><?php echo $en_riesgo; ?></b>
              <span class="<?php echo $sin_stock['total'] > 0 ? 'dx-bad' : 'dx-muted'; ?>">
                <?php echo $sin_stock['total'] > 0 ? $sin_stock['total'] . ' sin stock' : 'Ninguno sin stock'; ?>
              </span>
            </a>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="dx-metric">
              <small>Ticket promedio</small>
              <b>&#36;<?php echo number_format_short($ticket, 2); ?></b>
              <span class="dx-muted">este mes</span>
            </div>
          </div>
        </div>

        <!-- Gráfico y pedidos por estado -->
        <div class="row">
          <div class="col-md-8">
            <div class="dx-card">
              <div class="dx-head">
                <h3 style="margin:0">Ventas por mes <span class="dx-muted" style="font-weight:400">· total <?php echo (int) $year; ?>: &#36;<?php echo number_format_short($total_anio, 2); ?></span></h3>
                <form class="form-inline">
                  <label style="font-weight:400; font-size:13px">Año:</label>
                  <select class="form-control input-sm" id="select_year">
                    <?php
                    for ($i = 2020; $i <= (int) date('Y') + 1; $i++) {
                      $selected = ($i == $year) ? 'selected' : '';
                      echo "<option value='" . $i . "' " . $selected . ">" . $i . "</option>";
                    }
                    ?>
                  </select>
                </form>
              </div>
              <div class="chart" style="position:relative;height:300px">
                <canvas id="barChart" role="img" aria-label="Ventas mensuales de <?php echo (int) $year; ?>"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="dx-card">
              <h3>Pedidos por estado</h3>
              <?php foreach ($estados as $e) : ?>
                <div class="dx-row">
                  <span class="dx-name"><?php echo $e[0]; ?></span>
                  <span class="dx-bar"><i style="width:<?php echo round($e[1] / $max_estado * 100); ?>%; background:<?php echo $e[2]; ?>"></i></span>
                  <span class="dx-n"><?php echo $e[1]; ?></span>
                </div>
              <?php endforeach; ?>
              <a href="sales.php" style="font-size:13px">Ver todas las ventas</a>
            </div>
          </div>
        </div>

        <!-- Más vendidos y stock crítico -->
        <div class="row">
          <div class="col-md-6">
            <div class="dx-card">
              <h3>Más vendidos de <?php echo $nombres_meses[$mes - 1]; ?></h3>
              <?php if (empty($mas_vendidos)) : ?>
                <p class="dx-muted" style="font-size:13px; margin:0">Todavía no hay ventas este mes.</p>
                <?php else : foreach ($mas_vendidos as $mv) : ?>
                  <div class="dx-list">
                    <span class="dx-t"><?php echo dx_e($mv['name']); ?></span>
                    <span><?php echo (int) $mv['q']; ?> uds</span>
                  </div>
              <?php endforeach;
              endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="dx-card">
              <h3>Stock crítico</h3>
              <?php if (empty($lista_critica)) : ?>
                <p class="dx-muted" style="font-size:13px; margin:0">Todo el inventario está por encima del mínimo.</p>
                <?php else : foreach ($lista_critica as $lc) : ?>
                  <div class="dx-list">
                    <span class="dx-t"><?php echo dx_e($lc['name']); ?></span>
                    <span class="<?php echo $lc['stock'] == 0 ? 'dx-bad' : 'dx-warn'; ?>"><?php echo (int) $lc['stock']; ?> / mín. <?php echo (int) $lc['stock_minimo']; ?></span>
                  </div>
              <?php endforeach;
              endif; ?>
              <a href="inventario.php" style="font-size:13px">Ver inventario</a>
            </div>
          </div>
        </div>

      </section>
    </div>
    <?php include 'includes/footer.php'; ?>

  </div>
  <!-- ./envoltura -->

  <?php $pdo->close(); ?>
  <?php include 'includes/scripts.php'; ?>
  <script src="../bower_components/chart.js/dist/chart.umd.min.js"></script>
  <script>
    $(function() {
      function dinero(v) {
        v = Number(v);
        if (v >= 1000000) return '$' + (Math.round(v / 100000) / 10) + 'M';
        if (v >= 1000) return '$' + Math.round(v / 1000) + 'K';
        return '$' + v;
      }

      var barChartCanvas = document.getElementById('barChart');
      var barChartData = {
        labels: <?php echo $months; ?>,
        datasets: [{
          label: 'VENTAS',
          backgroundColor: 'rgba(60,141,188,0.9)',
          borderColor: 'rgba(60,141,188,0.8)',
          borderWidth: 2,
          data: <?php echo $sales; ?>
        }]
      }
      var barChartOptions = {
        scales: {
          x: {grid: {display: false}},
          y: {
            beginAtZero: true,
            grid: {color: 'rgba(0,0,0,.05)'},
            ticks: {callback: dinero}
          }
        },
        plugins: {
          legend: {position: 'top'},
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'VENTAS: $' + Number(context.parsed.y).toLocaleString('es-CO');
              }
            }
          }
        },
        responsive: true,
        maintainAspectRatio: false
      }

      new Chart(barChartCanvas, {
        type: 'bar', data: barChartData, options: barChartOptions
      });
    });
  </script>
  <script>
    $(function() {
      $('#select_year').change(function() {
        window.location.href = 'home.php?year=' + $(this).val();
      });
    });
  </script>
  <script>
    // Enviar alerta de stock en segundo plano
    $.ajax({
      type: 'POST',
      url: 'enviar_alerta_stock.php',
      data: {}
    });
  </script>
</body>

</html>
