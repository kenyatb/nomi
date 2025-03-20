<?php
session_start();
include('../../config/conexion.php');
header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);

$response = ['data' => [], 'error' => null];

try {
    // 1. Validar existencia del parámetro 'tipo'
    if (!isset($_POST['tipo'])) {
        throw new Exception("Parámetro 'tipo' no recibido");
    }
    $tipoConsulta = $_POST['tipo'];

    if ($tipoConsulta === 'ficha') {
        // 2. Validar fichas como números
        if (!isset($_POST['ficha1']) || !isset($_POST['ficha2'])) {
            throw new Exception("Faltan fichas en la solicitud");
        }
        $ficha1 = (int)$_POST['ficha1'];
        $ficha2 = (int)$_POST['ficha2'];

        if ($ficha1 <= 0 || $ficha2 <= 0) {
            throw new Exception("Rango de fichas inválido");
        }

        // Consulta parametrizada
        $sql = "SELECT ficha, nombre, apaterno, amaterno 
                FROM tblnomemplea 
                WHERE ficha BETWEEN ? AND ? 
                ORDER BY ficha";
        $stmt = sqlsrv_query($conn, $sql, array($ficha1, $ficha2));

    } elseif ($tipoConsulta === 'depto') {
        // 3. Validar y sanitizar departamento
        if (!isset($_POST['deptosSelect'])) {
            throw new Exception("Falta seleccionar departamento");
        }
        $departamento = (int)$_POST['deptosSelect']; 

        if ($departamento <= 0) {
            throw new Exception("Departamento inválido");
        }

        $sql = "SELECT e.ficha, e.nombre, e.apaterno, e.amaterno 
                FROM tblnomemplea e
                INNER JOIN tblnomdepto d ON e.idendepto = d.idendepto 
                WHERE d.idendepto = ? 
                ORDER BY e.ficha";
        $stmt = sqlsrv_query($conn, $sql, array($departamento));

    } else {
        throw new Exception("Tipo de consulta no válido");
    }

    // 4. Manejo claro de errores SQL
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        throw new Exception("Error en SQL: " . $errors[0]['message']);
    }

    // Procesar resultados
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $nombreCompleto = trim(implode(' ', [
            $row['nombre'], 
            $row['apaterno'], 
            $row['amaterno']
        ]));
        
        $response['data'][] = [
            'ficha' => (int)$row['ficha'], 
            'nombre' => $nombreCompleto
        ];
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} finally {
    if (isset($stmt)) sqlsrv_free_stmt($stmt);
    if (isset($conn)) sqlsrv_close($conn);
}

echo json_encode($response);
exit;
