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

require_once 'config.php';
require_once '../funciones/log_errores.php';    // Logueo de los mensajes de error en un archivo

// Permite verificar que el DNI introducido por un usuario es correcto. Es importante notar que no se hace una comporbación del CIF
function verificar_dni($dni) {
    if(strlen($dni) != 9) {
        return false;
    }

    if(substr($dni, 0) == "X") {
        $dni = "0" . substr($dni, 1);
    } elseif (substr($dni, 0) == "Y") {
        $dni = "1" . substr($dni, 1);
    } elseif (substr($dni, 0) == "Z"){
        $dni = "2" . substr($dni, 1);
    }

    $letra = strtoupper(substr($dni, -1));
    $numeros = substr($dni, 0, -1);

    if(!ctype_digit($numeros)) {
        return false;
    }

    $numeros = intval($numeros, 10);

    $string_letras = "TRWAGMYFPDXBNJZSQVHLCK";

    return ($letra == substr($string_letras, $numeros%23, 1));
}

// Permite verificar si existe una entidad en la base e datos con el id proporcionado
function verificar_entidad($mysqli, $entidad, $entidad_id) {
    $entidades = [
        "usuario"       => "dni",
        "empresa"       => "id",
        "apartamento"   => "id",
        "ingresos"      => "id",
        "gastos"        => "id",
        "tarifas"        => "id",
    ];

    if(!isset($entidades[$entidad])){
        return false;
    } else {
        $columna = $entidades[$entidad];
    }

    $params = "";
    
    if($entidad === "usuario") {
        if(!verificar_dni($entidad_id)){
            return false;
        }
        $params = "s";
    } else {
        if(!is_numeric($entidad_id)) {
            return false;
        }
        $params = "i";
    }

    $query = "  SELECT  *
                FROM    $entidad
                WHERE   $columna = ?";
    $stmt = $mysqli->prepare($query);
    if($stmt) {
        $stmt->bind_param($params, $entidad_id);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();
            return ($result->num_rows > 0);
        } else {
            echo "Ha habido un problema en la verificación";
            loguear_error("verificar_entidad", $stmt->error);
            $stmt->close();
            return false;
        }
    } else {
        echo "Ha habido un problema en la verificación";
        loguear_error("verificar_entidad", $mysqli->error);

        return false;
    }
    // No se debería llegar aquí en ningún momento
    return false;
}

// Permite verificar si un apartamento está libre, en tres configuraciones distintas:
//     - Con respecto a solo una fecha, mirando si está ocupada en algún ingreso
//     - Con respecto a dos fechas, mirando si el periodo entre ellas está comprendido en su totalidad o en parte sobre otra reserva
//     - Permite añadir un id_ingreso contra el que no comparar, en caso de que se quiera hacer un update
function verificar_apartamento_libre($mysqli, $apartamento, $fentrada, $fsalida=null, $ingreso_id=null) {
    $libre = true;

    $query_apartamentos = ' SELECT  id,
                                    fecha_entrada,
                                    fecha_salida
                            FROM    ingresos
                            WHERE   apartamento_id = ?';
    $stmt_apartamentos = $mysqli->prepare($query_apartamentos);
    if(!$stmt_apartamentos) {
        loguear_error("verificar_apartamento_libre", $mysqli->error);
        return false;
    }

    $stmt_apartamentos->bind_param('i', $apartamento);

    if(!$stmt_apartamentos->execute()) {
        loguear_error("verificar_apartamento_libre", $stmt_apartamentos->error);
        return false;
    }

    $result_apartamentos = $stmt_apartamentos->get_result();

    if($result_apartamentos->num_rows > 0) {
        while ($row_apartamentos = $result_apartamentos->fetch_assoc()) {

            if($row_apartamentos['id'] != $ingreso_id) { // No se debe comprobar contra el propio ingreso que se está modificando
                if($fsalida) {
                    $fecha_entrada_ok = (($fentrada < $row_apartamentos['fecha_entrada']) && ($fsalida < $row_apartamentos['fecha_salida']));
                    $fecha_salida_ok = (($fentrada > $row_apartamentos['fecha_entrada']) && ($fsalida > $row_apartamentos['fecha_salida']));
        
                    if(!((!$fecha_entrada_ok && $fecha_salida_ok) || ($fecha_entrada_ok && !$fecha_salida_ok))) {
                        return false;
                    }
                } else {
                    if(($fentrada > $row_apartamentos['fecha_entrada']) && ($fentrada > $row_apartamentos['fecha_salida'])) {
                        return false;
                    }
                }
            }  
        }
    }
    return true;
}

// Verifica si un usuario tiene permisos para ejecutar una tardea asignada a una empresa
function verificar_permisos($mysqli, $usuario_id, $empresa_id, $admin_task) {
    if(!verificar_entidad($mysqli, "usuario", $usuario_id) || !verificar_entidad($mysqli, "empresa", $empresa_id)) {
        return false; // El usuario o la empresa no son correctos
    }

    $query = "  SELECT  empresa_id,
                        es_root,
                        es_admin
                FROM    usuario
                WHERE   dni = ?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt) {
        loguear_error("verificar_permisos", $mysqli->error);
        return false;
    }

    $stmt->bind_param('s', $usuario_id);

    if(!$stmt->execute()) {
        loguear_error("verificar_permisos", $stmt->error);
        return false;
    }

    $result = $stmt->get_result();
    
    if($result->num_rows == 0) {
        $stmt->close();
        return false;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    if($row['es_root']) {
        return true;
    } elseif ($row['empresa_id'] != $empresa_id) {
        return false;
    } elseif($admin_task) {
        return $row['es_admin'] == 1;
    } else {
        return true; //El usuario no es root, es de la misma empresa, y la tarea a realizar no requiere de permisos de administrador
    }
    // No se debería llegar aquí en ningún momento
    return false;
}

// Se verifica si un intermediario trabaja con la empresa y, si no, lo crea
function verificar_intermediario($mysqli, $empresa_id, $intermediario_id) {
    if(!verificar_entidad($mysqli, "empresa", $empresa_id)) {
        return false;
    }

    if($intermediario_id == 0 ) { // Si el intermediario es "Sin intermediario", funciona siempre
        return true;
    }

    $query = "  SELECT DISTINCT intermediario_id
                FROM            intermediarios
                WHERE           empresa_id = ?";
    $stmt = $mysqli->prepare($query);

    if($stmt) {
        $stmt->bind_param("i", $empresa_id);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                if($row["intermediario_id"] == $intermediario_id) {
                    $stmt->close();
                    return true;
                }
            }
            $stmt->close();
            return insertar_intermediario($mysqli, $intermediario_id, $empresa_id);
        } else {
            loguear_error("verificar_intermediario", $stmt->error);
            return false;
        }
    } else {
        loguear_error("verificar_intermediario", $mysqli->error);
        return false;
    }
    // No se debería llegar aquí en ningún momento
    return false;
}

function insertar_intermediario($mysqli, $intermediario_id, $empresa_id) {
    if(!verificar_entidad($mysqli, "empresa", $empresa_id)) {
        return false;
    }

    $query_nombre = "   SELECT DISTINCT nombre_intermediario
                                FROM            intermediarios
                                WHERE           intermediario_id = ?";
    $stmt_nombre = $mysqli->prepare($query_nombre);
    if($stmt_nombre) {
        $stmt_nombre->bind_param("s", $intermediario_id);
        if($stmt_nombre->execute()) {
            $result_nombre = $stmt_nombre->get_result();
            $stmt_nombre->close();
            if($result_nombre->num_rows == 0) {
                // El intermediario no está registrado en el sistema
                return false;
            }
            $nombre_row = $result_nombre->fetch_assoc();
            $nombre_intermediario = $nombre_row['nombre_intermediario'];

            $query_insert = "INSERT INTO intermediarios (   intermediario_id,
                                                            empresa_id,
                                                            nombre_intermediario)
                                        VALUES             (?, ?, ?)";
            $stmt_insert = $mysqli->prepare($query_insert);
            if($stmt_insert) {
                $stmt_insert->bind_param("sis", $intermediario_id, $empresa_id, $nombre_intermediario);
                if($stmt_insert->execute()) {
                    $stmt_insert->close();
                    return true;
                } else {
                    loguear_error("insertar_intermediario", $stmt_insert->error);
                    $stmt_insert->close();
                    return false;
                }
            } else {
                loguear_error("insertar_intermediario", $mysqli->error);
                return false;
            }
        } else {
            $stmt_nombre->close();
            loguear_error("insertar_intermediario", $stmt_nombre->error);
            return false;
        }
    } else {
        loguear_error("insertar_intermediario", $mysqli->error);
        return false;
    }
    // No se debería llegar aquí en ningún momento
    return false;
}

function verificar_numero_personas($mysqli, $apartamento_id, $num_personas) {
    if(!verificar_entidad($mysqli, "apartamento", $apartamento_id)) {
        return false;
    }

    $query = "  SELECT  max_personas
                FROM    apartamento
                WHERE   id = ?";
    $stmt = $mysqli->prepare($query);

    if($stmt) {
        $stmt->bind_param("i", $apartamento_id);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $num_personas <= $row['max_personas'];
        } else {
            loguear_error("verificar_numero_personas", $stmt->error);
            return false;
        }
    } else {
        loguear_error("verificar_numero_personas", $mysqli->error);
        return false;
    }

    // No se debería llegar aquí en ningún momento
    return false;
}
