<!DOCTYPE html>
<!-------------------------------------------------------------------------------------------------
 - Proyecto de Fin de Ciclo Formativo de Grado Superior                                           -
 - 'Software de Gestión Económica Alquileres Turísticos' (SIGEcAT)                                -
 - Alumno: Alberto A. Alsina Ambrós                                                               -
 - Tutor: Jordan Llorach Beltrán                                                                  -
 - Centro formativo: IES Joan Coromines (Benicarló, España)                                       -
 - Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (CFGS DAW)                 -
 - Curso 2023/2024                                                                                -
 --------------------------------------------------------------------------------------------------
 - Licencia:                                                                                      -
 - Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional (CC BY-NC-SA 4.0)     -
 -     • Atribución (BY): El licenciante permite a otros distribuir, remezclar, retocar y crear a -
 -                        partir de  su obra, incluso con  fines comerciales, siempre y cuando se -
 -                        reconozca   la autoría  de   la   obra  original de    manera adecuada. -
 -     • No Comercial (NC): El licenciante permite a otros copiar, distribuir, mostrar y ejecutar -
 -                          la obra,  así como hacer obras derivadas basadas en ella, pero no con -
 -                          fines comerciales. Si desean utilizar  la obra con fines comerciales, -
 -                          necesitarán       obtener        permiso       del       licenciante. -
 -     • Compartir Igual (SA): Si se remezcla, transforma o se crea a partir de la obra original, -
 -                             la nueva  obra generada debe    ser distribuida bajo  una licencia -
 -                             idéntica                             a                       ésta. -
 --------------------------------------------------------------------------------------------------
 - ESTE SOFTWARE ES PROPORCIONADO POR LOS TITULARES DE LOS DERECHOS DE AUTOR Y LOS CONTRIBUYENTES -
 - "TAL CUAL"  Y CUALQUIER GARANTÍA EXPRESA O   IMPLÍCITA,  INCLUYENDO,  PERO NO  LIMITADA A, LAS -
 - GARANTÍAS   IMPLÍCITAS  DE COMERCIABILIDAD   Y   APTITUD PARA UN  PROPÓSITO  PARTICULAR QUEDAN -
 - RECHAZADAS.  EN NINGÚN CASO EL TITULAR DE LOS   DERECHOS DE  AUTOR O  LOS CONTRIBUYENTES SERÁN -
 - RESPONSABLES POR NINGÚN DAÑO DIRECTO, INDIRECTO, INCIDENTAL, ESPECIAL,  EJEMPLAR O CONSECUENTE -
 - (INCLUYENDO, PERO NO LIMITADO A, LA  ADQUISICIÓN DE BIENES O SERVICIOS  SUSTITUTOS; PÉRDIDA DE -
 - USO,  DATOS O  BENEFICIOS; O  INTERRUPCIÓN  DE  NEGOCIOS) SIN IMPORTAR LA CAUSA Y EN CUALQUIER -
 - TEORÍA DE RESPONSABILIDAD, YA SEA EN CONTRATO,  RESPONSABILIDAD ESTRICTA O AGRAVIO (INCLUYENDO -
 - NEGLIGENCIA O DE OTRO MODO) QUE SURJA DE CUALQUIER MANERA DEL USO DE ESTE SOFTWARE, INCLUSO SI -
 - SE        HA    ADVERTIDO    DE           LA        POSIBILIDAD     DE            TALES DAÑOS. -
 -------------------------------------------------------------------------------------------------->
<?php
    // Podría hacerse una llamada a las funciones, pero en más de una ocasión ha dado problemas al
    // reconocer la sesión. Como el Dashboard es lo primero que se carga, se ha preferido mantener
    // una llamada explícita.
    session_start();

    if(!isset($_SESSION['usuario'])) {
        header("Location: ./index.php");
        exit(0);
    }
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard de SIGEcAT: Software de Gestión Económica Alquileres Turísticos.">
    <meta name="keywords" content="SIGEcAT, dashboard, gestión, alquileres, economía, turismo">
    <meta name="author" content="Alberto A. Alsina Ambrós (IES Joan Coromines)">
    <!-- Esta es una aplicación demo, no debe ser indexada por buscadores -->
    <meta name="robots" content="noindex, nofollow">
    
    <title>SIGEcAT - Dashboard</title>
    
    <link rel="icon" type="image/x-icon" href="/icons/favicon.ico">
    <link rel="stylesheet" href="styles/dashboard.css">

    <!-- Librería para generar el PDF con el IVA trimestral -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

    <!-- Librería para poder mostrar las fechas con un formato más amigable -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

    <body>
        <div class="dashboard-container">
            <div class="sidebar">
                <div id="usuario">
                    <img src="/icons/logo.png" alt="SIGEcAT logo - Un gato minimalista en traje" id="logo-sigecat" width="64px"></img>
                    <?php
                        echo "<h1>" . $_SESSION['nombre'] . " " . $_SESSION['apellidos'] . "</h1>";
                    ?>
                    <!-- Para evitar cambios de página, no es un <a>, sino que se tratará con JavaScript -->
                    <p id="mod-perfil"><i>Modificar perfil...</i></p>
                </div>
                <div id="botones">
                    <button class="boton-sidebar" id="listado-apartamentos" type="button">Listado de Apartamentos</button>
                    <button class="boton-sidebar" id="listado-ingresos" type="button">Ingresos</button>
                    <button class="boton-sidebar" id="listado-gastos" type="button">Gastos</button>
                    <button class="boton-sidebar" id="asiento-contable" type="button">Asiento contable</button>
                    <button class="boton-sidebar" id="liquidacion-trimestral" type="button">Liquidación IVA</button>
                    <?php
                      if($_SESSION['es_admin'] == 1) {
                        echo'<button class="boton-sidebar" id="admin-usuarios" type="button">Administrar usuarios</button>';
                      }
                    ?>
                </div>
                <div id="boton-logout">
                    <button class="boton-logout" id="cerrar-sesion" type="button">Cerrar sesión</button>
                    <div id="version">
                        <p>SIGEcAT - Versión 1.2<br /><a href="https://github.com/albalblo/sigecat" target="_blank">Github</a> | <a href="mailto:sigecat@albertoalsina.es" target="_blank">Contacto</a></p>
                    </div>
                </div>
            </div>
            <div class="main-content">
                <div id="top-bar">
                    <button class="boton-topbar" id="añadir-apartamento" type="button" style="display: none;">Nuevo Apartamento</button>
                    <button class="boton-topbar" id="añadir-ingreso" type="button" style="display: none;">Nuevo Ingreso</button>
                    <button class="boton-topbar" id="añadir-gasto" type="button" style="display: none;">Nuevo Gasto</button>
                    <button class="boton-topbar" id="añadir-usuario" type="button" style="display: none;">Nuevo Usuario</button>
                    <button class="boton-topbar" id="siguiente-contable" type="button" style="display: none;">Siguiente</button>
                    <button class="boton-topbar" id="mostrar-contable" type="button" style="display: none;">Generar asiento contable</button>
                    <button class="boton-topbar" id="pdf-contable" type="button" style="display: none;">Descargar documento PDF</button>
                </div>
                <div id="cuerpo">
                    <p>Seleccione una opción para continuar...</p>
                </div>
                <div id="popup-info" style="display: none;" class="popup-container">
                    <div class="popup-content">
                        <span class="close-btn">&times;</span>
                        <div id="popup-text"></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Estos scripts deberían poder estar en dashboard.js, pero siempre cargaban antes del DOM, por lo que se movieron.
            function toggleCheckboxes(source) {
                checkboxes = document.querySelectorAll('.rowCheckbox');
                for(var i = 0, n = checkboxes.length; i < n; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
            function updateMaxPersonas() {
                var select = document.getElementById('apartamento_id');
                var maxPersonas = select.options[select.selectedIndex].getAttribute('data-max-personas');
                document.getElementById('num_clientes').max = maxPersonas;
            }
            document.addEventListener('DOMContentLoaded', function() {
		    flatpickr(".fecha_formulario", {
		   	dateFormat: "d-m-Y",
	    	    });
            });
        </script>
        <script src="script/dashboard.js" type="module"></script>
    </body>
</html>
