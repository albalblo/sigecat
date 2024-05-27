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

require_once '../funciones/con_db.php';     // Conexión con la base de datos
require_once '../funciones/config.php';     // Configuración de la página y verificación de sesión
require_once '../funciones/verificar.php';  // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $mensaje = "";
    $continuar = true;

    if(isset($_POST['nombre']) && isset($_POST['nif']) && isset($_POST['empresa_id']) && isset($_POST['es_admin'])) {
        $nombreNuevo = trim($mysqli->real_escape_string($_POST['nombre']));
        $apellidosNuevo = trim($mysqli->real_escape_string($_POST['apellidos']));
        $dni = trim(strtoupper($mysqli->real_escape_string($_POST['nif'])));
        $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
        $es_admin = (isset($_POST['es_admin']) && $_POST['es_admin'] == 1) ? 1 : 0;
        $fecha = $fecha = date('Y-m-d');
    
        if(verificar_dni($dni) && verificar_entidad($mysqli, "empresa", $empresa_id)) {
            $query = "  SELECT  nombre,
                                empresa_id
                        FROM    usuario
                        WHERE   dni = ?";
    
            $stmt = $mysqli->prepare($query);
            if($stmt) {
                $stmt->bind_param("s", $dni);
                if($stmt->execute()) {
                    $result = $stmt->get_result();
            
                    if($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        if($_SESSION['es_root']) {
                            $continuar = true;
                        } elseif($_SESSION['es_admin']) {
                            if($_SESSION['empresa_id'] == $row['empresa_id']) {
                                $continuar = true;
                            } else {
                                $continuar = false;
                                $mensaje .= "Permisos insuficientes.";
                            }
                        } else {
                            $continuar = false;
                            $mensaje .= "Permisos insuficientes.";
                        }
                    } else {
                        $continuar = false;
                        $mensaje .= "Error al seleccionar el usuario.";
                    }
                } else {
                    $mensaje .= "Ha habido un error al verificar los datos.";
                    $continuar = false;
                    loguear_error("update_usuario", $stmt->error);
                }
                $stmt->close();
               
            } else {
                $continuar = false;
                $mensaje .= "Ha habido un error al verificar los datos.";
                loguear_error("update_usuario", $mysqli->error);
            }
        } else {
            $continuar = false;
            $mensaje .= "Error en la verificación de datos.";
        }

        if($dni == $_SESSION['usuario']) {
            $continuar = false;
            $mensaje .= "Edite su usuario desde su panel de usuario.";
        }

        if(strlen($nombreNuevo) > 256 || strlen($apellidosNuevo) > 256) {
            $continuar = false;
            $mensaje .= "Nombre del usuario demasiado largo.";
        }
        
        if($continuar) {
            $update = " UPDATE  usuario
                        SET     nombre = ?,
                                apellidos = ?,
                                empresa_id = ?,
                                es_admin = ?,
                                fecha_ultima_modificacion = ?
                        WHERE   dni = ?";
            $stmt_update = $mysqli->prepare($update);
            if($stmt_update) {
                $stmt_update->bind_param("ssiiss", $nombreNuevo, $apellidosNuevo, $empresa_id, $es_admin, $fecha, $dni);
                if($stmt_update->execute()) {
                    $mensaje = "Usuario actualizado con éxito.";
                } else {
                    $mensaje .= "Ha habido un error. Inténtelo de nuevo más tarde.";
                    loguear_error("update_usuario", $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                $mensaje .= "Error al actualizar los datos del usuario.";
                loguear_error("update_usuario", $mysqli->error);
            }
        }
    } else {
        $mensaje .= "Parámetros incorrectos";
    }

    $mysqli->close();
    echo "  <script>
                alert('" . $mensaje . "');
                window.location.href='../../dashboard.php';
            </script>";
}
