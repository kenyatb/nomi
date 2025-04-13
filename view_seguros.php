<?php
session_start();
$varsesion = $_SESSION['username'];

if ($varsesion == null || $varsesion = '') 
{
    echo "Usted no tiene autorización!!";
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('../parts/encabezado.php') ?>
    <!-- Agrega estos dos enlaces dentro del <head> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <style>
    /* Estilos para tablas */
    .table, thead, tr, th, td {
        border: 1px solid black;
        border-bottom: 1px solid black !important;
    }

    /* Estilos de Selects */
    #workerIDSelect1, #workerIDSelect2, #deptosSelect {
        font-size: 0.9rem; /* Reduce el tamaño de fuente */
        padding: 5px 20px; /* Ajusta el padding */
    }

    #workerIDSelect1, #workerIDSelect2 {
        max-width: 150px; /* Define un ancho fijo */
    }

    #deptosSelect {
        max-width: 250px;
        padding: 5px 50px;
    }

    /* Contenedores flexibles */
    .select-checkbox-container,
    .checkboxes,
    .radio-container {
        display: flex;
        flex-wrap: wrap;
        align-items: left;
        gap: 15px;
    }

    .checkboxes, .radio-container {
        border: 1px solid black;
        padding: 10px;
        background-color: #F0F0F0;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-check-label {
        margin-bottom: 0;
        line-height: 1.6;
    }

    /* Contenedores con bordes */
    .bordered-container,
    .radio-bordered-container,
    .bordes-container {
        border: 1px solid black;
        padding: 10px;
        background-color: #F0F0F0;
        box-sizing: border-box;
    }

    /* Estilos generales */
    .bordered-container {
            border: 1px solid black;
            padding: 10px;
            background-color: #F0F0F0;
        }

    .radio-bordered-container {
        margin-bottom: 15px;
    }

    .bordes-container {
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 15px;
    }

    /* Estilos para alineación */
    .options-container {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-left: 20px;
    }

    .radio-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

    .fecha-container {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* Fila personalizada */
    .custom-radio-row {
        align-items: flex-start;
        gap: 40px;
    }

    /* Botón aceptar */
    .custom-accept-btn {
        margin-top: 20px !important;
        margin-left: 10px !important;
    }

    /* Botón de seguros firmados */
    .custom-firmados-btn {
        margin-left: 10px;
    }

    /* Contenedor de tabla */
    .contenedor-tabla {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        position: relative;
    }

    .tabla-container {
        flex-grow: 1;
        overflow: auto;
    }

    .contenedor-botones {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: sticky;
        top: 0;
        background: white;
        padding: 10px;
        z-index: 1000; /* Asegura que esté sobre otros elementos */
    }

    /* Ajustes para filtros */
    .filter-container {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .ficha-container,
    .departamento-container {
        flex: 1;
        min-width: 300px;
    }

    .departamento-container .bordered-container {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
    }

    /* Estilos para secciones activas e inactivas */
    .active-section {
        opacity: 1;
        pointer-events: all;
        transition: opacity 0.3s ease;
    }

    .ficha-container:not(.active-section),
    .departamento-container:not(.active-section) {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Contenedor principal que alinea Filtro de Fichas y Ficha/Depto */
    .contenedor-principal {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }

        .filtro-fichas {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap; /* Para mantener responsividad */
        }

        .filtro-fichas > div {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    /* Ajustes responsive */
    @media (max-width: 1024px) {
        .custom-radio-row {
            flex-direction: column;
            gap: 20px;
        }

        .bordered-container,
        .bordes-container,
        .radio-bordered-container {
            width: auto;
            margin: 10px 0;
        }

        .fecha-container,
        .form-group {
            flex-direction: column;
            align-items: flex-start;
        }

        .custom-accept-btn {
            margin-top: 10px !important;
            margin-left: 0 !important;
        }

        .tabla th {
            text-align: center;
            vertical-align: middle;
        }

        /* Fondo blanco para la tabla */
        #tablaEmpleados {
            background-color: white;
        }

        /* Fondo blanco para las filas y encabezados */
        #tablaEmpleados tbody tr td,
        #tablaEmpleados thead th {
            background-color: white !important;
            color: black;
        }

        .departamento-container,
        .tipo-container {
            width: 100%;
        }
    }

     /* Ajustes responsivos */
        @media (max-width: 768px) {
            .contenedor-principal {
                flex-direction: column;
            }

            .contenedor-botones {
                position: static;
            }
        }
        h4 {
            display: block;
            width: 100%;
            margin-bottom: 10px; /* Espaciado entre el título y el div */
        }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <header class="main-header">
            <?php include('../parts/header.php') ?>
        </header>
        <aside class="main-sidebar">
            <?php include('../parts/sidebar.php') ?>
        </aside>
            <div class="content-wrapper">
                <?php include('../parts/contenido.php') ?>
                    <section class="content container-fluid">
                        <h3>Reporte GENERAL DE SEGUROS</h3>
                        <div class="col-sm-9" style="background-color:#2c4e5f3d; padding:10px; border:1px solid #d2d2d2;">
                            <div style="text-align: right;">
                                <img src="../dist/img/logogeneral.png" style="width: 200px; height: auto;">
                            </div>
                            <div class="row" style="padding:20px; min-height: 300px;">
                                <form id="consultaEmplea" method="post" action = "poliza.php" target="_blank">
                                    <div class="filter-container">
                                        <!-- Sección de Ficha/Dpto y Pantalla/Impresora -->                                    
                                            <h4>Por ficha:</h4>
                                            <div class="contenedor-principal">
                                                <div class="filtro-fichas bordered-container">
                                                    <div>
                                                        <label for="workerIDSelect1">De la</label>
                                                        <select class="form-control form-control-sm select-box" name="workerIDSelect1" id="workerIDSelect1">
                                                            <option value="" selected disabled>Seleccione una ficha</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label for="workerIDSelect2">a la</label>
                                                        <select class="form-control form-control-sm select-box" name="workerIDSelect2" id="workerIDSelect2">
                                                            <option value="" selected disabled>Seleccione una ficha</option>
                                                        </select>
                                                    </div>
                                                    <div class="options-container">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="eventuales" id="eventuales">
                                                            <label for="eventuales" class="form-check-label">Solo eventuales</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="bajas" id="bajas">
                                                            <label for="bajas" class="form-check-label">Incluir bajas</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                    <!-- Sección Ficha/Depto. -->
                                                    <div class="bordered-container">
                                                        <div class="form-check">
                                                            <input type="radio" class="form-check-input" name="tipoConsulta" id="ficha" value="ficha" checked>
                                                            <label for="ficha" class="form-check-label">Ficha</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" class="form-check-input" name="tipoConsulta" id="depto" value="depto">
                                                            <label for="depto" class="form-check-label">Depto.</label>
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- Sección de Departamento -->
                                            <h4>Por departamento:</h4>
                                                <div class="bordered-container">
                                                    <div class="form-group">
                                                        <label for="deptosSelect">Departamento: </label>
                                                        <select class="form-control form-control-sm w-auto" name="deptosSelect" id="deptosSelect" required>
                                                            <option value="" selected disabled>Seleccione un departamento</option>
                                                        </select>
                                                    </div>
                                                </div>

                                            <!-- Sección tipo -->
                                                <div class="radio-container">
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" name="tipo" id="confianza" value="confianza" checked>
                                                        <label for="confianza" class="form-check-label">Confianza</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" name="tipo" id="sindicalizado" value="sindicalizado">
                                                        <label for="sindicalizado" class="form-check-label">Sindicalizado SUTIC</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" name="tipo" id="comisaria" value="comisaria">
                                                        <label for="comisaria" class="form-check-label">Comisaría</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" name="tipo" id="movilidad" value="movilidad">
                                                        <label for="movilidad" class="form-check-label">Operativo de Movilidad y Transporte</label>
                                                    </div>
                                                </div>
                                                <!-- Sección de fecha y botones -->
                                                <div class="bordered-container">
                                                    <div class="fecha-container"> 
                                                        <label for="fechaPoliza" class="me-2">Fecha Póliza:</label>
                                                        <input type="date" name="fechaPoliza" id="fechaPoliza" class="form-control" style="width: 150px;"> 
                                                            Solo para eventuales
                                                    </div>
                                                    <br>
                                                    <div class="form-group d-flex align-items-center">
                                                        <label for="fechaIngreso" class="me-2">Fecha Ingreso:</label>
                                                        <input type="date" name="fechaIngreso" id="fechaIngreso" class="form-control" style="width: 150px;">
                                                    </div>
                                                    <br>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="beneficiarios" id="beneficiarios">
                                                        <label for="beneficiarios" class="form-check-label">Con Beneficiarios</label>
                                                    </div>
                                                    <br>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="designacion" id="designacion">
                                                        <label for="designacion" class="form-check-label">Designación Irrevocable</label>
                                                    </div>
                                                </div>
                                </div>
                            </div>
                            <br>
                            <!-- Tabla para mostrar resultados -->
                            <div class="contenedor-tabla">
                                <div style="overflow: auto; white-space:nowrap;" class="container mt-3">
                                    <table id="tablaEmpleados" class="display nowrap" style="background-color: white;">
                                        <thead>
                                            <tr>
                                                <th>Imprimir</th>
                                                <th>Ficha</th>
                                                <th>Nombre</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Se llenará dinámicamente con DataTables -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                    <!-- Contenedor para alinear los botones a la derecha -->
                                    <div class="contenedor-botones">
                                                    <!-- Botón Aceptar -->
                                                    <button type="submit" id="" class="btn custom-accept-btn align-self-start">
                                                        <img src="../dist/img/aceptar.png" height="20px" class="me-2">
                                                        Aceptar
                                                    </button>
                                                    <div class="d-flex flex-column align-items-right">
                                                        <!-- Contenedor de radios -->
                                                        <div class="radio-bordered-container" style="width: fit-content; min-width: 130px">
                                                            <div class="form-check">
                                                                <input type="radio" class="form-check-input" name="destino" id="pantalla" value="pantalla" checked>
                                                                <label for="pantalla" class="form-check-label">Pantalla</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" class="form-check-input" name="destino" id="impresora" value="impresora">
                                                                <label for="impresora" class="form-check-label">Impresora</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <button type="submit" class="btn btn-default btn-md" id="imprimir">
                                                    <img src="../dist/img/impresora.png" height="30px"><br> Imprimir
                                                </button>
                                                <button type="submit" class="btn btn-default btn-md" id="seleccionar">
                                                    <img src="../dist/img/select_allpng.png" height="30px"><br> Selecc. todos
                                                </button>
                                                <button type="submit" class="btn btn-default btn-md" id="quitar">
                                                    <img src="../dist/img/quitar_todo.png" height="30px"><br> Quitar todos
                                                </button>
                                    </div>
                            </div>
</form>
    </div>
</div>
</div>
</div>
</section>
</div>
        <footer class="main-footer">
            <?php include('../parts/piepag.php') ?>
        </footer>
        <aside class="control-sidebar control-sidebar-dark">
            <?php include('../parts/sidebarl.php') ?>
        </aside>
        <div class="control-sidebar-bg"></div>
        <script src="../bower_components/jquery/dist/jquery.min.js"></script>
        <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../dist/js/adminlte.min.js"></script>
        <!-- Agrega este script al final del <body> -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap5.min.css">
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js" ></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js" ></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js" ></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/responsive.bootstrap5.min.js" ></script>
        <script>
            $(document).ready(function() {
                $('#workerIDSelect1, #workerIDSelect2').select2({
                    placeholder: 'Seleccione una ficha',
                    width: 'resolve'
                });

                $('#deptosSelect').select2({
                    placeholder: 'Seleccione un departamento',
                    width: 'resolve'
                });

                // Llamar a la función para obtener y llenar los datos
                obtenerDatosSelects();
            });

            function obtenerDatosSelects() {
                $.ajax({
                    url: '../../controllers/js/ajaxSeguros.php',
                    type: 'get',
                    success: function(response) {
                        var data = JSON.parse(response);

                        if (Array.isArray(data.worker) && data.worker.length > 0) {
                                var select1 = $('#workerIDSelect1');
                                var select2 = $('#workerIDSelect2');

                                // Agregar opción por defecto
                                select1.empty().append(new Option("Seleccione una ficha", "", true, true));
                                select2.empty().append(new Option("Seleccione una ficha", "", true, true));

                                data.worker.forEach(function(worker) {
                                    var option1 = new Option(worker.ficha + '.- ' + worker.nombre, worker.ficha, false, false);
                                    var option2 = new Option(worker.ficha + '.- ' + worker.nombre, worker.ficha, false, false);

                                    select1.append(option1);
                                    select2.append(option2);
                                });

                                // Forzar actualización de Select2
                                select1.trigger('change.select2');
                                select2.trigger('change.select2');
                            } else {
                                console.log('No worker data available');
                            }

                            if (Array.isArray(data.depto) && data.depto.length > 0) {
                                var select = $('#deptosSelect');
                                select.empty().append(new Option("Seleccione un departamento", "", true, true));

                                data.depto.forEach(function(depto) {
                                    var option = new Option(depto.descripcion, depto.idendepto, false, false);
                                    select.append(option);
                                });

                                select.trigger('change.select2');
                            } else {
                                console.log('No department data available');
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('AJAX request failed: ' + error);
                        }
                    });
                }
        </script>
        <script>
            $(document).ready(function() {
                // Inicializar DataTable
                var table = $('#tablaEmpleados').DataTable({
                    scrollY: "200px",  // Altura máxima para activar scroll vertical
                    scrollX: true,  // Habilita scroll horizontal automático si es necesario
                    scrollCollapse: true,  // Colapsa el scroll si hay menos datos
                    paging: false,
                    searching: false,
                    info: false,
                    responsive: false, // Desactiva responsive para evitar cambios inesperados
                    autoWidth: false,  // Evita que DataTables ajuste automáticamente el ancho
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay información",
                        "infoEmpty": "Mostrando 0 a 0 de 0 Entradas",
                        "zeroRecords": "Sin resultados encontrados",
                    },
                    columns: [
                        {
                            "data": null,
                            render: function() {
                                return '<input type="checkbox" class="print-checkbox">';
                            },
                            "orderable": false // La columna de checkbox no es ordenable
                        },
                        { "data": "ficha" },
                        { "data": "nombre" },
                    ],
                    columnDefs: [
                        { targets: "_all", className: "text-center" },
                    ]
                });

                // Ajusta el ancho de la tabla después de la inicialización
                $('#tablaEmpleados').css("margin-right", "40px"); // Espacio para los botones
                $('.dataTables_scrollBody').css("overflow-x", "hidden"); // Oculta el scrollbar horizontal

                // Seleccionar todos los checkboxes
                $('#seleccionar').on('click', function(e) {
                    e.preventDefault(); // Evitar acción por defecto
                    var table = $('#tablaEmpleados').DataTable();
                    
                    table.rows().every(function() {
                        var $row = $(this.node());
                        var $checkbox = $row.find('.print-checkbox');
                        $checkbox.prop('checked', true).trigger('change'); // Marcar y activar evento
                    });
                });

                // Quitar todos los checkboxes
                $('#quitar').on('click', function(e) {
                    e.preventDefault(); // Evitar acción por defecto
                    var table = $('#tablaEmpleados').DataTable();
                    
                    table.rows().every(function() {
                        var $row = $(this.node());
                        var $checkbox = $row.find('.print-checkbox');
                        $checkbox.prop('checked', false).trigger('change'); // Desmarcar y activar evento
                    });
                });

                    // Manejar el envío del formulario
                    $('#consultaEmplea').on('submit', function(e) {
                        e.preventDefault(); // Evitar recarga de la página
                        console.log("Formulario enviado");
                        
                        let ficha1 = $("#workerIDSelect1").val();
                        let ficha2 = $("#workerIDSelect2").val();
                        let idDepto = $("#deptosSelect").val();
                        let eventual = $("#eventuales").is(":checked") ?  1 : 0;
                        let bajas = $("#bajas").is(":checked") ? 1 : 0;
                        
                        let requestData = {};

                        // Verificamos si se seleccionó un departamento o un rango de fichas
                        if (idDepto) { 
                            requestData = { 
                                            tipo: "departamento", 
                                            idDepto: idDepto,
                                            eventual: eventual,
                                            bajas: bajas};
                        } else if (ficha1 && ficha2) {
                            requestData = { tipo: "ficha", ficha1: ficha1, 
                                            ficha2: ficha2,
                                            eventual: eventual,
                                            bajas: bajas};
                        }
                        else {
                            alert("Seleccione un departamento o ingrese un rango de fichas.");
                            return;
                        }

                        console.log("Datos enviados en AJAX:", requestData);

                        // Realizar consulta AJAX
                        $.ajax({
                            url: "MostrarEmp.php",
                            type: "POST",
                            dataType: "json",
                            data: requestData,
                            success: function (response) {
                                console.log("Datos completos recibidos:", response); // Verifica la respuesta completa

                                if (!response || response.error) {
                                    console.error("Error en la respuesta:", response.error);
                                    alert("Error: " + (response.error || "Respuesta inválida"));
                                    return;
                                }

                                let tabla = $("#tablaEmpleados").DataTable();
                                tabla.clear().rows.add(response.data).draw(false); // <-- Usar draw(false)
                                console.log("Datos en DataTable:", tabla.rows().data().toArray())
                                tabla.rows().every(function(index, element) {
                                    console.log("Fila " + index, this.data());
                                });
                            },
                            error: function (xhr, status, error) {
                                console.error("Error en AJAX:", status, error);
                                alert("Ocurrió un error al procesar la solicitud.");
                            }
                        });
                    });
                });

                // Evento para resaltar filas seleccionadas
                $('#tablaEmpleados tbody').on('change', 'input.print-checkbox', function() {
                    var row = $(this).closest('tr'); // Obtener la fila correspondiente
                    if ($(this).is(':checked')) {
                        row.css('background-color', '#D7E178'); // Cambiar color de fondo si está marcada
                    } else {
                        row.css('background-color', ''); // Restaurar color original si no está marcada
                    }
                });
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoConsulta = document.querySelectorAll('input[name="tipoConsulta"]');
            const ficha1 = document.getElementById('workerIDSelect1');
            const ficha2 = document.getElementById('workerIDSelect2');
            const depto = document.getElementById('deptosSelect');
            const fichaContainer = document.querySelector('.ficha-container');
            const deptoContainer = document.querySelector('.departamento-container');

            function actualizarCampos() {
                const esFicha = document.getElementById('ficha').checked;
                // Habilitar/deshabilitar campos
                ficha1.disabled = !esFicha;
                ficha2.disabled = !esFicha;
                depto.disabled = esFicha;

                 // Validación requerida
                ficha1.required = esFicha;
                ficha2.required = esFicha;
                depto.required = !esFicha;
                // Limpiar campos no usados
                esFicha ? depto.value = '' : (ficha1.value = '', ficha2.value = '');

                // Efecto visual
                fichaContainer.classList.toggle('active-section', esFicha);
                deptoContainer.classList.toggle('active-section', !esFicha);
        }

        tipoConsulta.forEach(radio => {
            radio.addEventListener('change', actualizarCampos);
        });
    
        // Inicializar estado
        actualizarCampos();
});
    </script>
    <script>
        $('#imprimir').on('click', function (e) {
    e.preventDefault();

    const destino = $('input[name="destino"]:checked').val();
    const fechaPoliza = $('#fechaPoliza').val() || ''; // opcional

    // Fichas seleccionadas desde la tabla
    const fichasSeleccionadas = [];
    $('input.print-checkbox:checked').each(function () {
        const ficha = $(this).closest('tr').find('td:eq(1)').text().trim(); // Extraer la ficha de la columna 2
        if (ficha) {
            fichasSeleccionadas.push(ficha);
        }
    });

    if (fichasSeleccionadas.length === 0) {
        alert("Debes seleccionar al menos una ficha para imprimir.");
        return;
    }

    // Formulario oculto
    const form = $('<form>', {
        action: 'poliza.php',
        method: 'POST',
        target: '_blank'
    });

    // Enviar fichas
    fichasSeleccionadas.forEach(function (ficha) {
        form.append($('<input>', {
            type: 'hidden',
            name: 'fichas[]',
            value: ficha
        }));
    });

    // Enviar si está marcada la designación irrevocable
    const designacionMarcada = $('#designacion').is(':checked') ? 1 : 0;
    form.append($('<input>', {
        type: 'hidden',
        name: 'designacion',
        value: designacionMarcada
    }));

    // Enviar fecha de póliza si aplica
    if (fechaPoliza) {
        form.append($('<input>', {
            type: 'hidden',
            name: 'fechaPoliza',
            value: fechaPoliza
        }));
    }

    // Enviar destino (pantalla / impresora)
    form.append($('<input>', {
        type: 'hidden',
        name: 'destino',
        value: destino
    }));

    // Agregar el formulario al body
    $('body').append(form);

    if (destino === 'pantalla') {
        // Si el destino es pantalla, abre una nueva ventana emergente para mostrar la póliza
        const ventanaEmergente = window.open('', '_blank', 'width=800,height=600');
        ventanaEmergente.document.write('<html><head><title>Póliza</title></head><body>');
        ventanaEmergente.document.write(form[0].outerHTML);
        ventanaEmergente.document.write('</body></html>');
        ventanaEmergente.document.close();
    } else if (destino === 'impresora') {
        // Si el destino es impresora, abrir la página en nueva ventana y forzar la impresión
        const ventanaEmergente = window.open('', '_blank', 'width=800,height=600');
        ventanaEmergente.document.write('<html><head><title>Póliza</title></head><body>');
        ventanaEmergente.document.write(form[0].outerHTML);
        ventanaEmergente.document.write('</body></html>');
        ventanaEmergente.document.close();
        
        // Después de que la ventana emergente se haya cargado, forzamos la impresión
        ventanaEmergente.onload = function () {
            ventanaEmergente.print();
        };
    }
});
</script>
</body>
</html>
