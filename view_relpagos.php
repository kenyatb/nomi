<?php
include('../../config/conexion.php');

function manejarError($mensaje)
{
    error_log($mensaje);
    die("<p style='color:red; font-weight:bold;'>$mensaje</p>");
}

$catorcena = isset($_POST['cat_rel']) ? ($_POST['cat_rel']) : 1;
$concentrado = isset($_POST['optionDetalle']) ? intval($_POST['optionDetalle']) : 1;
$pago = isset($_POST['optionPago']) ? $_POST['optionPago'] : [];
$conTarjeta = in_array(3, $pago);
$porTransferencia = in_array(4, $pago);
$santander = in_array(5, $pago);
$destino = isset($_POST['optionDestino']) ? intval($_POST['optionDestino']) : 6;
$total_unidades = isset($_POST['optionTotUnidad']) ? intval($_POST['optionTotUnidad']) : 0;

$acumuladoDeFondos = [];
$fondosTarjeta = [];
$fondosTransferencia = [];
$fondosSantander = [];
if ($catorcena <= 0) {
    manejarError('No hay catorcena 0, Por favor, selecciona una catorcena válida...');
}
$queryF = "SELECT * FROM tblnomfechas WHERE numcat = ?";
$params = [$catorcena];
$resultF = sqlsrv_query($conn, $queryF, $params);
if ($resultF === false) {
    manejarError("Error en la consulta de fechas: " . print_r(sqlsrv_errors(), true));
}
// Para el letrero del periodo de las fechas de catorcena
$vperiodo = " ";
if (sqlsrv_has_rows($resultF)) {
    $row = sqlsrv_fetch_array($resultF, SQLSRV_FETCH_ASSOC);
    if (isset($row['inicio']) && isset($row['final'])) {
        $inicio = $row['inicio'];
        $final = $row['final'];
        $vperiodo = "( De " . date_format($inicio, "d/m/Y") . " A " . date_format($final, "d/m/Y") . " )";
    } else {
        $vperiodo = "Periodo no disponible.";
    }
}
sqlsrv_free_stmt($resultF);

if(in_array(3, $pago)) { // Banorte
    $condiciones_pago[] = "(1=1)"; // Siempre verdadero
}

if(in_array(5, $pago)) { // Santander
    $condiciones_pago[] = "(1=1)"; // Siempre verdadero
}

if(in_array(4, $pago)) { // Transferencia
    $condiciones_pago[] = "(1=1)"; // Siempre verdadero
}

$where_base = !empty($condiciones_pago) ? " AND (" . implode(" OR ", $condiciones_pago) . ")" : "";
//query para los departamentos 
$queryDeps = "WITH datosBase AS (
    SELECT 
        s.idenDepDep,
        s.unidadN, 
        s.deptoN,
        s.imp,
        s.cato,
        s.conc,
        e.ctabanco,
        e.ctabanco_santander,
        e.ficha,
        CASE 
            WHEN (e.ctabanco IS NULL OR e.ctabanco = '') 
                 AND (e.ctabanco_santander IS NULL OR e.ctabanco_santander = '') 
            THEN 'S' 
            ELSE 'N'  
        END AS pagoCheque,
        CASE 
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 'SANTANDER'
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 'BANORTE'
            ELSE 'TRANSFERENCIA'
        END AS tipoBanco,
        CASE 
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 5
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 3
            ELSE 4
        END AS tipoPagoId
    FROM tblnomsobres s
    INNER JOIN tblnomemplea e ON s.ficha = e.ficha
    WHERE cato = ? " . $where_base . "
),

percepciones AS (
    SELECT 
        idenDepDep, 
        unidadN, 
        deptoN, 
        pagoCheque,
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS percepciones
    FROM datosBase
    WHERE conc < 500 AND conc NOT IN (2, 4, 20)
    GROUP BY idenDepDep, unidadN, deptoN, pagoCheque, tipoBanco, tipoPagoId
),

canasta AS (
    SELECT 
        idenDepDep, 
        unidadN, 
        deptoN, 
        pagoCheque,
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS canasta
    FROM datosBase
    WHERE conc IN (2, 4, 20)
    GROUP BY idenDepDep, unidadN, deptoN, pagoCheque, tipoBanco, tipoPagoId
),

deducciones AS (
    SELECT 
        idenDepDep, 
        unidadN, 
        deptoN, 
        pagoCheque,
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS deducciones
    FROM datosBase
    WHERE conc > 500 AND conc <> 555
    GROUP BY idenDepDep, unidadN, deptoN, pagoCheque, tipoBanco, tipoPagoId
)

SELECT 
    t4.descripcion AS Nombre_Depto,
    COALESCE(t4.fondo, '') AS fondo,
    COALESCE(t4.unidadN, '') AS unidadN,
    COALESCE(t4.deptoN, '') AS deptoN,
    COALESCE(t1.percepciones, 0) AS percepciones, 
    COALESCE(t2.canasta, 0) AS canasta, 
    COALESCE(t3.deducciones, 0) AS deducciones, 
    (COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) AS total_efectivo, 
    ((COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) + COALESCE(t2.canasta, 0)) AS total,
    t1.pagoCheque,
    t1.tipoBanco,  
    CASE 
        WHEN t1.pagoCheque = 'S' THEN 0
        WHEN t1.pagoCheque = 'N' THEN 1
        ELSE 0
    END AS ctaBan,
    t1.tipoPagoId
FROM percepciones AS t1
FULL OUTER JOIN canasta AS t2 
    ON t1.idenDepDep = t2.idenDepDep 
    AND t1.pagoCheque = t2.pagoCheque 
    AND t1.tipoBanco = t2.tipoBanco
    AND t1.tipoPagoId = t2.tipoPagoId
FULL OUTER JOIN deducciones AS t3 
    ON COALESCE(t1.idenDepDep, t2.idenDepDep) = t3.idenDepDep 
    AND COALESCE(t1.pagoCheque, t2.pagoCheque) = t3.pagoCheque 
    AND COALESCE(t1.tipoBanco, t2.tipoBanco) = t3.tipoBanco
    AND COALESCE(t1.tipoPagoId, t2.tipoPagoId) = t3.tipoPagoId
LEFT JOIN tblnomdepto AS t4
    ON COALESCE(t1.idenDepDep, t2.idenDepDep, t3.idenDepDep) = t4.idendepto
GROUP BY 
    t4.descripcion, 
    t4.fondo, 
    t4.unidadN, 
    t4.deptoN, 
    t1.percepciones, 
    t2.canasta, 
    t3.deducciones, 
    t1.pagoCheque,
    t1.tipoBanco,
    t1.tipoPagoId
ORDER BY 
    pagoCheque, 
    t4.unidadN, 
    t4.deptoN, 
    t4.fondo";
$paramDeps = [$catorcena];
$resultDeps = sqlsrv_query($conn, $queryDeps, $paramDeps);

if ($resultDeps === false) {
    manejarError("Error en la consulta de concentrado: " . print_r(sqlsrv_errors(), true));
}

$vtipo_pago = '';
$data = [];

while ($rowDeps = sqlsrv_fetch_array($resultDeps, SQLSRV_FETCH_ASSOC)) {
   if(in_array($rowDeps['tipoPagoId'], $pago)) {
        switch ($rowDeps['tipoPagoId']) {
        case 3:
            $rowDeps['vtipo_pago'] = 'Con Tarjeta Banorte';
            break;
        case 5:
            $rowDeps['vtipo_pago'] = 'Con Tarjeta Santander';
            break;
        case 4:
            $rowDeps['vtipo_pago'] = 'Por transferencia';
            break;
        default:
            $rowDeps['vtipo_pago'] = 'Sin tarjeta';
    }
    $data[] = $rowDeps;
}
}

$queryTotTipoPago = "WITH datosBase AS (
    SELECT 
        s.idenDepDep,
        s.unidadN, 
        s.deptoN,
        s.imp,
        s.cato,
        s.conc,
        e.ctabanco,
        e.ctabanco_santander,
        e.ficha,
        CASE 
            WHEN (e.ctabanco IS NULL OR e.ctabanco = '') 
                 AND (e.ctabanco_santander IS NULL OR e.ctabanco_santander = '') 
            THEN 'S'  -- Transferencia
            ELSE 'N'  -- Tiene cuenta bancaria
        END AS pagoCheque,
        CASE 
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 'SANTANDER'
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 'BANORTE'
            ELSE 'TRANSFERENCIA'
        END AS tipoBanco,
        CASE 
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 5
            WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 3
            ELSE 4
        END AS tipoPagoId
    FROM tblnomsobres s
    INNER JOIN tblnomemplea e ON s.ficha = e.ficha
    WHERE cato = ? 
    
),

percepciones AS (
    SELECT 
        pagoCheque, 
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS percepciones
    FROM datosBase
    WHERE conc < 500 AND conc NOT IN (2, 4, 20)
    GROUP BY pagoCheque, tipoBanco, tipoPagoId
),

canasta AS (
    SELECT 
        pagoCheque, 
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS canasta
    FROM datosBase
    WHERE conc IN (2, 4, 20)
    GROUP BY pagoCheque, tipoBanco, tipoPagoId
),

deducciones AS (
    SELECT 
        pagoCheque, 
        tipoBanco,
        tipoPagoId,
        SUM(imp) AS deducciones 
    FROM datosBase
    WHERE conc > 500 AND conc != 555
    GROUP BY pagoCheque, tipoBanco, tipoPagoId
),

totalesGlobales AS (
    SELECT 
        p.pagoCheque,  
        p.tipoBanco,
        p.tipoPagoId,
        COALESCE(p.percepciones, 0) AS total_percepciones,
        COALESCE(c.canasta, 0) AS total_canasta,
        COALESCE(d.deducciones, 0) AS total_deducciones,
        COALESCE(p.percepciones, 0) - COALESCE(d.deducciones, 0) AS total_efectivo,
        COALESCE((p.percepciones - d.deducciones) + c.canasta, 0) AS total_general
    FROM percepciones p
    LEFT JOIN canasta c ON p.pagoCheque = c.pagoCheque AND p.tipoBanco = c.tipoBanco AND p.tipoPagoId = c.tipoPagoId
    LEFT JOIN deducciones d ON p.pagoCheque = d.pagoCheque AND p.tipoBanco = d.tipoBanco AND p.tipoPagoId = d.tipoPagoId
)

SELECT 
    CASE 
        WHEN tipoPagoId = 3 THEN 'N-BANORTE'
        WHEN tipoPagoId = 5 THEN 'N-SANTANDER'
        ELSE 'S'
    END AS Tipo_de_Pago,
    total_percepciones AS Total_Percepciones,
    total_canasta AS Total_Canasta,
    total_deducciones AS Total_Deducciones,
    total_efectivo AS Total_Efectivo,
    total_general AS Total_General,
    tipoPagoId AS ID_Tipo_Pago  -- Nuevo campo para referencia
FROM totalesGlobales
ORDER BY 
    CASE 
        WHEN tipoPagoId = 3 THEN 1  -- Banorte primero
        WHEN tipoPagoId = 5 THEN 2  -- Santander después
        ELSE 4                     -- Transferencia al final
    END";
$paramTipo = [$catorcena];
$resultTipo = sqlsrv_query($conn, $queryTotTipoPago, $paramTipo);
if ($resultTipo === false) {
    manejarError("Error en la consulta de concentrado: " . print_r(sqlsrv_errors(), true));
}
$dataTipo = [];
while ($rowTipo = sqlsrv_fetch_array($resultTipo, SQLSRV_FETCH_ASSOC)) {
	//print_r($rowTipo);
    $dataTipo[] = $rowTipo;
}

if ($total_unidades == 5) {
    $tot_unidad = "WITH datosBase AS (
        SELECT 
            s.idenDepDep,
            s.unidadN, 
            s.deptoN,
            s.imp,
            s.cato,
            s.conc,
            e.ctabanco,
            e.ctabanco_santander,
            e.ficha,
            CASE 
                WHEN (e.ctabanco IS NULL OR e.ctabanco = '') 
                    AND (e.ctabanco_santander IS NULL OR e.ctabanco_santander = '') 
                THEN 'S' 
                ELSE 'N'  
            END AS pagoCheque,
            CASE 
                WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 'SANTANDER'
                WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 'BANORTE'
                ELSE 'TRANSFERENCIA'
            END AS tipoBanco,
            CASE 
                WHEN LTRIM(RTRIM(ISNULL(e.ctabanco_santander, ''))) <> '' THEN 5
                WHEN LTRIM(RTRIM(ISNULL(e.ctabanco, ''))) <> '' THEN 3
                ELSE 4
            END AS tipoPagoId
        FROM tblnomsobres s
        INNER JOIN tblnomemplea e ON s.ficha = e.ficha
        WHERE cato = ? " . $where_base . "
    ),
    percepciones AS (...),
    canasta AS (...),
    deducciones AS (...)
    
    SELECT 
        t4.descripcion AS Nombre_Depto,
        COALESCE(t4.fondo, '') AS fondo,
        COALESCE(t4.unidadN, '') AS unidadN,
        COALESCE(t4.deptoN, '') AS deptoN,
        COALESCE(SUM(t1.percepciones), 0) AS percepciones_fondo, 
        COALESCE(SUM(t2.canasta), 0) AS canasta_fondo, 
        COALESCE(SUM(t3.deducciones), 0) AS deducciones_fondo, 
        COALESCE(SUM(t1.percepciones), 0) - COALESCE(SUM(t3.deducciones), 0) AS total_efectivo_fondo,      
        (COALESCE(SUM(t1.percepciones), 0) - COALESCE(SUM(t3.deducciones), 0)) + COALESCE(SUM(t2.canasta), 0) AS total_fondo,
        t1.pagoCheque,
        t1.tipoBanco,
        t1.tipoPagoId,
        CASE 
            WHEN t1.tipoPagoId = 3 THEN 'N-BANORTE'
            WHEN t1.tipoPagoId = 5 THEN 'N-SANTANDER'
            ELSE 'S'
        END AS desc_tipo_pago
    FROM percepciones AS t1
    FULL OUTER JOIN canasta AS t2 
        ON t1.idenDepDep = t2.idenDepDep 
        AND t1.pagoCheque = t2.pagoCheque
        AND t1.tipoBanco = t2.tipoBanco
        AND t1.tipoPagoId = t2.tipoPagoId
    FULL OUTER JOIN deducciones AS t3 
        ON COALESCE(t1.idenDepDep, t2.idenDepDep) = t3.idenDepDep 
        AND COALESCE(t1.pagoCheque, t2.pagoCheque) = t3.pagoCheque
        AND COALESCE(t1.tipoBanco, t2.tipoBanco) = t3.tipoBanco
        AND COALESCE(t1.tipoPagoId, t2.tipoPagoId) = t3.tipoPagoId
    LEFT JOIN tblnomdepto AS t4
        ON COALESCE(t1.idenDepDep, t2.idenDepDep, t3.idenDepDep) = t4.idendepto
    GROUP BY 
        t4.descripcion, 
        t4.fondo, 
        t4.unidadN, 
        t4.deptoN, 
        t1.pagoCheque,
        t1.tipoBanco,
        t1.tipoPagoId
    ORDER BY 
        t1.pagoCheque, 
        t4.unidadN, 
        t4.deptoN, 
        t4.fondo";
    
    $paramUni = [$catorcena];
    $resultTotUnidad = sqlsrv_query($conn, $tot_unidad, $paramUni);
    
    if ($resultTotUnidad === false) {
        die('Error en consulta: ' . print_r(sqlsrv_errors(), true));
    } else {
        $curTot_Unidades = [];
        while ($row = sqlsrv_fetch_array($resultTotUnidad, SQLSRV_FETCH_ASSOC)) {
            $curTot_Unidades[] = $row;
        }
        sqlsrv_free_stmt($resultTotUnidad);
    }
}

?>
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
			$totalesPorTipoPago = [];
			// Mapeo de tipos de pago (usando tipoPagoId)
			$mapFormaPago = [
    			3 => ["nombre" => "Con Tarjeta Banorte", "clave" => "N-BANORTE"],
    			5 => ["nombre" => "Con Tarjeta Santander", "clave" => "N-SANTANDER"],
    			4 => ["nombre" => "Por transferencia", "clave" => "S"]
			];
			
			// Agrupar datos por tipo de pago y unidad
			foreach ($data as $row) {
    			$tipoId = $row['tipoPagoId'];
    			if (isset($mapFormaPago[$tipoId])) {
        			$formaPago = $mapFormaPago[$tipoId]['clave'];
        			$pagosAgrupadosPorForma[$formaPago][$row['unidadN']][] = $row;
    			}
			}
			
			// Preparar totales por tipo de pago desde $dataTipo
			foreach ($dataTipo as $total) 
            {
            	$clave = isset($total['ID_Tipo_Pago']) ? $mapFormaPago[$total['ID_Tipo_Pago']]['clave'] : 'S';
				$totalesPorTipoPago[$clave] = $total;
			}
			
            if ($total_unidades == 5) 
            {
                $total_percepciones = $total_vales = $total_deducciones = $total_subtotal = $total_general = 0; // Totales generales
                $mapFormaPago = [
    					3 => ["nombre" => "Con Tarjeta Banorte", "clave" => "N-BANORTE"],
    					5 => ["nombre" => "Con Tarjeta Santander", "clave" => "N-SANTANDER"],
    					4 => ["nombre" => "Por transferencia", "clave" => "S"]
				];
            	//dentro de la condición de totales por unidad
                $pagosAgrupadosPorForma = [];
                foreach ($data as $row) { 
    				 $tipoId = $row['tipoPagoId'];
    				if (isset($mapFormaPago[$tipoId])) {
        				$formaPago = $mapFormaPago[$tipoId]['clave'];
        				$pagosAgrupadosPorForma[$formaPago][$row['unidadN']][] = $row;
    				}
				}
                $totalesPorTipoPago = [];
                foreach ($dataTipo as $total) {
                    $totalesPorTipoPago[$total['Tipo_de_Pago']] = $total;
                }
            	/*echo "<pre>";
					var_dump($totalesPorTipoPago);
					exit;
				echo "</pre>";*/
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
           			$formaPago = $infoFormaPago['clave'];
    				$nombreFormaPago = $infoFormaPago['nombre'];
    
    				if (isset($pagosAgrupadosPorForma[$formaPago])) {
        				// Encabezado del tipo de pago
        				echo "<tr><td colspan='9' style='font-weight: bold; font-size: 20px;'>Forma de Pago: $nombreFormaPago</td></tr>";
                	
                    foreach ($pagosAgrupadosPorForma[$formaPago] as $unidad => $empleados) 
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
                                        <td colspan='3'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][4], 2) . "</td>
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
                        echo "<td colspan='3' style='text-align: right;font-weight: bold; font-size: 20px;'>Totales ";

                        if ($formaPago == "N-BANORTE") {
            					echo " con Tarjeta Banorte: ";
                        }
        				else if ($formaPago == "N-SANTANDER") {
            					echo " con Tarjeta Santander: ";
    					} else {
        						echo " por Transferencia: ";
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
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                </tr>";
                            }
                        }else if($formaPago == "N-SANTANDER"){
                        		
                        	// Imprimir las filas de los fondos
                            foreach ($fondosSantander as $fondo) {
                                echo "<tr>
                                    <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                    <td style='text-align: right;'>" . number_format($fondo['valores'][4], 2) . "</td>
                                </tr>";
                            }
                        }else{
                            // Imprimir las filas de los fondos
                            foreach ($fondosTransferencia as $fondo) {
                                    echo "<tr>
                                        <td colspan='3' style='font-weight: bold;'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][0], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][1], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][2], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][3], 2) . "</td>
                                        <td style='text-align: right;'>" . number_format($fondo['valores'][4], 2) . "</td>
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
                                <td colspan='3'>{$fondo['fondo']} {$fondo['nombre']}</td>
                                <td style='text-align: right;'>" . number_format($acumulados[0], 2) . "</td>
                                <td style='text-align: right;'>" . number_format($acumulados[1], 2) . "</td>
                                <td style='text-align: right;'>" . number_format($acumulados[2], 2) . "</td>
                                <td style='text-align: right;'>" . number_format($acumulados[3], 2) . "</td>
                                <td style='text-align: right;'>" . number_format($acumulados[4], 2) . "</td>
                            </tr>";
                }
              }
            } else { // aquí es para el reporte de concentrados
                $total_percepciones = $total_vales = $total_deducciones = $total_subtotal = $total_general = 0;
                
                foreach ($mapFormaPago as $tipoId => $infoFormaPago) {
                   	$formaPago = $infoFormaPago['clave'];
    				$nombreFormaPago = $infoFormaPago['nombre'];
    
    				if (isset($pagosAgrupadosPorForma[$formaPago])) {
        				// Encabezado del tipo de pago
        				echo "<tr><td colspan='9' style='font-weight: bold; font-size: 20px;'>Forma de Pago: $nombreFormaPago</td></tr>";

                        // Detalle por unidad
        foreach ($pagosAgrupadosPorForma[$formaPago] as $unidad => $empleados) {
            foreach ($empleados as $row) {
                // Acumular totales (usando los nombres de campo correctos)
                $percepciones = $row['percepciones'] ?? $row['percepciones'];
                $canasta = $row['canasta'] ?? $row['canasta'];
                $deducciones = $row['deducciones'] ?? $row['deducciones'];
                $total_efectivo = $row['total_efectivo'] ?? $row['total_efectivo'];
                $total = $row['total'] ?? $row['total'];
                
                $total_percepciones += $percepciones;
                $total_vales += $canasta;
                $total_deducciones += $deducciones;
                $total_subtotal += $total_efectivo;
                $total_general += $total;
                
                echo "<tr>
                        <td>{$row['unidadN']}</td>
                        <td>{$row['deptoN']}</td>
                        <td>{$row['Nombre_Depto']}</td>
                        <td class='text-right'>" . number_format($percepciones, 2) . "</td>
                        <td class='text-right'>" . number_format($canasta, 2) . "</td>
                        <td class='text-right'>" . number_format($deducciones, 2) . "</td>
                        <td class='text-right'>" . number_format($total_efectivo, 2) . "</td>
                        <td class='text-right'>" . number_format($total, 2) . "</td>
                    </tr>";
            }
        }
        
        // Totales por tipo de pago
        $claveTotal = $formaPago; // Usamos directamente la clave del mapeo
      
        if (isset($totalesPorTipoPago[$claveTotal])) {
            $totales = $totalesPorTipoPago[$claveTotal];
        	 
            echo "<tr class='total-tipo-pago'>
                    <td colspan='3' style='text-align: right; font-weight: bold;'>
                        Totales $nombreFormaPago
                    </td>
                    <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($totales['Total_Percepciones'], 2) . "</td>
                    <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($totales['Total_Canasta'], 2) . "</td>
                    <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($totales['Total_Deducciones'], 2) . "</td>
                    <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($totales['Total_Efectivo'], 2) . "</td>
                    <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($totales['Total_General'], 2) . "</td>
                </tr>";
        }
        
        // Espaciado entre secciones
        echo "<tr><td colspan='9' style='height: 20px;'></td></tr>";
    }
}

// Total general
echo "<tr class='total-general'>
        <td colspan='3' style='text-align: right; font-weight: bold;'>
            SUMA TOTAL
        </td>
        <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($total_percepciones, 2) . "</td>
        <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($total_vales, 2) . "</td>
        <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($total_deducciones, 2) . "</td>
        <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($total_subtotal, 2) . "</td>
        <td class='text-right' style='border-top: 2px solid #000;'>" . number_format($total_general, 2) . "</td>
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
