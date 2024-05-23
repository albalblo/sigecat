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

require_once '../funciones/con_db.php';     // Conexión con la base de datos
require_once '../funciones/config.php';     // Configuración de la página y verificación de sesión
require_once '../funciones/listar.php';     // Funciones de visualización
require_once '../funciones/verificar.php';  // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = "";

    if(isset($_POST['fecha_gasto']) && isset($_POST['empresa_id'])){
        $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
        if(verificar_entidad($mysqli, "empresa", $empresa_id)) {
            $fecha_gasto = trim($mysqli->real_escape_string($_POST['fecha_gasto']));
            $concepto = trim($mysqli->real_escape_string($_POST['concepto']));
            $gasto_interno = isset($_POST['gasto_interno']) ? 1 : 0;
            $nif_proveedor = trim($mysqli->real_escape_string($_POST['nif_proveedor']));
            $gasto_sin_iva = isset($_POST['gasto_sin_iva']) ? abs(filter_input(INPUT_POST, 'gasto_sin_iva', FILTER_VALIDATE_FLOAT)) : 0;
            $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
            $pagado = isset($_POST['pagado']) ? 1 : 0;
            $fecha = date('Y-m-d');
        
            if((is_numeric($gasto_sin_iva)) && ($gasto_sin_iva >= 0) && ($gasto_sin_iva <= 9999999.99)) {
                if ($gasto_interno == 0) {
                    $query = "INSERT INTO gastos (empresa_id, gasto_interno, fecha, nif_proveedor, total_gasto, pagado, fecha_creacion, fecha_ultima_modificacion, concepto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = "iissdisss";
                    $campos = [$empresa_id, $gasto_interno, $fecha_gasto, $nif_proveedor, $gasto_sin_iva, $pagado, $fecha, $fecha, $concepto];
        
                } else {
                    $query = "INSERT INTO gastos (empresa_id, gasto_interno, fecha, total_gasto, pagado, fecha_creacion, fecha_ultima_modificacion, concepto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = "iisdisss";
                    $campos = [$empresa_id, $gasto_interno, $fecha_gasto, $gasto_sin_iva, $pagado, $fecha, $fecha, $concepto];
                }

                $stmt = $mysqli->prepare($query);

                if($stmt) {
                    $stmt->bind_param($params, ...$campos);
                    if($stmt->execute()) {
                        $mensaje = "Nuevo gasto registrado con éxito.";
                    } else {
                        $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde";
                        loguear_error("insert_gastos", $stmt->error);
                    }
                    $stmt->close();
                    } else {
                        $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde";
                        loguear_error("insert_gastos", $mysqli->error);
                    }
                } else {
                    $mensaje = "Error en los datos introducidos";
                }
        } else {
            $mensaje = "Error en la empresa introducida";
        }
    } else {
        $mensaje = "Error en los datos introducidos";
    }

    echo '  <script>
                alert("'.$mensaje.'");
                window.location.href="../../dashboard.php";
            </script>';
        $mysqli->close();
}
