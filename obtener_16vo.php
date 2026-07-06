<?php
header('Content-Type: application/json');
require 'coneccion.php';

// Crear tablas si no existen
$conn->query("
    CREATE TABLE IF NOT EXISTS resultados_16avos (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        partido_idx  INT NOT NULL,
        local        VARCHAR(60) NOT NULL,
        visita       VARCHAR(60) NOT NULL,
        goles_local  INT DEFAULT NULL,
        goles_visita INT DEFAULT NULL,
        res          VARCHAR(1)  DEFAULT NULL,
        actualizado  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY partido_unico (partido_idx)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$conn->query("
    CREATE TABLE IF NOT EXISTS config_quiniela (
        clave VARCHAR(50) PRIMARY KEY,
        valor VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$conn->query("
    INSERT IGNORE INTO config_quiniela (clave, valor) VALUES ('16avos_activos', '0')
");

// ¿Están activos?
$row = $conn->query("SELECT valor FROM config_quiniela WHERE clave='16avos_activos'")->fetch_assoc();
$activos = ($row['valor'] ?? '0') === '1';

// Partidos con resultados capturados por el admin
$partidos = [];
$res = $conn->query("SELECT * FROM resultados_16avos ORDER BY partido_idx ASC");
while ($r = $res->fetch_assoc()) {
    $partidos[] = $r;
}

echo json_encode([
    'ok'       => true,
    'activos'  => $activos,
    'partidos' => $partidos
]);