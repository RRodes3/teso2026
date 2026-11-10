<?php
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_SESSION['U_spprivilegio']) and in_array("m_regmovimiento", $_SESSION['U_spprivilegio'])) {

  require_once('Connections/conexion.php');
  $menuactivo = 'movimiento';
?>
  <?php
  if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
      if (PHP_VERSION < 6) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
      }

      //$theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($theValue) : mysqli_escape_string($theValue);
      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
        case "double":
          $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
          break;
        case "date":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "defined":
          $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
          break;
      }
      return $theValue;
    }
  }
  //inventario
  $dniusuario = '';
  if (isset($_SESSION["U_sdni"])) {
    $dniusuario = $_SESSION["U_sdni"];
  }
  if (isset($_POST["Grabar"])) {
    $updateSQL = sprintf(
      "UPDATE bien SET `user`=%s, fechaad=NOW(), inventario=%s WHERE idbien=%s",
      GetSQLValueString($dniusuario, "text"),
      GetSQLValueString($_POST['inventario'], "text"),
      GetSQLValueString($_POST['idbien'], "int")
    );


    $Result1 = mysqli_query($conexion, $updateSQL) or die(mysqli_error());
  }
  //DAR DE BAJA
  if (isset($_POST["baja"])) {
    $updateSQL = sprintf(
      "UPDATE bien SET `user`=%s, fechaad=NOW(), debaja='SI' WHERE idbien=%s",
      GetSQLValueString($dniusuario, "text"),
      GetSQLValueString($_POST['idbien'], "int")
    );
    $Result1 = mysqli_query($conexion, $updateSQL) or die(mysqli_error());
  }
  $currentPage = $_SERVER["PHP_SELF"];

  /* ====== MANEJO DE PETICIONES AJAX PARA BÚSQUEDA DINÁMICA ====== */
  if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    // Esto es una petición AJAX, devolvemos solo las filas HTML
    
    $buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $startRow = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $maxRows = 100;

    $filtro_busqueda = "";
    if (!empty($buscar)) {
      $filtro_busqueda = " AND bien.codbieninv LIKE '%" . $buscar . "%'";
    }

    if (in_array("m_tododepedencia", $_SESSION['U_spprivilegio'])) {
      $query_ajax = "SELECT bien.*, biencatalogo.nombien, m.idmov, m.idoficinadest, m.foto, m.condicion, m.observacion, 
      m.idusuarioreg, m.objetomov, m.doctransf, familiabien.codfamiliabien, 
      proveedor.codproveedor, proveedor.razonsocial FROM bien 
      INNER JOIN biencatalogo ON bien.codbiencat = biencatalogo.codbiencat  
      LEFT JOIN movimiento m ON m.idbien = bien.idbien AND m.idmov = bien.idmovimiento 
      LEFT JOIN familiabien ON familiabien.idfamiliabien = bien.idfamiliabien 
      LEFT JOIN proveedor ON proveedor.idproveedor = bien.idproveedor 
      WHERE bien.codmarca = '' AND bien.idbien > 0 " . $filtro_busqueda . " ORDER BY bien.codbieninv ASC LIMIT " . $startRow . ", " . $maxRows;
    } else {
      $query_ajax = "SELECT bien.*, biencatalogo.nombien, m.idmov, m.idoficinadest, m.foto, 
      m.condicion, m.observacion, m.idusuarioreg, m.objetomov, m.doctransf, oficina.nomoficina, dependencia.dependencia, 
      concat_ws(' ', r.nombres, r.apellidos) as responsable FROM bien 
      INNER JOIN biencatalogo ON bien.codbiencat = biencatalogo.codbiencat  
      LEFT JOIN movimiento m ON m.idbien = bien.idbien AND m.idmov = bien.idmovimiento 
      LEFT JOIN oficina ON oficina.idoficina = m.idoficinadest 
      LEFT JOIN dependencia ON dependencia.iddependencia = oficina.iddependencia 
      LEFT JOIN responsable r ON r.idresponsable = oficina.idrespoficina 
      WHERE (bien.debaja <> '1' OR bien.debaja IS NULL) 
      AND dependencia.iddependencia = '" . $_SESSION['U_spiddependencia'] . "' " . $filtro_busqueda . " 
      ORDER BY biencatalogo.nombien, bien.codbieninv ASC LIMIT " . $startRow . ", " . $maxRows;
    }

    $lista_ajax = mysqli_query($conexion, $query_ajax);
    
    $result = $startRow;
    $modal_html = '';
    if (mysqli_num_rows($lista_ajax) > 0) {
      while ($row_ajax = mysqli_fetch_assoc($lista_ajax)) {
        $result++;
        echo '<tr>';
        echo '<td><strong>' . $result . '</strong></td>';
        echo '<td>' . $row_ajax['codbieninv'] . '<br></td>';
        echo '<td>' . $row_ajax['nombien'] . '</td>';
        echo '<td><strong>' . $row_ajax['codunicoinfor'] . '</strong></td>';
        echo '<td><strong>Empresa: </strong>' . $row_ajax['razonsocial'] . '<br>';
        echo '<strong>Norma: </strong>' . $row_ajax['codfamiliabien'] . '<br>';
        echo '<strong>Marca: </strong> ' . $row_ajax['codmarca'] . '<br>';
        echo '<strong>Modelo: </strong> ' . $row_ajax['codmodelo'] . '<br>';
        echo '<strong>Serie: </strong> ' . $row_ajax['serie'] . '</td>';
        echo '<td>';
        echo '<strong>Consta / incluye:</strong>' . $row_ajax['caracteristica'] . '<br>';
        echo '<strong>Gestor:</strong> ' . $row_ajax['codcolor'] . '<br>';
        echo '<strong>Categoria:</strong> ' . $row_ajax['condicion'] . '<br>';
        echo '<strong>Observaciones:</strong> ' . $row_ajax['observacion'] . '<br>';
        echo '</td>';
        echo '<td>' . $row_ajax['refapartado'] . '</td>';
        echo '<td>' . $row_ajax['quienregis'] . '</td>';
        echo '<td><a href="#" data-toggle="modal" data-target="#apartado' . $row_ajax['idbien'] . '" class="btn btn-warning btn-sm">Apartar</a></td>';
        echo '</tr>';

        $modal_html .= '<form action="crear-apartado.php" method="post" name="frmimv">';
        $modal_html .= '<div class="modal fade" id="apartado' . $row_ajax['idbien'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
        $modal_html .= '<div class="modal-dialog" role="document"><div class="modal-content">';
        $modal_html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        if ($row_ajax['apartado'] == 1) {
          $modal_html .= '<h4 style="color: white;" class="modal-title">Ingrese Su contraseña</h4>';
        } else {
          $modal_html .= '<h4 style="color: white;" class="modal-title">Para poder apartar este informe favor de ingresar una referencia</h4>';
        }
        $modal_html .= '</div>';
        $modal_html .= '<div class="modal-body">';
        $modal_html .= '<input name="idbien" type="hidden" value="' . $row_ajax['idbien'] . '">';
        $modal_html .= '<input type="hidden" name="apartado" value="' . $row_ajax['apartado'] . '">';
        $modal_html .= '<input type="hidden" name="idusuarioreg" value="' . $row_ajax['idusuarioreg'] . '">';
        if ($row_ajax['apartado'] == 1) {
          $modal_html .= '<label for="password">Password</label><input name="password" id="password" type="password" required class="form-control">';
        } else {
          $modal_html .= '<label for="refapartado">Referencia</label><input name="refapartado" id="refapartado" type="text" required class="form-control">';
        }
        $modal_html .= '</div>';
        $modal_html .= '<div class="modal-footer">';
        if ($row_ajax['apartado'] == 1) {
          $modal_html .= '<label style="font-size:14px" class="label label-danger">El informe ya fue APARTADO</label>';
        }
        $modal_html .= '<button type="button" class="btn btn-default" data-dismiss="modal" title="Cancelar">Cancelar</button>';
        $modal_html .= '<input name="Grabar" type="submit" class="art-button btn btn-primary" title="Apartar" value="Ok">';
        $modal_html .= '<input type="hidden" name="autorizado" value="frmimv">';
        $modal_html .= '</div></div></div></div></form>';
      }
      echo '<!--MODAL_CONTAINER-->' . $modal_html;
    } else {
      echo '<tr><td colspan="9" style="text-align:center;">No se encontraron registros</td></tr>';
      echo '<!--MODAL_CONTAINER-->'; 
    }
    
    mysqli_free_result($lista_ajax);
    exit; // Termina aquí, no continúa con el resto de la página
  }
  /* ====== FIN DE MANEJO AJAX ====== */

  /*
    Armado de consulta con paginación (Antes se traía 20000 registros y se paginaba en el cliente.
    Ahora se limitó a 50 registros por página y se hace la paginación en el servidor, lo que mejora el rendimiento y evita problemas de memoria. 15/06/2026 RRR)
    
  */
  $maxRows_lista = 100;
  $pageNum_lista = 0;
  if (isset($_GET['pageNum_lista'])) {
    $pageNum_lista = $_GET['pageNum_lista'];
  }
  $startRow_lista = $pageNum_lista * $maxRows_lista;
  $colname_lista = "-1";

  /* Filtro de búsqueda: permite buscar por código de informe (codbieninv) en la BD completa */
  $filtro_busqueda = "";
  if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $termino_busqueda = mysqli_real_escape_string($conexion, $_GET['buscar']);
    $filtro_busqueda = " AND bien.codbieninv LIKE '%" . $termino_busqueda . "%'";
    $pageNum_lista = 0; // Reinicia a página 1 cuando busca
    $startRow_lista = 0;
  }

  if (in_array("m_tododepedencia", $_SESSION['U_spprivilegio'])) {
    $query_lista = "SELECT  bien.*,biencatalogo.nombien,  m.idmov, m.idoficinadest, m.foto, m.condicion, m.observacion, 
m.idusuarioreg,m.objetomov, m.doctransf, familiabien.codfamiliabien, 
proveedor.codproveedor, proveedor.razonsocial FROM bien 
inner join biencatalogo on bien.codbiencat = biencatalogo.codbiencat  
left join movimiento m on m.idbien = bien.idbien and m.idmov = bien.idmovimiento 
left join familiabien on familiabien.idfamiliabien = bien.idfamiliabien 
left join proveedor on proveedor.idproveedor = bien.idproveedor 
where bien.codmarca = '' and bien.idbien > 0 " . $filtro_busqueda . " ORDER BY bien.codbieninv ASC ";
  } else {
    $query_lista = "SELECT  bien.*,biencatalogo.nombien,  m.idmov, m.idoficinadest, m.foto, 
  m.condicion, m.observacion, m.idusuarioreg,m.objetomov, m.doctransf, oficina.nomoficina,dependencia.dependencia, 
  concat_ws (' ',r.nombres , r.apellidos) as responsable FROM bien 
  inner join biencatalogo on bien.codbiencat = biencatalogo.codbiencat  
  left join movimiento m on m.idbien = bien.idbien and m.idmov = bien.idmovimiento 
  left join oficina on oficina.idoficina = m.idoficinadest 
  left join dependencia on dependencia.iddependencia = oficina.iddependencia 
  left join responsable r on r.idresponsable = oficina.idrespoficina where (bien.debaja <> '1' or bien.debaja is null) 
  and dependencia.iddependencia  ='" . $_SESSION['U_spiddependencia'] . "' " . $filtro_busqueda . "  order by biencatalogo.nombien, bien.codbieninv asc";
  }

  $query_limit_lista = sprintf("%s LIMIT %d, %d", $query_lista, $startRow_lista, $maxRows_lista);
  $lista = mysqli_query($conexion, $query_limit_lista) or die(mysqli_error());
  $row_lista = mysqli_fetch_assoc($lista);


  if (isset($_GET['totalRows_lista'])) {
    $totalRows_lista = $_GET['totalRows_lista'];
  } else {

  /* MODIFICACION DE CÓDIGO: Se limitó la consulta para contar el total de registros según el privilegio del usuario
     Antes: Traía todas las filas de la tabla bien para luego contarlas (Guardaba todo en la memoria, motivo por el que se alentaba la página).
     Ahora: Hace una consulta específica para contar solo los registros que corresponden al privilegio del usuario, lo que mejora el rendimiento y evita problemas de memoria. 15/06/2026 RRR
  */

    if (in_array("m_tododepedencia", $_SESSION['U_spprivilegio'])) {
    // Si tiene acceso a todo
    $query_count = "SELECT COUNT(*) AS total FROM bien 
                    WHERE bien.codmarca = '' AND bien.idbien > 0";
  } else {
    // Si tiene acceso restringido por dependencia
    $query_count = "SELECT COUNT(*) AS total FROM bien 
                    INNER JOIN movimiento m ON m.idbien = bien.idbien AND m.idmov = bien.idmovimiento 
                    INNER JOIN oficina ON oficina.idoficina = m.idoficinadest 
                    INNER JOIN dependencia ON dependencia.iddependencia = oficina.iddependencia 
                    WHERE (bien.debaja <> '1' OR bien.debaja IS NULL) 
                    AND dependencia.iddependencia = '" . $_SESSION['U_spiddependencia'] . "'";
  }
  
  $result_count = mysqli_query($conexion, $query_count);
  $row_count = mysqli_fetch_assoc($result_count);
  $totalRows_lista = $row_count['total'];

  /*
    ***** FIN DE MODIFICACIÓN DE CÓDIGO 15/06/2026 RRR *****
  */

}
  $totalPages_lista = ceil($totalRows_lista / $maxRows_lista) - 1;

  $queryString_lista = "";
  if (!empty($_SERVER['QUERY_STRING'])) {
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
      if (
        stristr($param, "pageNum_lista") == false &&
        stristr($param, "totalRows_lista") == false
      ) {
        array_push($newParams, $param);
      }
    }
    if (count($newParams) != 0) {
      $queryString_lista = "&" . htmlentities(implode("&", $newParams));
    }
  }
  $queryString_lista = sprintf("&totalRows_lista=%d%s", $totalRows_lista, $queryString_lista);
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema Teso</title>
    <link rel="shortcut icon" href="img/icono.png" type="image/x-icon">
    <meta name="author" content="Eduardo Sandoval">
    <!--Desarrollado por: Eduardo Sandoval -->
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="estilos/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <link rel="stylesheet" href="estilos/jquery.dataTables.min.css">
    <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
  </head>

  <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
      <header class="main-header">
        <?php include('header.php'); ?>
      </header>
      <?php
      $activarmenu = 'lista-bienmov';
      include('aside.php');
      ?>
      <div class="content-wrapper">
        <section class="content-header">
          <h1>
            <i class="fa fa-retweet"></i>
            Lista de Informes [Movimiento - Impresiones]
          </h1>
        </section>
        <!--Cabecera de impresión-->
        <?php include('header_impr.php'); ?>
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                  <div class="table-responsive">
                    <table id="servicios" width="100%" class="table table-hover display">
                      <thead>
                        <tr class="titulotabla">
                          <td width="1%"><strong>N°</strong></td>
                          <td width="1px">N°. INFORME</td>
                          <td width="1px">ITEM</td>
                          <td>CLAVE</td>
                          <td>NORMA</td>
                          <td>CARACTERISTICAS</td>
                          <td>REFERENCIA</td>
                          <td>USER</td>
                          <td>APARTAR</td>
                        </tr>
                      </thead>
                      <?php $result = $startRow_lista;
                      if ($totalRows_lista > 0) {
                        do { ?>
                          <tr>
                            <td>
                              <strong>
                                <?php
                                $result += 1;
                                echo  $result;
                                ?>
                              </strong>
                            </td>
                            <td><?php echo $row_lista['codbieninv']; ?><br></td>
                            <td><?php echo $row_lista['nombien']; ?></td>
                            <td><strong><?php echo $row_lista['codunicoinfor']; ?></strong></td>
                            <td><strong>Empresa: </strong><?php echo $row_lista['razonsocial']; ?><br>
                              <strong>Norma: </strong><?php echo $row_lista['codfamiliabien']; ?><br>
                              <strong>Marca: </strong> <?php echo $row_lista['codmarca']; ?><br>
                              <strong>Modelo: </strong> <?php echo $row_lista['codmodelo']; ?><br>
                              <strong>Serie: </strong> <?php echo $row_lista['serie']; ?>
                            </td>

                            <td>
                              <strong>Consta / incluye:</strong><?php echo $row_lista['caracteristica']; ?><br>
                              <strong>Gestor:</strong> <?php echo $row_lista['codcolor']; ?><br>
                              <strong>Categoria:</strong> <?php echo $row_lista['condicion']; ?><br>
                              <strong>Observaciones:</strong> <?php echo $row_lista['observacion']; ?><br>
                            </td>

                            <td><?php echo $row_lista['refapartado']; ?></td>
                            <td><?php echo $row_lista['quienregis'];  ?></td>
                            <td><a href="#" data-toggle="modal" <?php
                                                                if (($row_lista['apartado'] == 1)) {
                                                                  echo "disabled";
                                                                } else {
                                                                  echo "enabled";
                                                                } ?> data-target="#apartado<?php echo $row_lista['idbien']; ?>" title="Apartado" stylesd="padding-bottom:0px; padding-top:0px" class="btn btn-warning btn-sm">Apartar</span></a>

                              <form action="crear-apartado.php" method="post" name="frmimv">
                                <div class="modal fade" id="apartado<?php echo $row_lista['idbien']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                  <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php if ($row_lista['apartado'] == 1) {
                                          echo '<h4 style="color: white;" class="modal-title">Ingrese Su contraseña
                                          </h4>';
                                        } else {
                                          echo '   <h4  style="color: white;" class="modal-title">Para poder apartar este informe favor de ingresar una referencia
                                          </h4>';
                                        } ?>

                                      </div>
                                      <div class="modal-body">
                                        <input name="idbien" type="hidden" value="<?php echo $row_lista['idbien']; ?>">
                                        <input type="hidden" name="apartado" value="<?php echo $row_lista['apartado'] ?>">
                                        <input type="hidden" name="idusuarioreg" value="<?php echo $row_lista['idusuarioreg'] ?>">

                                        <?php
                                        if ($row_lista['apartado'] == 1) {
                                          echo '
                                            <label for="password">Password</label>
                                        <input name="password" id="password" type="password" required>';
                                        } else {
                                          echo '
                                            <label for="refapartado">Referencia</label>
                                            <input name="refapartado" id="refapartado" type="text" required>';
                                        }
                                        ?>

                                      </div>
                                      <div class="modal-footer">
                                        <?php if ($row_lista['apartado'] == 1) {
                                          echo '<label style ="font-size:14px"  class="label label-danger">El informe ya fue APARTADO</label>';
                                        } else {
                                          "enabled";
                                        } ?>
                                        <button type="button" class="btn btn-default" data-dismiss="modal" title="Cancelar">Cancelar</button>
                                        <input name="Grabar" type="submit" class="art-button" title="Apartar" value="Ok">
                                        <input type="hidden" name="autorizado" value="frmimv">
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </form>
                            </td>
                          </tr>
                      <?php } while ($row_lista = mysqli_fetch_assoc($lista));
                      } ?>
                    </table>
                    <div id="ajax-modal-container"></div>
                  </div>

                  <!--        Para imagen modal-->

                  <div id="lightbox" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <button type="button" class="close hidden" data-dismiss="modal" aria-hidden="true">×</button>
                      <div class="modal-content">
                        <div class="modal-body">
                          <img src="" alt="" />
                        </div>
                      </div>
                    </div>
                  </div>


                </div>
                <!-- /.box-body -->
              </div>
              <!-- /.box -->
            </div>
          </div>
        </section>

      </div>

      <?php include('footer.html'); ?>

    </div>

    <!-- jQuery 2.2.3 -->
    <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.6 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/fastclick/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js"></script>
    <!-- DataTables -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="javascript" class="init">
      $(document).ready(function() {
        var table = $('#servicios').DataTable({
          searching: true,  // Activa la barra de búsqueda
          paging: false     // Desactiva la paginación client-side (usamos la de PHP)
        });

        // Si hay un parámetro GET 'buscar', lo coloca en la barra de búsqueda de DataTables
        var urlParams = new URLSearchParams(window.location.search);
        var searchTerm = urlParams.get('buscar');
        if (searchTerm) {
          $('#servicios_filter input').val(decodeURIComponent(searchTerm));
        }

        // Búsqueda dinámica con AJAX: espera 600ms y luego busca sin recargar
        var searchTimeout;
        $('#servicios_filter input').off('keyup').on('keyup', function() {
          clearTimeout(searchTimeout); // Cancela el timeout anterior
          
          var search = $(this).val();
          
          searchTimeout = setTimeout(() => {
            $.ajax({
              url: '?ajax=1',
              type: 'GET',
              data: { buscar: search, start: 0 },
              dataType: 'html',
              success: function(data) {
                var parts = data.split('<!--MODAL_CONTAINER-->');
                $('#servicios tbody').html(parts[0]);
                $('#ajax-modal-container').html(parts[1] || '');
              },
              error: function() {
                $('#servicios tbody').html('<tr><td colspan="9" style="text-align:center;">Error en la búsqueda</td></tr>');
                $('#ajax-modal-container').html('');
              }
            });
          }, 600); // Espera 600ms después de que dejas de escribir
        });

        // Carga inicial de datos si hay búsqueda en la URL
        if (searchTerm) {
          $.ajax({
            url: '?ajax=1',
            type: 'GET',
            data: { buscar: searchTerm, start: 0 },
            dataType: 'html',
            success: function(data) {
              var parts = data.split('<!--MODAL_CONTAINER-->');
              $('#servicios tbody').html(parts[0]);
              $('#ajax-modal-container').html(parts[1] || '');
            }
          });
        }
      });
    </script>

  </body>

  </html>
  <?php
  mysqli_free_result($lista);
  ?>
<?php } else {
  header("Location: pagina-restringida.php");
} ?>