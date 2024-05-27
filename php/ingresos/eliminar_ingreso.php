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
 require_once '../funciones/listar.php';     // Funciones de visualización
 require_once '../funciones/verificar.php';  // Funciones de verificación
 require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingreso_id = filter_input(INPUT_POST, 'id_ingreso', FILTER_VALIDATE_INT);
    $empresa_id = $_SESSION['empresa_id'];

    $continuar = false;
    $mensaje = "";

    if ($_SESSION['es_root'] == 1) {
        $continuar = true;
    } elseif ($_SESSION['es_admin'] == 1) {
        $query_confirmar_ingreso = "SELECT empresa_id FROM ingresos WHERE id = ?";
        $stmt_confirmar_ingreso = $mysqli->prepare($query_confirmar_ingreso);
        if ($stmt_confirmar_ingreso) {
            $stmt_confirmar_ingreso->bind_param("i", $ingreso_id);
            $stmt_confirmar_ingreso->execute();
            $resultado_confirmacion = $stmt_confirmar_ingreso->get_result();
            if ($resultado_confirmacion->num_rows > 0) {
                $row_confirmacion = $resultado_confirmacion->fetch_assoc();
                if ($row_confirmacion['empresa_id'] == $empresa_id) {
                    $continuar = true;
                } else {
                    $mensaje = "Permisos insuficientes.";
                }
            } else {
                $mensaje = "Ha habido un error, inténtelo de nuevo más tarde.";
                loguear_error("eliminar_ingreso", $mysqli->error);
            }
            $stmt_confirmar_ingreso->close();
        } else {
            $mensaje = "Ha habido un error, inténtelo de nuevo más tarde.";
            loguear_error("eliminar_ingreso", $mysqli->error);

        }
    } else {
        $mensaje = "Permisos insuficientes.";
    }

    if ($continuar) {
        $query_delete = "DELETE FROM ingresos WHERE id = ?";
        $stmt_delete = $mysqli->prepare($query_delete);
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $ingreso_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            $mensaje = "Ingreso eliminado correctamente.";
        } else {
            $mensaje = "Ha habido un error, inténtelo de nuevo más tarde.";
            loguear_error("eliminar_ingreso", $mysqli->error);
        }
    }
    echo $mensaje;
}

?>
