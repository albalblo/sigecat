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

$mensaje = '    <div id="formulario_cambios">
                    <h2>Añadir Apartamento</h2>
                    <form id="formApartamento" style="display: block;" method="post" action="/php/apartamentos/insert_apartamento.php">
                        <label for="nombre">Nombre del Apartamento:*</label>
                            <input type="text" id="nombre" name="nombre" maxlength="255" placeholder="Nombre del Apartamento" required>
                            <br /><br />
                        <label for="direccion">Dirección:*</label>
                            <input type="text" id="direccion" name="direccion" maxlength="255" placeholder="Dirección" required>
                            <br /><br />
                        <label for="precio_noche">Precio por noche:*</label>
                            <input type="number" step="0.01" id="precio_noche" name="precio_noche" placeholder="Precio por Noche" min="0" max="9999.99" required> 
                            <br /><br />
                        <label for="max_personas">Número máximo de personas:*</label>
                            <input type="number" step="1" id="max_personas" name="max_personas" placeholder="Número máximo de personas" min="0" max="99" required> 
                            <br /><br />
                        <label for="empresa_id">Empresa:*</label>
                            <select id="empresa_id" name="empresa_id">' .
                                listar_empresas($mysqli) .
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
echo $mensaje;
