<?php
// Buffer de salida defensivo: si por BOM, espacios o algún include hay
// una salida accidental antes de este punto, esto evita que
// session_start() / header() truenen con "headers already sent".
if (ob_get_level() === 0) {
    ob_start();
}
 
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ══════════════════════════════════════
//  CONFIGURACIÓN ADMIN
// ══════════════════════════════════════
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '@Charlote.20'); // ← cambia esto
 
if (headers_sent($archivo, $linea)) {
    die("Los headers ya fueron enviados en:<br><strong>$archivo</strong><br>Línea: <strong>$linea</strong>");
}

session_start();
 
// ── Login / Logout (va primero para poder saber si está logueado) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['usuario'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
$logueado = !empty($_SESSION['admin']);
 
//========================================
// CONEXIÓN A BD — SIEMPRE ANTES DE USAR $conn
//========================================
if ($logueado) {
    require_once __DIR__ . '/coneccion.php'; // define $conn
}
 
//========================================
// ACTUALIZAR PARTIDO ELIMINATORIA  (PASO 1)
//========================================
 
if($logueado && isset($_POST['actualizar_eliminatoria'])){
 
    $id=(int)$_POST['id'];
 
    $fase=(int)$_POST['fase'];
 
    $local=trim($_POST['local']);
 
    $visita=trim($_POST['visita']);
 
    $fecha=$_POST['fecha'];
 
    $hora=$_POST['hora'];
 
    $sede=$_POST['sede'];
 
    $activo=isset($_POST['activo'])?1:0;
 
    $stmt=$conn->prepare("
    UPDATE partidos_eliminatorias
    SET
        fase=?,
        local=?,
        visita=?,
        fecha=?,
        hora=?,
        sede=?,
        activo=?
    WHERE id=?
    ");
 
    $stmt->bind_param(
        "isssssii",
        $fase,
        $local,
        $visita,
        $fecha,
        $hora,
        $sede,
        $activo,
        $id
    );
 
    $stmt->execute();
    $stmt->close();
 
    header("Location: admin.php");
    exit;
}
 
//========================================
// ELIMINAR PARTIDO ELIMINATORIA  (PASO 2)
//========================================
 
if($logueado && isset($_GET['eliminarPartido'])){
 
    $id=(int)$_GET['eliminarPartido'];
 
    $stmt=$conn->prepare("
        DELETE FROM partidos_eliminatorias
        WHERE id=?
    ");
 
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
 
    header("Location: admin.php");
    exit;
}
 
//========================================
// CARGAR PARTIDO A EDITAR  (PASO 1)
//========================================
 
$editando = null;
 
if($logueado && isset($_GET['editar'])){
 
    $id=(int)$_GET['editar'];
 
    $stmt=$conn->prepare("
        SELECT *
        FROM partidos_eliminatorias
        WHERE id=?
    ");
 
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $editando=$stmt->get_result()->fetch_assoc();
    $stmt->close();
}
 
//========================================
// GUARDAR PARTIDO NUEVO
//========================================
 
if($logueado && isset($_POST['guardar_eliminatoria'])){
 
    $fase=(int)$_POST['fase'];
 
    $local=trim($_POST['local']);
 
    $visita=trim($_POST['visita']);
 
    $fecha=$_POST['fecha'];
 
    $hora=$_POST['hora'];
 
    $sede=$_POST['sede'];
 
    $activo=isset($_POST['activo'])?1:0;
 
    $stmt=$conn->prepare("
    SELECT
    IFNULL(MAX(partido_idx),0)+1 siguiente
    FROM partidos_eliminatorias
    WHERE fase=?
    ");
 
    $stmt->bind_param("i",$fase);
    $stmt->execute();
    $sig=$stmt->get_result()->fetch_assoc()['siguiente'];
    $stmt->close();
 
    $stmt=$conn->prepare("
    INSERT INTO partidos_eliminatorias
    (
    fase,
    partido_idx,
    local,
    visita,
    fecha,
    hora,
    sede,
    activo
    )
    VALUES
    (?,?,?,?,?,?,?,?)
    ");
 
    $stmt->bind_param(
        "iisssssi",
        $fase,
        $sig,
        $local,
        $visita,
        $fecha,
        $hora,
        $sede,
        $activo
    );
 
    $stmt->execute();
    $stmt->close();
 
    header("Location: admin.php");
    exit;
}
 
//========================================
// INFO DE FASES  (PASO 4 y PASO 5)
//========================================
// 'total' = número de partidos que debe haber en esa fase cuando está completa
$faseInfo = [
    32 => ['nombre' => '16avos',    'clase' => 'fase-32', 'total' => 16],
    16 => ['nombre' => '8avos',     'clase' => 'fase-16', 'total' => 8],
    8  => ['nombre' => 'Cuartos',   'clase' => 'fase-8',  'total' => 4],
    4  => ['nombre' => 'Semifinal', 'clase' => 'fase-4',  'total' => 2],
    3  => ['nombre' => '3er Lugar', 'clase' => 'fase-3',  'total' => 1],
    2  => ['nombre' => 'Final',     'clase' => 'fase-2',  'total' => 1],
];

//========================================
// VARIABLES POR DEFECTO
//========================================

$resultados = [];
$resultadosElim = [];
$participantes = null;
$totalParticipantes = 0;
$partidosEliminatoria = [];
$totalPartidosEliminatoria = 0;

$conteoFases = [
    32 => 0,
    16 => 0,
    8  => 0,
    4  => 0,
    3  => 0,
    2  => 0
];
// ── Resto de datos de la BD (ya con $conn disponible) ──
if ($logueado) {
 
    // Crear tabla resultados si no existe
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
 
    // Tabla de resultados reales de ELIMINATORIAS
    // (columnas: id, fase, partido_idx, local, visita, goles_local, goles_visita, res)
    // Se identifica el partido por fase + partido_idx, igual que resultados_reales
    // identifica los partidos de grupo por grupo + partido_idx.
    $conn->query("
        CREATE TABLE IF NOT EXISTS resultados_eliminatorias (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            fase         INT         NOT NULL,
            partido_idx  INT         NOT NULL,
            local        VARCHAR(60) NOT NULL DEFAULT '',
            visita       VARCHAR(60) NOT NULL DEFAULT '',
            goles_local  INT         NOT NULL DEFAULT 0,
            goles_visita INT         NOT NULL DEFAULT 0,
            res          VARCHAR(1)  NOT NULL,
            UNIQUE KEY fase_partido_unico (fase, partido_idx)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
 
    // ── Guardar resultado real de un partido de ELIMINATORIA ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_resultado_eliminatoria'])) {
        $faseElim  = (int)$_POST['fase'];
        $piElim    = (int)$_POST['partido_idx'];
        $localElim = trim($_POST['local']);
        $vistaElim = trim($_POST['visita']);
        $gl        = max(0, (int)$_POST['goles_local']);
        $gv        = max(0, (int)$_POST['goles_visita']);
        $res       = $gl > $gv ? '1' : ($gl < $gv ? '2' : 'x');
 
        $stmt = $conn->prepare("
            INSERT INTO resultados_eliminatorias (fase, partido_idx, local, visita, goles_local, goles_visita, res)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE goles_local=VALUES(goles_local), goles_visita=VALUES(goles_visita), res=VALUES(res)
        ");
        $stmt->bind_param("iissiis", $faseElim, $piElim, $localElim, $vistaElim, $gl, $gv, $res);
        $stmt->execute();
        $stmt->close();
        $guardadoElim = true;
    }
 
    // ── Guardar resultado ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_resultado'])) {
        $grupo   = $conn->real_escape_string($_POST['grupo']);
        $pi      = (int)$_POST['partido_idx'];
        $gl      = max(0, (int)$_POST['goles_local']);
        $gv      = max(0, (int)$_POST['goles_visita']);
        $res     = $gl > $gv ? '1' : ($gl < $gv ? '2' : 'x');
 
        $stmt = $conn->prepare("
            INSERT INTO resultados_reales (grupo, partido_idx, goles_local, goles_visita, res)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE goles_local=VALUES(goles_local), goles_visita=VALUES(goles_visita), res=VALUES(res)
        ");
        $stmt->bind_param("ssiis", $grupo, $pi, $gl, $gv, $res);
        $stmt->execute();
        $stmt->close();
        $guardado = true;
    }
 
    // ── Cargar todos los resultados guardados ──
    $resultados = [];
    $rows = $conn->query("SELECT * FROM resultados_reales");
    while ($row = $rows->fetch_assoc()) {
        $resultados[$row['grupo'] . '_' . $row['partido_idx']] = $row;
    }
 
    // ── Contar participantes ──
    $totalParticipantes = $conn->query("SELECT COUNT(*) as total FROM quinielas")->fetch_assoc()['total'];
 
    $participantes = $conn->query("
        SELECT
            id,
            nombre,
            correo,
            empresa,
            fecha_envio,
            quiniela_json
        FROM quinielas
        ORDER BY fecha_envio DESC
    ");
 
    //========================================
    // CARGAR PARTIDOS DE ELIMINATORIAS  (PASO 3)
    //========================================
    $partidosEliminatoria = [];
    $resElim = $conn->query("
        SELECT *
        FROM partidos_eliminatorias
        ORDER BY fase DESC, partido_idx ASC
    ");
    while ($row = $resElim->fetch_assoc()) {
        $partidosEliminatoria[] = $row;
    }
 
    // Conteo de partidos registrados por fase (PASO 5)
    $conteoFases = [
    32 => 0,
    16 => 0,
    8  => 0,
    4  => 0,
    3  => 0,
    2  => 0
    ];
    foreach ($partidosEliminatoria as $pe) {
        if (isset($conteoFases[$pe['fase']])) {
            $conteoFases[$pe['fase']]++;
        }
    }
 
    // ── Cargar resultados reales de eliminatorias (keyed por fase_partido_idx) ──
    $resultadosElim = [];
    $rowsElim = $conn->query("SELECT * FROM resultados_eliminatorias");
    while ($row = $rowsElim->fetch_assoc()) {
        $resultadosElim[$row['fase'] . '_' . $row['partido_idx']] = $row;
    }
}
 
// ── Datos de partidos ──
$grupos = [
  ["nombre"=>"A","partidos"=>[
    ["local"=>"México",        "visita"=>"Sudáfrica",     "fecha"=>"11 Jun"],
    ["local"=>"Corea del Sur", "visita"=>"Chequia",        "fecha"=>"12 Jun"],
    ["local"=>"México",        "visita"=>"Corea del Sur", "fecha"=>"18 Jun"],
    ["local"=>"Chequia",       "visita"=>"Sudáfrica",     "fecha"=>"18 Jun"],
    ["local"=>"Chequia",       "visita"=>"México",        "fecha"=>"24 Jun"],
    ["local"=>"Sudáfrica",     "visita"=>"Corea del Sur", "fecha"=>"24 Jun"],
  ]],
  ["nombre"=>"B","partidos"=>[
    ["local"=>"Canadá", "visita"=>"Bosnia", "fecha"=>"12 Jun"],
    ["local"=>"Qatar",  "visita"=>"Suiza",  "fecha"=>"13 Jun"],
    ["local"=>"Suiza",  "visita"=>"Bosnia", "fecha"=>"18 Jun"],
    ["local"=>"Canadá", "visita"=>"Qatar",  "fecha"=>"18 Jun"],
    ["local"=>"Canadá ",  "visita"=>"Suiza", "fecha"=>"24 Jun"],
    ["local"=>"Bosnia", "visita"=>"Qatar",  "fecha"=>"24 Jun"],
  ]],
  ["nombre"=>"C","partidos"=>[
    ["local"=>"Brasil",    "visita"=>"Marruecos", "fecha"=>"13 Jun"],
    ["local"=>"Haití",     "visita"=>"Escocia",   "fecha"=>"14 Jun"],
    ["local"=>"Brasil",    "visita"=>"Haití",     "fecha"=>"19 Jun"],
    ["local"=>"Escocia",   "visita"=>"Marruecos", "fecha"=>"19 Jun"],
    ["local"=>"Escocia",   "visita"=>"Brasil",    "fecha"=>"24 Jun"],
    ["local"=>"Marruecos", "visita"=>"Haití",     "fecha"=>"24 Jun"],
  ]],
  ["nombre"=>"D","partidos"=>[
    ["local"=>"Estados Unidos", "visita"=>"Paraguay",       "fecha"=>"12 Jun"],
    ["local"=>"Australia",      "visita"=>"Turquía",        "fecha"=>"14 Jun"],
    ["local"=>"Estados Unidos", "visita"=>"Australia",      "fecha"=>"19 Jun"],
    ["local"=>"Paraguay",        "visita"=>"Turquía ",       "fecha"=>"19 Jun"],
    ["local"=>"Turquía",        "visita"=>"Estados Unidos", "fecha"=>"25 Jun"],
    ["local"=>"Paraguay",       "visita"=>"Australia",      "fecha"=>"25 Jun"],
  ]],
  ["nombre"=>"E","partidos"=>[
    ["local"=>"Alemania",        "visita"=>"Curaçao",         "fecha"=>"14 Jun"],
    ["local"=>"Costa de Marfil", "visita"=>"Ecuador",         "fecha"=>"15 Jun"],
    ["local"=>"Alemania",        "visita"=>"Costa de Marfil", "fecha"=>"20 Jun"],
    ["local"=>"Ecuador",         "visita"=>"Curaçao",         "fecha"=>"20 Jun"],
    ["local"=>"Ecuador",         "visita"=>"Alemania",        "fecha"=>"25 Jun"],
    ["local"=>"Curaçao",         "visita"=>"Costa de Marfil", "fecha"=>"25 Jun"],
  ]],
  ["nombre"=>"F","partidos"=>[
    ["local"=>"Países Bajos", "visita"=>"Japón",        "fecha"=>"15 Jun"],
    ["local"=>"Suecia",       "visita"=>"Túnez",        "fecha"=>"15 Jun"],
    ["local"=>"Países Bajos", "visita"=>"Suecia",       "fecha"=>"20 Jun"],
    ["local"=>"Túnez",        "visita"=>"Japón",        "fecha"=>"20 Jun"],
    ["local"=>"Túnez",        "visita"=>"Países Bajos", "fecha"=>"25 Jun"],
    ["local"=>"Japón",        "visita"=>"Suecia",       "fecha"=>"25 Jun"],
  ]],
  ["nombre"=>"G","partidos"=>[
    ["local"=>"Bélgica",       "visita"=>"Egipto",        "fecha"=>"15 Jun"],
    ["local"=>"Irán",          "visita"=>"Nueva Zelanda", "fecha"=>"15 Jun"],
    ["local"=>"Bélgica",       "visita"=>"Irán",          "fecha"=>"21 Jun"],
    ["local"=>"Nueva Zelanda", "visita"=>"Egipto",        "fecha"=>"21 Jun"],
    ["local"=>"Bélgica", "visita"=>"Nueva Zelanda ",       "fecha"=>"26 Jun"],
    ["local"=>"Egipto",        "visita"=>"Irán",          "fecha"=>"26 Jun"],
  ]],
  ["nombre"=>"H","partidos"=>[
    ["local"=>"España",         "visita"=>"Cabo Verde",     "fecha"=>"15 Jun"],
    ["local"=>"Arabia Saudita", "visita"=>"Uruguay",        "fecha"=>"15 Jun"],
    ["local"=>"España",         "visita"=>"Arabia Saudita", "fecha"=>"21 Jun"],
    ["local"=>"Uruguay",        "visita"=>"Cabo Verde",     "fecha"=>"21 Jun"],
    ["local"=>"Uruguay",        "visita"=>"España",         "fecha"=>"26 Jun"],
    ["local"=>"Cabo Verde",     "visita"=>"Arabia Saudita", "fecha"=>"26 Jun"],
  ]],
  ["nombre"=>"I","partidos"=>[
    ["local"=>"Francia",  "visita"=>"Senegal", "fecha"=>"16 Jun"],
    ["local"=>"Irak",    "visita"=>"Noruega", "fecha"=>"16 Jun"],
    ["local"=>"Francia",  "visita"=>"Irak",   "fecha"=>"22 Jun"],
    ["local"=>"Senegal", "visita"=>"Noruega ", "fecha"=>"22 Jun"],
    ["local"=>"Noruega", "visita"=>"Francia", "fecha"=>"26 Jun"],
    ["local"=>"Senegal", "visita"=>"Irak",   "fecha"=>"26 Jun"],
  ]],
  ["nombre"=>"J","partidos"=>[
    ["local"=>"Argentina", "visita"=>"Argelia",   "fecha"=>"16 Jun"],
    ["local"=>"Austria",   "visita"=>"Jordania",  "fecha"=>"17 Jun"],
    ["local"=>"Argentina", "visita"=>"Austria",   "fecha"=>"22 Jun"],
    ["local"=>"Jordania",  "visita"=>"Argelia",   "fecha"=>"22 Jun"],
    ["local"=>"Argentina",  "visita"=>"Jordania ", "fecha"=>"27 Jun"],
    ["local"=>"Argelia",   "visita"=>"Austria",   "fecha"=>"27 Jun"],
  ]],
  ["nombre"=>"K","partidos"=>[
    ["local"=>"Portugal",   "visita"=>"RD Congo",   "fecha"=>"17 Jun"],
    ["local"=>"Uzbekistán", "visita"=>"Colombia",   "fecha"=>"17 Jun"],
    ["local"=>"Portugal",   "visita"=>"Uzbekistán", "fecha"=>"22 Jun"],
    ["local"=>"RD Congo",   "visita"=>"Colombia",   "fecha"=>"22 Jun"],
    ["local"=>"Portugal",   "visita"=>"Colombia ",   "fecha"=>"27 Jun"],
    ["local"=>"RD Congo",   "visita"=>"Uzbekistán", "fecha"=>"27 Jun"],
  ]],
  ["nombre"=>"L","partidos"=>[
    ["local"=>"Inglaterra", "visita"=>"Croacia",    "fecha"=>"17 Jun"],
    ["local"=>"Ghana",      "visita"=>"Panamá",     "fecha"=>"17 Jun"],
    ["local"=>"Inglaterra", "visita"=>"Ghana",      "fecha"=>"23 Jun"],
    ["local"=>"Panamá",     "visita"=>"Croacia",    "fecha"=>"23 Jun"],
    ["local"=>"Inglaterra",     "visita"=>"Panamá ", "fecha"=>"27 Jun"],
    ["local"=>"Croacia",    "visita"=>"Ghana",      "fecha"=>"27 Jun"],
  ]],
];

$totalPartidosEliminatoria = 0;


if ($logueado) {
    $totalPartidosEliminatoria = $conn->query("
        SELECT COUNT(*) total 
        FROM partidos_eliminatorias
    ")->fetch_assoc()['total'];
}

$totalPartidosMundial = 72 + $totalPartidosEliminatoria;
$totalResultados = count($resultados) + count($resultadosElim);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin – Quiniela Mundial 2026</title>
<style>
  :root{--azul:#003c69;--amarillo:#f9a825;--rojo:#d32f2f;--gris:#f4f4f4;--borde:#e0e0e0;}
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:Arial,sans-serif;background:#f0f2f5;color:#1a1a1a;}
  .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;}
  .login-card{background:#fff;border-radius:16px;padding:40px;width:360px;box-shadow:0 4px 24px rgba(0,0,0,.12);}
  .login-card h2{color:var(--azul);margin-bottom:24px;text-align:center;}
  .login-card input{width:100%;padding:12px;margin-bottom:14px;border:1px solid var(--borde);border-radius:8px;font-size:14px;}
  .login-card button{width:100%;padding:13px;background:var(--azul);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;}
  .error{background:#fdeaea;color:var(--rojo);padding:10px;border-radius:6px;margin-bottom:14px;font-size:13px;}
  .alert-ok{background:#e8f5e9;color:#2e7d32;padding:12px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;}
  header{background:var(--azul);color:#fff;padding:14px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
  header h1{font-size:17px;}
  header a{color:var(--amarillo);font-size:13px;text-decoration:none;}
  .stats-bar{background:#014e88;padding:10px 28px;display:flex;gap:28px;}
  .sb{color:#fff;font-size:13px;} .sb strong{color:var(--amarillo);}
  .container{max-width:1000px;margin:24px auto;padding:0 16px 80px;}
  .info-pts{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#5d4037;}
  .grupo-card{background:#fff;border-radius:12px;border:1px solid var(--borde);margin-bottom:20px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06);}
  .grupo-header{background:var(--azul);color:#fff;padding:10px 18px;font-weight:700;font-size:16px;}
  table{width:100%;border-collapse:collapse;}
  th{background:var(--gris);padding:9px 14px;font-size:11px;text-transform:uppercase;color:#666;text-align:left;}
  td{padding:10px 14px;font-size:13px;border-top:1px solid var(--gris);vertical-align:middle;}
  .score-inp{width:50px;padding:7px;text-align:center;border:1.5px solid var(--borde);border-radius:6px;font-size:15px;font-weight:700;}
  .btn-save{background:var(--azul);color:#fff;border:none;border-radius:6px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer;}
  .btn-save:hover{background:#014e88;}
  .badge{display:inline-block;font-size:11px;font-weight:600;padding:3px 9px;border-radius:5px;}
  .b1{background:#e6f7ee;color:#003c69;} .bx{background:#fffde7;color:#7a5c00;} .b2{background:#fdeaea;color:#d32f2f;}
  .pendiente{color:#bbb;font-style:italic;font-size:12px;}
  .fecha-col{color:#999;font-size:12px;}
 
  /* ── PASO 4: colores por fase ── */
  .fase-badge{display:inline-block;font-size:11px;font-weight:700;padding:4px 10px;border-radius:6px;white-space:nowrap;}
  .fase-16{background:#e3f2fd;color:#1565c0;}
  .fase-8 {background:#f3e5f5;color:#7b1fa2;}
  .fase-4 {background:#fff3e0;color:#ef6c00;}
  .fase-3 {background:#e0f2f1;color:#00897b;}
  .fase-2 {background:#fce4ec;color:#c2185b;}
 
  /* ── PASO 5: indicador de partidos faltantes ── */
  .resumen-fases{display:flex;flex-wrap:wrap;gap:10px;padding:16px;background:#fafafa;border-bottom:1px solid var(--borde);}
  .resumen-item{flex:1 1 140px;border-radius:8px;padding:10px 14px;font-size:12px;border:1px solid var(--borde);background:#fff;}
  .resumen-item .rf-nombre{font-weight:700;margin-bottom:4px;display:block;}
  .resumen-item.completo{border-color:#a5d6a7;background:#f1f8f2;}
  .resumen-item.incompleto{border-color:#ffcc80;background:#fff8ef;}
  .rf-ok{color:#2e7d32;font-weight:700;}
  .rf-falta{color:#e65100;font-weight:700;}
 
  .form-editando{background:#fff8e1;border:2px solid var(--amarillo);}
</style>
</head>
<body>
 
<?php if (!$logueado): ?>
<div class="login-wrap">
  <div class="login-card">
    <h2>⚽ Admin Quiniela 2026</h2>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="text"     name="usuario"  placeholder="Usuario"    required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit" name="login">Entrar</button>
    </form>
  </div>
</div>
 
<?php else: ?>
<header>
  <h1>⚽ Panel Admin – Quiniela Mundial 2026</h1>
  <a href="?logout">Cerrar sesión</a>
</header>
<div class="stats-bar">
  <div class="sb">Participantes registrados: <strong><?= $totalParticipantes ?></strong></div>

</div>
 
<div class="container">
  <?php if (!empty($guardado)): ?>
    <div class="alert-ok">✅ Resultado guardado correctamente en la base de datos.</div>
  <?php endif; ?>
  <?php if (!empty($guardadoElim)): ?>
    <div class="alert-ok">✅ Resultado real de eliminatoria guardado correctamente.</div>
  <?php endif; ?>
 
  <div class="info-pts">
    🏆 <strong>Sistema de puntos:</strong> &nbsp;
    <strong>3 pts</strong> marcador exacto &nbsp;|&nbsp;
    <strong>1 pt</strong> ganador o empate correcto &nbsp;|&nbsp;
    <strong>0 pts</strong> pronóstico incorrecto
  </div>
 
  <div class="grupo-card">
 
    <div class="grupo-header">
      ⚔️ Administración Eliminatorias
      <?php if ($editando): ?>
        &nbsp;—&nbsp;<span style="color:var(--amarillo);">Editando partido #<?= $editando['id'] ?></span>
      <?php endif; ?>
    </div>
 
    <!-- PASO 5: indicador de partidos faltantes por fase -->
    <div class="resumen-fases">
      <?php foreach ($faseInfo as $faseId => $info):
        $reg = $conteoFases[$faseId] ?? 0;
        $tot = $info['total'];
        $completo = $reg >= $tot;
      ?>
        <div class="resumen-item <?= $completo ? 'completo' : 'incompleto' ?>">
          <span class="rf-nombre"><?= $info['nombre'] ?></span>
          <?php if ($completo): ?>
            <span class="rf-ok">✅ Completo (<?= $reg ?>/<?= $tot ?>)</span>
          <?php else: ?>
            <span class="rf-falta">⚠️ Faltan <?= $tot - $reg ?> (<?= $reg ?>/<?= $tot ?>)</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
 
    <div style="padding:20px;">
 
      <form method="POST" class="<?= $editando ? 'form-editando' : '' ?>" style="padding:16px;border-radius:10px;">
 
        <?php if ($editando): ?>
          <input type="hidden" name="id" value="<?= $editando['id'] ?>">
        <?php endif; ?>
 
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:15px;">
 
         <div>
          <label>Fase</label>
          <select name="fase" required style="width:100%;padding:10px;">

            <option value="32" <?= (($editando['fase'] ?? 32) == 32) ? 'selected' : '' ?>>
              16avos
            </option>

            <option value="16" <?= (($editando['fase'] ?? 32) == 16) ? 'selected' : '' ?>>
              8avos
            </option>

            <option value="8" <?= (($editando['fase'] ?? 32) == 8) ? 'selected' : '' ?>>
              Cuartos
            </option>

            <option value="4" <?= (($editando['fase'] ?? 32) == 4) ? 'selected' : '' ?>>
              Semifinal
            </option>

            <option value="3" <?= (($editando['fase'] ?? 32) == 3) ? 'selected' : '' ?>>
              3er Lugar
            </option>

            <option value="2" <?= (($editando['fase'] ?? 32) == 2) ? 'selected' : '' ?>>
              Final
            </option>

          </select>
        </div>
          <div>
            <label>Equipo Local</label>
            <input type="text" name="local" required style="width:100%;padding:10px;" value="<?= htmlspecialchars($editando['local'] ?? '') ?>">
          </div>
 
          <div>
            <label>Equipo Visitante</label>
            <input type="text" name="visita" required style="width:100%;padding:10px;" value="<?= htmlspecialchars($editando['visita'] ?? '') ?>">
          </div>
 
          <div>
            <label>Fecha</label>
            <input type="date" name="fecha" style="width:100%;padding:10px;" value="<?= htmlspecialchars($editando['fecha'] ?? '') ?>">
          </div>
 
          <div>
            <label>Hora</label>
            <input type="time" name="hora" style="width:100%;padding:10px;" value="<?= htmlspecialchars($editando['hora'] ?? '') ?>">
          </div>
 
          <div>
            <label>Sede</label>
            <input type="text" name="sede" style="width:100%;padding:10px;" value="<?= htmlspecialchars($editando['sede'] ?? '') ?>">
          </div>
 
        </div>
 
        <br>
 
        <label>
          <input type="checkbox" name="activo" <?= (($editando['activo'] ?? 1) == 1) ? 'checked' : '' ?>>
          Activo
        </label>
 
        <br><br>
 
        <button class="btn-save" type="submit" name="<?= $editando ? 'actualizar_eliminatoria' : 'guardar_eliminatoria' ?>">
          <?php if ($editando): ?>
            Actualizar Partido
          <?php else: ?>
            Guardar Partido
          <?php endif; ?>
        </button>
 
        <?php if ($editando): ?>
          &nbsp;<a href="admin.php" style="font-size:12px;color:#999;">Cancelar edición</a>
        <?php endif; ?>
 
      </form>
 
    </div>
 
  </div>
 
  <?php foreach ($grupos as $grupo): ?>
  <div class="grupo-card">
    <div class="grupo-header">Grupo <?= $grupo['nombre'] ?></div>
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Partido</th>
          <th>Capturar resultado real</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grupo['partidos'] as $pi => $partido):
          $key = $grupo['nombre'] . '_' . $pi;
          $r   = $resultados[$key] ?? null;
        ?>
        <tr>
          <td class="fecha-col"><?= $partido['fecha'] ?></td>
          <td><strong><?= htmlspecialchars($partido['local']) ?></strong> vs <?= htmlspecialchars($partido['visita']) ?></td>
          <td>
            <form method="POST" style="display:flex;align-items:center;gap:8px;">
              <input type="hidden" name="grupo"         value="<?= $grupo['nombre'] ?>">
              <input type="hidden" name="partido_idx"   value="<?= $pi ?>">
              <input type="hidden" name="guardar_resultado" value="1">
              <input class="score-inp" type="number" name="goles_local"  min="0" max="20" value="<?= $r ? $r['goles_local']  : '' ?>" placeholder="–">
              <span style="font-weight:700;color:#bbb;font-size:18px;">:</span>
              <input class="score-inp" type="number" name="goles_visita" min="0" max="20" value="<?= $r ? $r['goles_visita'] : '' ?>" placeholder="–">
              <button class="btn-save" type="submit">Guardar</button>
            </form>
          </td>
          <td>
            <?php if ($r): ?>
              <span class="badge <?= $r['res']==='1'?'b1':($r['res']==='x'?'bx':'b2') ?>">
                <?= $r['goles_local'] ?>-<?= $r['goles_visita'] ?> &nbsp;·&nbsp;
                <?= $r['res']==='1'?'Local':($r['res']==='x'?'Empate':'Visitante') ?>
              </span>
            <?php else: ?>
              <span class="pendiente">Pendiente</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endforeach; ?>
 
  <!--
    PASO 3: tabla de partidos de eliminatorias ordenada por fase
    (ya se carga UNA sola vez arriba, fuera del foreach de grupos)
  -->
  <div class="grupo-card">
 
    <div class="grupo-header">
      📋 Partidos Registrados
    </div>
 
    <table>
      <thead>
        <tr>
          <th>Fase</th>
          <th>#</th>
          <th>Local</th>
          <th>Visitante</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Marcador real</th>
          <th>Activo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($partidosEliminatoria)): ?>
          <tr><td colspan="9" class="pendiente">Aún no hay partidos de eliminatorias registrados.</td></tr>
        <?php endif; ?>
 
        <?php foreach ($partidosEliminatoria as $p):
          $info = $faseInfo[$p['fase']] ?? ['nombre' => $p['fase'], 'clase' => ''];
          $rE   = $resultadosElim[$p['fase'] . '_' . $p['partido_idx']] ?? null;
        ?>
        <tr>
          <td>
            <!-- PASO 4: badge de color por fase -->
            <span class="fase-badge <?= $info['clase'] ?>"><?= $info['nombre'] ?></span>
          </td>
          <td><?= $p['partido_idx'] ?></td>
          <td><?= htmlspecialchars($p['local']) ?></td>
          <td><?= htmlspecialchars($p['visita']) ?></td>
          <td><?= htmlspecialchars($p['fecha']) ?></td>
          <td><?= htmlspecialchars($p['hora']) ?></td>
          <td>
            <!-- Captura de marcador REAL del partido de eliminatoria -->
            <form method="POST" style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
              <input type="hidden" name="fase"        value="<?= $p['fase'] ?>">
              <input type="hidden" name="partido_idx" value="<?= $p['partido_idx'] ?>">
              <input type="hidden" name="local"       value="<?= htmlspecialchars($p['local']) ?>">
              <input type="hidden" name="visita"      value="<?= htmlspecialchars($p['visita']) ?>">
              <input type="hidden" name="guardar_resultado_eliminatoria" value="1">
              <input class="score-inp" type="number" name="goles_local"  min="0" max="20" value="<?= $rE ? $rE['goles_local']  : '' ?>" placeholder="–" style="width:40px;">
              <span style="font-weight:700;color:#bbb;">:</span>
              <input class="score-inp" type="number" name="goles_visita" min="0" max="20" value="<?= $rE ? $rE['goles_visita'] : '' ?>" placeholder="–" style="width:40px;">
              <button class="btn-save" type="submit">Guardar</button>
            </form>
            <?php if ($rE): ?>
              <span class="badge <?= $rE['res']==='1'?'b1':($rE['res']==='x'?'bx':'b2') ?>">
                <?= $rE['goles_local'] ?>-<?= $rE['goles_visita'] ?> &nbsp;·&nbsp;
                <?= $rE['res']==='1'?'Local':($rE['res']==='x'?'Empate':'Visitante') ?>
              </span>
            <?php else: ?>
              <span class="pendiente">Pendiente</span>
            <?php endif; ?>
          </td>
          <td><?= $p['activo'] ? "✅" : "❌" ?></td>
          <td>
            <!-- PASO 1: link de editar -->
            <a href="?editar=<?= $p['id'] ?>">✏️</a>
            &nbsp;
            <!-- PASO 2: link de eliminar -->
            <a href="?eliminarPartido=<?= $p['id'] ?>" onclick="return confirm('¿Eliminar este partido?')">🗑</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
 
  <div class="grupo-card">
    <div class="grupo-header">
        👥 Participantes Registrados
    </div>
 
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Fecha</th>
                <th>Ver Quiniela</th>
            </tr>
        </thead>
 
        <tbody>
 
        <?php while($p = $participantes->fetch_assoc()): ?>
 
        <tr>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= htmlspecialchars($p['empresa']) ?></td>
            <td><?= htmlspecialchars($p['fecha_envio']) ?></td>
 
            <td>
                <details>
                    <summary>Ver</summary>
 
                    <?php
                    $quiniela = json_decode($p['quiniela_json'], true);
 
                    foreach($quiniela as $jornada => $partidos){
                        echo "<br><strong>Jornada ".($jornada+1)."</strong><br>";
 
                        foreach($partidos as $num => $partido){
 
                            echo "Partido ".($num+1).
                                 " → ".
                                 strtoupper($partido['res']).
                                 " (".
                                 $partido['gl']."-".$partido['gv'].
                                 ")<br>";
                        }
                    }
                    ?>
 
                </details>
            </td>
        </tr>
 
        <?php endwhile; ?>
 
        </tbody>
    </table>
</div>
</div>
<?php endif; ?>
</body>
</html>
 