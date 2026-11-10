
<div class="content-wrapper">
  <section class="content-header"> 
    <h1>    
      Administrar Recibos   
    </h1>
    <ol class="breadcrumb">      
      <li><a href="../diagrama.php"><i class="fa fa-dashboard"></i> Inicio</a></li>     
      <li class="active">Administrar Recibos</li>    
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border"> 
        <a href="crear-venta">
          <button class="btn btn-primary">           
            Agregar Recibos
          </button>
        </a>
         <button type="button" class="btn btn-default pull-right" id="daterange-btn">          
            <span>
              <i class="fa fa-calendar"></i> Rango de fecha
            </span>
            <i class="fa fa-caret-down"></i>
         </button>
      </div>
      <div class="box-body">        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">         
        <thead>        
         <tr >          
           <th style="width:10px">#</th>
           <!-- <th>Recibo No.</th> -->
           <!-- <td>'.$value["codigo"].'</td>'; -->

           
           <th>Cliente</th>   
           <th style="width:30px">Descripcion</th>   
           <!-- <th>Forma de pago</th> -->
           <th>Neto</th>
           <th>Total</th> 
           <th>Fecha</th>
           <th>Acciones</th>
         </tr> 
        </thead>
        <tbody>
        <?php
          if(isset($_GET["fechaInicial"])){
            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];
          }else{
            $fechaInicial = null;
            $fechaFinal = null;
          }

          $respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);


          // NUEVO FRAGMENTO DE CÓDIGO PARA MOSTRAR LA DESCRIPCIÓN DE LOS PRODUCTOS VENDIDOS 03/06/2026 RRR
          
          $todosClientes = ControladorClientes::ctrMostrarClientes(null, null);
          $mapClientes = array();
          if (is_array($todosClientes)){
            foreach ($todosClientes as $c) {
              $mapClientes[$c["idproveedor"]] = $c;
            }
          }

          $todosUsuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
          $mapUsuarios = array();
          if (is_array($todosUsuarios)){
            foreach ($todosUsuarios as $u) {
              $mapUsuarios[$u["id"]] = $u;
            }
          }

          // NUEVO FRAGMENTO DE CÓDIGO PARA MOSTRAR LA DESCRIPCIÓN DE LOS PRODUCTOS VENDIDOS 03/06/2026 RRR

          foreach ($respuesta as $key => $value) {
            $clienteInfo = isset($mapClientes[$value["id_cliente"]]) ? $mapClientes[$value["id_cliente"]] : null;
            $clienteTexto = $clienteInfo ? ($clienteInfo["codproveedor"].' - '.$clienteInfo["razonsocial"]) : 'N/D';

            echo '<tr>';
            echo '<td>'.($key+1).'</td>';
            echo '<td>'.$clienteTexto.'</td>';
            echo '<td>'.$value["productos"].'</td>';
            echo '<td>$ '.number_format($value["neto"],2).'</td>';
            echo '<td>$ '.number_format($value["total"],2).'</td>';
            echo '<td>'.$value["fecha"].'</td>';
            echo '<td>';
            echo '  <div class="btn-group">';
            echo '    <button class="btn btn-info btnImprimirFactura" codigoVenta="'.$value["codigo"].'">';
            echo '      <i class="fa fa-print"></i>';
            echo '    </button>';
            echo '    <button class="btn btn-sky btnImprimirCotizacion" codigoVenta="'.$value["codigo"].'">';
            echo '      <i class="fa fa-print"></i>';
            echo '    </button>';
            echo '    <button class="btn btn-warning btnEditarVenta" idVenta="'.$value["codigo"].'" data-toggle="modal" data-target="#modalEditarVenta">';
            echo '      <i class="fa fa-pencil"></i>';
            echo '    </button>';
            echo '  </div>';
            echo '</td>';
            echo '</tr>';

          }

          //Se sacaron del forech los botones de eliminar y editar para ponerlos dentro del modal, ya que al estar fuera no se podía acceder a ellos, se les asigno la clase btnEditarVenta y btnEliminarVenta respectivamente, además se les asigno el idVenta para poder identificar cada venta al momento de editar o eliminar. 03/06/2026 RRR - 03-06-2026 RRR
          
        ?>              
        </tbody>
       </table>
       <?php
      $eliminarVenta = new ControladorVentas();
      $eliminarVenta -> ctrEliminarVenta();
      ?>     
      </div>
    </div>
  </section>
</div>

<!-- echo '<button class="btn btn-warning btnEditarVenta" idVenta="'.$value["id"].'"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger btnEliminarVenta" idVenta="'.$value["id"].'"><i class="fa fa-times"></i></button>';
                    echo '</div>  -->

<div id="modalEditarVenta" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Recibo</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA EL CÓDIGO -->
           
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" id="editarCodigo" name="editarCodigo" readonly required>
                <input type="hidden"  name="idVenta" id="idVenta" required>
              </div>

            </div>


            <!-- ENTRADA PARA LA MARCA -->

            <div class="form-group">
              <div class="input-group">              
                <!-- <span class="input-group-addon"><i class="fa fa-code"></i></span>  -->
                <span class="input-group-addon">FECHA:</i></span> 
                <input type="text" class="form-control input-lg" id="editarFecha" name="editarFecha" required>
              </div>
            </div>



          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
        <?php
          $editarVenta = new ControladorVentas();
          $editarVenta -> ctrEditarVenta();
        ?>      
    </div>
  </div>
</div>