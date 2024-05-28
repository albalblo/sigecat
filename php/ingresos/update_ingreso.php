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
require_once '../funciones/log_errores.php';    // Configuración de la página y verificación de sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = "";

    //$continuar = (!empty($nombre_cliente) && !empty($nif_cliente) && !empty($tel_cliente) && !empty($correo_cliente) && !empty($num_clientes) && !empty($descuento) && !empty($tarifa_id) && !empty($intermediario_id));

    $continuar = true;

    if(isset($_POST['nombre_cliente']) && isset($_POST['nif_cliente']) && isset($_POST['tel_cliente']) && isset($_POST['correo_cliente']) && isset($_POST['num_clientes']) && isset($_POST['tarifa_id']) && isset($_POST['intermediario_id']) && isset($_POST['fecha_entrada']) && isset($_POST['fecha_salida'])) {
        $ingreso_id = filter_input(INPUT_POST, 'ingreso_id', FILTER_VALIDATE_INT);
        $apartamento_id = filter_input(INPUT_POST, 'apartamento_id', FILTER_VALIDATE_INT);
        $fecha_entrada = trim($mysqli->real_escape_string($_POST['fecha_entrada']));
        $fecha_salida = trim($mysqli->real_escape_string($_POST['fecha_salida']));
        $nombre_cliente = trim($mysqli->real_escape_string($_POST['nombre_cliente']));
        $apellidos_cliente= isset($_POST['apellidos_cliente']) ? trim($mysqli->real_escape_string($_POST['apellidos_cliente'])) : "";
        $nif_cliente = trim($mysqli->real_escape_string($_POST['nif_cliente']));
        $tel_cliente = trim($mysqli->real_escape_string($_POST['tel_cliente']));
        $correo_cliente = trim(filter_input(INPUT_POST, 'correo_cliente', FIILTER_SANITIZE_EMAIL));
        $correo_cliente = filter_var($correo_cliente, FILTER_VALIDATE_EMAIL);    $num_personas = filter_input(INPUT_POST, 'num_clientes', FILTER_VALIDATE_INT);
        $num_clientes = filter_input(INPUT_POST, 'num_clientes', FILTER_VALIDATE_INT);
        $descuento = isset($_POST['descuento']) ? filter_input(INPUT_POST, 'descuento', FILTER_VALIDATE_FLOAT) : 0;
        $tarifa_id = filter_input(INPUT_POST, 'tarifa_id', FILTER_VALIDATE_INT);
        $intermediario_id = $_POST['intermediario_id'] != 0 ? trim($mysqli->real_escape_string($_POST['intermediario_id'])) : "000000000";
        $comentario = trim($mysqli->real_escape_string($_POST['comentario']));
        $fecha = date('Y-m-d');


        /*$mensaje .= verificar_dni($nif_cliente) ? "dni ok\n" : "dni not ok.";
        $mensaje .= verificar_entidad($mysqli, "ingresos", $ingreso_id) ? "ingresok\n" : "ingresonotok.";
        $mensaje .= verificar_entidad($mysqli, "apartamento", $apartamento_id) ? "apartamentok\n" : "apartamentonok.";
        $mensaje .= verificar_entidad($mysqli, "tarifas", $tarifa_id) ? "tarifa ok\n" : "tarifa not ok.";*/

        if(verificar_dni($nif_cliente) && verificar_entidad($mysqli, "ingresos", $ingreso_id) && verificar_entidad($mysqli, "apartamento", $apartamento_id)  && verificar_entidad($mysqli, "tarifas", $tarifa_id)) {
            if(($fecha_entrada < $fecha_salida) && verificar_apartamento_libre($mysqli, $apartamento_id, $fecha_entrada, $fecha_salida, $ingreso_id)) {
                $query = "  SELECT  empresa_id
                            FROM    apartamento
                            WHERE   id = ?";
                $stmt = $mysqli->prepare($query);
                if($stmt) {
                    $stmt->bind_param("i", $apartamento_id);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if($result->num_rows == 0) {
                            $continuar = false;
                            $mensaje = "Error al recuperar los datos de la reserva";
                        } else {
                            $row = $result->fetch_assoc();
                            $empresa_id = $row['empresa_id'];
                        }
                    } else {
                        $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde";
                        loguear_error("update_ingreso", $stmt->error);
                        $continuar = false;
                    }
                    $stmt->close();

                    if(!$_SESSION['es_root']) {
                        if($_SESSION['es_admin']) {
                            if($row['empresa_id'] != $_SESSION['empresa_id']) {
                                $continuar = false;
                                $mensaje .= " Permisos insuficientes.";
                            }
                        } else {
                            $continuar = false;
                            $mensaje .= " Permisos insuficientes.";
                        }
                    }

                    if(!verificar_intermediario($mysqli, $empresa_id, $intermediario_id)) {
                        $continuar = false;
                        $mensaje .= "Error en el intermediario";
                    }

                    if(!($correo_cliente === false)) {
                        $continuar = false;
                    }

                    if(!verificar_numero_personas($mysqli, $apartamento_id, $num_clientes)) {
                        $continuar = false;
                    }

                    if($continuar) {
                        $query = "  UPDATE  ingresos
                                    SET     apartamento_id = ?,
                                            fecha_entrada = ?,
                                            fecha_salida = ?,
                                            nombre_cliente = ?,
                                            apellidos_cliente = ?,
                                            nif_cliente = ?,
                                            tel_cliente = ?,
                                            correo_cliente = ?,
                                            num_personas = ?,
                                            descuento = ?,
                                            tarifa = ?,
                                            empresa_id = ?,
                                            intermediario_id = ?,
                                            comentario = ?,
                                            fecha_ultima_modificacion = ?
                                WHERE     id = ?";
                        $stmt = $mysqli->prepare($query);
                        if($stmt) {
                            $stmt->bind_param('isssssssidiisssi', $apartamento_id, $fecha_entrada, $fecha_salida, $nombre_cliente, $apellidos_cliente, $nif_cliente, $tel_cliente, $correo_cliente, $num_clientes, $descuento, $tarifa_id, $empresa_id, $intermediario_id, $comentario, $fecha, $ingreso_id);
                            if ($stmt->execute()) {
                                $mensaje = "Ingreso modificado con éxito.";
                            } else {
                                $mensaje = "Ha habido un error, inténtelo de nuevo más tarde.";
                                loguear_error("update_gasto", $stmt->error);
                            }
                        } else {
                            $mensaje .= "Error interno del servidor, inténtelo de nuevo más tarde";
                            loguear_error("update_ingreso", $mysqli->error);
                        }
                    }
                } else {
                    $mensaje = "Error interno del servidor, inténtelo de nuevo más tarde";
                    loguear_error("update_ingreso", $mysqli->error);
                }
            } else {
                $mensaje .= "Error en los datos introducidos";
            }
        } else {
            $mensaje .= "Error en los datos introducidos";
        }
    } else {
        $mensaje .= "Error en los datos introducidos";
    }

    $mensaje = addslashes($mensaje);

    $mysqli->close();
    echo "  <script>
                alert('" . $mensaje . "');
                window.location.href='../../dashboard.php';
            </script>";

}
