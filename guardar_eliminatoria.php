<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require 'coneccion.php';


// =====================================
// RECIBIR DATOS JSON
// =====================================

$data = json_decode(
    file_get_contents("php://input"),
    true
);



if(!$data){

    echo json_encode([
        "ok"=>false,
        "error"=>"Sin datos"
    ]);

    exit;

}



// =====================================
// DATOS PRINCIPALES
// =====================================

$quiniela_id = intval(
    $data['folio'] ?? 0
);


$fase = intval(
    $data['fase'] ?? 0
);


$partidos = $data['partidos'] ?? [];




// =====================================
// VALIDACIONES
// =====================================

if(!$quiniela_id){

    echo json_encode([
        "ok"=>false,
        "error"=>"Folio inválido"
    ]);

    exit;

}



if(!$fase){

    echo json_encode([
        "ok"=>false,
        "error"=>"Fase inválida"
    ]);

    exit;

}



if(empty($partidos)){


    echo json_encode([
        "ok"=>false,
        "error"=>"No hay pronósticos"
    ]);

    exit;

}




// =====================================
// EVITAR DUPLICAR UNA FASE
// =====================================


$check = $conn->prepare("
SELECT id
FROM quinielas_eliminatorias
WHERE quiniela_id = ?
AND fase = ?
LIMIT 1
");


$check->bind_param(
    "ii",
    $quiniela_id,
    $fase
);


$check->execute();


$resultado = $check->get_result();



if($resultado->num_rows > 0){


    echo json_encode([

        "ok"=>false,

        "error"=>"Ya tienes registrada esta fase eliminatoria"

    ]);


    exit;

}




// =====================================
// CONVERTIR PRONOSTICOS A JSON
// =====================================


$json = json_encode(
    $partidos,
    JSON_UNESCAPED_UNICODE
);




$ip = $_SERVER['REMOTE_ADDR'];




// =====================================
// GUARDAR
// =====================================


$stmt = $conn->prepare("

INSERT INTO quinielas_eliminatorias
(
quiniela_id,
fase,
quiniela_json,
fecha_envio,
ip
)
VALUES
(
?,
?,
?,
NOW(),
?
)

");



$stmt->bind_param(

    "iiss",

    $quiniela_id,

    $fase,

    $json,

    $ip

);





if($stmt->execute()){


    echo json_encode([

        "ok"=>true,

        "id"=>$conn->insert_id,

        "fase"=>$fase

    ]);



}else{


    echo json_encode([

        "ok"=>false,

        "error"=>$stmt->error

    ]);


}


?>