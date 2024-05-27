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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = "";


    if(isset($_POST['apartamento_id']) && isset($_POST['nombre']) && isset($_POST['direccion']) && isset($_POST['precio_noche']) && isset($_POST['empresa_id']) && isset($_POST['comentario'])) {
        $apartamento_id = filter_input(INPUT_POST, 'apartamento_id', FILTER_VALIDATE_INT);
        $nombre = trim($mysqli->real_escape_string($_POST['nombre']));
        $direccion = trim($mysqli->real_escape_string($_POST['direccion']));
        $precio_noche = abs(filter_input(INPUT_POST, 'precio_noche', FILTER_VALIDATE_FLOAT));
        $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
        $comentario = isset($_POST['comentario']) ? trim($mysqli->real_escape_string($_POST['comentario'])) : "";
        $fecha = date('Y-m-d');
    
        $es_root = $_SESSION['es_root'];
        $es_admin = $_SESSION['es_admin'];
        $empresa_id_user = $_SESSION['empresa_id'];
        $permiso = true;
        $mensaje = "";
    
        //Verificación de permisos
        if(!$_SESSION['es_root']) {
            if(!$_SESSION['es_admin']){
                $mensaje .= "Permisos insuficientes..";
                $permiso = false;
            } elseif($empresa_id_user != $empresa_id){
                    $mensaje .= "Permisos insuficientes.";
                    $permiso = false;
            }
        }

        if($permiso) {
            if(!empty($nombre) && !empty($direccion) && !empty($precio_noche) && verificar_entidad($mysqli, "empresa", $empresa_id)){
                if ((strlen($nombre) < 256) && (strlen($direccion) < 256) && (strlen($comentario) < 256)) {
                    if (is_numeric($precio_noche) && ($precio_noche <= 9999.99) && ($precio_noche > 0)) {
                        $query = "  UPDATE  apartamento
                                    SET     nombre = ?,
                                            direccion = ?,
                                            precio_noche = ?,
                                            empresa_id = ?,
                                            comentario = ?, 
                                            fecha_ultima_modificacion = ?
                                    WHERE   id = ?";
    
                        $stmt = $mysqli->prepare($query);

                        if($stmt) {
                            $stmt->bind_param("ssdissi", $nombre, $direccion, $precio_noche, $empresa_id, $comentario, $fecha, $apartamento_id);
    
                            if ($stmt->execute()) {
                                $mensaje = "Apartamento actualizado con éxito.";
                            } else {
                                $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                                loguear_error("update_apartamento", $stmt->error);
                            }
                        } else {
                            $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                            loguear_error("update_apartamento", $mysqli->error);
                        }
                    } else {
                        $mensaje .= "Se ha producido un error: datos incorrectos.";
                    }
                } else {
                    $mensaje .= "Se ha producido un error: datos incorrectos.";
                }
            } else {
                $mensaje .= "Se ha producido un error: datos incorrectos.";
            }
        }
    } else {
        $mensaje .= "Error en los datos introducidos";
    }
        
    $mysqli->close();
    echo "  <script>
                alert('" . $mensaje . "');
                window.location.href='../../dashboard.php';
            </script>";
}
