<?php

include 'coneccion.php';

$folio = intval($_GET['folio']);

$sql = "SELECT * FROM quinielas WHERE id = $folio";

$result = $conn->query($sql);

if($result && $result->num_rows > 0){

    $row = $result->fetch_assoc();

    echo json_encode([
        "ok" => true,
        "quiniela" => json_decode($row['quiniela_json'], true),
        "raw" => $row['quiniela_json']
    ]);

}else{

    echo json_encode([
        "ok" => false
    ]);

}