<?php

header('Content-Type: application/json');

require 'coneccion.php';

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode([
        "ok" => false,
        "error" => "No se recibieron datos"
    ]);
    exit;
}

$nombre = trim($data['nombre'] ?? '');
$correo = trim($data['correo'] ?? '');
$empresa = trim($data['empresa'] ?? '');
$telefono = trim($data['telefono'] ?? '');

$quiniela_json = json_encode(
    $data['quiniela'] ?? [],
    JSON_UNESCAPED_UNICODE
);

$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("
INSERT INTO quinielas
(
nombre,
correo,
empresa,
telefono,
quiniela_json,
ip
)
VALUES
(
?,?,?,?,?,?
)
");

$stmt->bind_param(
    "ssssss",
    $nombre,
    $correo,
    $empresa,
    $telefono,
    $quiniela_json,
    $ip
);

if($stmt->execute()){

    echo json_encode([
        "ok" => true,
        "id" => $conn->insert_id
    ]);

}else{

    echo json_encode([
        "ok" => false,
        "error" => $stmt->error
    ]);
}