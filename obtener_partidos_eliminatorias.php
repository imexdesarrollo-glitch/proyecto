<?php
// ══════════════════════════════════════
//  obtener_partidos_eliminatorias.php
//  Devuelve en JSON los partidos de eliminatoria
//  ACTIVOS, ordenados por fase (16avos → Final),
//  incluyendo el marcador real si el admin ya lo
//  capturó en resultados_eliminatorias.
// ══════════════════════════════════════
 
header('Content-Type: application/json; charset=utf-8');
 
require_once __DIR__ . '/coneccion.php'; // $conn
 
$sql = "
    SELECT
        pe.id,
        pe.fase,
        pe.partido_idx,
        pe.local,
        pe.visita,
        pe.fecha,
        pe.hora,
        pe.sede,
        pe.activo,
        re.goles_local,
        re.goles_visita,
        re.res
    FROM partidos_eliminatorias pe
    LEFT JOIN resultados_eliminatorias re
        ON re.fase = pe.fase AND re.partido_idx = pe.partido_idx
    WHERE pe.activo = 1
    ORDER BY pe.fase DESC, pe.partido_idx ASC
";
 
$result = $conn->query($sql);
 
$partidos = [];
 
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Normaliza tipos: null si no hay resultado capturado todavía
        $row['id']           = (int)$row['id'];
        $row['fase']         = (int)$row['fase'];
        $row['partido_idx']  = (int)$row['partido_idx'];
        $row['activo']       = (int)$row['activo'];
        $row['goles_local']  = is_null($row['goles_local'])  ? null : (int)$row['goles_local'];
        $row['goles_visita'] = is_null($row['goles_visita']) ? null : (int)$row['goles_visita'];
        // res queda tal cual: '1' | 'x' | '2' | null
 
        $partidos[] = $row;
    }
}
 
echo json_encode($partidos, JSON_UNESCAPED_UNICODE);
 