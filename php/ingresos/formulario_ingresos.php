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

echo '    <div id="formulario_cambios">
            <h2>Añadir Ingreso</h2>
            <form id="formIngreso" style="display: block;" method="post" action="/php/ingresos/insert_ingresos.php">
                <label for="apartamento_id">Apartamento:*</label>
                    <select id="apartamento_id" name="apartamento_id" onchange="updateMaxPersonas()">' .
                        listar_apartamentos($mysqli) .
                    '</select>
                    <br /><br />
                <label for="fecha_entrada">Fecha de entrada:*</label>
                    <input class="fecha_formulario" type="date" id="fecha_entrada" name="fecha_entrada" required>
                    <br /><br />
                <label for="fecha_salida">Fecha de salida:*</label>
                    <input class="fecha_formulario" type="date" id="fecha_salida" name="fecha_salida" required>
                    <br /><br />
                <label for="nombre_cliente">Nombre del cliente:*</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" maxlength="255" placeholder="Nombre del cliente" required>
                    <br /><br />
                <label for="apellidos_cliente">Apellidos del cliente:</label>
                    <input type="text" id="apellidos_cliente" name="apellidos_cliente" maxlength="255" placeholder="Apellidos del cliente">
                    <br /><br />
                <label for="nif_cliente">NIF del cliente:*</label>
                    <input type="text" id="nif_cliente" pattern="[0-9A-Za-z][0-9]{7}[0-9A-Za-z]" name="nif_cliente" maxlength="9" placeholder="NIF del cliente" required>
                    <br /><br />
                <label for="tel_cliente">Teléfono del cliente:*</label>
                    <input type="text" id="tel_cliente" name="tel_cliente" maxlength="15" placeholder="Teléfono del cliente" required>
                    <br /><br />
                <label for="correo_cliente">Correo del cliente:*</label>
                    <input type="text" id="correo_cliente" name="correo_cliente" maxlength="255" placeholder="Correo del cliente" required>
                    <br /><br />
                <label for="num_clientes">Número de personas en la casa:*</label>
                    <input type="number" step="1" id="num_clientes" name="num_clientes" placeholder="Número de personas" min="1" max="2" value ="1" required> 
                    <br /><br />
                <label for="descuento">Descuento a aplicar:*</label>
                    <input type="number" step="0.01" id="descuento" name="descuento" placeholder="Descuento" min="0" max="100" value="0" required>% 
                    <br /><br />
                <label for="tarifa_id">Tarifa a aplicar:*</label>
                    <select id="tarifa_id" name="tarifa_id">' .
                        listar_tarifas($mysqli) .
                    '</select>
                    <br /><br />
                <label for="intermediario_id">Intermediario:*</label>
                    <select id="intermediario_id" name="intermediario_id">' .
                        listar_intermediarios($mysqli) .
                    '</select>
                    <br /><br />
                <label for="comentario">Comentarios:</label> 
                    <textarea id="comentario" name="comentario" maxlength="255" placeholder="Comentario"></textarea> 
                    <br />
                    <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                    <br /><br />
                <button type="submit">Registrar</button> 
            </form>
        </div>';

$mysqli->close();

?>
