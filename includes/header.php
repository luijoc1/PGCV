<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/table_loading.php'; ?>
  	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<title>Almacén los Almendros</title>
  	<!-- Dile al navegador que responda al ancho de la pantalla -->
  	<meta content="width=device-width, initial-scale=1" name="viewport">
    <!-- Bootstrap 5 customized to preserve the existing public theme. -->
    <link rel="stylesheet" href="dist/css/pgcv-public.min.css">
  	<!-- DataTables -->
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  	<!-- Fuente impresionante -->
  	<link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  	<!--Estilo del tema-->
  	<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  	<!-- AdminLTE Pieles. Elija una máscara de css / Pieles
       Carpeta en lugar de descargarlas todas para reducir la carga. -->
       <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!-- Magnify -->
    <link rel="stylesheet" href="magnify/magnify.min.css">
   

  	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  	<!--[if lt IE 9]>
  	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  	<![endif]-->

  	<!-- Fuente de Google -->
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Paypal Express -->
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    <!-- Google Recaptcha -->
    <script src='https://www.google.com/recaptcha/api.js'></script>

    
  	<!-- CSS personalizado -->
    <style type="text/css">
    .word-wrap{
      overflow-wrap: break-word;
    }
    .prod-body{
      height:300px;
    }

    .box:hover {
        box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    }
    .register-box{
      margin-top:20px;
    }

    .profile-avatar {
      display: block;
      width: 160px;
      max-width: 100%;
      height: 160px;
      object-fit: cover;
      margin: 0 auto 16px;
      border-radius: 8px;
    }
    .profile-details {
      display: grid;
      grid-template-columns: minmax(120px, 1fr) minmax(0, 2fr);
      gap: 8px 16px;
    }
    .profile-details dd {
      margin: 0;
      overflow-wrap: anywhere;
    }
    #example1 th, #example1 td, .dataTables_scrollHead th {
      white-space: nowrap;
    }
    #resumen-total, #resumen-tbody td:last-child {
      white-space: nowrap;
      text-align: right;
    }
    @media (max-width: 767px) {
      .profile-details {
        grid-template-columns: 1fr;
        gap: 4px;
      }
      .profile-details dd {
        margin-bottom: 12px;
      }
    }

    @media (max-width: 991px) {
      .layout-top-nav .main-header {
        max-height: none;
      }
      .layout-top-nav .main-header .navbar-header {
        float: none;
        width: 100%;
      }
      .layout-top-nav .main-header .navbar-collapse {
        float: none !important;
        clear: both;
      }
      .layout-top-nav .main-header .navbar-custom-menu {
        position: static;
        float: none;
        clear: both;
        width: 100%;
      }
      .layout-top-nav .main-header .navbar-custom-menu > .navbar-nav {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        float: none;
        margin: 0;
      }
    }

    #trending{
      list-style: none;
      padding:10px 5px 10px 15px;
    }
    #trending li {
      padding-left: 1.3em;
    }
    #trending li:before {
      content: "\f046";
      font-family: FontAwesome;
      display: inline-block;
      margin-left: -1.3em; 
      width: 1.3em;
    }

    /*Aumentar*/
    .magnify > .magnify-lens {
      width: 100px;
      height: 100px;
    }
    
    .main-footer {
    background: #1a2e4a !important;
    color: rgba(255,255,255,0.85) !important;
    border-top: none !important;
}

.wrapper {
    background: #f0f2f5 !important;
}
    </style>

</head>
