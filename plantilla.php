    <?php
    session_start();
    $varsesion = $_SESSION['username'];
    if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
        header("Location: ../index.php"); // Redirige al usuario a la página de login
    }
    header("Content-type:text/html; charset=utf-8");
    header("Pragma: no-cache"); //HTTP 1.0
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    setlocale(LC_TIME, "es_ES");
    include('../../config/conexion.php');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php include('../parts/encabezado.php') ?>
        <meta http-equiv="content-type" content="text/plain; charset=UTF-8"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
    </head>

    <style media="screen">
    .table,thead,tr,th,td {
        border: 1px solid black;
        border-bottom: 1px solid black !important;
    }
    #mytable_presup th{
        font-size: 1.2rem;
        font-weight: bold;
        line-height: 0.8;
        padding:5px;
        border-color:gray !important;
    }
    #mytable_presup>tbody>tr>td{
        padding:3px;
    }
    .trcolors{
        background-color: #337ab7;
        color:white;
    }
    .mostly-customized-scrollbar::-webkit-scrollbar {
    width: 5px;
    height: 8px;
    background-color: #f2f2f2;
    }
    .mostly-customized-scrollbar::-webkit-scrollbar-thumb {
    background: #000;
    color:pink;
    }
    .input-sm{
    font-size: 1.3rem;
    }
    td{
    font-size: 1.2rem;
    }
    #printdiv{
    text-align: center;
    }
    .open{
    max-width: -webkit-fill-available;
    max-height: 378.781px;
    }
    #mytable_operadoresExport>tbody>tr>td{
    font-size: 1rem;
    line-height: 1;
    font-weight: 500;
    padding:2px;
    text-align: left;
    }
    #mytable_operadoresExport th{
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 0.8;
    padding:2px;
    white-space: nowrap;
    }
    #mytable_operadoresExport thead>tr{
    background-color: #99ffff;
    }

    .input-xs {
        height: 25px;
        font-size: 12px;
        padding: 2px 5px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
    }
    @media (max-width: 357px) {
    #mytable_empleadosincidencias>tbody>tr>td{
        white-space: normal;
        min-width:100%;
    }
    #mytable_empleadosincidencias>tr>th{
        white-space: normal;
        min-width:100%;
    }
    .open, .bs-searchbox{
        max-height: 278.781px;
        max-width: 257.781px;
    }
    }
    </style>
    <style media="print">
    @media print {
        table{
        text-align: center;
        border: 1px solid #333;
        border-right: 1px solid #333;
        border-bottom: 1px solid #333;
        border-left: 1px solid #333;
        }
        .table th{
        background-color: #d2d2d2;
        }
        #printdiv{
        text-align: center;
        }
        #mytable_operadoresExport>tbody>tr>td{
        font-size: 1rem;
        line-height: 1;
        font-weight: 500;
        padding:2px;
        text-align: left;
        }
        #mytable_operadoresExport th{
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 0.8;
        padding:2px;
        white-space: nowrap;
        }
        #mytable_operadoresExport thead>tr{
        background-color: #99ffff;
        }
    }
    </style>
    <style>
            .cards-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .card {
        background-color: #ffffff;
        padding: 15px 20px;
        border: 1px solid #ccc;
        border-radius: 6px;
        min-width: 200px;
        margin: 10px;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
    }
    
    </style>
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
        <!-- Main content -->
        <section class="content container-fluid" id="seccionOperadores">
            <h3>Plantilla Administrativa</h3>
            <div class="col-md-12 " style="background-color:#dcdcdcba; padding:10px; border:1px solid #d2d2d2;">
                <div class="d-flex justify-content-center text-center cards-container">
                    <div class="card mx-2">
                        <h4>Plazas Autorizadas</h4>
                        <p id="plaza_autorizada">0</p>
                    </div>
                    <div class="card mx-2">
                        <h4>Plazas Ocupadas</h4>
                        <p id="plaza_ocupada">0</p>
                    </div>
                    <div class="card mx-2">
                        <h4>Plazas Disponibles</h4>
                        <p id="plaza_disponible">0</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-2">
                        <label>Unidad</label>
                        <?php
                            include('../../controllers/UnidadController.php');
                            $controller = new UnidadController();
                            echo $controller->unidades();
                        ?>
                    </div>
                    <div class="col-md-2">
                        <label>Depto.</label>
                        <select id="idenDepto" name="idenDepto" class="form-control selectpicker form-control-sm" data-live-search="true">
                            <!-- <option value="">Seleccione</option> -->
                        </select>
                    </div>
                    <div class="col-md-2">
                    <label>Puesto</label>
                    <?php
                        include('../../controllers/CategoriaController.php');
                        $controller = new CategoriaController();
                        echo $controller->puestos();
                    ?>
                    </div>
                    <div class="col-md-3">
                    <label>Perfil puesto</label>
                    <?php
                        include('../../controllers/PerfilPuestoController.php');
                        $controller = new PerfilPuestoController();
                        echo $controller->perfiles();
                    ?>
                    </div>
                    <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" id="consultar">Consultar</button>
                </div>
            </div>
                <br>
                <table id="tablaPlantilla" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Depto.</th>
                        <th>Puesto</th>
                        <th>Plazas Autorizadas</th>
                        <th>Plazas Ocupadas</th>
                        <th>Plazas Disponibles</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($empleados) && is_array($empleados)): ?>
                        <?php foreach ($empleados as $fila): ?>
                            <tr>
                                <td><?= $fila['unidad'] ?></td>
                                <td><?= $fila['departamento'] ?></td>
                                <td><?= $fila['nombre_puesto'] ?></td>
                                <td><?= $fila['plaza_ocupada'] ?></td>
                                <td><?= $fila['plaza_autorizada'] ?></td>
                                <td><?= $fila['plaza_disponible'] ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-editar" data-id="<?= $fila['folio_gral'] ?>">
                                        <i class="fa fa-pencil"></i> Actualizar
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $fila['folio_gral'] ?>">
                                        <i class="fa fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No se encontraron datos.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                </table>
                <!-- Botones de Excel -->
                <div class="row">
                <div class="col-xs-12 text-right" style="margin-top: 20px;">
                    <a href="rep_plantilla_completa.php" class="btn btn-success">
                    <span class="glyphicon glyphicon-download-alt"></span> Excel Plantilla Completa
                    </a>
                    <a href="rep_plantilla_comprimida.php" class="btn btn-primary">
                    <span class="glyphicon glyphicon-download-alt"></span> Excel Plantilla Comprimida
                    </a>
                </div>
                </div>
            </div>
            <!-- Modal Actualizar -->
            <div class="modal fade" id="modalActualizar" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Actualizar plaza</h4> 
                </div>
                <div class="modal-body">
                    <label>Unidad:</label>
                    <select class="form-control" id="unidad"></select>
                    <label>Depto:</label>
                    <select class="form-control" id="depto"></select>
                    <label>Puesto:</label>
                    <input type="text" class="form-control" id="edit_puesto">
                    <label>Perfil puesto:</label>
                    <select class="form-control" id="puesto"></select>
                    <label>Plazas Autorizadas:</label>
                    <input type="number" class="form-control" id="plazas_autorizadas" value="0">
                    <label>Plazas Ocupadas:</label> 
                    <input type="number" class="form-control" id="plazas_ocupadas" value="0">
                    <label>Plazas Disponibles:</label>  
                    <input type="number" class="form-control" id="plazas_disponibles" value="0">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Guardar</button>
                    <button class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
                </div>
            </div>
            </div>
            <!-- Modal Eliminar -->
            <div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Eliminar Registro</h4>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que deseas eliminar este registro?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger">Eliminar</button>
                    <button class="btn btn-default" data-dismiss="modal">Cancelar</button>
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
    </div>
    <!-- <script src="../bower_components/jquery/dist/jquery.min.js"></script> -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script src="../dist/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Eventos para botones (ejemplo)
        $(document).on('click', '.btn-editar', function() {
        $('#modalActualizar').modal('show');
        });

        $(document).on('click', '.btn-eliminar', function() {
        $('#modalEliminar').modal('show');
        });
    });
    </script>
    <script>
$(document).ready(function () {
    // 1. Cargar departamentos dinámicamente
    $('#unidad').on('change', function () {
        var unidad = $(this).val();

        $.ajax({
            url: '../../controllers/DeptoController.php',
            type: 'GET',
            data: { unidad: unidad },
            dataType: 'json',
            success: function (response) {
                var deptoSelect = $('#idenDepto');
                deptoSelect.empty();
                deptoSelect.append('<option value="">Seleccione</option>');

                $.each(response, function (index, item) {
                    deptoSelect.append(
                        $('<option>', {
                            value: item.idenDepto,
                            text: item.Descripcion
                        })
                    );
                });

                deptoSelect.selectpicker('refresh');
            },
            error: function () {
                alert('Error al cargar departamentos');
            }
        });
    });

    // 2. Inicializar la DataTable (solo una vez)
    let tabla = $('#tablaPlantilla').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        destroy: true // Permite reinicializar sin error (por si acaso)
    });

    // 3. Acción del botón Consultar
    $('#consultar').on('click', function () {
        let unidad = $('#unidad').val();
        let depto = $('#idenDepto').val();
        let puesto = $('#puesto').val();
        let perfil = $('#grupo').val();

        $.ajax({
            url: '../../controllers/PlantillaController.php',
            method: 'POST',
            data: {
                unidad: unidad,
                depto: depto,
                puesto: puesto,
                perfil: perfil
            },
            dataType: 'json',
            success: function (data) {
                tabla.clear(); // Limpiar tabla

                data.forEach(function (row) {
                    tabla.row.add([
                        row.unidad,
                        row.nombre_depto,
                        row.nombre_puesto,
                        row.plaza_autorizada,
                        row.plaza_ocupada,
                        row.plaza_disponible,
                        `<button class="btn btn-xs btn-info btn-editar" title="Actualizar" data-id="${row.folio_gral}">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>
                        <button class="btn btn-xs btn-danger btn-eliminar" title="Eliminar" data-id="${row.folio_gral}">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>`
                    ]);
                });

                tabla.draw(); // Redibujar tabla
            },
            error: function () {
                alert('Error al obtener datos de plantilla.');
            }
        });
    });

    // 4. Escuchar clics en botones de la tabla (delegado)
    $(document).on('click', '.btn-editar', function () {
        let folio = $(this).data('id');
        console.log('Actualizar folio:', folio);
        // Aquí podrías lanzar un modal o redirigir
    });

    $(document).on('click', '.btn-eliminar', function () {
        let folio = $(this).data('id');
        console.log('Eliminar folio:', folio);
        // Confirmar y luego llamar al backend para eliminar
    });
});
</script>
</body>
</html>
