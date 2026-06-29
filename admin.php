<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ══════════════════════════════════════
//  CONFIGURACIÓN ADMIN
// ══════════════════════════════════════
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'imex2026'); // ← cambia esto

session_start();

// ── Login / Logout ──
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

// ── Conexión BD ──
if ($logueado) {
    require_once __DIR__ . '/coneccion.php'; // $conn

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
  <div class="sb">Partidos con resultado: <strong><?= count($resultados) ?></strong> / 72</div>
</div>

<div class="container">
  <?php if (!empty($guardado)): ?>
    <div class="alert-ok">✅ Resultado guardado correctamente en la base de datos.</div>
  <?php endif; ?>

  <div class="info-pts">
    🏆 <strong>Sistema de puntos:</strong> &nbsp;
    <strong>3 pts</strong> marcador exacto &nbsp;|&nbsp;
    <strong>1 pt</strong> ganador o empate correcto &nbsp;|&nbsp;
    <strong>0 pts</strong> pronóstico incorrecto
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
