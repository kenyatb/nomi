<?php
// require '../../../vendor/autoload.php';
ini_set('memory_limit', '1024M');
require '../../vendor/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include('../../config/conexion.php');

$reportType = isset($_POST['reportType']) ? $_POST['reportType'] : 1;
$initialFortnight = isset($_POST['initialFortnight']) ? $_POST['initialFortnight'] : 1;
$finalFortnight = isset($_POST['finalFortnight']) ? $_POST['finalFortnight'] : 1;
$concept = isset($_POST['concept']) ? $_POST['concept'] : 1;
$radioOptions = isset($_POST['radioOptions']) ? $_POST['radioOptions'] : 'general';
$unit = isset($_POST['unit']) ? $_POST['unit'] : 1;
$unit_initialRange = isset($_POST['unit_initialRange']) ? $_POST['unit_initialRange'] : 1;
$unit_finalRange = isset($_POST['unit_finalRange']) ? $_POST['unit_finalRange'] : 1;
$department = isset($_POST['department']) ? $_POST['department'] : 1;
$workerID = isset($_POST['workerID']) ? $_POST['workerID'] : 1;

if ($reportType == 1) {

    $sql = "SELECT 
    t4.unidadN as unidad, 
    t4.deptoN as depto, 
    t3.descripcion as 'Nombre_Puesto', 
    t1.ficha, 
    COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') + ' ' + COALESCE(t1.amaterno, '') as nombre, 
    t2.importe, 
    CASE 
        WHEN ROW_NUMBER() OVER (PARTITION BY t1.ficha ORDER BY t2.catorcena DESC) = 1 THEN SUM(t2.importe) OVER (PARTITION BY t1.ficha) 
        ELSE NULL 
    END AS suma_importe,
    t2.cantidad AS cantidad, 
    CASE 
        WHEN ROW_NUMBER() OVER (PARTITION BY t1.ficha ORDER BY t2.catorcena DESC) = 1 THEN SUM(t2.cantidad) OVER (PARTITION BY t1.ficha) 
        ELSE NULL 
        END AS suma_cantidad,
        t1.puesto, 
        t1.imss,
        t1.fechaantig,
        t2.catorcena,
        t1.rfc2,
        t4.fondo, 
        t1.claveE 
        FROM 
            tblnomemplea as t1
        LEFT JOIN 
            tblnommov as t2 ON t1.ficha = t2.ficha 
        LEFT JOIN 
            tblNomPuesto as t3 ON t1.puesto = t3.puesto 
        JOIN 
            tblnomdepto as t4 ON t1.unidad = t4.unidad AND t1.idendepto = t4.idendepto ";

    // Define the conditions for the radio button options 
    switch ($radioOptions) {
        case 'general':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'";
            break;

        case 'option2':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.unidad = '$unit'";
            break;

        case 'option3':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.unidad BETWEEN '$unit_initialRange' AND '$unit_finalRange'";
            break;

        case 'option4':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.idendepto = '$department'";
            break;

        case 'option5':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.ficha = '$workerID'";
            break;

        default:
            header("Location: view_aux_concep.php");
            exit;
            break;
    }

    $sql .= " GROUP BY 
        t4.unidadN, 
        t4.deptoN, 
        t3.descripcion, 
        t1.ficha, 
        COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') +  ' ' + COALESCE(t1.amaterno, ''), 
        t2.importe, 
        t2.cantidad,
        t1.puesto, 
        t1.imss,
        t1.fechaantig,
        t2.catorcena,
        t1.rfc2, 
        t4.fondo, 
        t1.claveE 
    ORDER BY 
        t1.ficha, 
        t2.catorcena ";
        //faltas
        if ($concept == 601) {
            $sql = "SELECT 
                        d.unidadN as unidad, 
                        d.deptoN as depto,  
                        p.descripcion AS Nombre_Puesto,
                        e.ficha,
                        CONCAT(COALESCE(e.apaterno, ''), ' ', COALESCE(e.amaterno, ''), ' ', COALESCE(e.nombre,'')) AS nombre,  
                        m.importe,
                        SUM(m.importe) AS suma_importe,
                        m.cantidad,
                        SUM(m.cantidad) AS suma_cantidad, 
                        e.puesto,
                        e.imss, 
                        e.fechaantig, 
                        m.catorcena, 
                        e.rfc2,
                        i.FECHAINI,
                        i.FECHAFIN,
                        i.OBSERVACIONES,
                        d.fondo, 
                        e.claveE
                    FROM 
                        tblnommov AS m
                    INNER JOIN 
                        tblNomMovInci AS i 
                        ON m.ficha = i.FICHA 
                        AND m.concepto = i.CONCEPTO 
                        AND m.catorcena = i.CATORCENA 
                    LEFT JOIN 
                        tblnomemplea AS e 
                        ON m.ficha = e.ficha 
                    LEFT JOIN 
                        tblNomPuesto AS p 
                        ON e.puesto = p.puesto 
                    LEFT JOIN 
                        tblnomdepto AS d 
                        ON e.idendepto = d.idendepto ";
            
            // Define the conditions for the radio button options 
            switch ($radioOptions) {
                case 'general':
                    $sql .= "WHERE m.catorcena >= '$initialFortnight' AND m.catorcena <= '$finalFortnight' AND m.concepto = '$concept'";
                    break;
        
                case 'option2':
                    $sql .= "WHERE m.catorcena >= '$initialFortnight' AND m.catorcena <= '$finalFortnight' AND m.concepto = '$concept' AND e.unidad = '$unit'";
                    break;
        
                case 'option3':
                    $sql .= "WHERE m.catorcena >= '$initialFortnight' AND m.catorcena <= '$finalFortnight' AND m.concepto = '$concept' AND e.unidad BETWEEN '$unit_initialRange' AND '$unit_finalRange'";
                    break;
        
                case 'option4':
                    $sql .= "WHERE m.catorcena >= '$initialFortnight' AND m.catorcena <= '$finalFortnight' AND m.concepto = '$concept' AND e.idendepto = '$department'";
                    break;
        
                case 'option5':
                    $sql .= "WHERE m.catorcena >= '$initialFortnight' AND m.catorcena <= '$finalFortnight' AND m.concepto = '$concept' AND e.ficha = '$workerID'";
                    break;
        
                default:
                    header("Location: view_aux_concep.php");
                    exit;
                    break;
            }
        
            $sql .= " GROUP BY 
                        d.unidadN, 
                        d.deptoN,  
                        p.descripcion,
                        e.ficha,
                        e.apaterno, 
                        e.amaterno, 
                        e.nombre, 
                        e.puesto,
                        e.imss, 
                        e.fechaantig, 
                        m.catorcena, 
                        e.rfc2,
                        i.FECHAINI,
                        i.FECHAFIN,
                        i.OBSERVACIONES,
                        d.fondo, 
                        e.claveE,
                        m.cantidad,
                        m.importe
                    ORDER BY 
                        e.ficha, 
                        i.FECHAINI";
        }
        //infonavit
        if($concept == 638)
        {
            $sql = "SELECT 
                    t4.unidadN AS unidad, 
                    t4.deptoN AS depto, 
                    t3.descripcion AS 'Nombre_Puesto', 
                    t1.ficha, 
                    COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') + ' ' + COALESCE(t1.amaterno, '') AS nombre, 
                    t2.importe, 
                    CASE 
                        WHEN ROW_NUMBER() OVER (PARTITION BY t1.ficha ORDER BY t2.catorcena DESC) = 1 THEN SUM(t2.importe) OVER (PARTITION BY t1.ficha)
                        ELSE NULL 
                    END AS suma_importe,
                    t2.cantidad AS cantidad, 
                    CASE 
                        WHEN ROW_NUMBER() OVER (PARTITION BY t1.ficha ORDER BY t2.catorcena DESC) = 1 THEN SUM(t2.cantidad) OVER (PARTITION BY t1.ficha)
                        ELSE NULL 
                    END AS suma_cantidad,
                    t1.puesto, 
                    t1.imss, 
                    t1.fechaantig, 
                    t2.catorcena,
                    ISNULL(
                        (
                            SELECT cant 
                            FROM TblNomSobres 
                            WHERE conc = 601
                            AND Cato BETWEEN $initialFortnight AND $finalFortnight
                            AND t1.ficha = TblNomSobres.Ficha
                        ), 0
                    ) AS C601, 
                    ISNULL(
                        (
                            SELECT cant 
                            FROM TblNomSobres 
                            WHERE conc = 603
                            AND Cato BETWEEN $initialFortnight AND $finalFortnight
                            AND t1.ficha = TblNomSobres.Ficha
                        ), 0
                    ) AS C603,
                    ISNULL(
                        (
                            SELECT cant 
                            FROM TblNomSobres 
                            WHERE conc = 605
                            AND Cato BETWEEN $initialFortnight AND $finalFortnight
                            AND t1.ficha = TblNomSobres.Ficha
                        ), 0
                    ) AS C605, 
                    ISNULL(
                        (
                            SELECT cant 
                            FROM TblNomSobres 
                            WHERE conc = 607
                            AND Cato BETWEEN $initialFortnight AND $finalFortnight
                            AND t1.ficha = TblNomSobres.Ficha
                        ), 0
                    ) AS C607,
                    t1.rfc2,
                    t4.fondo, 
                    t1.claveE 
                FROM 
                    tblnomemplea AS t1
                LEFT JOIN 
                    tblnommov AS t2 ON t1.ficha = t2.ficha 
                LEFT JOIN 
                    tblNomPuesto AS t3 ON t1.puesto = t3.puesto 
                JOIN 
                    tblnomdepto AS t4 ON t1.unidad = t4.unidad AND t1.idendepto = t4.idendepto";
    
                // Define the conditions for the radio button options 
            switch ($radioOptions) {
                case 'general':
                    $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'";
                    break;
    
                case 'option2':
                    $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.unidad = '$unit'";
                    break;
    
                case 'option3':
                    $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.unidad BETWEEN '$unit_initialRange' AND '$unit_finalRange'";
                    break;
    
                case 'option4':
                    $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.idendepto = '$department'";
                    break;
    
                case 'option5':
                    $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept' and t1.ficha = '$workerID'";
                    break;
    
                default:
                    header("Location: view_aux_concep.php");
                    exit;
                    break;
            }
    
                $sql .= " GROUP BY 
                            t4.unidadN, 
                            t4.deptoN, 
                            t3.descripcion, 
                            t1.ficha, 
                            COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') + ' ' + COALESCE(t1.amaterno, ''), 
                            t2.importe, 
                            t2.cantidad,
                            t1.puesto, 
                            t1.IMSS, 
                            t1.fechaantig, 
                            t2.catorcena,
                            t1.rfc2, 
                            t4.fondo, 
                            t1.claveE 
                        ORDER BY 
                            t1.ficha, 
                            t2.catorcena";
        
        	/*var_dump($initialFortnight);
        	var_dump($finalFortnight);
        	var_dump($concept);
        	var_dump($reportType);
        	var_dump($radioOptions);*/
        	
        }
        
}
if ($reportType == 0) 
{
    $currentDateTime = date('Y-m-d');
    $sql = "SELECT 
    t4.unidadN AS unidad, 
    t4.deptoN AS depto, 
    t3.descripcion AS descripcion_puesto, 
    t1.ficha, 
    COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') + ' ' + COALESCE(t1.amaterno, '') AS nombre, 
    t2.importe, 
    t2.cantidad AS cantidad, 
    t1.puesto, 
    t1.imss, 
    t1.fechaantig, 
    t2.catorcena, 
    t1.rfc2, 
    t1.nombfecini, 
    t1.nombfecfin, 
    MAX(t1.claveE) AS claveE, 
    MAX(t1.fecbaja) AS fecbaja
    FROM 
        tblnomemplea AS t1
    LEFT JOIN 
        tblnommov AS t2 ON t1.ficha = t2.ficha 
    LEFT JOIN 
        tblNomPuesto AS t3 ON t1.puesto = t3.puesto 
    JOIN 
        tblnomdepto AS t4 ON t1.unidad = t4.unidad AND t1.idendepto = t4.idendepto ";

    // Define the conditions for the radio button options 
    switch ($radioOptions) {
        case 'general':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'  and t1.claveE = 'E'  AND YEAR(t1.fecbaja) = 1900";
            break;

        case 'option2':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'  and t1.claveE = 'E' and t1.unidad = '$unit'";
            break;

        case 'option3':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'  and t1.claveE = 'E' and t1.unidad BETWEEN '$unit_initialRange' AND '$unit_finalRange'";
            break;

        case 'option4':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'  and t1.claveE = 'E' and t1.idendepto = '$department'";
            break;

        case 'option5':
            $sql .= "WHERE t2.catorcena >= '$initialFortnight' and t2.catorcena <= '$finalFortnight' and t2.concepto = '$concept'  and t1.claveE = 'E' and t1.ficha = '$workerID'";
            break;

        default:
            header("Location: index.php");
            exit;
            break;
    }

    $sql .= " GROUP BY 
    t4.unidadN, 
    t4.deptoN, 
    t3.descripcion, 
    t1.ficha, 
    COALESCE(t1.nombre, '') + ' ' + COALESCE(t1.apaterno, '') + ' ' + COALESCE(t1.amaterno, ''), 
    t2.importe, 
    t2.cantidad, 
    t1.puesto, 
    t1.IMSS, 
    t1.fechaantig, 
    t2.catorcena, 
    t1.rfc2, 
    t1.nombfecini, 
    t1.nombfecfin 
    ORDER BY 
        t1.ficha, 
		t2.catorcena ";
}

$concept_name = "SELECT descripcion FROM tblnomconcep WHERE concepto = '$concept'";
// Execute the query
$result = sqlsrv_query($conn, $sql);
$conceptName = sqlsrv_query($conn, $concept_name);

// Check if the query execution was successful
if ($conceptName === false || $result === false) {
    die(print_r(sqlsrv_errors(), true));
}
else{
    while ($row = sqlsrv_fetch_array($conceptName, SQLSRV_FETCH_ASSOC)) {
        $desc_concepto = $row['descripcion'];
    }
}

// Fetch the data
$data = [];
$distinctFondos = [];
$fondoTotals = [];
if ($reportType == 0) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        // Format the dates
        if ($row['fechaantig'] instanceof DateTime) {
            $row['fechaantig'] = $row['fechaantig']->format('d-m-Y');
        }
        if ($row['nombfecini'] instanceof DateTime) {
            $row['nombfecini'] = $row['nombfecini']->format('d-m-Y');
        }
        if ($row['nombfecfin'] instanceof DateTime) {
            $row['nombfecfin'] = $row['nombfecfin']->format('d-m-Y');
        }

        // Store the row data
        $data[] = $row;
    }
} elseif ($reportType == 1) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        // Format the dates
        if ($row['fechaantig'] instanceof DateTime) {
            $row['fechaantig'] = $row['fechaantig']->format('d-m-Y');
        }
        // Check and save distinct fondo values
        if (!in_array($row['fondo'], $distinctFondos)) {
            $distinctFondos[] = $row['fondo'];
        }
        // Accumulate totals for each fondo
        if (isset($fondoTotals[$row['fondo']])) {
            $fondoTotals[$row['fondo']] += $row['suma_importe'];
        } else {
            $fondoTotals[$row['fondo']] = $row['suma_importe'];
        }
        // Store the row data
        $data[] = $row;
    }
} else {
    echo "no hay datos";
}

// Crear nuevo objeto de hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(); // Inicializa $sheet
$sheet->setTitle('Hoja 1');

// Añadir información general
$sheet->setCellValue('A1', 'Presidencia de Salamanca');
$sheet->setCellValue('A2', 'Dirección de recursos humanos');
$sheet->setCellValue('A3', "Auxiliar de conceptos: ($concept) $desc_concepto");

// Definir encabezados según el tipo de reporte
$headers = [];
if ($reportType == 0) { // Reporte de eventuales
    $headers = [
        'Unidad',
        'Depto',
        'Descripción Departamento',
        'Ficha',
        'Nombre',
        'Importe',
        'Cantidad',
        'Puesto',
        'IMSS',
        'Fecha Antigüedad',
        'Cat',
        'RFC',
        'Nombramiento Inicio',
        'Nombramiento Fin',
    ];
} elseif ($reportType == 1) { // Reporte de base
    $headers = [
        'Unidad',
        'Depto.',
        'Puesto',
        'Ficha',
        'Nombre',
        'Importe',
        'Suma imp.',
        'Cantidad',
        'Suma cant.',
        'Puesto',
        'IMSS',
        'Fec. Antig.',
        'Cat.',
        'RFC',
    ];

    // Agregar columnas específicas para conceptos
    if ($concept == 601) {
        array_splice($headers, 14, 0, ['FECHA INIC. FALTA', 'FECHA FIN.', 'OBS. FALTAS']);
    }
    if ($concept == 638) {
        array_splice($headers, 14, 0, ['C601', 'C603', 'C605', 'C607']);
    }

    // Añadir columnas finales
    $headers = array_merge($headers, ['Fondo', 'Eventual']);
}

// Agregar encabezados al archivo
$sheet->fromArray($headers, NULL, 'A5');

// Encabezados y anchos básicos para todas las columnas
if ($reportType == 0) { // Eventuales
    $columnWidths = [
        'A' => 28,  // Unidad
        'B' => 20,  // Depto.
        'C' => 40,  // Descripcion Departamento
        'D' => 10,  // Ficha
        'E' => 40,  // Nombre
        'F' => 15,  // Importe
        'G' => 10,  // Cantidad
        'H' => 20,  // Puesto
        'I' => 15,  // IMSS
        'J' => 20,  // Fecha Antigüedad
        'K' => 15,  // CAT
        'L' => 20,  // RFC
        'M' => 25,  // Nombramiento Inicio
        'N' => 25,  // Nombramiento Fin
    ];
} elseif ($reportType == 1) { // Base
    $columnWidths = [
        'A' => 28,  // Unidad
        'B' => 20,  // Depto.
        'C' => 40,  // Puesto
        'D' => 10,  // Ficha
        'E' => 40,  // Nombre
        'F' => 15,  // Importe
        'G' => 15,  // Suma Importe / Cantidad
        'H' => 10,  // Cantidad
        'I' => 15,  // Suma Cantidad
        'J' => 20,  // Puesto
        'K' => 15,  // IMSS
        'L' => 15,  // Fecha Antigüedad
        'M' => 10,  // Cat.
        'N' => 20,  // RFC
    ];

    // Anchos adicionales según el concepto para Base
    if ($concept == 601) {
        $additionalWidths = [
            'O' => 30,  // FECHA INIC. FALTA
            'P' => 30,  // FECHA FIN.
            'Q' => 45,  // OBS. FALTAS
            'R' => 15,  // Fondo
            'S' => 10,  // Eventual
        ];
    } elseif ($concept == 638) {
        $additionalWidths = [
            'O' => 15,  // C601
            'P' => 15,  // C603
            'Q' => 15,  // C605
            'R' => 15,  // C607
            'S' => 15,  // Fondo
            'T' => 10,  // Eventual
        ];
    } else {
        $additionalWidths = [
            'O' => 15,  // Fondo
            'P' => 10,  // Eventual
        ];
    }
    // Combinar anchos originales con los adicionales
    $columnWidths = array_merge($columnWidths, $additionalWidths);
}

// Aplicar los anchos de columna al archivo Excel
foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Definir rango de encabezado de datos según el tipo de reporte y concepto
if ($reportType == 0) { // Eventuales
    $headerRange = 'A5:N5'; // Para reportes de eventuales
    $headerFillColor = 'DFDFDF'; // Fondo gris claro para eventuales
    $headerFontColor = '000000'; // Texto negro para eventuales
} elseif ($concept == 601) {
    $headerRange = 'A5:S5'; // Concepto 601
    $headerFillColor = '004080'; // Azul para concepto 601
    $headerFontColor = 'FFFFFF'; // Texto blanco para concepto 601
} elseif ($concept == 638) {
    $headerRange = 'A5:T5'; // Concepto 638
    $headerFillColor = 'FF0000'; // Rojo para concepto 638
    $headerFontColor = 'FFFFFF'; // Texto blanco para concepto 638
} else {
    $headerRange = 'A5:P5'; // Default para otros conceptos
    $headerFillColor = 'FF0000'; // Rojo por defecto
    $headerFontColor = 'FFFFFF'; // Texto blanco por defecto
}

// Aplicar estilos al encabezado de datos
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => $headerFontColor], // Color de texto dinámico
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => $headerFillColor], // Color de fondo dinámico
    ],
]);

$rowNum = 6; // Comienza después de los encabezados

foreach ($data as $row) {
    // Formatear fechas
    if (isset($row['FECHAINI']) && $row['FECHAINI'] instanceof DateTime) {
        $row['FECHAINI'] = $row['FECHAINI']->format('d-m-Y'); // Formato sin hora
    }
    if (isset($row['FECHAFIN']) && $row['FECHAFIN'] instanceof DateTime) {
        $row['FECHAFIN'] = $row['FECHAFIN']->format('d-m-Y'); // Formato sin hora
    }
    if (isset($row['fechaantig']) && $row['fechaantig'] instanceof DateTime) {
        $row['fechaantig'] = $row['fechaantig']->format('d-m-Y');
    }
    if (isset($row['nombfecini']) && $row['nombfecini'] instanceof DateTime) {
        $row['nombfecini'] = $row['nombfecini']->format('d-m-Y');
    }
    if (isset($row['nombfecfin']) && $row['nombfecfin'] instanceof DateTime) {
        $row['nombfecfin'] = $row['nombfecfin']->format('d-m-Y');
    }

    if ($reportType == 0) { // Eventuales
        $rowValues = [
            $row['unidad'],
            $row['depto'],
            $row['descripcion_puesto'],
            $row['ficha'],
            $row['nombre'],
            $row['importe'],
            $row['cantidad'],
            $row['puesto'],
            $row['imss'],
            $row['fechaantig'],
            $row['catorcena'],
            $row['rfc2'],
            $row['nombfecini'],
            $row['nombfecfin'],
        ];
    } elseif ($reportType == 1) { // Base
        $rowValues = [
            $row['unidad'],
            $row['depto'],
            $row['Nombre_Puesto'], // Nombre del puesto
            $row['ficha'],
            $row['nombre'],
            $row['importe'],
            $row['suma_importe'],
            $row['cantidad'],
            $row['suma_cantidad'],
            $row['puesto'],
            $row['imss'],
            $row['fechaantig'],
            $row['catorcena'],
            $row['rfc2'],
        ];
    
        // Agregar columnas adicionales según el concepto
        if ($concept == 601) {
            array_push($rowValues, $row['FECHAINI'], $row['FECHAFIN'], $row['OBSERVACIONES']);
        }
        if ($concept == 638) {
            array_push($rowValues, $row['C601'], $row['C603'], $row['C605'], $row['C607']);
        }
        // Agregar Fondo y Eventual al final
        array_push($rowValues, $row['fondo'], $row['claveE']);
    }
    
    // Escribir los valores en la hoja
    $sheet->fromArray($rowValues, NULL, 'A' . $rowNum);
    $rowNum++;
}

// Aplicar formato de TEXTO a la columna K para preservar ceros iniciales
$sheet->getStyle('K6:K' . $rowNum)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// Insertar totales solo si el reporte es de tipo base (reportType == 1)
if ($reportType == 1) {
    // Insertar totales sin filas en blanco adicionales
    $totalStartRow = $rowNum + 1;
    $sheet->setCellValue('A' . $totalStartRow, 'Fondo');
    $sheet->setCellValue('B' . $totalStartRow, 'Total Suma Importe');

    // Aplicar bordes a los encabezados de los totales
    $sheet->getStyle('A' . $totalStartRow . ':B' . $totalStartRow)->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

    // Agregar valores de fondo y total
    $rowNum = $totalStartRow + 1;
    foreach ($distinctFondos as $fondo) {
        $firstTwoDigits = substr($fondo, 0, 2);
        $secondTwoDigits = substr($fondo, 2, 2);
        $meaning = '';
        if ($firstTwoDigits == '11') {
            $meaning = 'Recursos fiscales';
        } elseif ($firstTwoDigits == '15') {
            $meaning = 'Participaciones';
        } elseif ($firstTwoDigits == '25') {
            $meaning = 'FORTAMUN';
        }

        $sheet->setCellValue('A' . $rowNum, "{$fondo} : {$meaning} {$secondTwoDigits}");
        $sheet->setCellValue('B' . $rowNum, $fondoTotals[$fondo]);

        // Aplicar bordes a las filas de totales
        $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $rowNum++;
    }
}

// Obtener la última fila y columna con datos correctamente antes de aplicar estilos
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

// Definir el rango de la tabla asegurando que no haya celdas adicionales
$dataRange = 'A5:' . $highestColumn . $highestRow;

// Aplicar bordes delgados negros solo a la tabla principal
$sheet->getStyle($dataRange)->applyFromArray([
    'borders' => [
        'allBorders' => [  
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
            'color' => ['rgb' => '000000'], 
        ],
    ],
]);

// Definir el nombre del archivo según el tipo de reporte
if ($reportType == 0) { // Reporte para eventuales
    $filename = 'reporte_auxiliar_eventuales.xlsx';
} elseif ($reportType == 1) { // Reporte para base
    $filename = 'reporte_auxiliar_conceptos.xlsx';
} else {
    $filename = 'reporte.xlsx'; // Nombre genérico como fallback
}

// Crear el escritor y guardar el archivo
$writer = new Xlsx($spreadsheet);

try {
    // Configurar las cabeceras antes de cualquier salida
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Guardar SOLO en la salida del navegador (no guardar localmente primero)
    $writer->save('php://output');
    
    // Liberar recursos
    sqlsrv_free_stmt($result);
    if (isset($conceptName) && $conceptName !== false) {
        sqlsrv_free_stmt($conceptName);
    }
    sqlsrv_close($conn);
    exit; // Salir después de enviar el archivo
} catch (Exception $e) {
    // Registrar y mostrar el error
    error_log("Error en la generación del reporte: " . $e->getMessage());
    echo "Error al generar el reporte: " . $e->getMessage();
}
?>
