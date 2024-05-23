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

function listar_empresas($mysqli) {
    $query_empresas = "SELECT id, nombre FROM empresa";
    if ($_SESSION['es_root'] == 0) {
        $query_empresas .= " WHERE id = ".$_SESSION['empresa_id'];
    }
    $query_empresas .= " ORDER BY nombre ASC";
    $resultado_empresas = $mysqli->query($query_empresas);
    $empresas = "";
    while ($row = $resultado_empresas->fetch_assoc()) {
        $empresas .= "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nombre']) . "</option>";
    }
    $resultado_empresas->close();
    return $empresas;
}

function listar_apartamentos($mysqli) {
    $query_apartamentos = "SELECT id, nombre, empresa_id FROM apartamento";
    if ($_SESSION['es_root'] == 0) {
        $query_apartamentos .= " WHERE empresa_id = ".$_SESSION['empresa_id'];
    }
    $query_apartamentos .= " ORDER BY nombre ASC";
    $resultado_apartamentos = $mysqli->query($query_apartamentos);
    $apartamentos = "";
    while ($row = $resultado_apartamentos->fetch_assoc()) {
        $apartamentos .= "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nombre']) . "</option>";
    }
    $resultado_apartamentos->close();
    return $apartamentos;
}

function listar_tarifas($mysqli) {
    $query_tarifas = "SELECT id, descripcion FROM tarifas ORDER BY descripcion ASC";
    $resultado_tarifas = $mysqli->query($query_tarifas);
    $tarifas = "";
    while ($row = $resultado_tarifas->fetch_assoc()) {
        $tarifas .= "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['descripcion']) . "</option>";
    }
    $resultado_tarifas->close();
    return $tarifas;
}

function listar_intermediarios($mysqli, $intermediario_en_uso=null) {
    $query_intermediarios = "SELECT DISTINCT intermediario_id, nombre_intermediario FROM intermediarios";
    if ($_SESSION['es_root'] == 0) {
        $query_intermediarios .= " WHERE empresa_id = ".$_SESSION['empresa_id'];
    }
    $query_intermediarios .= " ORDER BY nombre_intermediario ASC";
    $resultado_intermediarios = $mysqli->query($query_intermediarios);
    $intermediarios = (is_null($intermediario_en_uso)) ? '<option value="0" selected="selected">Sin Intermediario</option>' : '<option value="0">Sin Intermediario</option>';
    while ($row = $resultado_intermediarios->fetch_assoc()) {
        $intermediarios .= "<option value='" . $row['intermediario_id'] . "' ";
        if($row['intermediario_id'] == $intermediario_en_uso) {
            $intermediarios .= 'selected="selected"';
        }
        $intermediarios .= ">" . htmlspecialchars($row['nombre_intermediario']) . "</option>";
    }
    $resultado_intermediarios->close();
    return $intermediarios;
}