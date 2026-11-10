<?php
if (!isset($_SESSION)) {
  session_start();
}

if (isset($_SESSION['U_spprivilegio']) and in_array("m_regmovimiento", $_SESSION['U_spprivilegio'])) {
  require_once('Connections/conexion.php');

  $buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
  $startRow = isset($_GET['start']) ? intval($_GET['start']) : 0;
  $maxRows = 100;

  $filtro_busqueda = "";
  if (!empty($buscar)) {
    $filtro_busqueda = " AND bien.codbieninv LIKE '%" . $buscar . "%'";
  }

  if (in_array("m_tododepedencia", $_SESSION['U_spprivilegio'])) {
    $query_lista = "SELECT bien.*, biencatalogo.nombien, m.idmov, m.idoficinadest, m.foto, m.condicion, m.observacion, 
    m.idusuarioreg, m.objetomov, m.doctransf, familiabien.codfamiliabien, 
    proveedor.codproveedor, proveedor.razonsocial FROM bien 
    INNER JOIN biencatalogo ON bien.codbiencat = biencatalogo.codbiencat  
    LEFT JOIN movimiento m ON m.idbien = bien.idbien AND m.idmov = bien.idmovimiento 
    LEFT JOIN familiabien ON familiabien.idfamiliabien = bien.idfamiliabien 
    LEFT JOIN proveedor ON proveedor.idproveedor = bien.idproveedor 
    WHERE bien.codmarca = '' AND bien.idbien > 0 " . $filtro_busqueda . " ORDER BY bien.codbieninv ASC LIMIT " . $startRow . ", " . $maxRows;
  } else {
    $query_lista = "SELECT bien.*, biencatalogo.nombien, m.idmov, m.idoficinadest, m.foto, 
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

  $lista = mysqli_query($conexion, $query_lista);
  
  $html = '';
  $result = $startRow;
  if (mysqli_num_rows($lista) > 0) {
    while ($row_lista = mysqli_fetch_assoc($lista)) {
      $result++;
      $html .= '<tr>';
      $html .= '<td><strong>' . $result . '</strong></td>';
      $html .= '<td>' . $row_lista['codbieninv'] . '<br></td>';
      $html .= '<td>' . $row_lista['nombien'] . '</td>';
      $html .= '<td><strong>' . $row_lista['codunicoinfor'] . '</strong></td>';
      $html .= '<td><strong>Empresa: </strong>' . $row_lista['razonsocial'] . '<br>';
      $html .= '<strong>Norma: </strong>' . $row_lista['codfamiliabien'] . '<br>';
      $html .= '<strong>Marca: </strong> ' . $row_lista['codmarca'] . '<br>';
      $html .= '<strong>Modelo: </strong> ' . $row_lista['codmodelo'] . '<br>';
      $html .= '<strong>Serie: </strong> ' . $row_lista['serie'] . '</td>';
      $html .= '<td>';
      $html .= '<strong>Consta / incluye:</strong>' . $row_lista['caracteristica'] . '<br>';
      $html .= '<strong>Gestor:</strong> ' . $row_lista['codcolor'] . '<br>';
      $html .= '<strong>Categoria:</strong> ' . $row_lista['condicion'] . '<br>';
      $html .= '<strong>Observaciones:</strong> ' . $row_lista['observacion'] . '<br>';
      $html .= '</td>';
      $html .= '<td>' . $row_lista['refapartado'] . '</td>';
      $html .= '<td>' . $row_lista['quienregis'] . '</td>';
      $html .= '<td><a href="#" data-toggle="modal" data-target="#apartado' . $row_lista['idbien'] . '" class="btn btn-warning btn-sm">Apartar</a></td>';
      $html .= '</tr>';
    }
  } else {
    $html .= '<tr><td colspan="9" style="text-align:center;">No se encontraron registros</td></tr>';
  }

  echo $html;
  mysqli_free_result($lista);
} else {
  echo '<tr><td colspan="9">Sin permisos</td></tr>';
}
?>
