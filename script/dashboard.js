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

/**************************************************************************************************
 * El documento está dividido en cuatro partes diferenciadas:                                     *
 *         - Funciones de la barra lateral y de la barra superior                                 *
 *         - Funciones de los popups de información                                               *
 *         - Funciones de los iconos de editar y eliminar                                         *
 *         - Funciones de AJAX para tratar con formularios                                        *
 **************************************************************************************************/

document.addEventListener('DOMContentLoaded', function () {
    var cuerpo = document.getElementById('cuerpo');
    var popup_info = document.getElementById('popup-info');
    var popup_changes = document.getElementById('popup-changes');
    var popupText = document.getElementById('popup-text');
    var closeBtn = document.querySelector('.close-btn');
    var botones_topbar = document.querySelectorAll('.boton-topbar');

    // Llamada a la función para generar un documento PDF utilizando la libreria html2pdf 
    document.getElementById('pdf-contable').addEventListener('click', generarPDF);

/**************************************************************************************************
 *                     Funciones de la barra lateral y de la barra superior                       *
 **************************************************************************************************
 *                                            Usuario                                             *
 **************************************************************************************************/

    // Logout
    document.getElementById('cerrar-sesion').addEventListener('click', function() {
        window.location.href = '../php/usuario/logout.php';
    });

    //Vuelta a inicio clickando el logo
    document.getElementById('logo-sigecat').addEventListener('click', function() {
        fetchContent('../php/usuario/inicio.php');
    });

    // Modificar perfil
    document.getElementById('mod-perfil').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        fetchContent('../php/usuario/mod_usuario.php');
    });

    // Administrar usuarios
    const boton_admin_usuarios = document.getElementById('admin-usuarios');
    if (boton_admin_usuarios) {
        boton_admin_usuarios.addEventListener('click', function() {
            botones_topbar.forEach((boton_topbar) =>
                boton_topbar.style.display = "none");
            var boton_añadir = document.getElementById('añadir-usuario');
            boton_añadir.style.display = "inline";
            fetchContent('../php/admin_usuarios/mostrar_usuario.php');
        });
        document.getElementById('añadir-usuario').addEventListener('click', function() {
            fetchContent('../php/admin_usuarios/formulario_usuario.php');
        });
    }

 /**************************************************************************************************
 *                                          Apartamentos                                           *
 **************************************************************************************************/

    // Listar apartamentos
    document.getElementById('listado-apartamentos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-apartamento');
        boton_añadir.style.display = "inline";
        fetchContent('../php/apartamentos/mostrar_apartamento.php');
    });

    // Añadir apartamento
    document.getElementById('añadir-apartamento').addEventListener('click', function() {
        fetchContent('../php/apartamentos/formulario_apartamento.php');
    });


 /**************************************************************************************************
 *                                            Ingresos                                             *
 **************************************************************************************************/
    // Listar ingresos
    document.getElementById('listado-ingresos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-ingreso');
        boton_añadir.style.display = "inline";
        fetchContent('../php/ingresos/mostrar_ingresos.php');
    });

    // Añadir ingresos
    document.getElementById('añadir-ingreso').addEventListener('click', function() {
        fetchContent('../php/ingresos/formulario_ingresos.php');
    });


 /**************************************************************************************************
 *                                             Gastos                                              *
 **************************************************************************************************/

    // Listar gastos
    document.getElementById('listado-gastos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-gasto');
        boton_añadir.style.display = "inline";
        fetchContent('../php/gastos/mostrar_gastos.php');
    });

    // Añadir gasto
    document.getElementById('añadir-gasto').addEventListener('click', function() {
        fetchContent('../php/gastos/formulario_gastos.php');
    });


 /**************************************************************************************************
 *                                        Asiento contable                                         *
 **************************************************************************************************/

    // Se listan los gastos
    document.getElementById('asiento-contable').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_continuar = document.getElementById('siguiente-contable');
        boton_continuar.style.display = "inline";
        fetchContent('../php/asiento_contable/asiento_gastos.php');
    });

    // Se listan los ingresos y, escondido, se guarda el ID de los gastos seleccionados
    document.getElementById('siguiente-contable').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_mostrar = document.getElementById('mostrar-contable');
        boton_mostrar.style.display = "inline";

        var formData = new FormData();
        var checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        var selectedIds = [];
        
        checkboxes.forEach(function(checkbox) {
            selectedIds.push(checkbox.value);
            formData.append('ids[]', checkbox.value);
        });
        fetch('../php/asiento_contable/asiento_ingresos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('cuerpo').innerHTML = html;
        })
        .catch(error => {
            console.error('Error al recuperar los datos: ', error);
            document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
        });
    });

    // Se muestra el resultado del asiento contable por pantalla
    document.getElementById('mostrar-contable').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_mostrar = document.getElementById('pdf-contable');
        boton_mostrar.style.display = "inline";

        var formData = new FormData();
        var checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        var selectedIds = [];
        
        checkboxes.forEach(function(checkbox) {
            selectedIds.push(checkbox.value);
            formData.append('ids[]', checkbox.value);
        });

        console.log(selectedIds);

        fetch('../php/asiento_contable/asiento_contable.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('cuerpo').innerHTML = html;
        })
        .catch(error => {
            console.error('Error al recuperar los datos: ', error);
            document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
        });
    });

    // Función legacy, a refactorizar
    function postData(url = '', formData) {
        return fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text());
    }


 /**************************************************************************************************
 *                                     Liquidacion trimestral                                      *
 **************************************************************************************************/

    // Pantalla de selección de fecha y empresa
    document.getElementById('liquidacion-trimestral').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
            fetchContent('../php/trimestral/liquidacion_iva.php', function() {
                const form = document.getElementById('formIVA');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);

                        fetch('../php/trimestral/select_iva.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('cuerpo').innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error al recuperar los datos: ', error);
                            document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
                        });
                    });
                }
            });
    });



/**************************************************************************************************
 *                            Funciones de los popups de información                              *
 **************************************************************************************************
 *                               Handler del icono de información                                 *
 **************************************************************************************************/

    function handleInfoIcon(event) {
        var nextCell = event.target.parentNode.nextElementSibling;
        if (nextCell) {
            var dataHtml = nextCell.innerHTML;
            popupText.innerHTML = dataHtml;
            popup_info.style.display = 'flex';
        }
    }


 /**************************************************************************************************
 *                                 Handler del icono de delete                                     *
 **************************************************************************************************/

    function handleDelete(event, entity, endpoint, dataKey) {
        if (confirm(`¿Estás seguro de que deseas eliminar este ${entity}?`)) {
            let valor = event.target.getAttribute('data-value');
            const formData = new FormData();
            formData.append(dataKey, valor);
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('cuerpo').innerHTML = html;
            })
            .catch(error => {
                console.error('Error al recuperar los datos: ', error);
                document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
            });
        } else {
            window.location.href = '../dashboard.php';
        }
    }


 /**************************************************************************************************
 *                                Handler del icono de edicion                                     *
 **************************************************************************************************/

    function handleEdit(event, endpoint, dataKey) {
        let valor = event.target.getAttribute('data-value');
        const formData = new FormData();
        formData.append(dataKey, valor);
        console.log(valor);
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('cuerpo').innerHTML = html;
        })
        .catch(error => {
            console.error('Error al recuperar los datos: ', error);
            document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
        });
    }


 /**************************************************************************************************
 *                                  Handler del botón del IVA                                      *
 **************************************************************************************************/

    function handleFormIVA(event) {
        const form = document.getElementById('formIVA');
        const formData = new FormData(form);
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_mostrar = document.getElementById('pdf-contable');
        boton_mostrar.style.display = "inline";
        fetch('../php/trimestral/select_iva.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('cuerpo').innerHTML = html;
        })
        .catch(error => {
            console.error('Error al recuperar los datos: ', error);
            document.getElementById('cuerpo').innerHTML = 'Error al cargar los datos.';
        });
    }



/**************************************************************************************************
 *                         Funciones de los iconos de editar y eliminar                           *
 **************************************************************************************************/

    // Definición de los handlers y sus eventos relacionados
    const handlers = {
        'info-icon': handleInfoIcon,
        'delete-apartamento-icon': (event) => handleDelete(event, 'apartamento', '../php/apartamentos/eliminar_apartamento.php', 'id_apart'),
        'edit-apartamento-icon': (event) => handleEdit(event, '../php/apartamentos/mod_apartamento.php', 'apartamento_id'),
        'delete-gasto-icon': (event) => handleDelete(event, 'gasto', '../php/gastos/eliminar_gasto.php', 'id_gasto'),
        'edit-gasto-icon': (event) => handleEdit(event, '../php/gastos/mod_gasto.php', 'gasto_id'),
        'delete-ingreso-icon': (event) => handleDelete(event, 'ingreso', '../php/ingresos/eliminar_ingreso.php', 'id_ingreso'),
        'edit-ingreso-icon': (event) => handleEdit(event, '../php/ingresos/mod_ingreso.php', 'id_ingreso'),
        'delete-admin-users-icon': (event) => handleDelete(event, 'usuario', '../php/admin_usuarios/eliminar_usuario.php', 'id_usuario'),
        'edit-admin-users-icon': (event) => handleEdit(event, '../php/admin_usuarios/mod_usuario.php', 'id_usuario'),
        'formIVA': handleFormIVA
    };

    // Asegura la vinculación de eventos incluso después de la carga dinámica
    cuerpo.addEventListener('click', function (event) {
        for (let key in handlers) {
            if (event.target.classList.contains(key)) {
                console.log('Clicked on:', key);
                handlers[key](event);
                break;
            }
        }
    });

    // Cerrar popups
    closeBtn.addEventListener('click', function() {
        popup_info.style.display = 'none';
    });

    window.onclick = function(event) {
        if (event.target === popup_info || event.target === popup_changes) {
            popup_info.style.display = 'none';
        }
    };
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            popup_info.style.display = 'none';
        }
    });

    // Mostrar contenido en el cuerpo del dashboard
    function fetchContent(url, callback) {
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'loadData=true'
        })
        .then(response => response.text())
        .then(html => {
            cuerpo.innerHTML = html;
            if (callback) callback();
        })
        .catch(error => {
            console.error('Error recuperando los datos: ', error);
            cuerpo.innerHTML = 'Error al cargar los datos.';
        });
    }

    cuerpo.addEventListener('submit', function(event) {
        var target = event.target;
        if (target && target.id === 'formIVA') {
            event.preventDefault();
            botones_topbar.forEach((boton_topbar) =>
                boton_topbar.style.display = "none");
            var boton_mostrar = document.getElementById('pdf-contable');
            boton_mostrar.style.display = "inline";
            const formData = new FormData(target);

            fetch('../php/trimestral/select_iva.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                cuerpo.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al recuperar los datos: ', error);
                cuerpo.innerHTML = 'Error al cargar los datos.';
            });
        }
    });
});

function generarPDF() {
    const trimestral = document.getElementById('cuerpo');
    html2pdf(trimestral, {
        margin: 0,
        filename: 'documento_resumen_sigecat.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 1 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' } // En portrait la tabla no queda bien, demasiados datos
    });
}


// Prueba de inserción de esta función aquí, para asegurar su carga
flatpickr(".fecha_formulario", {
		   	dateFormat: "d-m-Y",
	    	    });
