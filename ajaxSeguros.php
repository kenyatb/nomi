<?php

include('../../config/conexion.php');

$sql = "SELECT 
        ficha, 
        COALESCE(nombre, '') + ' ' + COALESCE(apaterno, '') +  ' ' + COALESCE(amaterno, '') as nombre 
    FROM tblnomemplea";

$sql2 = "SELECT 
        idendepto, 
        descripcion 
    FROM tblnomdepto 
    ORDER BY idendepto ";

$result = sqlsrv_query($conn, $sql);
$result2 = sqlsrv_query($conn, $sql2);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$worker = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
{
    $worker[] = $row;
}
$response['worker'] = $worker;

$deptos = [];
while ($row = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)) 
{
    $deptos[] = $row;
}

$response['depto'] = $deptos;

sqlsrv_free_stmt($result);
sqlsrv_close($conn);

echo json_encode($response);

?>
