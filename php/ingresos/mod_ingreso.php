<?php
/**************************************************************************************************
 * Proyecto de Fin de Ciclo Formativo de Grado Superior                                           *
 * 'Sistema Integral de Gestión Económica Alquileres Turísticos' (SIGEcAT)                        *
 * Alumno: Alberto A. Alsina Ambrós                                                               *
 * Tutor: Jordan Llorach Beltrán                                                                  *
 * Centro formativo: IES Joan Coromines (Benicarló, España)                                       *
 * Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (CFGS DAW)                 *
 * Curso 2023/2024                                                                                *
 **************************************************************************************************
 * Licencia:                                                                                      *
 * Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional (CC BY-NC-SA 4.0)     *
 *     • Atribución (BY): El licenciante permite a otros distribuir, remezclar, retocar y crear a *
 *                        partir de  su obra, incluso con  fines comerciales, siempre y cuando se *
 *                        reconozca   la autoría  de   la   obra  original de    manera adecuada. *
 *     • No Comercial (NC): El licenciante permite a otros copiar, distribuir, mostrar y ejecutar *
 *                          la obra,  así como hacer obras derivadas basadas en ella, pero no con *
 *                          fines comerciales. Si desean utilizar  la obra con fines comerciales, *
 *                          necesitarán       obtener        permiso       del       licenciante. *
 *     • Compartir Igual (SA): Si se remezcla, transforma o se crea a partir de la obra original, *
 *                             la nueva  obra generada debe    ser distribuida bajo  una licencia *
 *                             idéntica                             a                       ésta. *
 **************************************************************************************************
 * ESTE SOFTWARE ES PROPORCIONADO POR LOS TITULARES DE LOS DERECHOS DE AUTOR Y LOS CONTRIBUYENTES *
 * "TAL CUAL"  Y CUALQUIER GARANTÍA EXPRESA O   IMPLÍCITA,  INCLUYENDO,  PERO NO  LIMITADA A, LAS *
 * GARANTÍAS   IMPLÍCITAS  DE COMERCIABILIDAD   Y   APTITUD PARA UN  PROPÓSITO  PARTICULAR QUEDAN *
 * RECHAZADAS.  EN NINGÚN CASO EL TITULAR DE LOS   DERECHOS DE  AUTOR O  LOS CONTRIBUYENTES SERÁN *
 * RESPONSABLES POR NINGÚN DAÑO DIRECTO, INDIRECTO, INCIDENTAL, ESPECIAL,  EJEMPLAR O CONSECUENTE *
 * (INCLUYENDO, PERO NO LIMITADO A, LA  ADQUISICIÓN DE BIENES O SERVICIOS  SUSTITUTOS; PÉRDIDA DE *
 * USO,  DATOS O  BENEFICIOS; O  INTERRUPCIÓN  DE  NEGOCIOS) SIN IMPORTAR LA CAUSA Y EN CUALQUIER *
 * TEORÍA DE RESPONSABILIDAD, YA SEA EN CONTRATO,  RESPONSABILIDAD ESTRICTA O AGRAVIO (INCLUYENDO *
 * NEGLIGENCIA O DE OTRO MODO) QUE SURJA DE CUALQUIER MANERA DEL USO DE ESTE SOFTWARE, INCLUSO SI *
 * SE        HA    ADVERTIDO    DE           LA        POSIBILIDAD     DE            TALES DAÑOS. *
 **************************************************************************************************/

require_once '../funciones/con_db.php';         // Conexión con la base de datos
require_once '../funciones/config.php';         // Configuración de la página y verificación de sesión
require_once '../funciones/listar.php';         // Funciones de visualización
require_once '../funciones/verificar.php';      // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = '';

    if(isset($_POST['id_ingreso']) && verificar_entidad($mysqli, "ingresos", $_POST['id_ingreso'])) {
        $ingreso_id = filter_input(INPUT_POST, 'id_ingreso', FILTER_VALIDATE_INT);
        $es_root = $_SESSION['es_root'];
        $es_admin = $_SESSION['es_admin'];
        $empresa_id = $_SESSION['empresa_id'];
        $permiso = true;
        $mensaje = "";

        //Verificación de permisos
        if(!$_SESSION['es_root']) {
            if($_SESSION['es_admin']){
                $query_prueba = "SELECT empresa_id
                                 FROM   ingresos
                                 WHERE  id = ?";
                $stmt_prueba = $mysqli->prepare($query_prueba);
                if($stmt_prueba) {
                    $stmt_prueba->bind_param('i', $ingreso_id);
                    if($stmt_prueba->execute()) {
                        $result_prueba = $stmt_prueba->get_result();
                        $empresa_id_db = $result_prueba->fetch_assoc();

                        if($empresa_id_db == NULL){
                            $mensaje = "Ha habido un error.";
                            $permiso = false;
                        } else if($empresa_id != $empresa_id_db['empresa_id']) {
                            $mensaje = "Permisos insuficientes.";
                            $permiso = false;
                        }
                    } else {
                        $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde<br/>";
                        $permiso = false;
                        loguear_error("mod_ingreso", $stmt_prueba->error);
                    }
                } else {
                    $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde<br/>";
                    $permiso = false;
                    loguear_error("mod_ingreso", $mysqli->error);
                }
                
            } else {
                $mensaje = "Permisos insuficientes..";
                $permiso = false;
            }
        }

        if($permiso) {
            $query = "SELECT    i.apartamento_id,
                                i.fecha_entrada,
                                i.fecha_salida,
                                i.nombre_cliente,
                                i.apellidos_cliente,
                                i.nif_cliente,
                                i.tel_cliente,
                                i.correo_cliente,
                                i.num_personas,
                                a.max_personas,
                                i.descuento,
                                i.tarifa,
                                i.intermediario_id,
                                i.comentario

                      FROM      ingresos AS i
                      JOIN      apartamento AS a
                            ON  a.id = i.apartamento_id
                      WHERE     i.id = ?";
            $stmt = $mysqli->prepare($query);
            if($stmt) {
                $stmt->bind_param('i', $ingreso_id);
                if($stmt->execute()) {
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();          

                    $mensaje .= '   <div id="formulario_cambios">
                                        <h2>Añadir Ingreso</h2>
                                        <form id="formIngreso" style="display: block;" method="post" action="/php/ingresos/update_ingreso.php">
                                            <label for="apartamento_id">Apartamento:*</label>
                                                <select id="apartamento_id" name="apartamento_id">' .
                                                    listar_apartamentos($mysqli, $row['apartamento_id']) .
                                                '</select>
                                                <br /><br />
                                            <label for="fecha_entrada">Fecha de entrada:*</label>
                                                <br />
                                                <input type="date" class="fecha_formulario" id="fecha_entrada" name="fecha_entrada" value="' . $row['fecha_entrada'] . '" required>
                                                <br /><br />
                                            <label for="fecha_salida">Fecha de salida:*</label>
                                                <br />
                                                <input type="date" class="fecha_formulario" id="fecha_salida" name="fecha_salida" value="' . $row['fecha_salida'] . '" required>
                                                <br /><br />
                                            <label for="nombre_cliente">Nombre del cliente:*</label>
                                                <br />
                                                <input type="text" id="nombre_cliente" name="nombre_cliente" maxlength="255" placeholder="Nombre del cliente" value="' . $row['nombre_cliente'] . '" required>
                                                <br /><br />
                                            <label for="apellidos_cliente">Apellidos del cliente:</label>
                                                <br />    
                                                <input type="text" id="apellidos_cliente" name="apellidos_cliente" maxlength="255" placeholder="Apellidos del cliente" value="' . $row['apellidos_cliente'] . '">
                                                <br /><br />
                                            <label for="nif_cliente">NIF del cliente:*</label>
                                                <br />
                                                <input type="text" id="nif_cliente" name="nif_cliente" maxlength="9" placeholder="NIF del cliente" value="' . $row['nif_cliente'] . '" required>
                                                <br /><br />
                                            <label for="tel_cliente">Teléfono del cliente:*</label>
                                                <br />
                                                <input type="text" id="tel_cliente" name="tel_cliente" maxlength="15" placeholder="Teléfono del cliente" value="' . $row['tel_cliente'] . '" required>
                                                <br /><br />
                                            <label for="correo_cliente">Correo del cliente:*</label>
                                                <br />
                                                <input type="text" id="correo_cliente" name="correo_cliente" maxlength="255" placeholder="Correo del cliente" value="' . $row['correo_cliente'] . '" required>
                                                <br /><br />
                                            <label for="num_clientes">Número de personas en la casa (Hasta: ' . $row['max_personas'] . '*</label>
                                                <br />
                                                <input type="number" step="1" id="num_clientes" name="num_clientes" placeholder="Número de personas" min="1" max="' . $row['max_personas'] . '" value ="1" value="' . $row['num_personas'] . '" required> 
                                                <br /><br />
                                            <label for="descuento">Descuento a aplicar:*</label>
                                                    <br />
                                                <input type="number" step="0.01" id="descuento" name="descuento" placeholder="Descuento" min="0" max="100" value="0" value="' . $row['descuento'] . '" required>% 
                                                <br /><br />
                                            <label for="tarifa_id">Tarifa a aplicar:*</label>
                                                <br />
                                                <select id="tarifa_id" name="tarifa_id">' .
                                                        listar_tarifas($mysqli, $row['tarifa']) .
                                                '</select>
                                                <br /><br />
                                            <label for="intermediario_id">Intermediario:</label>
                                                <br />
                                                <select id="intermediario_id" name="intermediario_id">' .
                                                    listar_intermediarios($mysqli, $row['intermediario_id']) .
                                                '</select>
                                                <br /><br />
                                            <label for="comentario">Comentarios:</label> 
                                                <br />
                                                <textarea id="comentario" name="comentario" maxlength="255" placeholder="Comentario">' . $row['comentario'] . '</textarea> 
                                                <br />
                                                <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                                                <br /><br />
                                            <input type="hidden" id="ingreso_id" name="ingreso_id" value="' . $ingreso_id . '">
                                            <button type="submit">Registrar</button> 
                                        </form>
                                    </div>';
                } else {
                    $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde<br/>";
                    loguear_error("mod_ingreso", $stmt->error);
                }
                $stmt->close();
            } else {
                $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde<br/>";
                loguear_error("mod_ingreso", $mysqli->error);
            }
        }
    } else {
        $mensaje = 'Ha habido un error en el ingreso seleccionado';
    }

    $mysqli->close();
    echo $mensaje;

}
