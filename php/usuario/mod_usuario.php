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

$query = "SELECT nombre, apellidos FROM usuario WHERE dni = ?";
$stmt = $mysqli->prepare($query);
$mensaje = '';

if($stmt) {
    $stmt->bind_param("s", $_SESSION['usuario']);
    if($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $mensaje .= '   <div id="formulario_cambios">
                                <h2>Modificar usuario</h2>  
                                <form id="updateUsuarioForm" method="post" action="/php/usuario/update_usuario.php">
                                    <label for="nombre">Nombre:*</label>
                                        <input type="text" id="nombre" name="nombre" value="' . $row["nombre"] . '" length="255" placeholder="Nombre" required>
                                        <br /><br/>
                                    <label for="apellidos">Apellidos:</label>
                                        <input type="text" id="apellidos" name="apellidos" value="' . $row["apellidos"] . '" length="255" placeholder="Apellidos">
                                        <br /><br/>
                                    <label for="nueva_password">Nueva contraseña:</label>
                                        <input type="password" id="nueva_password" name="nueva_password" placeholder="Nueva contraseña">
                                        <br /><br/>
                                    <label for="confirmacion_nueva_password">Repetir nueva contraseña:</label>
                                        <input type="password" id="confirmacion_nueva_password" name="confirmacion_nueva_password" placeholder="Repetir nueva contraseña">
                                        <br /><br/>
                                        <br /><br/>
                                        <br /><br/>
                                    <label for="password">Contraseña actual:*</label>
                                        <input type="password" id="password" name="password" placeholder="Contraseña actual" required>
                                        <br />
                                        <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                                        <br /><br />
                                    <button type="submit">Modificar datos de usuario</button>
                                </form>
                            </div>';
        } else {
            $mensaje = "Usuario no encontrado";
        }
    } else {
        $mensaje = "Error interno inténtelo de nuevo más tarde";
        loguear_error("mod_usuario", $stmt->error);
    }
    
    $stmt->close();
} else {
    $mensaje = "Error interno inténtelo de nuevo más tarde";
    loguear_error("mod_usuario", $mysqli->error);
}

echo $mensaje;
$mysqli->close();
