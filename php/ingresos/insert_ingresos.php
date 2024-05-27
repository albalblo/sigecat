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

function apartamento_libre($mysqli, $apartamento, $fentrada, $fsalida) {
    $libre = true;

    $query_apartamentos = ' SELECT  fecha_entrada,
                                    fecha_salida
                            FROM ingresos
                            WHERE apartamento_id = ?';
    $stmt_apartamentos = $mysqli->prepare($query_apartamentos);
    $stmt_apartamentos->bind_param('i', $apartamento);
    $stmt_apartamentos->execute();
    $result_apartamentos = $stmt_apartamentos->get_result();

    if($result_apartamentos->num_rows > 0) {
        while ($row_apartamentos = $result_apartamentos->fetch_assoc()) {

            $fecha_entrada_ok = (($fentrada < $row_apartamentos['fecha_entrada']) && ($fsalida < $row_apartamentos['fecha_salida']));
            $fecha_salida_ok = (($fentrada > $row_apartamentos['fecha_entrada']) && ($fsalida > $row_apartamentos['fecha_salida']));

            if(!((!$fecha_entrada_ok && $fecha_salida_ok) || ($fecha_entrada_ok && !$fecha_salida_ok))) {
                $libre = false;
            }
        }
    }
    return $libre;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = "";

    // Sanitización y validación de entradas
    $apartamento_id = filter_input(INPUT_POST, 'apartamento_id', FILTER_VALIDATE_INT);
    $fecha_entrada = trim($mysqli->real_escape_string($_POST['fecha_entrada']));
    $fecha_salida = trim($mysqli->real_escape_string($_POST['fecha_salida']));
    $nombre_cliente = trim($mysqli->real_escape_string($_POST['nombre_cliente']));
    $apellidos_cliente = trim($mysqli->real_escape_string($_POST['apellidos_cliente']));
    $nif_cliente = trim($mysqli->real_escape_string($_POST['nif_cliente']));
    $tel_cliente = trim($mysqli->real_escape_string($_POST['tel_cliente']));
    $correo_cliente = trim(filter_input(INPUT_POST, 'correo_cliente', FIILTER_SANITIZE_EMAIL));
    $correo_cliente = filter_var($correo_cliente, FILTER_VALIDATE_EMAIL);    $num_personas = filter_input(INPUT_POST, 'num_clientes', FILTER_VALIDATE_INT);
    $descuento = abs(filter_input(INPUT_POST, 'descuento', FILTER_VALIDATE_FLOAT));
    $tarifa_id = filter_input(INPUT_POST, 'tarifa_id', FILTER_VALIDATE_INT);
    $intermediario_id = $_POST['intermediario_id'] != 0 ? trim($mysqli->real_escape_string($_POST['intermediario_id'])) : "000000000";
    $comentario = trim($mysqli->real_escape_string($_POST['comentario']));
    $comentario = empty($comentario) ? "" : $comentario;
    $fecha = date('Y-m-d');

    if($fecha_salida <= $fecha_entrada) {
        $mensaje = "Formato de fechas incorrecto";
            echo '<div id="cuerpo"><script>alert("'.$mensaje.'"); window.location.href="../../dashboard.php";</script></div>';
    }

    $continuar = false;

    // Verificar apartamento
    $query_empresa = '  SELECT  empresa_id
                        FROM    apartamento
                        WHERE id = ?';
    $stmt_empresa = $mysqli->prepare($query_empresa);
    $stmt_empresa->bind_param('i', $apartamento_id);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();
    $row_empresa = $result_empresa->fetch_assoc();
    $empresa_id = $row_empresa['empresa_id'];
    $stmt_empresa->close();


    
    // Verificar intermediario
    if ($intermediario_id != "0") {
        $query_intermediario = 'SELECT  intermediario_id
                                FROM    intermediarios
                                WHERE empresa_id = ?';
        $stmt_intermediario = $mysqli->prepare($query_intermediario);
        $stmt_intermediario->bind_param('i', $empresa_id);
        $stmt_intermediario->execute();
        $resultado_confirmacion = $stmt_intermediario->get_result();

        if ($resultado_confirmacion->num_rows > 0) {
            while ($row = $resultado_confirmacion->fetch_assoc()) {
                if ($row['intermediario_id'] == $intermediario_id) {
                    $intermediario_registrado = true;
                    $continuar = true;
                    break;
                }
            }
        } else {
            $intermediario_registrado = false;
        }
       
        $stmt_intermediario->close();
    } else {
        $intermediario_registrado = true;
        $continuar = true;
    }

    // Si el intermediario no está registrado como un intermediario de la empresa, debe añadirse
    if (!$intermediario_registrado && (($_SESSION['es_admin'] == 1) || ($_SESSION['es_root'] == 1))) {
        $nombre_intermediario_query = ' SELECT DISTINCT nombre_intermediario
                                        FROM            intermediarios
                                        WHERE           intermediario_id = ?';
        $nombre_intermediario_stmt = $mysqli->prepare($nombre_intermediario_query);
        $nombre_intermediario_stmt->bind_param('s', $intermediario_id);
        $nombre_intermediario_stmt->execute();
        $nombre_intermediario_result = $nombre_intermediario_stmt->get_result();
        $nombre_intermediario_row =  $nombre_intermediario_result->fetch_assoc();
        $nombre_intermediario = $nombre_intermediario_row['nombre_intermediario'];
        $nombre_intermediario_stmt->close();

        $query_nuevo_intermediario = 'INSERT INTO intermediarios   (empresa_id,
                                                                    intermediario_id,
                                                                    nombre_intermediario)
                                      VALUES                       (?, ?, ?)';
        $stmt_nuevo_intermediario = $mysqli->prepare($query_nuevo_intermediario);
        $stmt_nuevo_intermediario->bind_param('iss', $empresa_id, $intermediario_id, $nombre_intermediario);
        $stmt_nuevo_intermediario->execute();
        $stmt_nuevo_intermediario->close();
        $continuar = true;

    } else if (!$intermediario_registrado) {
        $mensaje = "Permisos insuficientes";
    }

    if(!($correo_cliente === false)) {
        $continuar = false;
    }

    if ($continuar) {

        if(apartamento_libre($mysqli, $apartamento_id, $fecha_entrada, $fecha_salida)) {
            $query = 'INSERT INTO ingresos (apartamento_id,
                                            fecha_entrada,
                                            fecha_salida,
                                            nombre_cliente,
                                            apellidos_cliente,
                                            nif_cliente,
                                            tel_cliente,
                                            correo_cliente,
                                            num_personas,
                                            descuento,
                                            tarifa,
                                            empresa_id,
                                            intermediario_id,
                                            comentario,
                                            fecha_creacion,
                                            fecha_ultima_modificacion)
                     VALUES           (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $mysqli->prepare($query);

            if($stmt) {
                $stmt->bind_param("issssssssidissss", $apartamento_id, $fecha_entrada, $fecha_salida, $nombre_cliente, $apellidos_cliente, $nif_cliente, $tel_cliente, $correo_cliente, $num_personas, $descuento, $tarifa_id, $empresa_id, $intermediario_id, $comentario, $fecha, $fecha);
            
                if ($stmt->execute()) {
                    $mensaje = "Nuevo ingreso registrado con éxito.";
                } else {
                    $mensaje = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $mensaje = "Error";
            }

        } else {
            $mensaje = "El apartamento ya está reservado";
        }
    }

    echo '<script>alert("'.$mensaje.'"); window.location.href="../../dashboard.php";</script>';
}

//header("Location: ../../dashboard.php");
?>
