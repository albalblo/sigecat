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

$mensaje = "";

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
          JOIN empresa AS e ON g.empresa_id = e.id
          WHERE g.pagado = 1";

if (!$_SESSION['es_root']) {
    $query .= " AND g.empresa_id = ?";
}

$stmt = $mysqli->prepare($query);

if($stmt) {
    if (!$_SESSION['es_root']) {
        $stmt->bind_param("i", $_SESSION['empresa_id']);
    }
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $mensaje = "<table>
                        <thead>
                            <tr>
                            <th><input type='checkbox' class='selectAllGastos' onclick='toggleCheckboxes(this)'></th>"; // Checkbox maestra: al seleccionarla o deselecionarla, cambian todas
        
        if ($_SESSION['es_root']) {
            $mensaje.= "<th>ID</th><th>Empresa</th>";
        }

        $mensaje .= "   <th>Concepto</th>
                        <th>Fecha</th>
                        <th>NIF Proveedor</th>
                        <th>Gasto</th>
                    </tr>
                </thead>";
        
        if ($result->num_rows > 0) {
            $mensaje .= "<tbody>";
            while ($row = $result->fetch_assoc()) {
                $mensaje .= "   <tr>
                                    <td>
                                    <input type='checkbox' class='rowCheckbox' value='" . $row['id'] . "'></td>";
                if ($_SESSION['es_root'] == 1) {
                    $mensaje .= "   <td>".$row["id"]."</td>
                                    <td>".$row["nombre_empresa"]."</td>";
                }
                $fecha_ok = $row["fecha"];
                $fecha_ok = date('d-m-Y', strtotime($fecha_ok));

                $mensaje .= "   <td>" . $row["concepto"] . "</td>
                                <td>" . $fecha_ok . "</td>
                                <td>" . $row["nif_proveedor"] . "</td>
                                <td>-" . $row['total_gasto'] . "€</td>
                            </tr>";
            }
            $mensaje .= "   </tbody>
                        </table>
                       ";
        } else {
            $mensaje .= "No hay datos disponibles.";
        }
    } else {
        $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
        loguear_error("asiento_gastos", $stmt->error);
    }
} else {
    $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde..";
    loguear_error("asiento_gastos", $mysqli->error);
}

echo $mensaje;

$mysqli->close();
