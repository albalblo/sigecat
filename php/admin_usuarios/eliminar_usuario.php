<?php
/**************************************************************************************************
 * Proyecto de Fin de Ciclo Formativo de Grado Superior                                           *
 * 'Software de Gestión Económica Alquileres Turísticos' (SIGEcAT)                                *
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = '';

    if(isset($_POST['id_usuario'])) {
        $usuario_id = $mysqli->real_escape_string($_POST['id_usuario']);
        $empresa_id = $_SESSION['empresa_id'];

        $continuar = true;

        if($_SESSION['usuario'] == $usuario_id) {
          $mensaje .= "No se puede eliminar el propio usuario.";
          $continuar = false;
        } else {
            if ($_SESSION['es_root'] == 0) {
                if ($_SESSION['es_admin'] == 1) {
                    $query_confirmar_usuario = "SELECT  empresa_id
                                                FROM    usuario
                                                WHERE   dni = ?";
                    $stmt_confirmar_usuario = $mysqli->prepare($query_confirmar_usuario);
            
                    if ($stmt_confirmar_usuario) {
                        $stmt_confirmar_usuario->bind_param("s", $usuario_id);

                        if($stmt_confirmar_usuario->execute()) {
                            $resultado_confirmacion = $stmt_confirmar_usuario->get_result();
            
                            if ($resultado_confirmacion->num_rows > 0) {
                                $row_confirmacion = $resultado_confirmacion->fetch_assoc();
                                if ($row_confirmacion['empresa_id'] != $empresa_id) {
                                    $continuar = false;
                                    $mensaje .= "Permisos insuficientes..";
                                }
                            } else {
                                $continuar = false;
                                $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                                loguear_error("eliminar_usuario", $stmt_confirmar_usuario->error);
                            }
                            $stmt_confirmar_usuario->close();
                        } else {
                            $mensaje .= "Error en el servidor, inténtelo de nuevo más tarde.";
                            $continuar = false;
                        }

                        
                    } else {
                        $continuar = false;
                        $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                        loguear_error("eliminar_usuario", $mysqli->error);
                    }
                } else {
                    $continuar = false;
                    $mensaje .= "Permisos insuficientes..";
                }
            }
        }
        if ($continuar) {
            $query_delete = "   DELETE FROM usuario
                                WHERE dni = ?";
    
            $stmt_delete = $mysqli->prepare($query_delete);
            if ($stmt_delete) {
                $stmt_delete->bind_param("s", $usuario_id);
                if($stmt_delete->execute()) {
                    $mensaje = "Usuario eliminado correctamente.";
                } else {
                    $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                    loguear_error("eliminar_usuario", $stmt_delete->error);
                }
                $stmt_delete->close();
            } else {
                $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
                loguear_error("eliminar_usuario", $mysqli->error);
            }
        }
    } else {
        $mensaje .= "Error al seleccionar el usuario..";
    }
    $mysqli->close();

    echo '  <script>
                alert("' . $mensaje . '");
                window.location.href="../../dashboard.php";
            </script>';
}
