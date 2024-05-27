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
require_once '../funciones/verificar.php';      // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = "";

    if(isset($_POST['empresa_id']) && isset($_POST['gasto_id'])) {
        $gasto_id = filter_input(INPUT_POST, 'gasto_id', FILTER_VALIDATE_INT);
        $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);

        if(verificar_entidad($mysqli, "gastos", $gasto_id) && verificar_entidad($mysqli, 'empresa',  $empresa_id)) {
            $gasto_interno = (isset($_POST['gasto_interno']) && $_POST['gasto_interno'] == 0) ? 0 : 1;
            $fecha_gasto = trim($mysqli->real_escape_string($_POST['fecha_gasto']));
            $concepto = (isset($_POST['concepto'])) ? trim($mysqli->real_escape_string($_POST['concepto'])) : "";
            $nif_proveedor = ($gasto_interno == 0) ? "" : strtoupper(trim($mysqli->real_escape_string($_POST['nif_proveedor']))); // Este NIF puede ser el de una sociedad, por lo que no se hará una verificación del mismo a través de verificar_dni()
            $total_gasto = isset($_POST['total_gasto']) ? abs(filter_input(INPUT_POST, 'total_gasto', FILTER_VALIDATE_FLOAT)) : 0;
            $pagado = 0;
        
            $fecha = date('Y-m-d');
          
            $es_root = $_SESSION['es_root'];
            $es_admin = $_SESSION['es_admin'];
            $empresa_id_user = $_SESSION['empresa_id'];
            $permiso = true;
        
            //Verificación de permisos
            if($es_root == 0) {
                if($es_admin == 0){
                    $mensaje .= "Permisos insuficientes..";
                    $permiso = false;
                } else {
                    if($empresa_id_user != $empresa_id){
                        $mensaje .= "Permisos insuficientes.";
                        $permiso = false;
                    }
                }
            }
        
            if($permiso) {
                if ((strlen($concepto) < 256)) {
                    if (is_numeric($total_gasto) && ($total_gasto <= 9999.99)) {
                        $query = "UPDATE    gastos
                                    SET     concepto = ?,
                                            empresa_id = ?,
                                            gasto_interno = ?,
                                            fecha = ?,
                                            nif_proveedor = ?,
                                            total_gasto = ?,
                                            pagado = ?,
                                            fecha_ultima_modificacion = ?
                                    WHERE   id = ?";
        
                        $stmt = $mysqli->prepare($query);

                        if($stmt) {
                            $stmt->bind_param("siissdisi", $concepto, $empresa_id, $gasto_interno, $fecha_gasto, $nif_proveedor, $total_gasto, $pagado, $fecha, $gasto_id);

                            if ($stmt->execute()) {
                                $mensaje = "Gasto modificado con éxito.";
                            } else {
                                $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                                loguear_error("update_gasto", $stmt->error);
                            }
                        } else {
                            $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                                loguear_error("update_gasto", $mysqli->error);
                        }
                    } else {
                        $mensaje = "Se ha producido un error: datos incorrectos";
                    }
                } else {
                    $mensaje = "Se ha producido un error: datos incorrectos";
                }
            } else {
                $mensaje = "Se ha producido un error: datos incorrectos";
            }
        } else {
            $mensaje = "Error en los datos introducidos";
        }
    } else {
        $mensaje = "Error en los datos introducidos";
    }

    $mysqli->close();
    echo "  <script>
                alert('" . $mensaje . "');
                window.location.href='../../dashboard.php';
            </script>";
}
