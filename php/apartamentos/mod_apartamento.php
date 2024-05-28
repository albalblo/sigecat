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
require_once '../funciones/listar.php';         // Funciones de visualización
require_once '../funciones/verificar.php';      // Funciones de verificación
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = '';

    if(isset($_POST['apartamento_id'])) {
        $apartamento_id = filter_input(INPUT_POST, 'apartamento_id', FILTER_VALIDATE_INT);

        if(verificar_entidad($mysqli, "apartamento", $apartamento_id)) {
            $es_root = $_SESSION['es_root'];
            $es_admin = $_SESSION['es_admin'];
            $empresa_id = $_SESSION['empresa_id'];
            $permiso = true;

            //Verificación de permisos
            if(!$es_root) {
                if(!$es_admin){
                    $mensaje .= "Permisos insuficientes..";
                    $permiso = false;
                } else {
                    $query_prueba = "   SELECT empresa_id
                                        FROM   apartamento
                                        WHERE  id = ?";
                    $stmt_prueba = $mysqli->prepare($query_prueba);

                    if($stmt_prueba) {
                        $stmt_prueba->bind_param('i', $apartamento_id);
                        if($stmt_prueba->execute()) {
                            $result_prueba = $stmt_prueba->get_result();
                            $empresa_id_db = $result_prueba->fetch_assoc();
                
                            if($empresa_id_db == NULL){
                                $mensaje .= "Ha habido un error.";
                                $permiso = false;
                            } elseif($empresa_id != $empresa_id_db['empresa_id']) {
                                $mensaje .= "Permisos insuficientes.";
                                $permiso = false;
                            }
                        } else {
                            $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde.";
                            loguear_error("mod_apartamento", $stmt_prueba->error);
                            $permiso = false;
                        }
                        $stmt_prueba->close();
                    } else {
                        $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde.";
                        loguear_error("mod_apartamento", $mysqli->error);
                        $permiso = false;
                    }
                }
            }

            if($permiso) {
                $query = "SELECT    nombre,
                                    direccion,
                                    precio_noche,
                                    max_personas,
                                    comentario
                          FROM      apartamento
                          WHERE     id = ?";
                $stmt = $mysqli->prepare($query);

                if($stmt) {
                    $stmt->bind_param('i', $apartamento_id);
                    if($stmt->execute()) {
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                
                        $mensaje = '<div id="formulario_cambios">
                                        <h2>Modificar Apartamento</h2>  
                                        <form id="updateApartamentoForm" method="post" action="/php/apartamentos/update_apartamento.php">
                                            <label for="nombre">Nombre:*</label>
                                                <input type="text" id="nombre" name="nombre" maxlength="255" value="' . htmlspecialchars($row["nombre"]) . '" placeholder="Nombre" required>
                                                <br /><br/>
                                            <label for="direccion">Dirección:*</label>
                                                <input type="text" id="direccion" name="direccion" maxlength="255" value="' . htmlspecialchars($row["direccion"]) . '" placeholder="Dirección" required>
                                                <br /><br/>
                                            <label for="precio_noche">Precio por noche:*</label>
                                                <input type="number" step="0.01" id="precio_noche" name="precio_noche" value="' . htmlspecialchars($row["precio_noche"]) . '" placeholder="Precio por noche" min="0" max="9999.99" required>
                                                <br /><br/>
                                            <label for="max_personas">Número máximo de personas:*</label>
                                                <input type="number" step="1" id="max_personas" name="max_personas" value="' . htmlspecialchars($row["max_personas"]) . '" placeholder="Número máximo de personas" min="1" max="99" required>
                                                <br /><br/>
                                            <label for="empresa_id">Empresa:*</label>
                                                <select id="empresa_id" name="empresa_id">' . listar_empresas($mysqli) . '</select>
                                                <br /><br /> 
                                            <label for="comentario">Comentario:</label>
                                                <textarea id="comentario" name="comentario" placeholder="Comentario..." maxlength="255">' . htmlspecialchars($row['comentario']) . '</textarea>
                                                <br />
                                                <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                                                <br /><br />
                                                <br /><br/>
                                                <input type="hidden" name="apartamento_id" value="' . $apartamento_id . '">
                                            <button type="submit">Modificar</button>
                                        </form>
                                    </div>';
                    } else {
                            $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde.";
                            loguear_error("mod_apartamento", $stmt->error);
                            $permiso = false;
                        }
                    $stmt->close();
                } else {
                    $mensaje .= "Ha habido un error, inténtelo de nuevo más tarde.";
                    loguear_error("mod_apartamento", $mysqli->error);
                    $permiso = false;
                }
            }
        } else {
            $mensaje = "Error en el apartamento seleccionado";
        }
    } else {
        $mensaje = "Error en el apartamento a modificar";
    }

    echo $mensaje;
    $mysqli->close();

}
