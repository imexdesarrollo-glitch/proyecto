<?php

header('Content-Type: application/json');

require 'coneccion.php';

$folio = intval($_GET['folio'] ?? 0);

if ($folio <= 0) {

    echo json_encode([
        "ok" => false,
        "error" => "Folio inválido"
    ]);

    exit;
}

$stmt = $conn->prepare("
SELECT
    id,
    nombre,
    correo,
    empresa,
    telefono
FROM quinielas
WHERE id = ?
LIMIT 1
");

$stmt->bind_param("i", $folio);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo json_encode([
        "ok" => false,
        "error" => "No existe ese folio"
    ]);

    exit;
}

$participante = $result->fetch_assoc();

echo json_encode([
    "ok" => true,
    "participante" => $participante
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>