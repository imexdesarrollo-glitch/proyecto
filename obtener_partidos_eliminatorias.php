<?php

require 'coneccion.php';

$sql = "
SELECT *
FROM partidos_eliminatorias
WHERE activo = 1
ORDER BY fase DESC, partido_idx ASC
";

$resultado = $conn->query($sql);

$datos = [];

while($fila = $resultado->fetch_assoc()){
    $datos[] = $fila;
}

header("Content-Type: application/json; charset=utf-8");

echo json_encode($datos);