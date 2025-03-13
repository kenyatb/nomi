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
</head>
<style>
    .table,thead,tr,th,td {
    border: 1px solid black;
    border-bottom: 1px solid black !important;
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
        <section class="content container-fluid">
            <h3>Relación de Pagos</h3>
            <h4>Para generar este reporte se deben de imprimir los sobres.</h4>
                <div class="col-md-4" style="background-color:#2c4e5f3d; padding:10px; border:1px solid #d2d2d2;">
                    <div class="row" style="padding:20px;">
                        <div class="col-md-12">
                            <div class="col-md-10 pricing-box mx-auto p-4" style="background-color:#fff; padding:20px;">
                                <div class="col-md-6" style="border:1px solid #d2d2d2; font-size:1.3rem; width:180px; margin-top:5px; margin-left:15px">
                                    <form action="rep_relPagos.php" method="post" id="form_relPagos" target="_self">
                                        <h4>Catorcena</h4> <input type="number" id="cat_rel" class="form-control" name="cat_rel" style="width: 80px;">                       
                                        <div class="form-check">
                                            <h4>Elige una opción</h4>
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input" name="optionDetalle" id="optionConc" value="1" checked><label for="optionConc">Concentrado</label> <br>
                                                <input type="radio" class="form-check-input" name="optionDetalle" id="optionDet" value="2"> <label for="optionDet">Detallado</label>  <br><br>
                                                
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="optionPago[]" id="optionTarjeta" value="3">
                                                <label for="optionTarjeta">Con tarjeta banorte</label> <br>
                                                <input type="checkbox" class="form-check-input" name="optionPago[]" id="optionSantander" value="5">
                                                <label for="optionTransfer">Tarjeta Santander</label> <br>
                                                <input type="checkbox" class="form-check-input" name="optionPago[]" id="optionTransfer" value="4">
                                                <label for="optionTransfer">Pago por transferencia</label> <br>
                                            </label>

                                                <input type="radio" class="form-check-input" name="optionTotUnidad" id="optionTotUnidad" value="5"> <label for="optionTotUnidad">Totales por Unidad</label><br>
                                            </label>
                                        </div>
                                        </div>

                                            <div class="col-md-3" style="border:1px solid #d2d2d2; font-size:1.3rem; width:140px; margin-top:5px; margin-left:15px">
                                                <div class="form-check">
                                                    <h4>Destino</h4>
                                                    <label class="form-check-label">
                                                        <input type="radio" class="form-check-input" name="optionDestino" id="optionImp" value="6" checked><label for="optionImp">Impresora</label> <br>
                                                        <input type="radio" class="form-check-input" name="optionDestino" id="optionPant" value="7"> <label for="optionPant">Pantalla</label>  <br>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <br>
                                        <br>
                                        <div class="row" style="text-align:right; margin-top: 30px;">
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-default btn-md" id="imprimir"><img src="../dist/img/impresora.png" height="20px"><br> Imprimir</button>
                                            </div>
                                        </div>
                                        </div>
                                    </form>
                        </div>
                </div>
    </div>
    </section>
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
   <script>
        document.getElementById('imprimir').addEventListener('click', function (event) {
        event.preventDefault();

        // Confirmación antes de continuar
        const userConfirmed = confirm('Recuerda fondo de retiro policial...\n¿Deseas continuar con el proceso?');
        if (!userConfirmed) {
            return;
        }

        // Obtener referencias a los elementos
        const form = document.getElementById('form_relPagos');
        const radiosDetalle = document.querySelectorAll('input[name="optionDetalle"]');

        // Actualizar el action del formulario
        function actualizarAction() {
            const seleccionado = Array.from(radiosDetalle).find(radio => radio.checked);
            if (seleccionado) {
                form.action = seleccionado.value === '1'
                    ? 'rep_relPagos.php' // Concentrado
                    : 'rep_relPagos_det.php'; // Detallado
            }
        }

        // Llama a la función para actualizar el action
        actualizarAction();

        // Obtener todas las opciones seleccionadas
        const options = {
            optionDetalle: document.querySelector('input[name="optionDetalle"]:checked')?.value || null,
            optionTarjeta: document.querySelector('input[name="optionPago[]"][id="optionTarjeta"]:checked') ? '3' : null,
            optionTransfer: document.querySelector('input[name="optionPago[]"][id="optionTransfer"]:checked') ? '4' : null,
        	optionSantander: document.querySelector('input[name="optionPago[]"][id="optionSantander"]:checked') ? '5' : null,
            optionTotUnidad: document.querySelector('input[name="optionTotUnidad"]:checked')?.value || '0', // Valor predeterminado si no está seleccionado
            optionDestino: document.querySelector('input[name="optionDestino"]:checked')?.value || null,
            catRel: document.getElementById('cat_rel')?.value || null, // Catorcena
        };

        // Validar que las opciones obligatorias estén presentes
        if (!options.optionDetalle || !options.optionDestino || !options.catRel) {
            alert('Por favor, selecciona todas las opciones obligatorias antes de continuar.');
            return;
        }

        // Construir los datos del formulario
        const formData = new FormData();
        formData.append('optionDetalle', options.optionDetalle);
        formData.append('optionDestino', options.optionDestino);
        formData.append('cat_rel', options.catRel);

        if (options.optionTarjeta) formData.append('optionPago[]', options.optionTarjeta);
        if (options.optionTransfer) formData.append('optionPago[]', options.optionTransfer);
        if (options.optionSantander) formData.append('optionPago[]', options.optionSantander);                                               
        if (options.optionTotUnidad !== '0') formData.append('optionTotUnidad', options.optionTotUnidad);

        // Enviar los datos mediante POST usando fetch
        fetch(form.action, { // Usa el action actualizado dinámicamente
            method: 'POST',
            body: formData,
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Error en la solicitud. Código de estado: ' + response.status);
            }
            return response.text(); // Cambia a response.json() si esperas JSON del servidor
        })
        .then((data) => {
            // Si el destino es impresora (6), imprimir directamente
            if (options.optionDestino == 6) { // 6 es impresora
                const iframe = document.createElement('iframe');
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                document.body.appendChild(iframe);

                // Crear un documento en el iframe para procesar la impresión
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(data); // Cargar el contenido recibido en el iframe
                doc.close();

                setTimeout(() => {
                    // 🛠️ Mueve el <tfoot> al final antes de imprimir
                    const table = doc.querySelector("table");
                    const tfoot = doc.querySelector("tfoot");
                    
                    if (tfoot && table) {
                        table.removeChild(tfoot); // Elimina el <tfoot>
                        
                        // Crea un div con su contenido y agrégalo al final del documento
                        const footerDiv = doc.createElement("div");
                        footerDiv.innerHTML = tfoot.innerHTML;
                        footerDiv.style.textAlign = "right";
                        footerDiv.style.fontWeight = "bold";
                        footerDiv.style.marginTop = "20px";
                        doc.body.appendChild(footerDiv);
                    }

                    // Imprimir en el iframe
                    iframe.contentWindow.print();

                    // Eliminar el iframe después de la impresión
                    iframe.remove();
                }, 500); // Pequeño delay para asegurar que el contenido esté listo

            } else {
                // Si es destino pantalla, abrir en ventana emergente
                const popup = window.open('', 'Reporte Relacion Pagos', 'width=800,height=600,scrollbars=yes');
                if (popup) {
                    popup.document.write(data); // Mostrar la respuesta en la ventana emergente
                    popup.document.close();

                    popup.onload = function() {
                        const table = popup.document.querySelector("table");
                        const tfoot = popup.document.querySelector("tfoot");

                        if (tfoot && table) {
                            table.removeChild(tfoot); // Elimina el <tfoot>
                            
                            // Crea un div con su contenido y agrégalo al final del documento
                            const footerDiv = popup.document.createElement("div");
                            footerDiv.innerHTML = tfoot.innerHTML;
                            footerDiv.style.textAlign = "right";
                            footerDiv.style.fontWeight = "bold";
                            footerDiv.style.marginTop = "20px";
                            popup.document.body.appendChild(footerDiv);
                        }
                    };
                } else {
                    alert('Por favor, habilita los pop-ups para visualizar el reporte.');
                }
            }
        })
        .catch((error) => {
            console.error('Error al enviar los datos:', error);
            alert('Hubo un error al procesar la solicitud. Por favor, inténtalo nuevamente.');
        });
    });
</script>
</body>
</html>
