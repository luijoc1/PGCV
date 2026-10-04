<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../../includes/table_loading.php'; ?>
  	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<title>Panel administrativo</title>
  	<!-- Tell the browser to be responsive to screen width -->
  	<meta content="width=device-width, initial-scale=1" name="viewport">
    <!-- Bootstrap 5 compilado con las medidas y colores habituales. -->
    <link rel="stylesheet" href="../dist/css/pgcv-admin.min.css?v=<?php echo filemtime(__DIR__ . '/../../dist/css/pgcv-admin.min.css'); ?>">
    <link rel="stylesheet" href="../bower_components/jodit/es2021/jodit.min.css">
    <link rel="stylesheet" href="../dist/css/product-editor.css">
    <!-- Iconos habituales fijados en npm, sin cambiar símbolos ni medidas. -->
    <link rel="stylesheet" href="../bower_components/font-awesome/css/font-awesome.min.css?v=<?php echo filemtime(__DIR__ . '/../../bower_components/font-awesome/css/font-awesome.min.css'); ?>">
    <!-- Select2 -->
    <link rel="stylesheet" href="../bower_components/select2-v4/dist/css/select2.min.css?v=<?php echo filemtime(__DIR__ . '/../../bower_components/select2-v4/dist/css/select2.min.css'); ?>">
    <!-- Tema habitual conservado desde fuentes Sass propias. -->
    <link rel="stylesheet" href="../dist/css/pgcv-theme.min.css?v=<?php echo filemtime(__DIR__ . '/../../dist/css/pgcv-theme.min.css'); ?>">
  	<!-- DataTables -->
    <link rel="stylesheet" href="../bower_components/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <!-- daterange picker -->
    <link rel="stylesheet" href="../dist/css/pgcv-daterangepicker.min.css?v=<?php echo filemtime(__DIR__ . '/../../dist/css/pgcv-daterangepicker.min.css'); ?>">
    <!-- Tema azul habitual, compilado desde su fuente Sass. -->
    <link rel="stylesheet" href="../dist/css/pgcv-skin-blue.min.css?v=<?php echo filemtime(__DIR__ . '/../../dist/css/pgcv-skin-blue.min.css'); ?>">
  	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  	<!--[if lt IE 9]>
  	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  	<![endif]-->

  	<!-- Google Font -->
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  	<style type="text/css">
  		.mt20{
  			margin-top:20px;
  		}
      .bold{
        font-weight:bold;
      }
      .dt-scroll-head th, .dt-scroll-body td {
        white-space: nowrap;
      }
      .dt-scroll-body {
        -webkit-overflow-scrolling: touch;
      }
      @media (max-width: 767px) {
        .dt-container .dt-paging .pagination {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
        }
        .content .row > .col-sm-3 { margin-bottom: 12px; }
      }

      /*chart style*/
      #legend ul {
        list-style: none;
      }

      #legend ul li {
        display: inline;
        padding-left: 30px;
        position: relative;
       /* margin-bottom: 4px;*/
       /* border-radius: 5px;*/
        padding: 2px 8px 2px 28px;
        font-size: 14px;
        cursor: default;
        -webkit-transition: background-color 200ms ease-in-out;
        -moz-transition: background-color 200ms ease-in-out;
        -o-transition: background-color 200ms ease-in-out;
        transition: background-color 200ms ease-in-out;
      }

      #legend li span {
        display: block;
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 100%;
       /* border-radius: 5px;*/
      }
  	</style>
</head>
