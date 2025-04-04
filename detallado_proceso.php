<?php
            $totalesPorUnidad = [];
			foreach ($curTot_Unidades as $total) {
    			$clave = $total['unidad'] . '-' . $total['depto'];
    			$totalesPorUnidad[$clave] = $total;
			}
            $unidad_actual = null;
            $depto_actual = null;
            
            foreach ($Empleados as $key => $filas): 
            // Cuando cambia la unidad
            if ($unidad_actual !== null && ($unidad_actual !== $filas["unidadN"] || $depto_actual !== $filas["deptoN"])) {
                // Agregar los totales de la unidad actual
                if ($total_unidades == 5) {
            			$claveTotal = $unidad_actual . '-' . $depto_actual;
            			if (isset($totalesPorUnidad[$claveTotal])) {
                			$totales = $totalesPorUnidad[$claveTotal];
                echo "<tr>
                    <td colspan='3' style='font-weight: bold; text-align: right;'>Suma Unidad</td>
                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["percepciones"], 2) . "</td>
                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["canasta"], 2) . "</td>
                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["deducciones"], 2) . "</td>
                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["total_efectivo"], 2) . "</td>
                    <td style='text-align: right; border-top: 2px solid black;'>" . number_format($totales["total"], 2) . "</td>
                </tr>";
            }
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

		// Mostrar total de la última unidad
if ($total_unidades == 5 && $unidad_actual !== null) {
    $claveTotal = $unidad_actual . '-' . $depto_actual;
    if (isset($totalesPorUnidad[$claveTotal])) {
        $totales = $totalesPorUnidad[$claveTotal];
        echo "<tr>...</tr>"; // Mismo formato de fila de totales
    }
}
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
