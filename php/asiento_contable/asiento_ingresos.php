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

require_once '../funciones/con_db.php';     // Conexión con la base de datos
require_once '../funciones/config.php';     // Configuración de la página y verificación de sesión
require_once '../funciones/listar.php';     // Funciones de visualización
require_once '../funciones/verificar.php';  // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = '';

    if (isset($_POST['ids'])) {
        $id_gastos = $_POST['ids'];
        $id_gastos_str = implode(", ", $id_gastos);
    } else {
        $id_gastos_str = "0"; // Es un autonumérico, por lo que nunca será 0. Así, si es 0, en la lógica de más tarde sé que no se ha seleccionado ningún gasto para el asiento contable
    }

    $mensaje = '<input type="checkbox" class="rowCheckbox" id="inline" value="' . $id_gastos_str . '" checked disabled style="display: none;">'; // Los IDs de los gastos se recibirán como en una checkbox más

    $query = "SELECT    i.id,
                        a.nombre,
                        a.precio_noche,
                        i.fecha_entrada,
                        i.fecha_salida,
                        i.nombre_cliente,
                        i.num_noches,
                        t.descripcion AS nombre_tarifa,
                        t.tarifa,
                        i.descuento,
                        i.empresa_id,
                        e.nombre AS nombre_empresa,
                        i.comentario
            FROM ingresos AS i
            JOIN apartamento AS a
                ON a.id = i.apartamento_id
            JOIN tarifas AS t
                ON i.tarifa = t.id
            JOIN empresa AS e
                ON i.empresa_id = e.id";

    if (!$_SESSION['es_root']) {
        $query .= " WHERE i.empresa_id = ?";
    }

    $stmt = $mysqli->prepare($query);

    if($stmt) {
        if (!$_SESSION['es_root']) {
            $stmt->bind_param("s", $_SESSION['empresa_id']);
        }
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $precio_apartamento = 0;
        
            $mensaje .= "   <table>
                                <thead>
                                    <tr>
                                    <th><input type='checkbox' class='selectAllIngresos' onclick='toggleCheckboxes(this)'></th>"; // Checkbox maestra
            if ($_SESSION['es_root'] == 1) {
                $mensaje .= "   <th>ID</th>
                                <th>Empresa</th>";
            }
            $mensaje .= "   <th>Apartamento</th>
                            <th>Descripción</th>
                            <th>Fecha de entrada</th>
                            <th>Fecha de salida</th>
                            <th>Precio por noche</th>
                            <th>Cliente</th>
                            <th>Tarifa</th>
                            <th>Descuento</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>";
        
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $precio_apartamento = $row['num_noches'] * $row['precio_noche'] * $row['tarifa'];
                    $precio_apartamento = $precio_apartamento * (1 - ($row['descuento'] / 100));
                    $mensaje .= "   <tr>
                                        <td><input type='checkbox' class='rowCheckbox' value='".$row['id']."'></td>";
                    if ($_SESSION['es_root'] == 1) {
                        $mensaje .= "   <td>".$row['id']."</td>
                                        <td>".$row["nombre_empresa"]."</td>";
                    }
                    $mensaje .= "   <td>".$row["nombre"]."</td>
                                    <td>".$row["comentario"]."</td>
                                    <td>".$row["fecha_entrada"]."</td>
                                    <td>".$row["fecha_salida"]."</td>
                                    <td>".$row["precio_noche"]."€</td>
                                    <td>".$row["nombre_cliente"]."</td>
                                    <td>".$row["nombre_tarifa"]." (".$row["tarifa"]."x)</td>
                                    <td>".$row["descuento"]."%</td>
                                    <td>".$precio_apartamento."€</td>
                                </tr>";
                }
                $mensaje .= "</tbody></table>";
            } else {
                $mensaje .= "No hay datos disponibles.";
            }
        } else {
            $mensaje = "Error en la operación, inténtelo de nuevo más tarde";
            loguear_error("asiento_gastos", $stmt->error);
        }
    } else {
        $mensaje = "Error en la operación, inténtelo de nuevo más tarde";
        loguear_error("asiento_gastos", $mysqli->error);
    }

    echo $mensaje;
    $mysqli->close();
}
