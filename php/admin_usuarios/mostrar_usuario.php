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
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

$mensaje = "";

if($_SESSION['es_admin'] || $_SESSION['es_root']) {
    $query = " SELECT   u.dni,
                        u.nombre,
                        u.apellidos,
                        u.empresa_id,
                        u.es_admin,
                        e.nombre AS empresa_nombre
                FROM    usuario AS u
                JOIN    empresa AS e
                    ON  u.empresa_id = e.id";
    if($_SESSION['es_root'] != 1) {
        $query .= " WHERE empresa_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $_SESSION['empresa_id']);
    } else {
        $stmt = $mysqli->prepare($query);
    }

    if($stmt) {
        if($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensaje = "<table>
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Empresa</th>
                                        <th>Administrador</th>
                                        <th colspan='2'></th>
                                    </tr>
                                </thead>
                                <tbody>";

                $edit_icon = "../icons/edit.ico";
                $trash_icon = "../icons/trash.ico";
    
                while ($row = $result->fetch_assoc()) {
                        $admin_icon = $row['es_admin'] == 1 ? "../icons/check.ico" : "../icons/cross.ico";
    
                        $mensaje .= "   <tr>
                                            <td>" . $row["dni"] . "</td>
                                            <td>" . $row["nombre"] . " " . $row["apellidos"] . "</td>
                                            <td>" . $row["empresa_nombre"] . "</td>
                                            <td style='text-align: center;'><img src='" . $admin_icon . "'></td>
                                            <td style='text-align: center;'><img src='" . $edit_icon . "' class='edit-admin-users-icon icon-pointer' data-value='" . $row['dni'] . "'></td>
                                            <td style='text-align: center;'><img src='" . $trash_icon . "' class = 'delete-admin-users-icon icon-pointer' data-value='" . $row['dni'] . "'></td>
                                        </tr>";
                }
                $mensaje .= "   </tbody>
                            </table>";
            } else {
                $mensaje .= "No hay datos disponibles..";
            }
        } else {
            $mensaje .= "Error en la consulta.";
            loguear_error("mostrar_usuario", $stmt->error);
        }    
    } else {
        $mensaje .= "Error en la consulta.";
        loguear_error("mostrar_usuario", $mysqli->error);
    }

    $mysqli->close();

} else {
    $mensaje = "Permisos insuficientes";
}

echo $mensaje;
