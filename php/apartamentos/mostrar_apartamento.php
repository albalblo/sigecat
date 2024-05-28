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
require_once '../funciones/verificar.php';     // Configuración de la página y verificación de sesión
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

$mensaje = "";

// Uso un PREPARE para evitar inyecciones, ya que se trata con _SESSION
$query = "  SELECT  a.id,
                    a.nombre,
                    a.direccion,
                    a.precio_noche,
                    a.max_personas,
                    a.empresa_id,
                    a.comentario,
                    e.nombre AS nombre_empresa
            FROM    apartamento AS a
            JOIN    empresa AS e
                ON  a.empresa_id = e.id";

if (!$_SESSION['es_root']) {  // Si el usuario no es root, solamente se muestran las empresas que le correspondan
    $query .= " WHERE a.empresa_id = ?";
}

$stmt = $mysqli->prepare($query);

if($stmt) {
    if(!$_SESSION['es_root']){
        $stmt->bind_param("i", $_SESSION['empresa_id']);
    }

    if($stmt->execute()){
        $result = $stmt->get_result();
      
        $fecha_actual = date('Y-m-d');
        
        // Muestra de resultados
        if ($result->num_rows > 0) {
            $mensaje = "  <table>
                            <thead>
                            <tr>";
            if ($_SESSION['es_root'] == 1) {
                $mensaje.= "<th>Empresa</th>";
            }
        
            $mensaje .= "   <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Precio Noche</th>
                            <th>Número máximo de personas</th>
                            <th>Comentario</th>
                            <th style='text-align: center;'>Libre</th>
                            <th></th>";

            if ($_SESSION['es_admin'] == 1) {
                $mensaje .= "<th colspan='2'></th>";
            }

            $mensaje .= "</thead></tr><tbody>";

            while ($row = $result->fetch_assoc()) {
                $libre = verificar_apartamento_libre($mysqli, $row['id'], $fecha_actual);

                $libre_icon = $libre ? "../icons/check.ico" : "../icons/cross.ico";
                $edit_icon = "../icons/edit.ico";
                $trash_icon = "../icons/trash.ico";

                $mensaje .= "<tr>";
                if ($_SESSION['es_root'] == 1) { // Como el usuario root ve todos los apartamentos, debe ver de qué empresa es cada uno
                    $mensaje .= "<td>" . $row["nombre_empresa"] . "</td>";
                }
                $mensaje .= "   <td>" . $row["nombre"] . "</td>
                                <td>" . $row["direccion"] . "</td>
                                <td>" . $row["precio_noche"] . "€</td>
                                <td>" . $row["max_personas"] . "</td>
                                <td>" . $row["comentario"] . "</td>
                                <td style='text-align: center;'><img src='" . $libre_icon . "'></td>";

                if ($_SESSION['es_admin'] == 1) {
                    $mensaje .= "   <td style='text-align: center;'><img src='" . $edit_icon . "' class='edit-apartamento-icon icon-pointer' data-value='" . $row['id'] . "'></td>
                                    <td style='text-align: center;'><img src='" . $trash_icon . "' class = 'delete-apartamento-icon icon-pointer' data-value='" . $row['id'] . "'></td>";
                }
                $mensaje .= "</tr>";
            }
            $mensaje .= "</tbody></table>";
        } else {
            $mensaje .= "No hay datos disponibles..";
        }

    } else {
        $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
        loguear_error("mostrar_apartamento", $stmt->error);
    }

} else {
    $mensaje = "Ha habido un error, inténtelo de nuevo más tarde..";
    loguear_error("mostrar_apartamento", $mysqli->error);
}

$mysqli->close();
echo $mensaje;
