<?php
session_start();
require_once __DIR__ . '/coneccion.php'; // $conn

// Crear tabla resultados_reales si no existe
$conn->query("
    CREATE TABLE IF NOT EXISTS resultados_reales (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        grupo        VARCHAR(2)  NOT NULL,
        partido_idx  INT         NOT NULL,
        goles_local  INT         NOT NULL DEFAULT 0,
        goles_visita INT         NOT NULL DEFAULT 0,
        res          VARCHAR(1)  NOT NULL,
        actualizado  DATETIME    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY partido_unico (grupo, partido_idx)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── Grupos y partidos ──
$grupos = [
  ["nombre"=>"A","partidos"=>[
    ["local"=>"México","visita"=>"Sudáfrica"],["local"=>"Corea del Sur","visita"=>"Chequia"],
    ["local"=>"México","visita"=>"Corea del Sur"],["local"=>"Sudáfrica","visita"=>"Chequia"],
    ["local"=>"México","visita"=>"Chequia"],["local"=>"Sudáfrica","visita"=>"Corea del Sur"],
  ]],
  ["nombre"=>"B","partidos"=>[
    ["local"=>"Canadá","visita"=>"Bosnia"],["local"=>"Qatar","visita"=>"Suiza"],
    ["local"=>"Canadá","visita"=>"Qatar"],["local"=>"Bosnia","visita"=>"Suiza"],
    ["local"=>"Canadá","visita"=>"Suiza"],["local"=>"Bosnia","visita"=>"Qatar"],
  ]],
  ["nombre"=>"C","partidos"=>[
    ["local"=>"Brasil","visita"=>"Marruecos"],["local"=>"Haití","visita"=>"Escocia"],
    ["local"=>"Brasil","visita"=>"Haití"],["local"=>"Marruecos","visita"=>"Escocia"],
    ["local"=>"Brasil","visita"=>"Escocia"],["local"=>"Haití","visita"=>"Marruecos"],
  ]],
  ["nombre"=>"D","partidos"=>[
    ["local"=>"Estados Unidos","visita"=>"Paraguay"],["local"=>"Australia","visita"=>"Turquía"],
    ["local"=>"Estados Unidos","visita"=>"Australia"],["local"=>"Paraguay","visita"=>"Turquía"],
    ["local"=>"Estados Unidos","visita"=>"Turquía"],["local"=>"Paraguay","visita"=>"Australia"],
  ]],
  ["nombre"=>"E","partidos"=>[
    ["local"=>"Alemania","visita"=>"Curaçao"],["local"=>"Costa de Marfil","visita"=>"Ecuador"],
    ["local"=>"Alemania","visita"=>"Costa de Marfil"],["local"=>"Curaçao","visita"=>"Ecuador"],
    ["local"=>"Alemania","visita"=>"Ecuador"],["local"=>"Curaçao","visita"=>"Costa de Marfil"],
  ]],
  ["nombre"=>"F","partidos"=>[
    ["local"=>"Países Bajos","visita"=>"Japón"],["local"=>"Suecia","visita"=>"Túnez"],
    ["local"=>"Países Bajos","visita"=>"Suecia"],["local"=>"Japón","visita"=>"Túnez"],
    ["local"=>"Países Bajos","visita"=>"Túnez"],["local"=>"Japón","visita"=>"Suecia"],
  ]],
  ["nombre"=>"G","partidos"=>[
    ["local"=>"Bélgica","visita"=>"Egipto"],["local"=>"Irán","visita"=>"Nueva Zelanda"],
    ["local"=>"Bélgica","visita"=>"Irán"],["local"=>"Egipto","visita"=>"Nueva Zelanda"],
    ["local"=>"Bélgica","visita"=>"Nueva Zelanda"],["local"=>"Egipto","visita"=>"Irán"],
  ]],
  ["nombre"=>"H","partidos"=>[
    ["local"=>"España","visita"=>"Cabo Verde"],["local"=>"Arabia Saudita","visita"=>"Uruguay"],
    ["local"=>"España","visita"=>"Arabia Saudita"],["local"=>"Cabo Verde","visita"=>"Uruguay"],
    ["local"=>"España","visita"=>"Uruguay"],["local"=>"Cabo Verde","visita"=>"Arabia Saudita"],
  ]],
  ["nombre"=>"I","partidos"=>[
    ["local"=>"Francia","visita"=>"Senegal"],["local"=>"Irak","visita"=>"Noruega"],
    ["local"=>"Francia","visita"=>"Irak"],["local"=>"Senegal","visita"=>"Noruega"],
    ["local"=>"Francia","visita"=>"Noruega"],["local"=>"Irak","visita"=>"Senegal"],
  ]],
  ["nombre"=>"J","partidos"=>[
    ["local"=>"Argentina","visita"=>"Argelia"],["local"=>"Austria","visita"=>"Jordania"],
    ["local"=>"Argentina","visita"=>"Austria"],["local"=>"Argelia","visita"=>"Jordania"],
    ["local"=>"Argentina","visita"=>"Jordania"],["local"=>"Argelia","visita"=>"Austria"],
  ]],
  ["nombre"=>"K","partidos"=>[
    ["local"=>"Portugal","visita"=>"RD Congo"],["local"=>"Uzbekistán","visita"=>"Colombia"],
    ["local"=>"Portugal","visita"=>"Uzbekistán"],["local"=>"RD Congo","visita"=>"Colombia"],
    ["local"=>"Portugal","visita"=>"Colombia"],["local"=>"RD Congo","visita"=>"Uzbekistán"],
  ]],
  ["nombre"=>"L","partidos"=>[
    ["local"=>"Inglaterra","visita"=>"Panamá"],["local"=>"Croacia","visita"=>"Ghana"],
    ["local"=>"Inglaterra","visita"=>"Croacia"],["local"=>"Panamá","visita"=>"Ghana"],
    ["local"=>"Inglaterra","visita"=>"Ghana"],["local"=>"Croacia","visita"=>"Panamá"],
  ]],
];

// ── Función para calcular puntos de una quiniela ──
function calcularPuntos($quinielaJson, $resultados, $grupos) {
    $quiniela = json_decode($quinielaJson, true);
    if (!$quiniela) return ['puntos'=>0,'exactos'=>0,'ganadores'=>0,'jugados'=>0];

    $puntos = 0; $exactos = 0; $ganadores = 0; $jugados = 0;

    foreach ($grupos as $gi => $grupo) {
        foreach ($grupo['partidos'] as $pi => $partido) {
            $key = $grupo['nombre'] . '_' . $pi;
            if (!isset($resultados[$key])) continue;
            $real = $resultados[$key];
            $pron = $quiniela[$gi][$pi] ?? null;
            if (!$pron || !isset($pron['res']) || !$pron['res']) continue;
            $jugados++;
            if (isset($pron['gl'], $pron['gv'])
                && (string)$pron['gl'] === (string)$real['goles_local']
                && (string)$pron['gv'] === (string)$real['goles_visita']) {
                $puntos += 3; $exactos++;
            } elseif ($pron['res'] === $real['res']) {
                $puntos += 1; $ganadores++;
            }
        }
    }
    return compact('puntos','exactos','ganadores','jugados');
}


function calcularPuntosEliminatoria($quinielaId, $fase, $resultados, $conn)
{
    $stmt = $conn->prepare("
        SELECT quiniela_json
        FROM quinielas_eliminatorias
        WHERE quiniela_id = ?
        AND fase = ?
        LIMIT 1
    ");

    $stmt->bind_param("ii", $quinielaId, $fase);
    $stmt->execute();

    $res = $stmt->get_result();

    $stmt->close();

    if ($res->num_rows == 0) {
        return [
            'puntos'    => 0,
            'exactos'   => 0,
            'ganadores' => 0,
            'jugados'   => 0
        ];
    }

    $quiniela = json_decode(
        $res->fetch_assoc()['quiniela_json'],
        true
    );

    $puntos = 0;
    $exactos = 0;
    $ganadores = 0;
    $jugados = 0;

    foreach ($quiniela as $pron) {

        $partidoId = $pron['partido_id'];

        if (!isset($resultados[$partidoId])) {
            continue;
        }

        $real = $resultados[$partidoId];

        if (
            $real['goles_local'] === null ||
            $real['goles_visita'] === null
        ) {
            continue;
        }

        $jugados++;

        $esExacto =
            (string)$pron['gl'] == (string)$real['goles_local'] &&
            (string)$pron['gv'] == (string)$real['goles_visita'];

        if ($esExacto) {

            $puntos += 3;
            $exactos++;

        } elseif ($pron['res'] == $real['res']) {

            $puntos += 1;
            $ganadores++;

        }
    }

    return compact(
        'puntos',
        'exactos',
        'ganadores',
        'jugados'
    );
}

// ── Cargar resultados reales de la BD ──
$resultados = [];
$rows = $conn->query("SELECT * FROM resultados_reales");
while ($row = $rows->fetch_assoc()) {
    $resultados[$row['grupo'] . '_' . $row['partido_idx']] = $row;
}
$partidosJugados = count($resultados);

$resultadosEliminatoria = [];

$q = $conn->query("
SELECT r.*
FROM resultados_eliminatorias r
INNER JOIN partidos_eliminatorias p 
ON r.fase = p.fase 
AND r.partido_idx = p.partido_idx
WHERE p.evaluable = 1
");

while($r = $q->fetch_assoc()){

    $resultadosEliminatoria[$r['id']] = $r;

}

$partidosEliminatoriaJugados = count(
    array_filter(
        $resultadosEliminatoria,
        fn($p)=>$p['goles_local']!==null
    )
);

$totalPartidosJugados = 
    $partidosJugados + 
    $partidosEliminatoriaJugados;

$totalPartidosMundial = 104;

// ── Autenticación por correo o folio ──
$acceso     = false;
$miQuiniela = null;
$errorLogin = '';

if (isset($_POST['buscar'])) {
    $credencial = strtolower(trim($_POST['credencial']));
    $stmt = $conn->prepare("SELECT * FROM quinielas WHERE LOWER(correo)=? OR LOWER(CAST(id AS CHAR))=? LIMIT 1");
    $stmt->bind_param("ss", $credencial, $credencial);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $miQuiniela = $result->fetch_assoc();
        $_SESSION['quiniela_id'] = $miQuiniela['id'];
        $acceso = true;
    } else {
        $errorLogin = 'No encontramos ese correo o folio. Verifica e intenta de nuevo.';
    }
    $stmt->close();
}

if (!$acceso && isset($_SESSION['quiniela_id'])) {
    $id   = (int)$_SESSION['quiniela_id'];
    $stmt = $conn->prepare("SELECT * FROM quinielas WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $miQuiniela = $result->fetch_assoc();
        $acceso = true;
    }
    $stmt->close();
}

if (isset($_GET['salir'])) {
    unset($_SESSION['quiniela_id']);
    header('Location: posiciones.php');
    exit;
}

// ── Construir ranking (solo si está autenticado) ──
$ranking            = [];
$totalParticipantes = 0;

if ($acceso) {
    $todos = $conn->query("SELECT id, nombre, empresa, quiniela_json FROM quinielas ORDER BY id ASC");
    $totalParticipantes = $todos->num_rows;

    while ($q = $todos->fetch_assoc()) {
      $stats = calcularPuntos(
    $q['quiniela_json'],
    $resultados,
    $grupos
);

$elim32 = calcularPuntosEliminatoria(
    $q['id'],
    32,
    $resultadosEliminatoria,
    $conn
);

$elim16 = calcularPuntosEliminatoria(
    $q['id'],
    16,
    $resultadosEliminatoria,
    $conn
);

$elim8 = calcularPuntosEliminatoria(
    $q['id'],
    8,
    $resultadosEliminatoria,
    $conn
);

$elim4 = calcularPuntosEliminatoria(
    $q['id'],
    4,
    $resultadosEliminatoria,
    $conn
);

$elim3 = calcularPuntosEliminatoria(
    $q['id'],
    3,
    $resultadosEliminatoria,
    $conn
);

$elim2 = calcularPuntosEliminatoria(
    $q['id'],
    2,
    $resultadosEliminatoria,
    $conn
);

$bonus = 0;

$stmtBonus = $conn->prepare("
    SELECT SUM(puntos) total
    FROM puntos_bonus
    WHERE quiniela_id = ?
");

$stmtBonus->bind_param(
    "i",
    $q['id']
);

$stmtBonus->execute();

$rBonus = $stmtBonus->get_result()->fetch_assoc();

$bonus = $rBonus['total'] ?? 0;

// Bonus otorgado por 16avos + 8vos
$bonusGanadores = 0;
$bonusEvaluados = 0;

if($bonus > 0){
    $bonusGanadores = 24;
    $bonusEvaluados = 24;
}

$ranking[] = [

    'id'=>$q['id'],

    'nombre'=>$q['nombre'],

    'empresa'=>$q['empresa'],

    'puntos'=>

        $stats['puntos']+

        $elim32['puntos']+

        $elim16['puntos']+

        $elim8['puntos']+

        $elim4['puntos']+

        $elim3['puntos']+

        $elim2['puntos']+

        $bonus,

    'exactos'=>

        $stats['exactos']+

        $elim32['exactos']+

        $elim16['exactos']+

        $elim8['exactos']+

        $elim4['exactos']+

        $elim3['exactos']+

        $elim2['exactos'],

    'ganadores'=>

        $stats['ganadores']+

        $elim32['ganadores']+

        $elim16['ganadores']+

        $elim8['ganadores']+

        $elim4['ganadores']+

        $elim3['ganadores']+

        $elim2['ganadores']+

        $bonusGanadores,

    'jugados'=>

        $stats['jugados']+

        $elim32['jugados']+

        $elim16['jugados']+

        $elim8['jugados']+

        $elim4['jugados']+

        $elim3['jugados']+

        $elim2['jugados']+

        $bonusEvaluados,

    'esYo'=>($q['id']==($_SESSION['quiniela_id']??0))

];
}

    // Ordenar: más puntos → más exactos como desempate
    usort($ranking, fn($a,$b) => $b['puntos'] <=> $a['puntos'] ?: $b['exactos'] <=> $a['exactos']);
}



// ── Mi posición ──
$miPos   = 1;
$miDatos = null;
foreach ($ranking as $idx => $r) {
    if ($r['esYo']) { $miPos = $idx + 1; $miDatos = $r; break; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Posiciones – Quiniela Mundial 2026</title>
<style>
  :root{--azul:#003c69;--amarillo:#f9a825;--rojo:#d32f2f;--gris:#f4f4f4;--borde:#e0e0e0;}
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:Arial,sans-serif;background:#f0f2f5;color:#1a1a1a;min-height:100vh;}
  /* HERO */
  .hero{background:linear-gradient(135deg,#003c69,#006bbd);color:#fff;text-align:center;padding:36px 20px 28px;}
  .hero h1{font-size:clamp(26px,5vw,44px);font-weight:900;letter-spacing:.04em;}
  .hero h1 span{color:var(--amarillo);}
  .hero p{font-size:13px;opacity:.75;margin-top:6px;}
  .hero-stats{display:flex;justify-content:center;gap:32px;margin-top:22px;flex-wrap:wrap;}
  .hs{text-align:center;}
  .hs-num{font-size:30px;font-weight:900;color:var(--amarillo);}
  .hs-lbl{font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.06em;}
  /* LOGIN */
  .login-wrap{max-width:440px;margin:40px auto;padding:0 16px;}
  .login-card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,.1);}
  .login-card h2{color:var(--azul);margin-bottom:8px;font-size:18px;}
  .login-card p{color:#666;font-size:13px;margin-bottom:20px;line-height:1.5;}
  .login-card input{width:100%;padding:12px;border:1px solid var(--borde);border-radius:8px;font-size:14px;margin-bottom:12px;}
  .login-card button{width:100%;padding:13px;background:var(--azul);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;}
  .error{background:#fdeaea;color:var(--rojo);padding:10px 14px;border-radius:7px;margin-bottom:14px;font-size:13px;}
  /* RANKING */
  .container{max-width:900px;margin:28px auto;padding:0 16px 60px;}
  .top-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
  .top-bar h2{font-size:16px;color:var(--azul);}
  .top-bar a{font-size:12px;color:var(--rojo);text-decoration:none;}
  /* MI TARJETA */
  .mi-card{background:linear-gradient(135deg,#003c69,#014e88);color:#fff;border-radius:14px;padding:20px 24px;margin-bottom:22px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
  .mi-pos{font-size:42px;font-weight:900;color:var(--amarillo);min-width:56px;text-align:center;line-height:1;}
  .mi-info{flex:1;}
  .mi-nombre{font-size:18px;font-weight:700;}
  .mi-empresa{font-size:12px;opacity:.7;margin-top:3px;}
  .mi-detalle{font-size:12px;opacity:.8;margin-top:8px;}
  .mi-pts{text-align:center;}
  .mi-pts-num{font-size:38px;font-weight:900;color:var(--amarillo);line-height:1;}
  .mi-pts-lbl{font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;}
  /* TABLA */
  .tabla-card{background:#fff;border-radius:14px;border:1px solid var(--borde);overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07);}
  .tabla-header{background:var(--azul);color:#fff;padding:12px 18px;font-weight:700;font-size:15px;}
  table{width:100%;border-collapse:collapse;}
  th{background:var(--gris);padding:9px 14px;font-size:10px;text-transform:uppercase;color:#666;text-align:left;letter-spacing:.06em;}
  td{padding:11px 14px;font-size:13px;border-top:1px solid var(--gris);vertical-align:middle;}
  tr.yo td{background:#e8f4ff!important;font-weight:600;}
  tr:hover td{background:#fafafa;}
  .pos-badge{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;}
  .pos-1{background:#ffd700;color:#1a1a1a;}
  .pos-2{background:#c0c0c0;color:#1a1a1a;}
  .pos-3{background:#cd7f32;color:#fff;}
  .pos-n{background:var(--gris);color:#666;}
  .pts-num{font-size:18px;font-weight:700;color:var(--azul);}
  .yo-badge{background:var(--amarillo);color:#1a1a1a;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:6px;vertical-align:middle;}
  .info-pts{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#5d4037;}
  .nota{text-align:center;color:#aaa;font-size:12px;margin-top:14px;}
  @media(max-width:600px){
    th:nth-child(4),td:nth-child(4),th:nth-child(5),td:nth-child(5){display:none;}
    .mi-pts-num{font-size:28px;}
  }
</style>
</head>
<body>

<div class="hero">
  <h1>MUNDIAL <span>2026</span></h1>
  <p>Tabla de posiciones en tiempo real</p>

  <?php if ($acceso): ?>

  <div class="hero-stats">

    <div class="hs">
      <div class="hs-num"><?= $totalParticipantes ?></div>
      <div class="hs-lbl">Participantes</div>
    </div>

    <div class="hs">
      <div class="hs-num"><?= $partidosJugados ?></div>
      <div class="hs-lbl">Fase De Grupos</div>
    </div>

    <div class="hs">
      <div class="hs-num"><?= $partidosEliminatoriaJugados ?></div>
      <div class="hs-lbl">Eliminatorias</div>
    </div>

    <div class="hs">
      <div class="hs-num">
        <?= $partidosJugados + $partidosEliminatoriaJugados ?>
      </div>
      <div class="hs-lbl">Total Partidos Evaluados</div>
    </div>

    <div class="hs">
      <div class="hs-num">
        <?= !empty($ranking) ? $ranking[0]['puntos'] : 0 ?>
      </div>
      <div class="hs-lbl">Puntaje líder</div>
    </div>

  </div>

  <?php endif; ?>

</div>
<?php if (!$acceso): ?>
<!-- ── PANTALLA DE LOGIN ── -->
<div class="login-wrap">
  <div class="login-card">
    <h2>🔒 Ver posiciones</h2>
    <p>Ingresa el correo electrónico o el número de folio que recibiste al registrar tu quiniela.</p>
    <?php if ($errorLogin): ?>
      <div class="error"><?= htmlspecialchars($errorLogin) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="credencial" placeholder="Correo o número de folio" required autocomplete="off">
      <button type="submit" name="buscar">Ver mis posiciones →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── TABLA DE POSICIONES ── -->
<div class="container">

  <div class="top-bar">
    <h2>📊 Tabla de posiciones</h2>
    <a href="?salir">Cerrar sesión</a>
  </div>

  <?php if ($miDatos): ?>
  <div class="mi-card">
    <div class="mi-pos">#<?= $miPos ?></div>
    <div class="mi-info">
      <div class="mi-nombre"><?= htmlspecialchars($miDatos['nombre']) ?></div>
      <?php if ($miDatos['empresa']): ?>
        <div class="mi-empresa"><?= htmlspecialchars($miDatos['empresa']) ?></div>
      <?php endif; ?>
      <div class="mi-detalle">
        ⚡ <?= $miDatos['exactos'] ?> marcadores exactos (×3 pts)
        &nbsp;·&nbsp; ✅ <?= $miDatos['ganadores'] ?> ganadores (×1 pt)
        &nbsp;·&nbsp; 📋 <?= $totalPartidosJugados ?> / <?= $totalPartidosMundial ?> partidos evaluados
      </div>
    </div>
    <div class="mi-pts">
      <div class="mi-pts-num"><?= $miDatos['puntos'] ?></div>
      <div class="mi-pts-lbl">puntos</div>
    </div>
  </div>
  <?php endif; ?>

  <div class="info-pts">
    🏆 <strong>Puntuación:</strong>
    &nbsp;<strong>3 pts</strong> marcador exacto &nbsp;|&nbsp;
    <strong>1 pt</strong> ganador o empate correcto &nbsp;|&nbsp;
    <strong>0 pts</strong> pronóstico incorrecto
    &nbsp;·&nbsp; Desempate: mayor número de exactos.
  </div>

  <div class="tabla-card">
    <div class="tabla-header">🏅 Ranking general — <?= $totalParticipantes ?> participantes</div>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Participante</th>
          <th>Puntos</th>
          <th>Exactos</th>
          <th>Ganadores</th>
          <th>Evaluados</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ranking as $idx => $r):
          $pos = $idx + 1;
          $pc  = $pos===1?'pos-1':($pos===2?'pos-2':($pos===3?'pos-3':'pos-n'));
        ?>
        <tr <?= $r['esYo']?'class="yo"':'' ?>>
          <td><span class="pos-badge <?= $pc ?>"><?= $pos ?></span></td>
          <td>
            <?= htmlspecialchars($r['nombre']) ?>
            <?php if ($r['empresa']): ?>
              <span style="color:#aaa;font-size:11px;"> · <?= htmlspecialchars($r['empresa']) ?></span>
            <?php endif; ?>
            <?php if ($r['esYo']): ?><span class="yo-badge">TÚ</span><?php endif; ?>
          </td>
          <td><div class="pts-num"><?= $r['puntos'] ?></div></td>
          <td><span style="color:#003c69;font-weight:600;"><?= $r['exactos'] ?></span></td>
          <td><span style="color:#f9a825;font-weight:600;"><?= $r['ganadores'] ?></span></td>
          <td style="color:#aaa;"><?= $r['jugados'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p class="nota">La tabla se actualiza automáticamente cuando el admin registra los resultados de cada partido.</p>
</div>
<?php endif; ?>

</body>
</html>
