<?php

require_once __DIR__ . '/coneccion.php';

$concepto = "Bonus 16avos y 8vos";

$participantes = $conn->query("
    SELECT id 
    FROM quinielas
");


$insertados = 0;


while($p = $participantes->fetch_assoc()){

    $quiniela_id = $p['id'];


    // Revisar si ya existe
    $check = $conn->prepare("
        SELECT id
        FROM puntos_bonus
        WHERE quiniela_id = ?
        AND concepto = ?
    ");

    $check->bind_param(
        "is",
        $quiniela_id,
        $concepto
    );

    $check->execute();

    $existe = $check->get_result();


    if($existe->num_rows == 0){


        $stmt = $conn->prepare("
            INSERT INTO puntos_bonus
            (
                quiniela_id,
                concepto,
                puntos
            )
            VALUES
            (?,?,?)
        ");


        $puntos = 24;


        $stmt->bind_param(
            "isi",
            $quiniela_id,
            $concepto,
            $puntos
        );


if($stmt->execute()){

    $insertados++;

}else{

    echo "Error participante ".$quiniela_id.": ".$stmt->error."<br>";

}
    }

}


echo "Bonus aplicado a ".$insertados." participantes";