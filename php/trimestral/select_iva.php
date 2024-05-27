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

// Funciones solo para este archivo
function trimestral_gastos($row_gastos) {
    $gastos_array = [];
    $tipo_gasto = $row_gastos['gasto_interno'] == 1 ? "interno<br />" : "externo<br />";
    $nif_prov = $row_gastos['gasto_interno'] == 1 ? "" : "NIF del proveedor: " . $row_gastos['nif_proveedor'] . "<br />";
    $pag = $row_gastos['pagado'] == 0 ? "<b>Gasto no pagado</b>" : "";
    $total_gasto = $row_gastos['gasto_interno'] == 1 ? $row_gastos['total_gasto'] : ceil($row_gastos['total_gasto'] * 1.21);
    $iva_aplicado = $total_gasto - $row_gastos['total_gasto'];
    $iva = $row_gastos['gasto_interno'] == 1 ? "0" : "21";

    $fecha_ok = $row_gastos["fecha"];
    $fecha_ok = date('d-m-Y', strtotime($fecha_ok));

    $gastos_string = "";
    $gastos_string .= " <td> g" . $row_gastos['id'] . "</td>
                        <td>" . $row_gastos["nombre"] . "</td>
                        <td>" . $row_gastos["concepto"] . "</td>
                        <td>" . $fecha_ok . "</td>
                        <td>Tipo de gasto: " . $tipo_gasto . $nif_prov . $pag . "</td>
                        <td style='text-align: center;'>-" . $row_gastos['total_gasto'] . "€</td>
                        <td style='text-align: center;'>-" . $iva_aplicado . "€ (" . $iva . "%)</td>
                        <td></td>
                        <td style='text-align: center;'>-" . $total_gasto . "€</td>";
    $gastos_array = [$gastos_string, $row_gastos['total_gasto'], $iva_aplicado, $total_gasto];
    return $gastos_array;
}

function trimestral_ingresos($row_ingresos) {
    $ingresos_array = [];
    $total_ingreso_bruto = ceil($row_ingresos['precio_noche'] * $row_ingresos['tarifa'] * (1 - ($row_ingresos['descuento']/100)));
    $total_ingreso_neto = ceil($row_ingresos['precio_noche'] * $row_ingresos['tarifa'] * 1.21 * (1 - ($row_ingresos['descuento']/100)));
    $total_ingreso_iva = $total_ingreso_neto - $total_ingreso_bruto;

    $fecha_ok = $row_ingresos["fecha_entrada"];
    $fecha_ok = date('d-m-Y', strtotime($fecha_ok));

    $gastos_string = "";
    $gastos_string .= " <td> i" . $row_ingresos['id'] . "</td>
                        <td>" . $row_ingresos["nombre_empresa"] . "</td>
                        <td>" . $row_ingresos["nombre"] . "</td>
                        <td>" . $fecha_ok . "</td>
                        <td>Número de noches: " . $row_ingresos['num_noches'] . "<br/>Precio por noche: " . $row_ingresos['precio_noche'] . "€<br />Tarifa: " . $row_ingresos['descripcion'] . " (x" . $row_ingresos['tarifa'] . ")<br />Descuento: " . $row_ingresos['descuento'] . "%</td>
                        <td style='text-align: center;'>+" . $total_ingreso_bruto . "€</td>
                        <td style='text-align: center;'>+" . $total_ingreso_iva . "€ (21%)</td>
                        <td style='text-align: center;'>+" . $total_ingreso_neto . "€</td>
                        <td></td>";
    $gastos_array = [$gastos_string, $total_ingreso_bruto, $total_ingreso_iva, $total_ingreso_neto];
    return $gastos_array;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = "";

    if(isset($_POST['fecha_liquidacion']) && isset($_POST['empresa_id'])) {
        $fecha_liquidacion = trim($mysqli->real_escape_string($_POST['fecha_liquidacion']));
        $fecha_liquidacion = date($fecha_liquidacion); 

        $dia = date('d', strtotime($fecha_liquidacion)); 
        $mes = date('m', strtotime($fecha_liquidacion)); 
        $año = date('Y', strtotime($fecha_liquidacion)); 
        $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);

        if(verificar_entidad($mysqli, 'empresa', $empresa_id) && checkdate($mes, $dia, $año)){
            $mes_liquidacion = date("m", strtotime($fecha_liquidacion));
            $año_liquidacion = date("Y", strtotime($fecha_liquidacion));

            $trimestre = 0;

            // Según el trimestre que sea, le corresponderá una fecha de presentación distinta
            if ($mes_liquidacion <= 3) {
                $trimestre = 1;
                $trimestre_inicio = $año_liquidacion . '-01-01';
                $trimestre_fin = $año_liquidacion . '-03-31';
            } elseif ($mes_liquidacion <= 6) {
                $trimestre = 2;
                $trimestre_inicio = $año_liquidacion . '-04-01';
                $trimestre_fin = $año_liquidacion . '-06-30';
            } elseif ($mes_liquidacion <= 9) {
                $trimestre = 3;
                $trimestre_inicio = $año_liquidacion . '-07-01';
                $trimestre_fin = $año_liquidacion . '-09-30';
            } else {
                $trimestre = 4;
                $trimestre_inicio = $año_liquidacion . '-10-01';
                $trimestre_fin = $año_liquidacion . '-12-31';
            }

            $fechas_presentacion = [
                1 => [$año_liquidacion . '-04-01', $año_liquidacion . '-04-20'],
                2 => [$año_liquidacion . '-07-01', $año_liquidacion . '-07-20'],
                3 => [$año_liquidacion . '-10-01', $año_liquidacion . '-10-20'],
                4 => [($año_liquidacion+1) . '-01-01', ($año_liquidacion+1) . '-01-20'],
            ];

            $fecha_actual = date('Y-m-d');

            $estado_presentacion = '';

            if($fecha_actual == $fechas_presentacion[$trimestre][1]){
                $estado_presentacion = ' style="font-weight: bold; color:red;" ';
            } elseif ($fecha_actual > $fechas_presentacion[$trimestre][1]){
                $estado_presentacion = ' style="color:red;" ';
            } elseif ($fecha_actual > $fechas_presentacion[$trimestre][0]) {
                $estado_presentacion = ' style="color:green;" ';
            }

            $trimestre_inicio_ok = date('d-m-Y', strtotime($trimestre_inicio));
            $trimestre_fin_ok = date('d-m-Y', strtotime($trimestre_fin));
            $presentacion_inicio_ok = date('d-m-Y', strtotime($fechas_presentacion[$trimestre][0]));
            $presentacion_fin_ok = date('d-m-Y', strtotime($fechas_presentacion[$trimestre][1]));


            $mensaje_trimestral = ' <div id="tabla_trimestral">
                                        <div id="headertrimestral">
                                            <div><p>' . $trimestre . 'º trimestre (' . $año_liquidacion . ')</p></div>
                                            <div><p>' . $trimestre_inicio_ok . ' - ' . $trimestre_fin_ok . '</p></div>
                                            <div><p>A presentar: <span ' . $estado_presentacion . '>' . $presentacion_inicio_ok . ' - ' . $presentacion_fin_ok . '</span></p></div>
                                        </div>';
            // Prueba solo con ingresos por ahora

            $gastos_ok = true;
            $ingresos_ok = true;

            $query_gastos = "   SELECT  g.id,
                                        e.nombre,
                                        g.concepto,
                                        g.gasto_interno,
                                        g.nif_proveedor,
                                        g.total_gasto,
                                        g.fecha,
                                        g.pagado
                                FROM    gastos AS g
                                JOIN    empresa AS e
                                    ON  e.id = g.empresa_id
                                WHERE   (g.empresa_id = ?)
                                    AND (g.fecha BETWEEN ? AND ?)";
            $stmt_gastos = $mysqli->prepare($query_gastos);

            if($stmt_gastos) {
                $stmt_gastos->bind_param("iss", $empresa_id, $trimestre_inicio, $trimestre_fin);
                if($stmt_gastos->execute()) {
                    $resultado_gastos = $stmt_gastos->get_result();
                } else {
                    $gastos_ok = false;
                    $mensake = "Ha habido un error del servidor, inténtelo de nuevo más tarde";
                    loguear_error("select_iva_gastos", $stmt_gastos->error);
                }
                $stmt_gastos->close();
            } else {
                $gastos_ok = false;
                $mensaje = "Ha habido un error del servidor, inténtelo de nuevo más tarde";
                loguear_error("select_iva_gastos", $mysqli->error);
            }

            $query_ingresos = " SELECT  i.id,
                                        a.nombre,
                                        i.fecha_entrada,
                                        i.num_noches,
                                        a.precio_noche,
                                        t.tarifa,
                                        t.descripcion,
                                        i.descuento,
                                        e.nombre AS nombre_empresa
                                FROM    ingresos AS i
                                JOIN    apartamento AS a
                                    ON  i.apartamento_id = a.id
                                JOIN    tarifas AS t
                                    ON  i.tarifa = t.id
                                JOIN    empresa AS e
                                    ON  e.id = i.empresa_id
                                WHERE   (i.empresa_id = ?)
                                    AND (i.fecha_entrada BETWEEN ? AND ?)";
            $stmt_ingresos = $mysqli->prepare($query_ingresos);

            if($stmt_ingresos) {
                $stmt_ingresos->bind_param("iss", $empresa_id, $trimestre_inicio, $trimestre_fin);
                if($stmt_ingresos->execute()) {
                    $resultado_ingresos = $stmt_ingresos->get_result();
                } else {
                    $ingresos_ok = false;
                    $mensake = "Ha habido un error del servidor, inténtelo de nuevo más tarde";
                    loguear_error("select_iva_ingresos", $stmt_ingresos->error);
                }
                $stmt_ingresos->close();
            } else {
                $ingresos_ok = false;
                $mensaje = "Ha habido un error del servidor, inténtelo de nuevo más tarde";
                loguear_error("select_iva_ingresos", $mysqli->error);
            }

            if($ingresos_ok && $gastos_ok) {
                $mensaje = $mensaje_trimestral;

                $mensaje .= '   <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Empresa</th>
                                            <th>Concepto</th>
                                            <th>Fecha</th>
                                            <th>Notas</th>
                                            <th style="text-align: center;">Movimiento</th>
                                            <th style="text-align: center;">Impuesto</th>
                                            <th colspan="2" style="text-align: center;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                // Lógica similar a la del asiento contable
                if (($resultado_ingresos->num_rows > 0) || ($resultado_gastos->num_rows > 0)) {

                    $total_bruto = 0.0;
                    $total_iva = 0.0;
                    $total_neto = 0.0;

                    $ingreso_bruto = 0.0;
                    $ingreso_iva = 0.0;
                    $ingreso_neto = 0.0;

                    $gasto_bruto = 0.0;
                    $gasto_iva = 0.0;
                    $gasto_neto = 0.0;

                    $row_gastos = $resultado_gastos->fetch_assoc();
                    $row_ingresos = $resultado_ingresos->fetch_assoc();

                    while ($row_gastos || $row_ingresos) {
                        $mensaje .= "<tr>";
                        if($row_gastos == NULL) {
                            $ingresos = trimestral_ingresos($row_ingresos);
                            $mensaje .= $ingresos[0];
                            $ingreso_bruto += $ingresos[1];
                            $ingreso_iva += $ingresos[2];
                            $ingreso_neto += $ingresos[3];
                            $row_ingresos = $resultado_ingresos->fetch_assoc();
                        } elseif ($row_ingresos == NULL) {
                            $gastos = trimestral_gastos($row_gastos);
                            $mensaje .= $gastos[0];
                            $gasto_bruto += $gastos[1];
                            $gasto_iva += $gastos[2];
                            $gasto_neto += $gastos[3];
                            $row_gastos = $resultado_gastos->fetch_assoc();
                        } elseif($row_gastos['fecha'] <= $row_ingresos['fecha_entrada']) {
                            $gastos = trimestral_gastos($row_gastos);
                            $mensaje .= $gastos[0];
                            $gasto_bruto += $gastos[1];
                            $gasto_iva += $gastos[2];
                            $gasto_neto += $gastos[3];
                            $row_gastos = $resultado_gastos->fetch_assoc();
                        } else {
                            $ingresos = trimestral_ingresos($row_ingresos);
                            $mensaje .= $ingresos[0];
                            $ingreso_bruto += $ingresos[1];
                            $ingreso_iva += $ingresos[2];
                            $ingreso_neto += $ingresos[3];
                            $row_ingresos = $resultado_ingresos->fetch_assoc();
                        }
                        $mensaje .= "</tr>";
                    }

                    $total_bruto = $ingreso_bruto - $gasto_bruto;
                    $total_iva = $ingreso_iva - $gasto_iva;
                    $total_neto = $ingreso_neto - $gasto_neto;

                    $mensaje .= "   </tbody>
                                        <tfoot>
                                            <tr></tr>
                                            <tr>
                                                <td><b>Total:</b></td>
                                                <td colspan='4'></td>
                                                <td style='text-align: center;'>" . $total_bruto . "€</td>
                                                <td style='text-align: center;'>" . $total_iva . "€</td>
                                                <td style='text-align: center;' colspan='2'>" . $total_neto . "€</td>
                                            </tr>
                                        </tfoot>
                                    </table></div>";
                } else {
                    $mensaje .= "<tr><td colspan='5'>No se han seleccionado datos</td></tr>";
                }
            }
        } else {
            $mensaje = 'Error en los datos introducidos';
        }
    } else {
        $mensaje = 'Error en los datos introducidos';
    }

    $mysqli->close();
    echo $mensaje;
}
