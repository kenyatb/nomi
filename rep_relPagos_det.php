<?php
include('../../config/conexion.php');

function manejarError($mensaje) 
{
    error_log($mensaje);
    die("<p style='color:red; font-weight:bold;'>$mensaje</p>");
}

$catorcena = isset($_POST['cat_rel']) ? ($_POST['cat_rel']) : 1;
$detallado = isset($_POST['optionDet']) ? intval($_POST['optionDet']) : 0;
$destino = isset($_POST['optionDestino']) ? intval($_POST['optionDestino']) : 6;
$total_unidades = isset($_POST['optionTotUnidad']) ? intval($_POST['optionTotUnidad']) : 0;

if ($catorcena <= 0) 
{
    manejarError('No hay catorcena 0, Por favor, selecciona una catorcena válida...');
}
$queryF = "SELECT * FROM tblnomfechas WHERE numcat = ?";
$params = [$catorcena];
$resultF = sqlsrv_query($conn, $queryF, $params);
if ($resultF === false) 
{
    manejarError("Error en la consulta de fechas: " . print_r(sqlsrv_errors(), true));
}
// Para el letrero del periodo de las fechas de catorcena
$vperiodo = " "; // Valor predeterminado
if (sqlsrv_has_rows($resultF)) {
    $row = sqlsrv_fetch_array($resultF, SQLSRV_FETCH_ASSOC);
    if (isset($row['inicio']) && isset($row['final'])) 
    {
        $inicio = $row['inicio'];
        $final = $row['final'];
        $vperiodo = "( De " . date_format($inicio, "d/m/Y") . " A " . date_format($final, "d/m/Y") . " )";
    } else {
        $vperiodo = "Periodo no disponible.";
    }
}    
sqlsrv_free_stmt($resultF);

    $queryEmp = "WITH percepciones AS (
        SELECT 
            idenDepDep, 
            unidadN, 
            deptoN, 
            ficha,
			despue,
            SUM(imp) AS percepciones 
        FROM tblNomSobres 
        WHERE cato = ? AND conc < 500 AND conc NOT IN (2, 4, 20) 
        GROUP BY idenDepDep, unidadN, deptoN, ficha, despue
    ),
    canasta AS (
        SELECT 
            idenDepDep, 
            unidadN, 
            deptoN, 
            ficha,
			despue,
            SUM(imp) AS canasta 
        FROM tblNomSobres 
        WHERE cato = ? AND conc IN (2, 4, 20)
        GROUP BY idenDepDep, unidadN, deptoN, ficha, despue
    ),
    deducciones AS (
        SELECT 
            idenDepDep, 
            unidadN, 
            deptoN, 
            ficha,
			despue,
            SUM(imp) AS deducciones 
        FROM tblNomSobres 
        WHERE cato = ? AND conc > 500 and conc != 555 
        GROUP BY idenDepDep, unidadN, deptoN, ficha, despue
    )
    SELECT 
    t4.descripcion AS Nombre_Depto, 
    COALESCE(t1.ficha, t2.ficha, t3.ficha) AS ficha,  
    Nombre_Empleado = RTRIM(tblnomemplea.apaterno) + ' ' + RTRIM(tblnomemplea.amaterno) + ' ' + RTRIM(tblnomemplea.nombre),
    COALESCE(t1.despue, t2.despue, t3.despue) AS DescPuesto, 
    COALESCE(t4.unidadN, '') AS unidadN,
    COALESCE(t4.deptoN, '') AS deptoN,
    COALESCE(t1.percepciones, 0) AS percepciones, 
    COALESCE(t2.canasta, 0) AS canasta, 
    COALESCE(t3.deducciones, 0) AS deducciones, 
    (COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) AS total_efectivo,  
    (COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) + COALESCE(t2.canasta, 0) AS total
FROM percepciones AS t1
FULL OUTER JOIN canasta AS t2 
    ON t1.idenDepDep = t2.idenDepDep AND t1.ficha = t2.ficha
FULL OUTER JOIN deducciones AS t3 
    ON COALESCE(t1.idenDepDep, t2.idenDepDep) = t3.idenDepDep 
       AND COALESCE(t1.ficha, t2.ficha) = t3.ficha
LEFT JOIN tblnomdepto AS t4
    ON COALESCE(t1.idenDepDep, t2.idenDepDep, t3.idenDepDep) = t4.idendepto
LEFT JOIN tblnomemplea
    ON COALESCE(t1.ficha, t2.ficha, t3.ficha) = tblnomemplea.ficha
ORDER BY unidadN, deptoN, ficha";
    $paramsEmp = [$catorcena, $catorcena, $catorcena];
    $resultEmp = sqlsrv_query($conn, $queryEmp, $paramsEmp);
    if ($resultEmp === false) {
        manejarError("Error en la consulta de empleados: " . print_r(sqlsrv_errors(), true));
    }else{
        $Empleados = [];
        while($filasEmp = sqlsrv_fetch_array($resultEmp, SQLSRV_FETCH_ASSOC))
        {
            $Empleados[] = $filasEmp;
        }
    }
    sqlsrv_free_stmt($resultEmp);

if ($total_unidades == 5) 
{
    $tot_unidad = "WITH percepciones AS (
        SELECT unidadN, SUM(imp) AS percepciones
        FROM tblNomSobres
        WHERE cato = ? AND conc < 500 AND conc NOT IN (2, 4, 20)
        GROUP BY unidadN
    ),
    canasta AS (
        SELECT unidadN, SUM(imp) AS canasta
        FROM tblNomSobres
        WHERE cato = ? AND conc IN (2, 4, 20)
        GROUP BY unidadN
    ),
    deducciones AS (
        SELECT unidadN, SUM(imp) AS deducciones
        FROM tblNomSobres
        WHERE cato = ? AND conc > 500 and conc != 555 
        GROUP BY unidadN
    )
    SELECT 
        COALESCE(t1.unidadN, t2.unidadN, t3.unidadN) AS unidad,
        COALESCE(t1.percepciones, 0) AS percepciones, 
        COALESCE(t2.canasta, 0) AS canasta, 
        COALESCE(t3.deducciones, 0) AS deducciones, 
        (COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) AS total_efectivo,  
        (COALESCE(t1.percepciones, 0) - COALESCE(t3.deducciones, 0)) + COALESCE(t2.canasta, 0) AS total
    FROM percepciones AS t1
        FULL OUTER JOIN canasta AS t2 ON t1.unidadN = t2.unidadN
        FULL OUTER JOIN deducciones AS t3 ON COALESCE(t1.unidadN, t2.unidadN) = t3.unidadN
    ORDER BY unidad";
    $paramUni = [$catorcena, $catorcena, $catorcena];
    $resultTotUnidad = sqlsrv_query($conn, $tot_unidad, $paramUni);
    if ($resultTotUnidad === false) {
        $errors = sqlsrv_errors();
        die('Error en la consulta de total unidades: ' . print_r($errors, true));
    }
    else
    {
        $curTot_Unidades = [];
        while ($tunidades = sqlsrv_fetch_array($resultTotUnidad, SQLSRV_FETCH_ASSOC)) 
        {
            $curTot_Unidades[] = $tunidades;
        }
        sqlsrv_free_stmt($resultTotUnidad);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Relación de Pagos Detallado</title>
    <style>
        .no-border { 
            border: none; 
            text-align: center; 
            font-weight: bold; 
        }

        th, td {
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
            #previewContainer, .no-imprimir {
                display: none;
            }
			
			tfoot {
                display: table-row-group !important; /* Evita que se repita en cada página */
            }
        }
		
		.titulo-firma {
            text-align: center;  /* Centra el texto horizontalmente */
            width: 100%;         /* Ocupa todo el ancho disponible */
            display: block;      /* Asegura que se comporte como un bloque */
            margin-top: 20px;    /* Espacio superior opcional */
            font-weight: bold;   /* Opcional: negrita para mayor visibilidad */
        }
        @media screen {
            body {
                background-color: #ffffff;
            }
        }
    </style>
</head>
<body>
<table align="center" cellpadding="1" cellspacing="1" width="100%">
    <thead>
        <tr>
            <td colspan="12" class="no-border">PRESIDENCIA DE SALAMANCA, GUANAJUATO</td>
        </tr>
        <tr>
            <td colspan="12" class="no-border">DIRECCIÓN DE RECURSOS HUMANOS</td>
        </tr>
        <tr>
            <td colspan="12" class="no-border" id="tituloReporte">RELACIÓN DE PAGOS POR DEPARTAMENTOS DETALLADO</td>
        </tr>
        <tr>
            <td colspan="12" class="no-border">Catorcerna: <?php echo $catorcena . " " . $vperiodo ?></td>
        </tr>
        <tr>
            <th>Ficha</th>
            <th>Nombre del Empleado</th>
            <th>Puesto</th>
            <th>Percepciones</th>
            <th>VALES</th>
            <th>DEDUCCIONES</th>
            <th>TOTAL EFECTIVO</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $unidad_actual = null;
            $depto_actual = null;
            $indice_unidad_totales = 0; // Para iterar sobre $curTot_Unidades
            foreach ($Empleados as $key => $filas): 
            // Cuando cambia la unidad
            if ($unidad_actual !== null && $unidad_actual !== $filas["unidadN"]) {
                // Agregar los totales de la unidad actual
                if ($total_unidades == 5) {
                	
                    $totales = $curTot_Unidades[$indice_unidad_totales] ?? null;
                    if ($totales) {
                    echo "<tr>
                        <td colspan='3' style='font-weight: bold; text-align: right;'>Suma Unidad</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["percepciones"], 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["canasta"], 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["deducciones"], 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["total_efectivo"], 2) . "</td>
                        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["total"], 2) . "</td>
                    </tr>";
                }
                $indice_unidad_totales++; // Avanza al siguiente total de unidad
            }
            }
             // Cuando cambia la unidad
             if ($unidad_actual !== $filas["unidadN"]) {
                $unidad_actual = $filas["unidadN"];
                echo "<tr>
                    <td colspan='8' style='text-align: left; font-weight: bold; background-color: #d3d3d3;'>
                        {$filas["unidadN"]} - {$filas["deptoN"]} {$filas["Nombre_Depto"]}
                    </td>
                </tr>";
            }
             // Cuando cambia el departamento
        if ($depto_actual !== $filas["deptoN"]):
            $depto_actual = $filas["deptoN"];
            if($depto_actual != 1)
            {
                echo "<tr>
                <td colspan='8' style='text-align: left; padding-left: 15px; font-weight: bold; background-color: #d3d3d3;'>
                    {$filas["unidadN"]} - {$filas["deptoN"]} {$filas["Nombre_Depto"]}
                    </td>
                </tr>";
            }
        endif;
            // Mostrar los datos del empleado
        echo "<tr>
        <td>{$filas["ficha"]}</td>
        <td>{$filas["Nombre_Empleado"]}</td>
        <td>{$filas["DescPuesto"]}</td>
        <td>" . number_format($filas["percepciones"], 2) . "</td>
        <td>" . number_format($filas["canasta"], 2) . "</td>
        <td>" . number_format($filas["deducciones"], 2) . "</td>
        <td>" . number_format($filas["total_efectivo"], 2) . "</td>
        <td>" . number_format($filas["total"], 2) . "</td>
    </tr>";
        endforeach;
        //si no se selecciona la opción de total de unidades
        $totalPercepciones = $totalCanasta = $totalDeducciones = $totalEfectivo =  $totalGeneral = 0;  // Total
        // Procesar resultados de la consulta
        foreach($Empleados as $row) {
            // Acumular totales
            $totalPercepciones += $row['percepciones'];
            $totalCanasta += $row['canasta'];
            $totalDeducciones += $row['deducciones'];
            $totalEfectivo += $row['total_efectivo'];
            $totalGeneral += $row['total'];
        }
        // Totales generales
        echo "<tr>
        <td colspan='3' style='text-align: right; font-weight: bold;'>SUMA TOTAL</td>
        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totalPercepciones, 2) . "</td>
        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totalCanasta, 2) . "</td>
        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totalDeducciones, 2) . "</td>
        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totalEfectivo, 2) . "</td>
        <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totalGeneral, 2) . "</td>
    </tr>";
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
