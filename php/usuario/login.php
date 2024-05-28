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


/**************************************************************************************************
 * Para la evaluación del proyecto, se puede acceder a la aplicación mediante tres usuarios:      *
 *                                                                                                *
 * ╔══════════════════════════╦══════════════╦═════════════╗                                      *
 * ║ Tipo de usuario          ║   Usuario    ║ Contraseña  ║                                      *
 * ╠══════════════════════════╬══════════════╬═════════════╣                                      *
 * ║ Usuario raíz             │  00000000T   │    root     ║                                      *
 * ╟──────────────────────────┼──────────────┼─────────────╢                                      *
 * ║ Usuario administrador    │  11111111H   │    admin    ║                                      *
 * ╟──────────────────────────┼──────────────┼─────────────╢                                      *
 * ║ Usuario normal           │  22222222J   │    user     ║                                      *
 * ╚══════════════════════════╧══════════════╧═════════════╝                                      *
 *                                                                                                *
 **************************************************************************************************/

require_once '../funciones/con_db.php';      // Conexión con la base de datos

session_start();

function login_incorrecto($statement = null) {
    header("Location: ../../index.php?login=failed"); // Vuelta a index.php pero con una 'flag' para que muestre el mensaje de error
    if($statement) {
        $statement->close();
    }
    exit(0);
}

function loguear_error($loc, $err) { // La función normal requiere de una sesión activa, por lo que se redefinr una aquí para antes del log-in
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $mensaje_error = "[" . $fecha . "][" . $hora . "] - Error en el " . $loc . " desde [" . $ip . "]: " . $err . ".";

    $logFile = '../../logs/error.log';
    file_put_contents($logFile, $mensaje_error, FILE_APPEND | LOCK_EX);
}


if (isset($_SESSION['usuario'])) { // Si el usuario ya está logeado, se redirige al Dashboard
    header("Location: ../../dashboard.php");
    exit(0);
} 

if (isset($_POST['dni']) && isset($_POST['pass'])) {
    $dni = trim(strtoupper($_POST['dni']));
    $submittedPassword = $_POST['pass'];
    
    // Para proteger contra inyecciones de SQL, usaré 'statements'
    // No es necesario comprobar si el DNI es un DNI válido, solo
    // si está en la base de datos
    $query = "  SELECT  nombre,
                        apellidos,
                        pass,
                        empresa_id,
                        es_admin,
                        es_root
                FROM    usuario
                WHERE   DNI = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $dni);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                if (password_verify($submittedPassword, $user['pass'])) { // Hasheado para evitar guardar la contraseña en texto plano en la base de datos.
                    $_SESSION['usuario'] = $dni;                          // El usuario
                    $_SESSION['nombre'] = $user['nombre'];                // El nombre, para mostrarlo
                    $_SESSION['apellidos'] = $user['apellidos'];          // Los apellidos, para mostrarlos
                    $_SESSION['empresa_id'] = $user['empresa_id'];        // El ID de la empresa, solo se usará si no se es root
                    $_SESSION['es_admin'] = $user['es_admin'];            // Si se es admin, solo se usará si no se es root
                    $_SESSION['es_root'] = $user['es_root'];              // Si se es root, hay opciones adicionales

                    $fecha = date('Y-m-d');
                    $hora = date('H:i:s');
                    $ip = $_SERVER['REMOTE_ADDR'];                        // IP del usuario
                    $mensaje_login = "[" . $fecha . "][" . $hora . "] - Login correcto de " . $dni . " desde [" . $ip . "]\n";

                    // Los permisos del archivo de logs de acceso y de logs de errores están puestos a 777. Si esto no se transfiere en la entrega del proyecto,
                    // deben ser modificados
                    $logFile = '../../logs/access.log';
                    file_put_contents($logFile, $mensaje_login, FILE_APPEND | LOCK_EX);

                    header("Location: ../../dashboard.php");
                    $stmt->close();
                    $mysqli->close();
                    exit(0);
                } else {
                    login_incorrecto($stmt);
                }
            } else {
                login_incorrecto($stmt);
            }
        } else {
            loguear_error('login', $stmt->error);
            login_incorrecto($stmt);
        }
    } else {
        loguear_error('login', $mysqli->error);
        login_incorrecto();
    }
} else {
    login_incorrecto();
}