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

// Funciones solo utilizadas en este archivo
function obtener_datos_gastos($row_gastos) {
    $gastos_array = [];
    $gastos_string = "";
    if ($_SESSION['es_root'] == 1) {
        $gastos_string .= " <td>" . $row_gastos['id'] . "</td>
                            <td>" . $row_gastos["nombre_empresa"] . "</td>";
    }
    $fecha_ok = $row_gastos["fecha"];
    $fecha_ok = date('d-m-Y', strtotime($fecha_ok));

    $gastos_string .= " <td>" . $row_gastos["concepto"] . "</td>
                        <td>" . $fecha_ok . "</td>
                        <td></td>
                        <td style='text-align: right;'>-" . $row_gastos['total_gasto'] . "€</td>";
    $gastos_array = [$gastos_string, $row_gastos['total_gasto']];
    return $gastos_array;
}

function obtener_datos_ingresos($row_ingresos) {
    $ingresos_array = [];
    $ingresos_string = "";
    $precio_apartamento = $row_ingresos['num_noches'] * $row_ingresos['precio_noche'] * $row_ingresos['tarifa'];
    $precio_apartamento = $precio_apartamento * (1 - ($row_ingresos['descuento'] / 100));
    if ($_SESSION['es_root'] == 1) {
        $ingresos_string .= "   <td>" . $row_ingresos['id'] . "</td>
                                <td>" . $row_ingresos["nombre_empresa"]."</td>";
    }
    $fecha_ok = $row_ingresos["fecha"];
    $fecha_ok = date('d-m-Y', strtotime($fecha_ok));
    $ingresos_string .= "   <td>" . $row_ingresos["concepto"] . "</td>
                            <td>" . $fecha_ok . "</td>
                            <td style='text-align: right;'>" . $precio_apartamento . "€</td>
                            <td></td>";
    $ingresos_array = [$ingresos_string, $precio_apartamento];

    return $ingresos_array;
}


/* HAY QUE HACER IVA DE TODOS LOS GASTOS E INGRESOS, NO SOLO DE LO PAGADO */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = '';
    $id_gastos_str = "0";
    $id_ingresos_str = "0";

    $gastos_ok = false;
    $ingresos_ok = false;

    if (isset($_POST['ids'])) {
        $id_movimientos = $_POST['ids'];
        $id_gastos_str = $id_movimientos[0];
        $id_ingresos = array_slice($id_movimientos, 1);

        if($id_gastos_str == "0") {
            $id_gastos_array = [0];
            $gastos_placeholders = "?";
        } else {
            $id_gastos_array = explode(',', $id_gastos_str);
            $gastos_placeholders = implode(',', array_fill(0, count($id_gastos_array), '?'));
        }

        if ($id_ingresos == []) {
            $id_ingresos_array = [0];
            $ingresos_placeholders = "?";
        } else {
            $id_ingresos_array = $id_ingresos;
            $ingresos_placeholders = implode(',', array_fill(0, count($id_ingresos_array), '?'));
        }

        // Se harán los SELECTS por separado. En una versión antigua se intentó con un UNION, pero no había forma de obtener el mismo número de columnas sin sacrificar eficiencia
        $query_gastos = "SELECT g.id,
                            g.empresa_id,
                            e.nombre AS nombre_empresa,
                            g.concepto,
                            g.gasto_interno,
                            g.fecha,
                            g.total_gasto
                    FROM gastos AS g
                    JOIN empresa AS e
                        ON g.empresa_id = e.id
                    WHERE g.id IN (" . $gastos_placeholders . ")
                    ORDER BY fecha ASC";
        $stmt_gastos = $mysqli->prepare($query_gastos);
        
        if($stmt_gastos) {
            $stmt_gastos->bind_param(str_repeat('i', count($id_gastos_array)), ...$id_gastos_array);
            if($stmt_gastos->execute()) {
                $result_gastos = $stmt_gastos->get_result();
                $gastos_ok = true;
            } else {
            $mensaje = "Error del sistema, inténtelo de nuevo más tarde";
            loguear_error("asiento_contable", $stmt_gastos->error);
            }
            $stmt_gastos->close();
        } else {
            $mensaje = "Error del sistema, inténtelo de nuevo más tarde";
            loguear_error("asiento_contable", $mysqli->error);
        }

        $query_ingresos = "SELECT    i.id,
                            i.empresa_id,
                            e.nombre AS nombre_empresa,
                            a.nombre AS concepto,
                            i.fecha_entrada AS fecha,
                            a.precio_noche,
                            i.num_noches,
                            t.tarifa,
                            i.descuento
                FROM ingresos AS i
                JOIN apartamento AS a
                    ON a.id = i.apartamento_id
                JOIN tarifas AS t
                    ON i.tarifa = t.id
                JOIN empresa AS e
                    ON i.empresa_id = e.id
                WHERE i.id IN (".$ingresos_placeholders.")
                ORDER BY fecha_entrada ASC";
        $stmt_ingresos = $mysqli->prepare($query_ingresos);

        if($stmt_ingresos) {
            $stmt_ingresos->bind_param(str_repeat('i', count($id_ingresos_array)), ...$id_ingresos_array);
            if($stmt_ingresos->execute()) {
                $result_ingresos = $stmt_ingresos->get_result();
                $ingresos_ok = true;
            } else {
            $mensaje = "Error del sistema, inténtelo de nuevo más tarde";
            loguear_error("asiento_contable", $stmt_ingresos->error);
            }
            $stmt_ingresos->close();
        } else {
            $mensaje = "Error del sistema, inténtelo de nuevo más tarde";
            loguear_error("asiento_contable", $mysqli->error);
        }

        if($ingresos_ok && $gastos_ok) {
            $mensaje = "<table>
                            <thead>
                                <tr>";

            if ($_SESSION['es_root'] == 1) {
                $mensaje .= "   <th>ID</th>
                                <th>Empresa</th>";
            }
            $mensaje .= "   <th>Concepto</th>
                            <th>Fecha</th>
                            <th colspan='2' style='text-align: center;'>Total</th>
                            </tr>
                        <tbody>";
            
            // Se mostrarán los gastos y los ingresos ordenados por la fecha de ejecución. En los ingresos esta será la fecha de entrada
            if (($result_gastos->num_rows > 0) || ($result_ingresos->num_rows > 0)) {
                $total_gastos = 0.0;
                $total_ingresos = 0.0;
                $row_gastos = $result_gastos->fetch_assoc();
                $row_ingresos = $result_ingresos->fetch_assoc();
                while ($row_gastos || $row_ingresos) {
                    $mensaje .= "<tr>";
                    if($row_gastos == NULL) {
                        $ingresos = obtener_datos_ingresos($row_ingresos);
                        $mensaje .= $ingresos[0];
                        $total_ingresos += $ingresos[1];
                        $row_ingresos = $result_ingresos->fetch_assoc();
                    } elseif ($row_ingresos == NULL) {
                        $gastos = obtener_datos_gastos($row_gastos);
                        $mensaje .= $gastos[0];
                        $total_gastos += $gastos[1];
                        $row_gastos = $result_gastos->fetch_assoc();
                    } elseif($row_gastos['fecha'] <= $row_ingresos['fecha']) {
                        $gastos = obtener_datos_gastos($row_gastos);
                        $mensaje .= $gastos[0];
                        $total_gastos += $gastos[1];
                        $row_gastos = $result_gastos->fetch_assoc();
                    } else {
                        $ingresos = obtener_datos_ingresos($row_ingresos);
                        $mensaje .= $ingresos[0];
                        $total_ingresos += $ingresos[1];
                        $row_ingresos = $result_ingresos->fetch_assoc();
                    }
                    $mensaje .= "</tr>";
                }
                $total_asiento = $total_ingresos - $total_gastos;
                $columnas_span = $_SESSION['es_root'] ? 3 : 1;
    
                $mensaje .= "   </tbody>
                                    <tfoot>
                                        <tr></tr>
                                        <tr>
                                            <td><b>Total:</b></td>
                                            <td colspan='" . $columnas_span . "'></td>
                                            <td colspan='2' style='text-align: center;'>" . $total_asiento . "€</td>
                                        </tr>
                                    </tfoot>";
            } else {
                $mensaje .= "<tr><td colspan='5'>No se han seleccionado datos</td></tr>";
            }
    
            $mensaje .= "</table>";  
        } else {
            $mensaje .= "Error en la consulta.";
        }

    } else {
        $mensaje = "Error en la selección de datos"; // No debería darse de forma natural, ya que, como mínimo, se pasa "[0, ]"
    }

    echo $mensaje;
    $mysqli->close();

}
