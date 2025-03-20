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
        .table,thead,tr,th,td {
            border: 1px solid black;
            border-bottom: 1px solid black !important;
        }

        #workerIDSelect1, #workerIDSelect2 {
            max-width: 150px; /* Define un ancho fijo */
            font-size: 0.9rem; /* Reduce el tamaño de fuente */
            padding: 5px 20px; /* Ajusta el padding */
        }
        #deptosSelect {
            max-width: 250px; /* Define un ancho fijo */
            font-size: 0.9rem; /* Reduce el tamaño de fuente */
            padding: 5px 50px; /* Ajusta el padding */
        }
        .select-checkbox-container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px; /* Ajustar el espaciado entre elementos */
        }

        .select-box {
            max-width: 150px; /* Ajuste de ancho para los selects */
            font-size: 0.9rem;
            padding: 2px 5px;
        }

        .checkboxes, .radio-container {
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid black;
            padding: 10px;
            background-color: #F0F0F0;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 5px; /* Espaciado entre checkbox y texto */
        }

        .form-check-label {
            margin-bottom: 0; /* Asegurarse de que las etiquetas estén centradas verticalmente */
            line-height: 1.6; /* Ajustar la altura de la línea para mejor alineación */
        }
        .bordered-container {
            border: 1px; /* Borde negro */
            padding: 10px; /* Espaciado interno */
            display: inline-block; /* Ajustar el tamaño del contenedor al contenido */
            background-color: #F0F0F0; /* Color de fondo similar al de la imagen */
        }
        .options-container {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: 20px;
        }
        .radio-container {
            flex-direction: column; /* Para que los radios queden en columna */
        }
        .fecha-container {
            display: inline-flex;
            align-items: center;
            gap: 8px; /* Espacio entre el input y el texto */
        }
        .row.mb-3 {
            display: flex;
            align-items: center; /* Alinea verticalmente */
            gap: 10px; /* Ajusta el espacio entre elementos */
        }
        .radio-bordered-container {
            border: 1px; /* Borde negro */
            padding: 10px; /* Espaciado interno */
            background-color: #F0F0F0; /* Color de fondo similar al de la imagen */
            margin-bottom: 15px;
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
        /* botón de seguros firmados */
        .custom-firmados-btn {
                margin-left: 10px; /* Ajusta según necesites */
        }

        .bordes-container {
            border: 1px; /* Borde visible */
            padding: 10px; /* Espaciado interno */
            background-color: #F0F0F0; /* Color de fondo */
            text-align: center; /* Alinear el contenido */
            display: flex;
            justify-content: center; /* Centrar contenido */
            align-items: center; /* Asegurar alineación vertical */
            margin: 0 15px; /* Espaciado entre divs laterales */
        }
        .certificado-container {
            display: flex;
            justify-content: center; /* Centrar el div dentro de su contenedor */
            align-items: center; /* Centrar verticalmente */
            margin: 0 20px; /* Espaciado entre divs laterales */
        }

        @media (max-width: 1024px) {
        .custom-radio-row {
            flex-direction: column;
            gap: 20px;
        }
        .bordered-container, .bordes-container, .radio-bordered-container {
            width: 100%;
            margin: 10px 0;
        }
        .fecha-container, .form-group {
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

        /* Fondo blanco para las filas */
        #tablaEmpleados tbody tr td {
            background-color: white !important;
            color: black; /* Asegura que el texto sea visible */
        }

        /* Fondo blanco para los encabezados */
        #tablaEmpleados thead th {
            background-color: white !important;
            color: black; /* Asegura que el texto sea visible */
        }

        .filter-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap; /* Para responsividad */
        }

        .ficha-container, 
        .departamento-container {
                flex: 1; /* Ocuparán el mismo espacio */
                min-width: 300px; /* Ancho mínimo antes de envolver */
        }

    .bordered-container {
        padding: 10px;
        border: 1px solid #ccc;
        background-color: white;
        width: 100%;
    }
    .departamento-container .bordered-container {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
    }

    /* Estilos para secciones */
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

    .active-section {
        opacity: 1;
        pointer-events: all;
    }

    }

    .contenedor-botones {
    display: flex;
    flex-direction: column; /* Alinea los botones en columna */
    align-items: flex-end;  /* Alinea a la derecha */
    gap: 10px; /* Espaciado entre botones */
    margin-top: 10px;
    width: 100%;
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
                                <form id="consultaEmplea" method="post">
                                    <div class="filter-container">
                                        <!-- Sección de Ficha/Dpto y Pantalla/Impresora -->
                                        <div class="ficha-container">
                                            <h4>Por ficha:</h4>
                                            <div class="bordered-container">
                                                <div class="select-checkbox-container">
                                                    <div>
                                                        <label for="workerIDSelect1"> De la </label>
                                                        <select class="form-control form-control-sm select-box" name="workerIDSelect1" id="workerIDSelect1" required>
                                                            <option value="" selected disabled>Seleccione una ficha</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label for="workerIDSelect2"> a la</label> 
                                                        <select class="form-control form-control-sm select-box" name="workerIDSelect2" id="workerIDSelect2" required>
                                                            <option value="" selected disabled>Seleccione una ficha</option>
                                                        </select>
                                                    </div>
                                                    <div class="options-container">
                                                        <div class="checkboxes">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="eventuales" id="eventuales">
                                                                <label for="eventuales" class="form-check-label">Solo eventuales</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="bajas" id="bajas">
                                                                <label for="bajas" class="form-check-label">Incluir bajas</label>
                                                            </div>
                                                        </div>
                                                        <div class="radio-container">
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
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Sección de Departamento -->
                                            <div class="departamento-container">
                                                <h4>Por departamento:</h4>
                                                <div class="bordered-container">
                                                    <div class="form-group">
                                                        <label for="deptosSelect">Departamento: </label>
                                                        <select class="form-control form-control-sm w-auto" name="deptosSelect" id="deptosSelect" required>
                                                            <option value="" selected disabled>Seleccione un departamento</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                    </div>
                                    <div class="container">
                                        <div class="row mb-3 custom-radio-row">
                                            <!-- Fechas -->
                                            <div class="col-sm-3">
                                                <div class="bordered-container">
                                                    <div class="fecha-container"> 
                                                        <label for="fechaPoliza" class="me-2">Fecha Póliza:</label>
                                                        <input type="date" name="fechaPoliza" id="fechaPoliza" class="form-control" style="width: 150px;"> 
                                                            Solo para eventuales
                                                    </div>
                                                    <div class="form-group d-flex align-items-center">
                                                        <label for="fechaIngreso" class="me-2">Fecha Ingreso:</label>
                                                        <input type="date" name="fechaIngreso" id="fechaIngreso" class="form-control" style="width: 150px;">
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="beneficiarios" id="beneficiarios">
                                                        <label for="beneficiarios" class="form-check-label">Con Beneficiarios</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="nombreEmplea" id="nombreEmplea">
                                                        <label for="nombreEmplea" class="form-check-label">Con Nombre del Trabajador</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="designacion" id="designacion">
                                                        <label for="designacion" class="form-check-label">Designación Irrevocable</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Botón Aceptar -->
                                            <button type="submit" id="" class="btn custom-accept-btn align-self-start">
                                                <img src="../dist/img/aceptar.png" height="20px" class="me-2">
                                                Aceptar
                                            </button>
                                    <!-- Columna derecha (Radios + Botón) -->
                                    <div class="col-sm-2">
                                        <div class="d-flex flex-column align-items-start">
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
                                    </div>
                                </div>
                            </div>
                            <br>
                            <!-- Tabla para mostrar resultados -->
                            <div class="container mt-3">
                                <table id="tablaEmpleados" class="tabla table-striped table-hover">
                                    <thead class="table-dark">
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
</form>
                            <!-- Contenedor para alinear los botones a la derecha -->
                            <div class="contenedor-botones">
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
            $(document).ready(function() 
            {
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
                            select1.empty();
                            select2.empty();
                            
                            data.worker.forEach(function(worker) {
                                var option = new Option(worker.ficha + '.- ' + worker.nombre, worker.ficha);
                                select1.append(option);
                                select2.append(option.cloneNode(true)); // Clonar opción para evitar referencia duplicada
                            });

                            select1.trigger('change');
                            select2.trigger('change');
                        } else {
                            console.log('No worker data available');
                        }

                        if (Array.isArray(data.depto) && data.depto.length > 0) {
                            var select = $('#deptosSelect');
                            select.empty();
                            data.depto.forEach(function(depto) {
                                var option = new Option(depto.descripcion, depto.depto);
                                select.append(option);
                            });
                            select.trigger('change');
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
                    scrollX: true,     // Habilita el scroll horizontal
                    scrollCollapse: true,  // Colapsa el scroll si hay menos datos
                    paging: false,
                    searching: false,
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay información",
                        "info": false,
                                        "infoEmpty": "Mostrando 0 a 0 de 0 Entradas",
                                        "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                                        "infoPostFix": "",
                                        "thousands": ",",
                                        "lengthMenu": "Mostrar _MENU_ Entradas",
                                        "loadingRecords": "Cargando...",
                                        "processing": "Procesando...",
                                        "search": false,
                                        "zeroRecords": "Sin resultados encontrados",
                                    },
                    "columns": [
                        {
                            "data": null, // Checkbox en la primera columna
                            render: function() {
                                    return '<input type="checkbox" class="print-checkbox">';
                            },
                            "orderable": false // La columna de checkbox no es ordenable
                        },
                        { "data": "ficha" },
                        { "data": "nombre" },
                    ]
                });

        // Manejar el envío del formulario
        $('#consultaEmplea').on('submit', function(e) {
            e.preventDefault(); // Evitar recarga de la página
            console.log("Formulario enviado");
            const tipoConsulta = $('input[name="tipoConsulta"]:checked').val();
                // Preparar datos según el tipo de consulta
                let requestData = {};
                if(tipoConsulta === 'ficha') {
                    requestData = {
                        tipo: 'ficha',
                        ficha1: $('#workerIDSelect1').val(),
                        ficha2: $('#workerIDSelect2').val(),
                        // eventuales: $('#eventuales').is(':checked') ? 1 : 0,
                        // bajas: $('#bajas').is(':checked') ? 1 : 0
                    };
                } else {
                    requestData = {
                        tipo: 'depto',
                        departamento: $('#deptosSelect').val()
                    };
                }
            // Realizar consulta AJAX
            $.ajax({
                url: 'MostrarEmp.php',
                type: 'POST',
                dataType: 'json',
                data: requestData,
                success: function(response) {
                    console.log("Respuesta del servidor:", response);
                    if (response.error) {
                        alert(response.error);
                    } else {
                        table.clear().draw();
                        if(response.data.length > 0) {
                            table.rows.add(response.data).draw();
                        } else {
                            alert("No hay datos...");
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error al cargar los datos');
                }
            });
        });

        // Evento para resaltar filas seleccionadas para eliminación
        $('#tablaEmpleados tbody').on('change', 'input.print-checkbox', function() {
            var row = $(this).closest('tr'); // Obtener la fila correspondiente
            if ($(this).is(':checked')) {
                row.css('background-color', 'green'); // Cambiar color de fondo si está marcada
            } else {
                row.css('background-color', ''); // Restaurar color original si no está marcada
            }
        });
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
</body>
</html>
