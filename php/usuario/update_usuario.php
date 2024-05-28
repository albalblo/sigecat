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
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreNuevo = $mysqli->real_escape_string($_POST['nombre']);
    $apellidosNuevo = $mysqli->real_escape_string($_POST['apellidos']);
    $passwordNuevo = $_POST['nueva_password'];
    $passwordNuevo_confirmacion = $_POST['confirmacion_nueva_password'];
    $passwordActual = $_POST['password'];
    $dni = $_SESSION['usuario'];

    $query = "SELECT pass FROM usuario WHERE dni = ?";
    $mensaje = '';

    $stmt = $mysqli->prepare($query);

    if($stmt) {
        $stmt->bind_param("s", $dni);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        
            // La contraseña está hasheada, por lo que no se puede comparar directamente
            if (password_verify($passwordActual, $user['pass'])) {
                $update = "UPDATE usuario SET nombre = ?, apellidos = ?";
                // Se definen los tipos y parámetros a parte porque el usuario puede actualizar su contraseña o no
                $types = "ss";
                $params = [$nombreNuevo, $apellidosNuevo];

                if (!empty($passwordNuevo) && $passwordNuevo === $passwordNuevo_confirmacion) {
                    $hashedPassword = password_hash($passwordNuevo, PASSWORD_DEFAULT);
                    $update .= ", pass = ?";
                    $types .= "s";
                    array_push($params, $hashedPassword);
                }

                $update .= " WHERE dni = ?";
                $types .= "s";
                array_push($params, $dni);

                $stmt = $mysqli->prepare($update);
                if($stmt) {
                    $stmt->bind_param($types, ...$params);
                    if ($stmt->execute()) {

                        //Se hace el cambio ahora para que se vea reflejado en la barra lateral
                        $_SESSION['nombre'] = $nombreNuevo;
                        $_SESSION['apellidos'] = $apellidosNuevo;
                        $mensaje = "Usuario actualizado correctamente";

                    } else {
                        $mensaje = "Ha habido un error, inténtelo de nuevo";
                        loguear_error("update_usuario", $stmt->error);
                    }
                } else {
                    $mensaje = "Error interno, inténtelo de nuevo más tarde";
                    loguear_error("update_usuario", $mysqli->error);
                }
            } else {
                $mensaje = "La contraseña actual es incorrecta";
            }
        } else {
            $mensaje = "Error interno, inténtelo de nuevo más tarde";
            loguear_error("update_usuario", $stmt->error);
        }
        $stmt->close();
    } else {
        $mensaje = "Error interno, inténtelo de nuevo más tarde";
        loguear_error("update_usuario", $mysqli->error);
    }

$mysqli->close();

echo "  <script>
            alert('" . $mensaje . "');
            window.location.href='../../dashboard.php';
        </script>";

}
