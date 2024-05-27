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
$query = "SELECT    i.id AS id_factura,
                    a.nombre AS nombre_apartamento,
                    i.fecha_entrada,
                    i.fecha_salida,
                    i.nombre_cliente,
                    i.apellidos_cliente,
                    i.nif_cliente,
                    i.tel_cliente,
                    i.correo_cliente,
                    i.num_personas,
                    i.descuento,
                    t.descripcion AS tarifa_desc,
                    i.comentario,
                    t.tarifa,
                    i.num_noches,
                    a.precio_noche,
                    i.intermediario_id,
                    i.fecha_creacion,
                    t.id AS tarifa_id,
                    e.nombre AS nombre_empresa 
        FROM ingresos AS i 
        JOIN apartamento AS a ON i.apartamento_id = a.id 
        JOIN tarifas AS t ON i.tarifa = t.id
        JOIN empresa AS e ON i.empresa_id = e.id";

if ($_SESSION['es_root'] == 0) {  // Si el usuario no es root, solamente se muestran las empresas que le correspondan
    $query .= " WHERE i.empresa_id = ?";
}

$stmt = $mysqli->prepare($query);

if($stmt) {
    if ($_SESSION['es_root'] == 0) {  // Si el usuario no es root, solamente se muestran las empresas que le correspondan
        $stmt->bind_param("i", $_SESSION['empresa_id']);
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $precio_apartamento = 0;

        // Muestra de resultados
        if ($result->num_rows > 0) {
            $edit_icon = "../icons/edit.ico";
            $trash_icon = "../icons/trash.ico";
            $mensaje = "<table><thead><tr>";
            if ($_SESSION['es_root']) {
                $mensaje .= "<th>ID</th>";
            }
            $mensaje .= "<th>Apartamento</th><th>Cliente</th><th>Fecha de entrada</th><th>Fecha de salida</th><th>Precio</th><th>Tarifa</th><th>Descuento</th><th>Comentario</th><th></th>";
            if ($_SESSION['es_admin']) {
                $mensaje .= "<th colspan='2'></th>";
            }
            $mensaje .= "</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                $mensaje .= "<tr>";
                if ($_SESSION['es_root']) {
                    $mensaje .= "<td>".$row["id_factura"]."</td>";
                }
                $precio_apartamento = $row["num_noches"] * $row["precio_noche"] * $row["tarifa"];
                $precio_apartamento = ceil($precio_apartamento - ($precio_apartamento * ($row["descuento"]/100)));

                $intermediario = "";

                $fecha_entrada_ok = $row["fecha_entrada"];
                $fecha_entrada_ok = date('d-m-Y', strtotime($fecha_entrada_ok));
                $fecha_salida_ok = $row["fecha_salida"];
                $fecha_salida_ok = date('d-m-Y', strtotime($fecha_salida_ok));

                $datos_popup = "<h1>Ingreso</h1>
                                <p><b>ID interno:</b> " . $row["id_factura"] . "</p>
                                <p><b>Empresa:</b> " . $row["nombre_empresa"] . "</p>
                                <p><b>Apartamento:</b> " . $row["nombre_apartamento"] . "</p>
                                <p><b>Fecha de entrada:</b> " . $fecha_entrada_ok . "</p>
                                <p><b>Fecha de salida:</b> " . $fecha_salida_ok . "</p>
                                <p><b>Número de noches:</b> " . $row["num_noches"] . "</p>
                                <p><b>Precio por noche:</b> " . $row["precio_noche"] . "€</p>
                                <p><b>Precio total:</b> " . $precio_apartamento . "€</p>
                                <p><b>Nombre del cliente:</b> " . $row["nombre_cliente"] . " " . $row["apellidos_cliente"] . "</p>
                                <p><b>NIF del cliente:</b> " . $row["nif_cliente"] . "</p>
                                <p><b>Teléfono del cliente:</b> " . $row["tel_cliente"] . "</p>
                                <p><b>Correo electrónico del cliente:</b> " . $row["correo_cliente"] . "</p>
                                <p><b>Número de personas:</b> " . $row["num_personas"] . "</p>
                                <p><b>Descuento:</b> " . $row["descuento"] . "%</p>
                                <p><b>ID de tarifa aplicada:</b> " . $row["tarifa_id"] . "</p>
                                <p><b>Descripción de tarifa aplicada:</b> " . $row["tarifa_desc"] . "</p>
                                <p><b>Tarifa aplicada:</b> " . $row["tarifa"] . "x</p>
                                <p><b>Comentario:</b> " . $row["comentario"] . "</p>
                                <p><b>Fecha de creación:</b> " . $row["fecha_creacion"] . "</p>";
                
                $mensaje .= '   <td>' . $row['nombre_apartamento'] . '</td>
                                <td>' . $row['nombre_cliente'] . " " . $row["apellidos_cliente"] . '</td>
                                <td>' . $fecha_entrada_ok . '</td>
                                <td>' . $fecha_salida_ok . '</td>
                                <td>' . $precio_apartamento . '€</td>
                                <td>' . $row['tarifa_desc'] . ' (' . $row["tarifa"] . 'x)</td>
                                <td>' . $row['descuento'] . '%</td>
                                <td>' . $row['comentario'] . '</td>
                                <td style="text-align: center;"><img src="../icons/info.ico" class="info-icon icon-pointer"></td>
                                <td style="display: none;">'.$datos_popup . '</td>';
                if ($_SESSION['es_admin']) {
                    $mensaje .= '   <td style="text-align: center;"><img src="' . $edit_icon . '" class="edit-ingreso-icon icon-pointer" data-value="' . $row['id_factura'] . '"></td>
                                    <td style="text-align: center;"><img src="' . $trash_icon . '" class = "delete-ingreso-icon icon-pointer" data-value="' . $row['id_factura'] . '"></td>';
                }
                $mensaje .= '</tr>';
                $precio_apartamento = 0;
            }
            echo "</tbody></table>";
        } else {
            $mensaje =  "No hay datos disponibles.";
        }
            } else {
                $mensaje = "Error interno, inténtelo de nuevo más tarde";
                loguear_error("mostrar_ingresos", $stmt->error);
    }
} else {
    $mensaje = "Error interno, inténtelo de nuevo más tarde";
    loguear_error("mostrar_ingresos", $mysqli->error);
}

echo $mensaje;
$mysqli->close();
