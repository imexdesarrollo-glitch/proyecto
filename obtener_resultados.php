<?php

header('Content-Type: application/json');

require 'coneccion.php';

$resultados = [];

$sql = "SELECT * FROM resultados_reales";
$res = $conn->query($sql);

while($row = $res->fetch_assoc()){

    $key = $row['grupo'] . '_' . $row['partido_idx'];

    $resultados[$key] = [
        'goles_local' => $row['goles_local'],
        'goles_visita' => $row['goles_visita'],
        'res' => $row['res']
    ];
}

echo json_encode($resultados);