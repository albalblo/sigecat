document.addEventListener('DOMContentLoaded', function () {
    var cuerpo = document.getElementById('cuerpo');
    var popup_info = document.getElementById('popup-info');
    var popup_changes = document.getElementById('popup-changes');
    var popupText = document.getElementById('popup-text');
    var closeBtn = document.querySelector('.close-btn');
    var botones_topbar = document.querySelectorAll('.boton-topbar');

    // Genera un documento PDF utilizando la libreria html2pdf 
    document.getElementById('pdf-contable').addEventListener('click', generarPDF);

    // Usuario
    // Logout
    document.getElementById('cerrar-sesion').addEventListener('click', function() {
        window.location.href = '../php/usuario/logout.php';
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

    // Apartamentos
    document.getElementById('listado-apartamentos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-apartamento');
        boton_añadir.style.display = "inline";
        fetchContent('../php/apartamentos/mostrar_apartamento.php');
    });
    document.getElementById('añadir-apartamento').addEventListener('click', function() {
        fetchContent('../php/apartamentos/formulario_apartamento.php');
    });

    // Ingresos
    document.getElementById('listado-ingresos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-ingreso');
        boton_añadir.style.display = "inline";
        fetchContent('../php/ingresos/mostrar_ingresos.php');
    });
    document.getElementById('añadir-ingreso').addEventListener('click', function() {
        fetchContent('../php/ingresos/formulario_ingresos.php');
    });

    // Gastos
    document.getElementById('listado-gastos').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_añadir = document.getElementById('añadir-gasto');
        boton_añadir.style.display = "inline";
        fetchContent('../php/gastos/mostrar_gastos.php');
    });
    document.getElementById('añadir-gasto').addEventListener('click', function() {
        fetchContent('../php/gastos/formulario_gastos.php');
    });

    // Asiento contable de ingresos y gastos
    document.getElementById('asiento-contable').addEventListener('click', function() {
        botones_topbar.forEach((boton_topbar) =>
            boton_topbar.style.display = "none");
        var boton_continuar = document.getElementById('siguiente-contable');
        boton_continuar.style.display = "inline";
        fetchContent('../php/asiento_contable/asiento_gastos.php');
    });
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

        console.log(selectedIds);

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

    function postData(url = '', formData) {
        return fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text());
    }

    // Liquidación trimestral de IVA
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

    // Popup
    function handleInfoIcon(event) {
        var nextCell = event.target.parentNode.nextElementSibling;
        if (nextCell) {
            var dataHtml = nextCell.innerHTML;
            popupText.innerHTML = dataHtml;
            popup_info.style.display = 'flex';
        }
    }

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
        popup_changes.style.display = 'none';
    });

    window.onclick = function(event) {
        if (event.target === popup_info || event.target === popup_changes) {
            popup_info.style.display = 'none';
            popup_changes.style.display = 'none';
        }
    };

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
