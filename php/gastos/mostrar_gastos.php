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
require_once '../funciones/verificar.php';      // Configuración de la página y verificación de sesión
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

$mensaje = "";

// Uso un PREPARE para evitar inyecciones, ya que se trata con _SESSION
$query = "SELECT    g.id,
                    g.empresa_id,
                    e.nombre AS nombre_empresa,
                    g.concepto,
                    g.gasto_interno,
                    g.fecha,
                    g.nif_proveedor,
                    g.total_gasto,
                    g.pagado
        FROM gastos AS g
        JOIN empresa AS e ON g.empresa_id = e.id";

if (!$_SESSION['es_root']) {  // Si el usuario no es root, solamente se muestran las empresas que le correspondan
    $query .= " WHERE empresa_id = ?";
}

$stmt = $mysqli->prepare($query);

if($stmt) {
    if (!$_SESSION['es_root']) { 
        $stmt->bind_param("i", $_SESSION['empresa_id']);
    }

    if($stmt->execute()) {

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mensaje = "<table>
                            <thead>
                                <tr>";
        
            if ($_SESSION['es_root']) {
                $mensaje .= "   <th>ID</th>
                                <th>Empresa</th>";
            }
            
            $edit_icon = "../icons/edit.ico";
            $trash_icon = "../icons/trash.ico";
        
            $mensaje .= "   <th>Concepto</th>
                            <th>Fecha</th>
                            <th>NIF Proveedor</th>
                            <th>Gasto</th>
                            <th>IVA</th>
                            <th>Gasto Total</th>
                            <th>Pagado</th>
                            <th></th>
                            <th style='display: none;'></th>";

            if ($_SESSION['es_admin']) {
                $mensaje .= "<th colspan='2'></th>";
            }

            $mensaje .= "       </tr>
                            </thead>
                            <tbody>";
        
            while ($row = $result->fetch_assoc()) {
                $tipo_gasto = $row["gasto_interno"] == 1 ? "Interno" : "Externo";
                $total = ($row['gasto_interno'] == 1) ? $row['total_gasto'] : $row['total_gasto'] * 1.21;
                $icon_pagado = ($row['pagado'] == 1) ? "../icons/check.ico" : "../icons/cross.ico";
        
                $fecha_ok = $row["fecha"];
                $fecha_ok = date('d-m-Y', strtotime($fecha_ok));
        
                $datos_popup = "<h1>Gasto</h1>
                                <p><b>ID interno:</b> " . $row["id"] . "</p>
                                <p><b>Empresa:</b> " . $row["nombre_empresa"] . "</p>
                                <p><b>Fecha del gasto:</b> " . $fecha_ok . "</p>
                                <p><b>Tipo de gasto:</b> " . $tipo_gasto . "</p>";

                if ($row["gasto_interno"]) {
                    $datos_popup .= "<p><b>NIF del proveedor:</b> " . $row["nif_proveedor"] . "</p>
                                    <p><b>Total del gasto sin IVA:</b> " . $row["total_gasto"] .  "</p>
                                    <p><b>IVA: 21%</b></p>
                                    <p><b>Total del gasto con IVA:</b> " . $total . "€</p>";

                } else {
                    $datos_popup .= "<p><b>Total del gasto</b>: " . $row["total_gasto"] . "€</p>";

                }
        
                $es_pagado = $row['pagado'] == 1 ? "Pagado" : "Pendiente";

                $datos_popup .= "<p><b>Pagado:</b> " . $es_pagado . "</p>
                                <p><b>Concepto:</b> " . $row["concepto"] . "</p>";
        
                $mensaje .= "<tr>";

                if ($_SESSION['es_root']) {
                    $mensaje .= '   <td>' . $row['id'] . '</td>
                                    <td>' . $row['nombre_empresa'] . '</td>';
                }

                $gasto = $row['gasto_interno'] == 1 ? $row['total_gasto'] : $row['total_gasto'] * 1.21;

                $mensaje .= '   <td>' . $row['concepto'] . '</td>
                                <td>' . $fecha_ok . '</td>
                                <td>' . $row['nif_proveedor'] . '</td>
                                <td>' . $gasto . '</td>';

                $mensaje .= $row['gasto_interno'] == 1 ? '<td>0%</td>' : '<td>21%</td>';
                $mensaje .= '   <td>' . $total . '€</td>
                                <td style="text-align: center;"><img src="' . $icon_pagado . '"></td>
                                <td style="text-align: center;"><img src="../icons/info.ico" class="info-icon icon-pointer"></td>
                                <td style="display: none;">' . $datos_popup . '</td>';

                if ($_SESSION['es_admin']) {
                    $mensaje .= '   <td style="text-align: center;"><img src="' . $edit_icon . '" class="edit-gasto-icon icon-pointer" data-value="' . $row['id'] . '"></td>
                                    <td style="text-align: center;"><img src="' . $trash_icon . '" class = "delete-gasto-icon icon-pointer" data-value="' . $row['id'] . '"></td>';
                }
                $mensaje .= '</tr>';
            }
            $mensaje .= '       </tbody>
                            </table>';
        } else {
            $mensaje .= 'No hay datos disponibles.';
        }
    } else {
        $mensaje = "Se ha producido un error, inténtelo de nuevo más tarde";
        loguear_error("mostrar_apartamento", $stmt->error);
    }
} else {
    $mensaje = "Se ha producido un error, inténtelo de nuevo más tarde";
    loguear_error("mostrar_apartamento", $mysqli->error);
}

$mysqli->close();
echo $mensaje;
