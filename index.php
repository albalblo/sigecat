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
    // Si el usuario está logueado, se le redirige al Dashboard. Este es el único punto de la 
    // aplicación donde sucede esto, en el resto se llamará a una función que llevará el con -
    // trol de la sesión.
    if(isset($_SESSION['usuario'])) {
        header("Location: ./dashboard.php");
        exit(0);
    }
?>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="SIGEcAT: Software de Gestión Económica Alquileres Turísticos.">
        <meta name="keywords" content="SIGEcAT, gestión, alquileres, economía, turismo">
        <meta name="author" content="Alberto A. Alsina Ambrós (IES Joan Coromines)">
        <!-- Esta es una aplicación demo, no debe ser indexada por buscadores -->
        <meta name="robots" content="noindex, nofollow">
        
        <title>SIGEcAT - Login</title>
        
        <!--
            Para la evaluación del proyecto, se puede acceder a la aplicación mediante tres usuarios:
                ╔══════════════════════════╦══════════════╦═════════════╗
                ║ Tipo de usuario          ║   Usuario    ║ Contraseña  ║
                ╠══════════════════════════╬══════════════╬═════════════╣
                ║ Usuario raíz             │  00000000T   │    root     ║
                ╟──────────────────────────┼──────────────┼─────────────╢
                ║ Usuario administrador    │  11111111H   │    admin    ║
                ╟──────────────────────────┼──────────────┼─────────────╢
                ║ Usuario normal           │  22222222J   │    user     ║
                ╚══════════════════════════╧══════════════╧═════════════╝
        -->

        <link rel="icon" type="image/x-icon" href="/icons/favicon.ico">
        <!-- Podría tenerse solo un archivo CSS, pero resultaba inmenso -->
        <link rel="stylesheet" href="styles/index.css">
    </head>
    <body>
        <div class="logo-container">
            <img src="/icons/logo.png" width="100px" alt="SIGEcAT logo - Un gato minimalista en traje">
        </div>
        <div class="login-container">
            <h2>Identificación</h2>
            <?php
            // Ver si se ha intentado loguear y ha habido un fallo, para mostrar el aviso de error
            if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                echo '<div style="color: red; margin-bottom: 10px;">Login inválido. Inténtelo de nuevo.</div>';
            }
            ?>
            <form action="php/usuario/login.php" method="post" class="login-form">
                <div class="input-container">
                    <input type="text" name="dni" placeholder="NIF" pattern="[0-9]{8}[A-Za-z]" autocomplete="username" required>
                </div>
                <div class="input-container">
                    <input type="password" name="pass" placeholder="Contraseña" autocomplete="current-password" required>
                </div>
                <div class="input-container">
                    <input id="boton-login" type="submit" value="Iniciar sesión">
                </div>
                <div id="version">
                    <!-- La versión es orientativa -->
                    <p>SIGEcAT - Versión 1.2<br /><a href="https://github.com/albalblo/sigecat" target="_blank">Github</a></p>
                </div>
            </form>
        </div>
    </body>
</html>
