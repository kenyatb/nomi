<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de Relación de Pagos Concentrado</title>
    <style>
        .no-border {
            border: none;
            text-align: center;
            font-weight: bold;
        }
        th,
        td {
            text-align: center;
            padding: 5px;
        }

        th {
            background-color: #97A2A8;
            color: #fff;
        }
        @media print {
            body {
                background-color: #f4f4f4;
            }

            #previewContainer,
            .no-imprimir {
                display: none;
            }
        	tfoot {
                display: table-row-group !important; /* Evita que se repita en cada página */
            }
        }
        @media screen {
            body {
                background-color: #ffffff;
            }
        }
		.titulo-firma {
            text-align: center;  /* Centra el texto horizontalmente */
            width: 100%;         /* Ocupa todo el ancho disponible */
            display: block;      /* Asegura que se comporte como un bloque */
            margin-top: 20px;    /* Espacio superior opcional */
            font-weight: bold;   /* Opcional: negrita para mayor visibilidad */
        }
    </style>
</head>

<body>
    <table align='center' cellpadding='1' cellspacing='1' width='100%'>
        <thead>
            <tr>
                <td colspan='12' class='no-border'>PRESIDENCIA DE SALAMANCA, GUANAJUATO</td>
            </tr>
            <tr>
                <td colspan='12' class='no-border'>DIRECCIÓN DE RECURSOS HUMANOS</td>
            </tr>
            <tr>
                <td colspan='12' class='no-border' id='tituloReporte'>RELACIÓN DE PAGOS POR DEPARTAMENTOS</td>
            </tr>
            <tr>
                <td colspan='12' class='no-border'>Catorcerna: <?php echo $catorcena . " " . $vperiodo ?></td>
            </tr>
            <tr>
                <th>UNIDAD</th>
                <th>DEPTO.</th>
                <th>DEPARTAMENTO</th>
                <th>PERCEPCIONES</th>
                <th>VALES</th>
                <th>DEDUCCIONES</th>
                <th>TOTAL EFECTIVO</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $pagosAgrupadosPorForma = [];
            foreach ($data as $row) {
                $pagosAgrupadosPorForma[$row['vtipo_pago']][$row['unidadN']][] = $row;
            }
            // var_dump($total_unidades);  
            if ($total_unidades == 5) 
            {
                $total_percepciones = $total_vales = $total_deducciones = $total_subtotal = $total_general = 0; // Totales generales
                $mapFormaPago = [
    					"Con Tarjeta Banorte" => "N-BANORTE",
    					"Con Tarjeta Santander" => "N-SANTANDER",
    					"Por transferencia" => "S",
				];

                $pagosAgrupadosPorForma = [];
                foreach ($data as $row) { 
    				// Convertir 'vtipo_pago' a 'N' o 'S'
    				$formaPago = $mapFormaPago[$row['vtipo_pago']] ?? null;
                
                	if (!empty($formaPago)) {
                        $pagosAgrupadosPorForma[$formaPago][$row['unidadN']][] = $row;
                    }
				}
                $totalesPorTipoPago = [];
                foreach ($dataTipo as $total) {
                    $totalesPorTipoPago[$total['Tipo_de_Pago']] = $total;
                }
                // Filas base, cada que cambie de año editar aquí
                $fondosArray = [
                    '11' => ['fondo' => '1125100000', 'nombre' => 'Recursos Fiscales ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '15' => ['fondo' => '1525811100', 'nombre' => 'Participaciones ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '25' => ['fondo' => '2525822100', 'nombre' => 'FORTAMUN ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                ];
                $fondosTarjeta = [
                    '11' => ['fondo' => '1125100000', 'nombre' => 'Recursos Fiscales ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '15' => ['fondo' => '1525811100', 'nombre' => 'Participaciones ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '25' => ['fondo' => '2525822100', 'nombre' => 'FORTAMUN ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                ];
            	$fondosSantander = [
                    '11' => ['fondo' => '1125100000', 'nombre' => 'Recursos Fiscales ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '15' => ['fondo' => '1525811100', 'nombre' => 'Participaciones ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '25' => ['fondo' => '2525822100', 'nombre' => 'FORTAMUN ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                ];
                $fondosTransferencia = [
                    '11' => ['fondo' => '1125100000', 'nombre' => 'Recursos Fiscales ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '15' => ['fondo' => '1525811100', 'nombre' => 'Participaciones ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                    '25' => ['fondo' => '2525822100', 'nombre' => 'FORTAMUN ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                ];

                foreach ($pagosAgrupadosPorForma as $formaPago => $unidades)//tenía $vtipo_pago en vez de $formaPago 
                { 
           			if ($formaPago == "N-BANORTE") 
                    {
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Banorte </td></tr>";
                    
                    }if ($formaPago == "N-SANTANDER") 
                    {
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Santander </td></tr>";
                    }
                	else 
                    {
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Por transferencia </td></tr>";
                    }
                	
                    foreach ($unidades as $unidad => $empleados) 
                    {
                        $unidad_percepciones = $unidad_vales = $unidad_deducciones = $unidad_subtotal = $unidad_total = 0;

                        foreach ($empleados as $row) 
                        {
                            // este es el acumulado para la suma total
                            $unidad_percepciones += $row['percepciones'];
                            $unidad_vales += $row['canasta'];
                            $unidad_deducciones += $row['deducciones'];
                            $unidad_subtotal += $row['total_efectivo'];
                            $unidad_total += $row['total'];
                            // Extrae los dos primeros dígitos del fondo
                            $fondoPrefix = substr($row['fondo'], 0, 2);

                            // Si el prefijo existe en $fondosArray, actualizamos sus valores
                            if (array_key_exists($fondoPrefix, $fondosArray)) {
                                // Acumulamos los fondos en el array original
                                $fondosArray[$fondoPrefix]['valores'][0] += $row['percepciones'];
                                $fondosArray[$fondoPrefix]['valores'][1] += $row['canasta'];
                                $fondosArray[$fondoPrefix]['valores'][2] += $row['deducciones'];
                                $fondosArray[$fondoPrefix]['valores'][3] += $row['total_efectivo'];
                                $fondosArray[$fondoPrefix]['valores'][4] += $row['total'];

                                // Duplicamos los valores en el nuevo array acumulado
                                if (!isset($acumuladoDeFondos[$fondoPrefix])) {
                                    $acumuladoDeFondos[$fondoPrefix]['valores'] = [0, 0, 0, 0, 0];
                                }
                                $acumuladoDeFondos[$fondoPrefix]['valores'][0] += $row['percepciones'];
                                $acumuladoDeFondos[$fondoPrefix]['valores'][1] += $row['canasta'];
                                $acumuladoDeFondos[$fondoPrefix]['valores'][2] += $row['deducciones'];
                                $acumuladoDeFondos[$fondoPrefix]['valores'][3] += $row['total_efectivo'];
                                $acumuladoDeFondos[$fondoPrefix]['valores'][4] += $row['total'];

                                //fondos para tarjeta banorte
                                if (!isset($fondosTarjeta[$fondoPrefix])) {
                                    $fondosTarjeta[$fondoPrefix]['valores'] = [0, 0, 0, 0, 0];
                                }
                                if ($formaPago == 'N-BANORTE') {
                                	
                                    	$fondosTarjeta[$fondoPrefix]['valores'][0] += $row['percepciones'];
                                    	$fondosTarjeta[$fondoPrefix]['valores'][1] += $row['canasta'];
                                    	$fondosTarjeta[$fondoPrefix]['valores'][2] += $row['deducciones'];
                                    	$fondosTarjeta[$fondoPrefix]['valores'][3] += $row['total_efectivo'];
                                    	$fondosTarjeta[$fondoPrefix]['valores'][4] += $row['total'];
                                }
                            	//fondos para tarjeta santander
								if (!isset($fondosSantander[$fondoPrefix])) {
                                    $fondosSantander[$fondoPrefix]['valores'] = [0, 0, 0, 0, 0];
                                }
                                if ($formaPago == 'N-SANTANDER') 
                                {
                                   	 	$fondosSantander[$fondoPrefix]['valores'][0] += $row['percepciones']; 
                                    	$fondosSantander[$fondoPrefix]['valores'][1] += $row['canasta'];
                                    	$fondosSantander[$fondoPrefix]['valores'][2] += $row['deducciones'];
                                    	$fondosSantander[$fondoPrefix]['valores'][3] += $row['total_efectivo'];
                                    	$fondosSantander[$fondoPrefix]['valores'][4] += $row['total'];
                                }
                                // Duplicamos los valores en el nuevo array acumulado
                                if (!isset($fondosTransferencia[$fondoPrefix])) {
                                    $fondosTransferencia[$fondoPrefix]['valores'] = [0, 0, 0, 0, 0];
                                }
                                if ($formaPago == 'S') {
                                    //fondos para transferencia
                                    $fondosTransferencia[$fondoPrefix]['valores'][0] += $row['percepciones'];
                                    $fondosTransferencia[$fondoPrefix]['valores'][1] += $row['canasta'];
                                    $fondosTransferencia[$fondoPrefix]['valores'][2] += $row['deducciones'];
                                    $fondosTransferencia[$fondoPrefix]['valores'][3] += $row['total_efectivo'];
                                    $fondosTransferencia[$fondoPrefix]['valores'][4] += $row['total'];
                                }
                            }

                            echo "<tr>
                                        <td>{$row['unidadN']}</td>
                                        <td>{$row['deptoN']}</td>
                                        <td>{$row['Nombre_Depto']}</td>
                                        <td style='text-align: right;'>" . number_format($row['percepciones'], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($row['canasta'], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($row['deducciones'], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($row['total_efectivo'], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($row['total'], 2) . "</td>
                                    </tr>";
                        }

                        // Totales por unidad
                        echo "<tr>
                                    <td colspan='3' style='font-weight: bold; text-align: right;'>Suma Unidad </td>
                                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($unidad_percepciones, 2) . "</td>
                                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($unidad_vales, 2) . "</td>
                                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($unidad_deducciones, 2) . "</td>
                                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($unidad_subtotal, 2) . "</td>
                                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($unidad_total, 2) . "</td>
                                </tr>";
                                // estos son solo para el total final, el de hasta abajo
                                $total_percepciones += $unidad_percepciones;
                                $total_vales += $unidad_vales;
                                $total_deducciones += $unidad_deducciones;
                                $total_subtotal += $unidad_subtotal;
                                $total_general += $unidad_total;

                        // Imprimir las filas de los fondos
                        foreach ($fondosArray as $fondo) {
                            echo "<tr>
                                        <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                    </tr>";
                        }
                        // Resetear el array de fondos para la nueva unidad
                        $fondosArray = [
                            '11' => ['fondo' => '1125100000', 'nombre' => 'Recursos Fiscales ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                            '15' => ['fondo' => '1525811100', 'nombre' => 'Participaciones ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                            '25' => ['fondo' => '2525822100', 'nombre' => 'FORTAMUN ' . date("Y"), 'valores' => [0, 0, 0, 0, 0]],
                        ];
                    }
                    // var_dump($fondosTarjeta);
                    // Insertar totales para el tipo de pago actual
                    if (isset($totalesPorTipoPago[$formaPago])) 
                    {
                        $totales = $totalesPorTipoPago[$formaPago];
                        echo "<tr>";
                        echo "<td colspan='3' style='text-align: right;font-weight: bold;'>Totales ";

                        if ($formaPago == "N-BANORTE") {
        						
            					echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Banorte </td></tr>";
                        }
        				if ($formaPago == "N-SANTANDER") {
            					echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Santander </td></tr>";
    					} else {
        						echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Por transferencia </td></tr>";
    					}
                        echo "</td>";
                        echo "<td>" . number_format($totales['Total_Percepciones'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Canasta'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Deducciones'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Efectivo'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_General'], 2) . "</td>";
                        echo "</tr>";

                        if($formaPago == "N-BANORTE")
                        {
                            // Imprimir las filas de los fondos
                            foreach ($fondosTarjeta as $fondo) {
                                echo "<tr>
                                    <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                </tr>";
                            }
                        }if($formaPago == "N-SANTANDER"){
                        		
                        	// Imprimir las filas de los fondos
                            foreach ($fondosSantander as $fondo) {
                                echo "<tr>
                                    <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                    <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                </tr>";
                            }
                        }
                    else{
                            // Imprimir las filas de los fondos
                            foreach ($fondosTransferencia as $fondo) {
                                    echo "<tr>
                                        <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                        <td style='text-align: right; font-weight: bold;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                    </tr>";
                            }
                        }
                    }
                }
                // Totales generales finales
                echo "<tr style='font-weight: bold;'>
                        <td colspan='3' style='text-align: right;'>SUMA TOTAL</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($total_percepciones, 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($total_vales, 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($total_deducciones, 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($total_subtotal, 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($total_general, 2) . "</td>
                    </tr>";
                foreach ($fondosArray as $fondoPrefix => $fondo) 
                {
                    // Verificar si existen valores acumulados, si no, usar ceros
                    $acumulados = isset($acumuladoDeFondos[$fondoPrefix]) ? $acumuladoDeFondos[$fondoPrefix]['valores'] : [0, 0, 0, 0, 0];

                    echo "<tr>
                                <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                <td style='text-align: right; font-weight: bold;'>" . number_format($acumulados[0], 2) . "</td>
                                <td style='text-align: right; font-weight: bold;'>" . number_format($acumulados[1], 2) . "</td>
                                <td style='text-align: right; font-weight: bold;'>" . number_format($acumulados[2], 2) . "</td>
                                <td style='text-align: right; font-weight: bold;'>" . number_format($acumulados[3], 2) . "</td>
                                <td style='text-align: right; font-weight: bold;'>" . number_format($acumulados[4], 2) . "</td>
                            </tr>";
                }
            } else { // aquí es para el reporte de concentrados
                $total_percepciones = $total_vales = $total_deducciones = $total_subtotal = $total_general = 0;
                $mapFormaPago = [
                    "Con Tarjeta Banorte" => "N",
                    "Por transferencia" => "S",
                	"Con Tarjeta Santander" => "N"
                ];
                $pagosAgrupadosPorForma = [];
                foreach ($data as $row) { 
                    // Convertir 'vtipo_pago' a 'N' o 'S'
                    $formaPago = $mapFormaPago[$row['vtipo_pago']] ?? null;

                    if (!empty($formaPago)) {
                        $pagosAgrupadosPorForma[$formaPago][$row['unidadN']][] = $row;
                    }
                }
                $totalesPorTipoPago = [];
                foreach ($dataTipo as $total) {
                    $totalesPorTipoPago[$total['Tipo_de_Pago']] = $total;
                }

                foreach ($pagosAgrupadosPorForma as $formaPago => $unidades) {
                    if ($formaPago == "N") {
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Banorte </td></tr>";
                    
                    } else if ($formaPago == "N") {//agregar o quitar algo
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Con Tarjeta Santander </td></tr>";
                    }
                	else {
                        echo "<tr><td colspan='9' style='font-weight: bold;'>Forma de Pago: Por transferencia </td></tr>";
                    }

                    foreach ($unidades as $unidad => $empleados) {
                        foreach ($empleados as $row) {
                            // Acumular los totales de cada columna
                            $total_percepciones += $row['percepciones'];
                            $total_vales += $row['canasta'];
                            $total_deducciones += $row['deducciones'];
                            $total_subtotal += $row['total_efectivo'];
                            $total_general += $row['total'];
                            echo "<tr>
                                    <td>{$row['unidadN']}</td>
                                    <td>{$row['deptoN']}</td>
                                    <td>{$row['Nombre_Depto']}</td>
                                    <td>" . number_format($row['percepciones'], 2) . "</td>
                                    <td>" . number_format($row['canasta'], 2) . "</td>
                                    <td>" . number_format($row['deducciones'], 2) . "</td>
                                    <td>" . number_format($row['total_efectivo'], 2) . "</td>
                                    <td>" . number_format($row['total'], 2) . "</td>
                                </tr>";
                        }
                    }

                    // Insertar totales para el tipo de pago actual
                    if (isset($totalesPorTipoPago[$formaPago])) {
                        $totales = $totalesPorTipoPago[$formaPago];
                        echo "<tr>";
                        echo "<td colspan='3' style='text-align: right;font-weight: bold;'>Totales ";

                        if ($formaPago == "N") {
                            echo 'Con Tarjeta Banorte';
                        }else if ($formaPago == "N") {
                            echo 'Con Tarjeta Santander';
                        } else {
                            echo 'Por Transferencia';
                        }
                        echo "</td>";
                        echo "<td>" . number_format($totales['Total_Percepciones'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Canasta'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Deducciones'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_Efectivo'], 2) . "</td>";
                        echo "<td>" . number_format($totales['Total_General'], 2) . "</td>";
                        echo "</tr>";
                    }
                }

                // Imprimir los totales finales después de todas las iteraciones
                echo "<tr>
                    <td colspan='3' style='text-align: right;'><strong>SUMA TOTAL: </strong></td>
                    <td>" . number_format($total_percepciones, 2) . "</td>
                    <td>" . number_format($total_vales, 2) . "</td>
                    <td>" . number_format($total_deducciones, 2) . "</td>
                    <td>" . number_format($total_subtotal, 2) . "</td>
                    <td>" . number_format($total_general, 2) . "</td>
                    <td colspan='2'></td>
                </tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <div style='width: 250px; border-top: 1px solid black; margin: 0 auto;'></div>
                    <div class='titulo-firma'>
                        <strong style="display: block; margin-top: 5px;">C.P. MARIA TERESA GARCIA ROJAS</strong>
                        <span>Directora de Recursos Humanos</span>
                    </div>
                    
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
