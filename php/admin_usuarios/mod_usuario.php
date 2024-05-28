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
    $mensaje = "";
    if(isset($_POST['id_usuario'])) {
        $nif = strtoupper($mysqli->real_escape_string($_POST['id_usuario']));

        if(verificar_entidad($mysqli, "usuario", $nif) && verificar_dni($nif)){
            $query = "  SELECT  u.nombre,
                                u.apellidos,
                                u.es_admin,
                                u.empresa_id,
                                e.nombre AS empresa_nombre
                        FROM    usuario AS u
                        JOIN    empresa AS e
                            ON  u.empresa_id = e.id
                        WHERE   u.dni = ?";
            $stmt = $mysqli->prepare($query);

            if($stmt) {
                $stmt->bind_param("s", $nif);
                if($stmt->execute()) {
                    $result = $stmt->get_result();

                    if($result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        $admin_check = $row['es_admin'] == 1 ? 'checked' : '';

                        // No se contempla la opción de modificar el NIF de un usuario, ya que es su identificador legal. Si se ha producido un error
                        // en el registro por parte del administrador, se debe eliminar el usuario y este debe ser recreado
                        $mensaje = '<div id="formulario_cambios">
                                        <h2>Modificar usuario</h2>
                                        <form id="formUsuario" style="display: block;" method="post" action="/php/admin_usuarios/update_usuario.php">
                                            NIF:
                                                <br />
                                                ' . $nif . '
                                                <br /><br />
                                            <label for="nombre">Nombre:*</label>
                                                <br />
                                                <input type="text" id="nombre" name="nombre" maxlength"255" value="' . $row['nombre'] . '" required>
                                                <br /><br />
                                            <label for="apellidos">Apellidos:</label>
                                                <br />
                                                <input type="text" id="apellidos" name="apellidos" maxlength"255" value="' . $row['apellidos'] . '">
                                                <br /><br />
                                            <label for="empresa_id">Empresa:*</label>
                                                <br />
                                                <select id="empresa_id" name="empresa_id" required>' .
                                                    listar_empresas($mysqli) .
                                                '</select>
                                                <br /><br />
                                            <label for="es_admin">Administrador de la empresa:</label>
                                            <input type="checkbox" id="es_admin" name="es_admin" value="1" ' . $admin_check . ' >
                                            <br />
                                            <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                                            <br /><br />
                                            <input type="hidden" id="nif" name="nif" value="' . $nif . '">
                                            <input type="submit" value="Actualizar">
                                        </form>
                                    </div>';

                    } else {
                        $mensaje .= 'Error al recuperar el usuario<br />';
                    }
                } else {
                    $mensaje .= "Error interno, inténtelo de nuevo más tarde.<br />";
                    loguear_error("mod_usuario", $stmt->error);
                }
            } else {
                $mensaje .= "Error interno, inténtelo de nuevo más tarde.<br />";
                loguear_error("mod_usuario", $mysqli->error);
            }
            $stmt->close();
        } else {
            $mensaje .= "Error en el usuario<br />";
        }
    } else {
        $mensaje .= "Error al establecer el usuario<br />";
    }

    echo $mensaje;
    $mysqli->close();
}
