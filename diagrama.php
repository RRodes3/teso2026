<?php
require_once('Connections/conexion.php');
require_once('Connections/funciones.php');
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Sistema Teso</title>
  <link rel="icon" sizes="10x16" href="img/logo-ico.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Eduardo Sandoval">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="estilos/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

      <header class="main-header">
        <?php include('header.php'); ?>
      </header>


      <!-- Left side column. contains the logo and sidebar -->

      <?php 
      $menuactivo ='inicio';
      include('aside.php');
    ?>

  <!-- ---------------------------------------------- -->
   <div class="content-wrapper">
    <section class="content-header">

    </section>
    <section class="content">

      <div class="row">     
      <?php
        include "inicio/cajas-superiores.php";
      ?>
      </div> 
      <div class="row">      
          <div class="col-lg-12">
              <img src="img/intro-bng.png" alt="" class="img-fluid" style="width: 100%">
          </div>
      </div>
    </section>
     </div>

      <!-- ------------------------------------------------------ -->
      
 
    <?php include('footer.html'); ?>

    <div class="control-sidebar-bg"></div>
  </div>
  <!-- ./wrapper -->

  <!-- jQuery 2.2.3 -->
  <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
  <!-- Bootstrap 3.3.6 -->
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <!-- ChartJS 1.0.1 -->
  <script src="plugins/chartjs/Chart.min.js"></script>
  <!-- FastClick -->
  <script src="plugins/fastclick/fastclick.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/app.min.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="dist/js/demo.js"></script>
  <!-- page script -->

</body>

</html>