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
    if(isset($_POST['gasto_id'])) {
        $gasto_id = filter_input(INPUT_POST, 'gasto_id', FILTER_VALIDATE_INT);
        if(verificar_entidad($mysqli, "gastos", $gasto_id)) {
            $es_root = $_SESSION['es_root'];
            $es_admin = $_SESSION['es_admin'];
            $empresa_id = $_SESSION['empresa_id'];
            $permiso = true;
            $mensaje = "";
        
            //Verificación de permisos
            if(!$es_root) {
                if(!$es_admin){
                    $mensaje .= "Permisos insuficientes..";
                    $permiso = false;
                } else {
                    $query_prueba = "SELECT empresa_id
                                     FROM   gastos
                                     WHERE  id = ?";
                    $stmt_prueba = $mysqli->prepare($query_prueba);

                    if($stmt_prueba) {
                        $stmt_prueba->bind_param('i', $gasto_id);
                        if($stmt_prueba->execute()) {
                            $result_prueba = $stmt_prueba->get_result();
                            $empresa_id_db = $result_prueba->fetch_assoc();
                            if($empresa_id_db == NULL){
                                $mensaje = "Ha habido un error";
                                $permiso = false;
                            } else if($empresa_id != $empresa_id_db['empresa_id']) {
                                $mensaje = "Permisos insuficientes";
                                $permiso = false;
                            }
                        } else {
                            $mensaje .= "Ha habido un error.";
                            $permiso = false;
                        }
                        $stmt_prueba->close();
                    } else {
                        $mensaje .= "Ha habido un error.";
                        $permiso = false;
                    }
                }
            }
        
            if($permiso) {
                $query = "  SELECT  concepto,
                                    fecha,
                                    empresa_id,
                                    gasto_interno,
                                    nif_proveedor,
                                    total_gasto,
                                    pagado
                            FROM    gastos
                            WHERE   id = ?";
                $stmt = $mysqli->prepare($query);
                if($stmt) {
                    $stmt->bind_param('i', $gasto_id);
                    if($stmt->execute()) {
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $gasto_interno_checkbox = $row['gasto_interno'] == 0 ? 'checked' : '';
                        $pagado_checkbox = $row['pagado'] == 1 ? 'checked' : '';
                        $concepto_muestra = empty($row["concepto"]) ? "" : htmlspecialchars($row["concepto"]);
                        $proveedor_muestra = empty($row["nif_proveedor"]) ? "" : htmlspecialchars($row["nif_proveedor"]);
                        $fecha_gasto = $row['fecha'];

                        $mensaje = '    <div id="formulario_cambios">
                                            <h2>Modificar Gasto</h2>  
                                            <form id="updateGastoForm" method="post" action="/php/gastos/update_gasto.php">
                                                <label for="concepto">Concepto:*</label>
                                                    <input type="text" id="concepto" name="concepto" value="' . $concepto_muestra . '" placeholder="Concepto" required>
                                                    <br /><br/>
                                                <label for="fecha_gasto">Fecha del gasto:*</label>
                                                    <input type="date" id="fecha_gasto" name="fecha_gasto" value="' . $fecha_gasto . '" required>
                                                    <br /><br/>
                                                <label for="total_gasto">Total del gasto sin IVA:*</label>
                                                    <input type="number" step="0.01" id="total_gasto" name="total_gasto" value="' . htmlspecialchars($row["total_gasto"]) . '" placeholder="Gasto sin IVA" min="0" max="9999.99" required>
                                                    <br /><br/>
                                                <label for="gasto_interno">Gasto interno:</label>
                                                    <input type="checkbox" id="gasto_interno" name="gasto_interno" value="1" ' . $gasto_interno_checkbox . ' />
                                                    <br /><br />
                                                <label for="nif_proveedor">NIF del proveedor:</label>
                                                    <input type="text" id="nif_proveedor" name="nif_proveedor" pattern="[0-9A-Za-z][0-9]{7}[0-9A-Za-z]" value="' . $proveedor_muestra . '" placeholder="NIF del proveedor" />
                                                    <br /><br/>
                                                <label for="pagado">Gasto pagado:</label>
                                                    <input type="checkbox" id="pagado" name="pagado" value="1" ' . $pagado_checkbox . ' />
                                                    <br /><br />
                                                <label for="empresa_id">Empresa:*</label>
                                                    <select id="empresa_id" name="empresa_id">' . listar_empresas($mysqli) . '</select>
                                                    <br />
                                                    <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                                                    <br /><br />
                                                <input type="hidden" name="gasto_id" value="' . $gasto_id . '">
                                                <button type="submit">Modificar</button>
                                            </form>
                                        </div>';
                    } else {
                        $mensaje .= "Ha habido un error.";
                        loguear_error("mod_gasto", $stmt->error());
                    }
                } else {
                    $mensaje .= "Ha habido un error.";
                    loguear_error("mod_gasto", $mysqli->error());
                }
            }
        } else {
            $mensaje = "Error en el gasto seleccionado, inténtelo de nuevo más tarde";
        }
    } else {
        $mensaje = 'Error en el gasto seleccionado';
    }

    echo $mensaje;
    $mysqli->close();

}
