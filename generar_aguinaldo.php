<?php
require '../../config/conexion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // 10 minutos
set_time_limit(300);

// Debug logger
function log_debug($message) {
    $logFile = __DIR__ . "/debug_log.txt";
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] " . $message . "\n", FILE_APPEND);
}
    $xds = $_POST['diasextras'];
    $opcion = $_POST['grupo'];
    $nregistro = 0;
    log_debug("Iniciando generación de aguinaldo para grupo: $opcion");
    // Tabla de control
    $queryControl = " SELECT diaspago, salmindf, factorsdi, factimss1, factimss2, catproce, salminimo,ctrlVales, ctrlValesPoli, ctrlValesAytto, ppmavac, suticVales, diasplantilla, vacabase, vacasindi,
        diasagui, diasminagui,arcon, hrsextras, ppmadom FROM tblnomcontrol";
    $resultadoControl = sqlsrv_query($conn, $queryControl);
    if ($resultadoControl) {
        $filaControl = sqlsrv_fetch_array($resultadoControl, SQLSRV_FETCH_ASSOC);
        $catproce = $filaControl['catproce'];
        $diasplantilla = $filaControl['diasplantilla'];
        $diasagui = $filaControl['diasagui'];
        $arcon = $filaControl['arcon'];
        $diasminagui = $filaControl['diasminagui'];
        $salminimo = $filaControl['salminimo'];
        sqlsrv_free_stmt($resultadoControl);
        log_debug("Datos de control cargados correctamente");
    } else {
        log_debug("Error al consultar datos de control");
        echo json_encode(["estatus" => 0, "mensaje" => "Error al consultar tblnomcontrol."]);
        exit;
    }
    $catorcena = $catproce;
    // Empleados
    $consultaEmpleados = " SELECT * FROM tblnomemplea";
    $resultadoEmpleados = sqlsrv_query($conn, $consultaEmpleados);
    if ($resultadoEmpleados === false) 
    {
        log_debug("Error al consultar empleados");
        echo json_encode(["estatus" => 0, "mensaje" => "Error al consultar empleados."]);
        exit;
    }
    function procesarEmpleados($resultadoEmpleados, $filtro, $xds, $conn, $filaControl) 
    {
        while ($empleado = sqlsrv_fetch_array($resultadoEmpleados, SQLSRV_FETCH_ASSOC)) 
        {
            // Verifica si existe la clave 'ficha' y que tenga valor numérico válido
            if (!isset($empleado['ficha']) || !is_numeric($empleado['ficha'])) {
                log_debug("⚠️ Ficha ausente o inválida en empleado: " . print_r($empleado, true));
                continue;
            }

            // Evalúa filtro por grupo (unidad, depto, etc.)
            if ($filtro($empleado)) {
                $ficha = intval($empleado['ficha']);
                log_debug("✅ Procesando ficha: $ficha");
                calcularAguinaldo($xds, $ficha, $conn, $filaControl);
            } else {
                log_debug("⏩ Empleado omitido por filtro: ficha={$empleado['ficha']}, unidad={$empleado['unidad']}, depto={$empleado['idendepto']}");
            }
        }
    }

// Función para borrar movimientos
function eliminarMovimientos($conn, $sql, $params) {
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    if (!$stmt || !sqlsrv_execute($stmt)) {
        $errors = sqlsrv_errors();
        echo json_encode([
            "estatus" => 0,
            "mensaje" => "Error al eliminar movimientos.",
            "error_sqlsrv" => $errors
        ]);
        exit;
    }
}

switch ($opcion) {
    case 'general':
        $movs = " SELECT * FROM tblnommov WHERE catorcena = $catorcena";
        break;
    case 'unidad':
        $unidad = intval($_POST['unidad']);
        $movs = " SELECT * FROM tblnommov m JOIN tblnomemplea e ON e.ficha = m.ficha WHERE m.catorcena = $catorcena AND m.unidad = $unidad AND YEAR(e.fecbaja) = 1900 AND e.claveE != 'E'";
        break;
    case 'rango':
        $rango1 = intval($_POST['rango1']);
        $rango2 = intval($_POST['rango2']);
        $movs = " SELECT * FROM tblnommov m JOIN tblnomemplea e ON e.ficha = m.ficha WHERE m.catorcena = $catorcena AND m.unidad BETWEEN $rango1 AND $rango2 AND YEAR(e.fecbaja) = 1900 AND e.claveE != 'E'";
        break;
    case 'depto':
        $depto = intval($_POST['depto']);
        $movs = " SELECT * FROM tblnommov m JOIN tblnomemplea e ON e.ficha = m.ficha WHERE m.catorcena = $catorcena  AND m.idendepto = $depto AND YEAR(e.fecbaja) = 1900 AND e.claveE != 'E'";
        break;
    case 'ficha':
        $xficha = intval($_POST['ficha']);
        $movs = " SELECT * FROM tblnommov WHERE ficha = $xficha AND catorcena = $catorcena ";
        break;
    default:
        echo json_encode(["estatus" => 0, "mensaje" => "Grupo no válido."]);
        exit;
}
    // Ejecuta consulta de movimientos
    $stmtMovs = sqlsrv_query($conn, $movs, [], ['Scrollable' => SQLSRV_CURSOR_STATIC]);
    if ($stmtMovs === false) {
        $erroresSql = sqlsrv_errors();
        $mensajeErrores = print_r($erroresSql, true);
    
        log_debug("ERROR al ejecutar SQL: $movs");
        log_debug("ERRORES SQLSRV: $mensajeErrores");
    
        echo json_encode([
            "estatus" => 0,
            "mensaje" => "Error al consultar movimientos.",
            "sql" => $movs,
            "errores" => $mensajeErrores
        ]);
        exit;
    }    
    $nregistro = sqlsrv_num_rows($stmtMovs);
    $confirmar = isset($_POST['confirmar']) ? intval($_POST['confirmar']) : 0;
    if ($nregistro > 0 && !$confirmar) {
        $response = [
            "estatus" => 2,
            "mensaje" => "Ya hay movimientos generados para el aguinaldo. ¿Desea continuar el proceso?",
            "grupo" => $opcion
        ];
    
        switch ($opcion) {
            case 'unidad':
                $response['unidad'] = $unidad;
                break;
            case 'rango':
                $response['rango1'] = $rango1;
                $response['rango2'] = $rango2;
                break;
            case 'depto':
                $response['depto'] = $depto;
                break;
            case 'ficha':
                $response['ficha'] = $xficha;
                break;
        }
    
        echo json_encode($response);
        exit;
    }
    
// Si existen movimientos, eliminarlos
if ($nregistro > 0 && $confirmar) {
    switch ($opcion) {
        case 'general':
            eliminarMovimientos($conn,
                " DELETE FROM tblnommov WHERE catorcena = ?",
                [$catorcena]
            );
            break;
        case 'unidad':
            eliminarMovimientos($conn,
                " DELETE FROM tblnommov WHERE unidad = ? AND catorcena = ?",
                [$unidad, $catorcena]
            );
            break;
        case 'rango':
            eliminarMovimientos($conn,
                " DELETE FROM tblnommov WHERE unidad BETWEEN ? AND ? AND catorcena = ?",
                [$rango1, $rango2, $catorcena]
            );
            break;
        case 'depto':
            eliminarMovimientos($conn,
                " DELETE FROM tblnommov WHERE idendepto = ? AND catorcena = ?",
                [$depto, $catorcena]
            );
            break;
        case 'ficha':
            eliminarMovimientos($conn,
                " DELETE FROM tblnommov WHERE ficha = ? AND catorcena = ?",
                [$xficha, $catorcena]
            );
            break;
    }
}

switch ($opcion) {
    case 'general':
        procesarEmpleados($resultadoEmpleados, function($e) {
            log_debug("Evaluando empleado (general): unidad={$e['unidad']}, depto={$e['idendepto']}");
            return true;
        }, $xds, $conn, $filaControl);
        break;

    case 'unidad':
        $unidad = intval($_POST['unidad']);
        procesarEmpleados($resultadoEmpleados, function($e) use ($unidad) {
            log_debug("Evaluando empleado por unidad={$e['unidad']} contra unidad seleccionada=$unidad");
            return intval($e['unidad']) === $unidad;
        }, $xds, $conn, $filaControl);
        break;

    case 'rango':
        $rango1 = intval($_POST['rango1']);
        $rango2 = intval($_POST['rango2']);
        procesarEmpleados($resultadoEmpleados, function($e) use ($rango1, $rango2) {
            $unidad = intval($e['unidad']);
            log_debug("Evaluando unidad $unidad en rango $rango1 a $rango2");
            return $unidad >= $rango1 && $unidad <= $rango2;
        }, $xds, $conn, $filaControl);
        break;

    case 'depto':
        $depto = intval($_POST['depto']);
        procesarEmpleados($resultadoEmpleados, function($e) use ($depto) {
            log_debug("Evaluando depto={$e['idendepto']} contra $depto");
            return intval($e['idendepto']) === $depto;
        }, $xds, $conn, $filaControl);
        break;

    case 'ficha':
        $xficha = intval($_POST['ficha']);
        procesarEmpleados($resultadoEmpleados, function($e) use ($xficha) {
            log_debug("Evaluando ficha={$e['ficha']} contra $xficha");
            return intval($e['ficha']) === $xficha;
        }, $xds, $conn, $filaControl);
        break;

    default:
        echo json_encode(["estatus" => 0, "mensaje" => "Grupo no válido."]);
        exit;
}

    // Terminar proceso, se mandan los valores
    echo json_encode([
        "grupo" => $opcion,
        "estatus" => 1,
        "mensaje" => "Aguinaldo generado correctamente.",
        "ficha" => ($opcion === 'ficha') ? $xficha : null,
        "catorcena" => $catorcena
    ]);    
    exit;
    ////Cálculo del aguinaldo////
    function calcularAguinaldo($xds, $ficha, $conn, $filaControl)
    {
        log_debug("Entrando a calcularAguinaldo para ficha $ficha");
        // Inicialización
        $nGravable = 0;
        $nimpcat = 0;
        $salpue = 0;
        $embargo = 0;
        $diasplantilla = $filaControl['diasplantilla'];
        $diasagui      = $filaControl['diasagui'];
        $arcon         = $filaControl['arcon'];
        $salminimo     = $filaControl['salminimo'];
        $diasminagui   = $filaControl['diasminagui'];
        $catproce = $filaControl['catproce'];
        $Empleados = " SELECT e.ficha, e.fecbaja, e.claveE, e.unidad, e.depto, e.idendepto, e.cuenta, e.grupo, e.puesto, p.salario FROM tblnomemplea e 
                            INNER JOIN tblNomPuesto p ON e.puesto = p.puesto 
                            WHERE e.ficha = $ficha";

        $resultadoEmpleados = sqlsrv_query($conn, $Empleados);
        if ($resultadoEmpleados === false) 
        {
            log_debug("Error al consultar datos del empleado: " . print_r(sqlsrv_errors(), true));
            return;
        }
        else{
            $empleado = sqlsrv_fetch_array($resultadoEmpleados, SQLSRV_FETCH_ASSOC);
            if (!$empleado) {
                log_debug("No se encontró información para la ficha $ficha");
                return;
            }
            $ficha = $empleado['ficha'];
            $fecbaja = $empleado['fecbaja']; 
            $claveempleado = $empleado['claveE']; 
            $unidad = $empleado['unidad']; 
            $puestoEmpleado = $empleado['puesto'];
            $pue = $puestoEmpleado;
            $unidadEmpleado = $empleado['unidad'];
            $deptoEmpleado = $empleado['depto'];
            $idendeptoEmpleado = $empleado['idendepto'];
            $salpue = floatval($empleado['salario']);
            $cuentaEmpleado = $empleado['cuenta'];
            $grupoEmpleado = $empleado['grupo'];
        }
        
        $fvacia = '1900-01-01';
        $fecbaja_fmt = ($fecbaja instanceof DateTime) ? $fecbaja->format('Y-m-d') : null;

        if (($fecbaja_fmt !== null && $fecbaja_fmt !== $fvacia) || $claveempleado === 'E') {
            log_debug("Ficha $ficha tiene fecbaja = $fecbaja_fmt, se omite.");
            return;
        }

        // Fecha actual
        $hoy = new DateTime(); // equivale a DATE() en FoxPro
        $anio1 = (int) $hoy->format('Y');
        $mes1 = (int) $hoy->format('m');

        // Fecha de antigüedad
        $f_ant_obj = null;
        if (!empty($empleado['fechaantig'])) {
            $f_ant_obj = $empleado['fechaantig'] instanceof DateTime
                ? $empleado['fechaantig']
                : new DateTime($empleado['fechaantig']);
        }

        if ($f_ant_obj) {
            $anio2 = (int) $f_ant_obj->format('Y');
            $mes2 = (int) $f_ant_obj->format('m');

            $anios = $anio1 - $anio2;
            $mess  = $mes1 - $mes2;
        } else {
            $anio2 = $mes2 = $anios = $mess = null;
        }

        $anioActual = date('Y');
        $fec1_obj = new DateTime("$anioActual-01-01");
        $fec2_obj = new DateTime("$anioActual-12-31");

        if ($f_ant_obj && $f_ant_obj > $fec1_obj) {
            $fec1_obj = $f_ant_obj;
        }

        $fec1 = $fec1_obj->format('Y-m-d');
        $fec2 = $fec2_obj->format('Y-m-d');
        $f_ant = $f_ant_obj ? $f_ant_obj->format('Y-m-d') : null;

        log_debug("Ficha $ficha pasa validaciones iniciales. Continúa cálculo...");
        //fechas
        // $cat_trab = $catproce;
        $cat_trab = 0;
        log_debug("📌 Buscando catorcena para fecha de antigüedad: $fec1");
        log_debug("📌 Catorcena de proceso: $catproce");
        log_debug("📌 Valor de cat_trab: $cat_trab");
        $fechas = " SELECT TOP 1 numcat FROM tblnomfechas WHERE ? BETWEEN inicio AND final";
        $stmt = sqlsrv_query($conn, $fechas, [$fec1]);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        //movimientos
        $movs_ag = " SELECT SUM(cantidad) AS total FROM tblnommov WHERE ficha = ? AND catorcena >= ? AND concepto IN (1, 68, 71)";
        $params = [$ficha, $cat_trab];
        $stmt = sqlsrv_query($conn, $movs_ag, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $dt = $row && $row['total'] !== null ? floatval($row['total']) : 0;//valor de dt

        $dtt = $dt;
        $dV = 0;

        // dV: días de vacaciones concepto 21
        $sqlDV = " SELECT SUM(cantidad) AS total FROM tblnommov WHERE ficha = ? AND concepto = 21 AND catorcena >= ?";
        $stmtDV = sqlsrv_query($conn, $sqlDV, [$ficha, $cat_trab]);
        $rowDV = sqlsrv_fetch_array($stmtDV, SQLSRV_FETCH_ASSOC);
        $dV = $rowDV && $rowDV['total'] !== null ? floatval($rowDV['total']) : 0;

        // df: días de falta concepto 601
        $sqlDF = " SELECT SUM(cantidad) AS total FROM tblnommov WHERE ficha = ? AND concepto = 601 AND catorcena >= ?";
        $stmtDF = sqlsrv_query($conn, $sqlDF, [$ficha, $cat_trab]);
        $rowDF = sqlsrv_fetch_array($stmtDF, SQLSRV_FETCH_ASSOC);
        $df = $rowDF && $rowDF['total'] !== null ? floatval($rowDF['total']) : 0;

        $dt = $dt + $xds + $dV;

        if ($dt > $diasplantilla) 
        {
            $dt = $diasplantilla;
        }

        $dt = $dt - $df;
        if($dt > $diasminagui)
        {
            $diast = $dt;
            $diass_agui = ($dt*$diasagui)/$diasplantilla;
            $impte_mov = $diass_agui*$salpue;
            $imp_cat = $impte_mov;
            $nConcepto = 13;
            $imptegrav = $impte_mov;
            $nregistro = 0;
            $nImporteT = $impte_mov;
            $noCantidad = $diass_agui;//40
            //inserta concepto 13 - aguinaldo
            if($nImporteT > 0)
            {
                log_debug("insertando aguinaldo...");
                nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
            }
            //inserta concepto 14 - ARCON
            if(!in_array($grupoEmpleado, [1, 41]) && $unidadEmpleado != 999)
            {
                $nConcepto = 14;
                $nregistro = 0;
                $nImporteT = $dt*$arcon/$diasplantilla;
                $imp_cat = $imp_cat + $nImporteT;
                $imptegrav = $imptegrav + $nImporteT;
                $noCantidad = 1;

                if($nImporteT > 0)
                {
                    log_debug("insertando ARCON...");
                    nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
                }
            }
            $ispte = 0;
            $exen = 30*$salminimo;

            if($imptegrav > $exen)
            {
                $imptegrav = $imptegrav - $exen;
                $agui_mensualizado = $imptegrav/$dt*30.4;
                $porcmes = $salpue*30.4;
                $nGravable = $agui_mensualizado+$porcmes;
                $a_diario = $nGravable;
                $imptegrav = $a_diario;
                $nGravable = $imptegrav;
                // Calcular ISPT total con aguinaldo
                $ispte = calcularISPTValor($conn, $nGravable);
                // Calcular ISPT normal (solo sueldo)
                $isptmens = calcularISPTValor($conn, $porcmes);
                // Diferencia: impuesto solo del aguinaldo
                $ispte -= $isptmens;
                // Prorratear
                $ispte = ($ispte / 30.4) * $dt;
                // Asegurar que no sea undefined o negativa
                $ispte = max(0, round($ispte, 2));
            }

            // --- Inicializa saldos base para el embargo ---
            $newsalario = 0;
            $newpercep = 0;
            $newvales = 0;
            // $nembVales = 0;
            $embargo = 0;

            // //inserta concepto 501 - ISR/ISPT
            if($ispte != 0)
            {
                $nConcepto = 501;
                $noCantidad = 0;
                $nImporteT = $ispte;
                log_debug("insertando ISR/ISPT...");
                nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
            }
            $catorcena = $catproce;
            embargo($conn,$ficha,$empleado,$catproce, $catorcena,$newsalario,$newpercep,$newvales);
            embargo_mercantil($conn, $ficha, $empleado, $catorcena, $nImporteT);
        }
    }
    // Inserta movimientos
    function nuevoMovto($conn,$catproce,$cuentaEmpleado,$idendeptoEmpleado,$ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado,$unidadEmpleado,$deptoEmpleado,$embargo)
    {
        $queryMvto = " INSERT INTO tblnommov (catorcena,cuenta,idendepto,ficha,cantidad,importe,concepto,Puesto,UniCargo,CtaCargo,unidad,depto,embargo)
            VALUES ($catproce,$cuentaEmpleado,$idendeptoEmpleado,$ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado,0,0,$unidadEmpleado,$deptoEmpleado,$embargo)";
            log_debug("QUERY INSERT: $queryMvto");
            $resultadoMov = sqlsrv_query($conn, $queryMvto);	
            if ($resultadoMov === false) {
                log_debug("ERROR SQLSRV: " . print_r(sqlsrv_errors(), true));
                die(print_r(sqlsrv_errors(), true));  // Esto mostrará el error exacto
            }else
            {
                log_debug("Movimiento insertado correctamente para ficha $ficha, concepto $nConcepto");
            }
            
    }
    //calcula el valor que está dentro de los rangos del ISPT
    function calcularISPTValor($conn, $nGravable) 
    {
        $ispt = 0;
        $queryIsr = " SELECT * FROM tblnomispt WHERE $nGravable BETWEEN liminicial AND limfinal";
        $stmtIsr = sqlsrv_query($conn, $queryIsr);
    
        if ($stmtIsr && ($row = sqlsrv_fetch_array($stmtIsr, SQLSRV_FETCH_ASSOC))) {
            $limiteInf = $row['liminicial'];
            $porcentaje = $row['exento'];
            $cuotaFija = $row['cuotafija'];
            $ispt = (($nGravable - $limiteInf) * ($porcentaje / 100)) + $cuotaFija;
        }
    
        return $ispt;
    }
    //calcular el embargo
    function embargo($conn, $ficha, $filaEmpleado, $catproce, $catorcena, $newsalario, $newpercep, $newvales)
    {
        //EMBARGOS
        $queryEmbargo = " SELECT ficha,embargo,solosal,desctocat,porcentaje,aplicar,desctoVale,porcentajeVale FROM tblNomEmbargo WHERE ficha = $ficha ";
        $stmtvEmbargo = sqlsrv_query($conn, $queryEmbargo);
        while ($rowsEmbargo = sqlsrv_fetch_array($stmtvEmbargo, SQLSRV_FETCH_ASSOC)) {
            $emFicha = $rowsEmbargo['ficha'];
            $embargo = $rowsEmbargo['embargo'];
            $solosal = $rowsEmbargo['solosal'];
            $desctocat = $rowsEmbargo['desctocat'];
            $emPorcentaje = $rowsEmbargo['porcentaje'];
            $emAplicar = $rowsEmbargo['aplicar'];
            $desctoVale = $rowsEmbargo['desctoVale'];
            $porcentajeVale = $rowsEmbargo['porcentajeVale'];
            if ($emFicha == $ficha) {
            $cSoloSal = $solosal;
            if ($cSoloSal != 'S' ) {
                $cSoloSal = 'N';
            }
            }
        $queryMovs = "SELECT * FROM tblnommov WHERE ficha = $ficha AND catorcena = $catproce
                    AND ((tblnommov.concepto < 503) OR (tblnommov.concepto > 552) OR (tblnommov.concepto = 600) OR (tblnommov.concepto = 601)) order by tblnommov.concepto ";
        $stmtvMovs = sqlsrv_query($conn, $queryMovs);
        $subpagado = 0;
        $nesalario = 0;      
        $nembVales = 0;      
        $nepercep = $newsalario;
        $nembefvo = 0;
        $idendeptoEmpleado = $filaEmpleado['idendepto'] ?? 0;
        $puestoEmpleado = $filaEmpleado['puesto'] ?? '';
        $unidadEmpleado = $filaEmpleado['unidad'] ?? 0;
        $deptoEmpleado = $filaEmpleado['depto'] ?? 0;
        $cuentaEmpleado = $filaEmpleado['cuenta'] ?? '';
        while ($rowsMovs = sqlsrv_fetch_array($stmtvMovs, SQLSRV_FETCH_ASSOC)) {
        $nevales = 0;
        $movConcepto = $rowsMovs['concepto'];
        $movImporte = $rowsMovs['importe'];
        $cuentaEmpleado = $filaEmpleado['cuenta'];
        if ($emFicha == $ficha && $catorcena == $catproce && ($movConcepto <= 503 || $movConcepto == 552)) {
            if ($embargo == 1) {
            if ($movConcepto == 1 ) {
                // echo "concepto 1 //";
                $nesalario = $nesalario +$movImporte;
            }
            if ($movConcepto > 500) {
                $array_conceptosEm1 = array(503,552);
                $desc_conceptos_em1 = in_array($movConcepto, $array_conceptosEm1);
                if ($desc_conceptos_em1 == 1) {
                $nesalario = $nesalario -$movImporte;
                $nepercep = $nepercep -$movImporte;
                }else {
                if ($cSoloSal == 'S') {
                    $nesalario = $nesalario -$movImporte;
                }
                if ($nepercep > 0) {
                    $nepercep = $nepercep -$movImporte;
                }
                }
            }else {
                if ($movConcepto == 499) {
                $subpagado = $movImporte;
                }
                // echo $nepercep;
                $array_conceptosEm2 = array(2,4,20);
                $desc_conceptos_em2 = in_array($movConcepto, $array_conceptosEm2);
                if ($desc_conceptos_em2 == 0) {
                $nepercep = $nepercep + $movImporte;
                }else {
                $nevales = $nevales+$movImporte;
                }
            }
            }
            if ($subpagado > 0) {
                $nepercep = $nepercep -$subpagado;
            }
            }else {
            if ($emFicha == $ficha && $catorcena == $catproce && ($movConcepto == 601 || $movConcepto == 600)) {
                $nesalario = $nesalario -$movImporte;
                if ($nepercep > 0) {
                    $nepercep = $nepercep -$movImporte;
                }
            }
            if ($movConcepto == 1 ) {
                $nesalario =0;
            }
            }

            if ($embargo > 1) {
                $nevales = $newvales;
                $nesalario = $newsalario;
                $nepercep = $newpercep;
                $nembefvo = 0;
            }
            ///////
        if ($emFicha == $ficha) {
            if ($emAplicar == 'S') {
            // echo "string";
            if ($desctocat == 0 && $solosal == 'N') {
                $nembefvo = $nepercep*$emPorcentaje/100;
            }
            if ($desctocat > 0) {
                $nembefvo = $desctocat;
            }
            if ($solosal == 'S') {
                $nembefvo = $nesalario*$emPorcentaje/100;
            }
            if ($solosal == 'S' && $desctocat > 0) {
                $nembefvo = $desctocat;
            }
            if ($ficha != 4464 && $nepercep > 0) {
                $nepercepI = $nepercep- $nembefvo;
            }
            $nEmConcepto = 555;
            $nembVales = 0;
            if ($desctocat == 0 && $solosal != 'S') {
                if ($porcentajeVale > 0.00) {
                $nembVales = $nevales*$porcentajeVale/100;
                }else {
                    $nembVales = $nevales*$emPorcentaje/100;
                }
            }
            else {
                if ($desctoVale >= $nevales) {
                $nembVales = $nevales;
                }else {
                $nembVales = $desctoVale;
                }
            }
            }
        }
        }
        $newvales = $nevales-$nembVales;
        $newsalario = $nepercep-$nembefvo;
        $newpercep = $nepercep-$nembefvo;
        if ($emAplicar == 'S') {
        if ($nembVales > 0) {
            $nConcepto = $nEmConcepto;
            $noCantidad = '0.00';
            $nImporteT = floor($nembVales);
            log_debug("insertando embargo...");
            nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
        }
        if ($nembefvo>0) {
            $nConcepto = 568;
            $cuentaEmpleado = $filaEmpleado['cuenta'];
            $noCantidad = '0.00';
            $nImporteT =number_format($nembefvo, 2, '.', '');
            log_debug("insertando embargo...");
            nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
        }
        }
    }
    }

    function embargo_mercantil($conn, $ficha, $filaEmpleado, $vNum_catorcena,$nimporteEdoCuenta)
    {
        $vcampo = '';
        $comp = 0;
        $queryEdoCuenta = "SELECT * FROM tblnomedocta INNER JOIN tblnomconcep ON tblnomconcep.concepto = tblnomedocta.CONCEPTO WHERE ficha = $ficha AND tblnomedocta.CONCEPTO IN (624, 632)";
        $resultadoEdoCuenta = sqlsrv_query($conn, $queryEdoCuenta, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
        $row_count_edoc = sqlsrv_num_rows( $resultadoEdoCuenta );
        if ($row_count_edoc > 0 && $ncantidad > 0) 
        {
            // $vcampo = 'Catorcena'.$catorcena;
            $vcampo = 1;
            $vNum_catorcena = 99;
        }
        if ($vcampo != '') 
        {
            if($resultadoEdoCuenta)
            {
                while ($filaEdoCuenta = sqlsrv_fetch_array($resultadoEdoCuenta, SQLSRV_FETCH_ASSOC)) 
                {
                    ///////revisar la frecuencia de descuento
                    $frecuencia_cat = $filaEdoCuenta['frecuencia'];
                    $queryEdoCuentaFrecuencia = "SELECT * FROM tblNomFrecuencias_Pago WHERE idFrecuencia = $frecuencia_cat";
                    $resultadoEdoCuentaFrecuencia = sqlsrv_query($conn, $queryEdoCuentaFrecuencia, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
                    $filaEdoCuentaFrecuencia = sqlsrv_fetch_array($resultadoEdoCuentaFrecuencia, SQLSRV_FETCH_ASSOC);
                    $frecuencia_pago = $filaEdoCuentaFrecuencia[$vcampo];
                    if ($frecuencia_pago != 0) 
                    {
                        $afectar = $filaEdoCuenta['AFECTAR'];
                        $cargo = $filaEdoCuenta['CARGO'];
                        $abonos = $filaEdoCuenta['ABONOS'];
                        // echo $cargo - $abonos;
                        // echo "//////";
                        if ($afectar == 'S' && ( $cargo - $abonos > 0 || $permanente == 'S')) 
                        {
                            $desctocat = $filaEdoCuenta['DESCTOCAT'];
                            $permanente = $filaEdoCuenta['PERMANENTE'];
                            $conceptoEdoCuenta = $filaEdoCuenta['CONCEPTO'];
                            $queryFrecConcepto = "SELECT concepto,proporcional,frecuencia,ctacargo FROM tblnomconcep WHERE concepto = $conceptoEdoCuenta ";
                            $params = array();
                            $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
                            $stmtFrecConcepto = sqlsrv_query($conn, $queryFrecConcepto, $params, $options);
                            $rowsFrecConcepto = sqlsrv_fetch_array($stmtFrecConcepto, SQLSRV_FETCH_ASSOC);
                            $row_count_frecuencia = sqlsrv_num_rows( $stmtFrecConcepto );
                            $conceptoFrecuencia = $rowsFrecConcepto['frecuencia'];
                            $proporcionalDescto = $rowsFrecConcepto['proporcional'];

                            $idendeptoEmpleado = $filaEmpleado['idendepto'] ?? 0;
                            $puestoEmpleado = $filaEmpleado['puesto'] ?? '';
                            $unidadEmpleado = $filaEmpleado['unidad'] ?? 0;
                            $deptoEmpleado = $filaEmpleado['depto'] ?? 0;
                            $cuentaEmpleado = $filaEmpleado['cuenta'] ?? '';
                            if ($row_count_frecuencia > 0) 
                            {
                                if ($conceptoEdoCuenta < 500) {
                                if ($cargo - ($abonos+$desctocat) < 1 && $permanente == 'N') {
                                    $nimporteEdoCuenta = ($cargo - $abonos)/14*$ncantidad;
                                }else {
                                    if ($proporcionalDescto == 'S') {
                                    $nimporteEdoCuenta = $desctocat/14*$ncantidad;
                                    }else {
                                    $nimporteEdoCuenta = $desctocat;
                                    }
                                }
                                if ($proporcionalDescto == 'N') {
                                    $nimporteEdoCuenta = $desctocat;
                                }
                                $array_conceptos1 = array(18,23,47,48,26,41,57);
                                $desc_conceptos_1 = in_array($conceptoEdoCuenta, $array_conceptos1);
                                if ($desc_conceptos_1 == 1) {
                                    $nGravable = $nGravable+$nimporteEdoCuenta;
                                    if ($conceptoEdoCuenta == 18) {
                                    $comp = $nimporteEdoCuenta;
                                    }
                                }
                                $companti = $companti + $comp;
                                $array_conceptos2 = array(23,47,48,26,41);
                                $desc_conceptos_2 = in_array($conceptoEdoCuenta, $array_conceptos2);
                                if ($desc_conceptos_2 == 1) {
                                    $nseguro = $nseguro+($nimporteEdoCuenta/$ncantidad);
                                }
                                }else {
                                if ($cargo-($abonos+$desctocat) < 1 && $permanente == 'N') {
                                    $nimporteEdoCuenta = $cargo - $abonos;
                                }else {
                                    if ($proporcionalDescto == 'S') {
                                    $nimporteEdoCuenta = $desctocat/14*$ncantidad;
                                    }else {
                                    $nimporteEdoCuenta = $desctocat;
                                    }
                                }
                                }
                                // echo $companti;
                                $nConcepto = $conceptoEdoCuenta;
                                $noCantidad = '0.00';
                                $nImporteT = $nimporteEdoCuenta;
                                if ($nimporteEdoCuenta > 0) 
                                {
                                    log_debug("insertando embargo mercantil...");
                                    nuevoMovto($conn,$catproce, $cuentaEmpleado, $idendeptoEmpleado, $ficha,$noCantidad,$nImporteT,$nConcepto,$puestoEmpleado, $unidadEmpleado,$deptoEmpleado,$embargo);
                                }
                                $nimporteEdoCuenta = 0;
                                $array_conceptos_priesgo = array(27,9,28,29,30);
                                $desc_conceptos_priesgo = in_array($conceptoEdoCuenta, $array_conceptos_priesgo);
                                if ($desc_conceptos_priesgo == 1) {
                                $priesgo = $priesgo+$nimporteEdoCuenta;
                                }
                                if ($conceptoEdoCuenta == 18) {
                                $companti = $companti + $nimporteEdoCuenta;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
