<?php
header('Content-Type: application/json');
require 'coneccion.php';

$folio = (int)($_GET['folio'] ?? 0);

if (!$folio) {
    echo json_encode(['ok' => false, 'registrado' => false]);
    exit;
}

$stmt = $conn->prepare("
    SELECT quiniela_json
    FROM quinielas_16avos
    WHERE quiniela_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $folio);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo json_encode([
        'ok'         => true,
        'registrado' => true,
        'quiniela'   => json_decode($row['quiniela_json'], true)
    ]);
} else {
    echo json_encode([
        'ok'         => true,
        'registrado' => false
    ]);
}