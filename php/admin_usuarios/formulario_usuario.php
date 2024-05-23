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

echo '  <div id="formulario_cambios">
            <h2>Añadir usuario</h2>
            <form id="formUsuario" style="display: block;" method="post" action="/php/admin_usuarios/insert_usuario.php">
                <label for="nif">NIF:*</label>
                    <br />
                    <input type="text" id="nif" name="nif" maxlength"9" placeholder="DNI del usuario" required>
                    <br /><br />
                <label for="nombre">Nombre:*</label>
                    <br />
                    <input type="text" id="nombre" name="nombre" maxlength"255" placeholder="Nombre del usuario" required>
                    <br /><br />
                <label for="apellidos">Apellidos:</label>
                    <br />
                    <input type="text" id="apellidos" name="apellidos" maxlength"255" placeholder="Apellidos">
                    <br /><br />
                <label for="password">Contraseña:*</label>
                    <br />
                    <input type="password" id="password" name="password" maxlength"20" placeholder="Contraseña" required>
                    <br /><br />
                <label for="password_confir">Confirmar contraseña:*</label>
                    <br />
                    <input type="password" id="password_confir" name="password_confir" maxlength"20" placeholder="Confirmar contraseña" required>
                    <br /><br />
                <label for="empresa_id">Empresa:*</label>
                    <br />
                    <select id="empresa_id" name="empresa_id">' .
                        listar_empresas($mysqli) .
                    '</select>
                    <br /><br />
                <label for="es_admin">Administrador de la empresa:</label>
                <input type="checkbox" id="es_admin" name="es_admin" value="1">
                <br />
                <p id="footnote">Los campos marcados con un asterisco (*) son obligatorios</p>
                <br /><br />
                <input type="submit" value="Registrar">
            </form>
        </div>';

$mysqli->close();
