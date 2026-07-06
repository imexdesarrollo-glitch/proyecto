<?php
header('Content-Type: application/json');
require 'coneccion.php';

// Verificar que 16avos estén activos
$row = $conn->query("
    SELECT valor FROM config_quiniela WHERE clave='16avos_activos'
")->fetch_assoc();

if (($row['valor'] ?? '0') !== '1') {
    echo json_encode(['ok' => false, 'error' => 'Los 16avos aún no están activos.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
    exit;
}

$quiniela_id   = (int)($data['quiniela_id'] ?? 0);
$quiniela_json = json_encode($data['quiniela'] ?? [], JSON_UNESCAPED_UNICODE);
$ip            = $_SERVER['REMOTE_ADDR'];

if (!$quiniela_id) {
    echo json_encode(['ok' => false, 'error' => 'Folio inválido.']);
    exit;
}

// Verificar que el folio exista en quinielas
$check = $conn->prepare("SELECT id FROM quinielas WHERE id = ? LIMIT 1");
$check->bind_param("i", $quiniela_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['ok' => false, 'error' => 'Folio no encontrado.']);
    exit;
}
$check->close();

// Guardar o actualizar pronóstico de 16avos
$stmt = $conn->prepare("
    INSERT INTO quinielas_16avos (quiniela_id, quiniela_json, ip)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        quiniela_json = VALUES(quiniela_json),
        ip            = VALUES(ip)
");
$stmt->bind_param("iss", $quiniela_id, $quiniela_json, $ip);

if ($stmt->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => $stmt->error]);
}
$stmt->close();