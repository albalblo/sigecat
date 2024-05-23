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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = '';
    $continuar = true;

    if($_SESSION['es_admin'] == 1){
        if ($_SESSION['es_root'] == 0) {
            $query_confirmacion = " SELECT  empresa_id
                                    FROM    usuario
                                    WHERE   dni = ?";
            $stmt_confirmacion = $mysqli->prepare($query_confirmacion);
            if($stmt_confirmacion) {
                $stmt_confirmacion->bind_param("s", $_SESSION['usuario']);
                if($stmt_confirmacion->execute()) {
                    $result_confirmacion = $stmt_confirmacion->get_result();
                    if($result_confirmacion->num_rows > 0) {
                        $row_confirmacion = $result_confirmacion->fetch_assoc();
                        if($row_confirmacion['empresa_id'] != $_SESSION['empresa_id']) {
                            $continuar = false;
                            $mensaje .= "Permisos insuficientes.";
                        }
                    } else {
                        $continuar = false;
                        $mensaje .= "Permisos insuficientes.";
                    }
                } else {
                    $continuar = false;
                    $mensaje .= "Fallo en el servidor.";
                    loguear_error("insert_usuario", $stmt_confirmacion->error);
                }
                $stmt_confirmacion->close();
            } else {
                $continuar = false;
                $mensaje .= "Fallo en el servidor.";
                loguear_error("insert_usuario", $mysqli->error);
            }
        }
    } else {
        $continuar = false;
        $mensaje .= "Permisos insuficientes.";
    }

    if($continuar) {
        if(isset($_POST['nif'])) {
            $nif = trim(strtoupper($mysqli->real_escape_string($_POST['nif'])));

            if(verificar_dni($nif)) {
                if(verificar_entidad($mysqli, "usuario", $nif)){ // El NIF debe ser correcto, y no debe pertenecer a ningún usuario ya registrado
                    $continuar = false;
                    $mensaje .= "Atención: usuario ya registrado.";
                }
            } else {
                $continuar = false;
                $mensaje .= "Formato del DNI incorrecto.";
            }
        }        
    }

    if($continuar) {
        if(isset($_POST['nombre']) && isset($_POST['empresa_id']) && isset($_POST['password']) && isset($_POST['password_confir'])) { // Ya se ha verificado que el NIF se introdujo y es correcto
            $nombre = trim($mysqli->real_escape_string($_POST['nombre']));
            $apellidos = isset($_POST['apellidos']) ? trim($mysqli->real_escape_string($_POST['apellidos'])) : '';
            $password = $mysqli->real_escape_string($_POST['password']);
            $password_confirmacion = $mysqli->real_escape_string($_POST['password_confir']);
            $empresa_id = intval($_POST['empresa_id']);
            $es_admin = isset($_POST['es_admin']) && $_POST['es_admin'] == '1' ? 1 : 0;
            $fecha = date('Y-m-d');
        }

        if(verificar_entidad($mysqli, "empresa", $empresa_id)) {
            if($password === $password_confirmacion) {
                if((strlen($nombre) < 265) && (strlen($apellidos)) < 256 && (strlen($password) < 21)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
                    $query = "INSERT INTO usuario  (dni,
                                                    nombre,
                                                    apellidos,
                                                    pass,
                                                    empresa_id,
                                                    es_admin,
                                                    es_root,
                                                    fecha_creacion,
                                                    fecha_ultima_modificacion)
                            VALUES               (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
        
                    if($stmt) {
                        $es_root = 0; // MySQL da un error si se le pasa un valor directamente. No se contempla la opción de crear usuarios root a través de este formulario
                        $stmt->bind_param("ssssiiiss", $nif, $nombre, $apellidos, $hashedPassword, $empresa_id, $es_admin, $es_root, $fecha, $fecha);
                    
                        if ($stmt->execute()) {
                            $mensaje = "Usuario registrado con éxito.";
                        } else {
                            $mensaje .=  "Error del servidor, inténtelo de nuevo más tarde.";
                            loguear_error("insert_usuario", $stmt->error);
                        }
                        
                        $stmt->close();
                    } else {
                        $mensaje .= "Error del servidor, inténtelo de nuevo más tarde.";
                        loguear_error("insert_usuario", $mysqli->error);
                    }
                } else {
                    $mensaje .= "Error en los datos introducidos.";
                }
            } else {
                $mensaje .= "Contraseña incorrecta.";
            }
        } else {
            $mensaje .= "Error en los datos introducidos: Empresa.";
        }
    }

    $mysqli->close();
    echo '<script>
            alert("'.$mensaje.'");
            window.location.href="../../dashboard.php";
          </script>';
}
