let ultimoIdInsertado = null;
let datosParticipante = {};
let resultadosReales = {};
let partidosEliminatoria = [];
let stateEliminatoria = [];
let folioEliminatoriaActual = null;
let participanteEliminatoria = null;

const grupos = [
  {
    nombre: "A", partidos: [
      { local: "🇲🇽 México", visita: "🇿🇦 Sudáfrica", fecha: "11 Jun", sede: "Azteca, CDMX" },
      { local: "🇰🇷 Corea del Sur", visita: "🇨🇿 Chequia", fecha: "12 Jun", sede: "Akron, Guadalajara" },
      { local: "🇲🇽 México", visita: "🇰🇷 Corea del Sur", fecha: "18 Jun", sede: "Akron, Guadalajara" },
      { local: "🇨🇿 Chequia", visita: "🇿🇦 Sudáfrica", fecha: "18 Jun", sede: "Mercedes-Benz, Atlanta" },
      { local: "🇨🇿 Chequia", visita: "🇲🇽 México", fecha: "24 Jun", sede: "Azteca, CDMX" },
      { local: "🇿🇦 Sudáfrica", visita: "🇰🇷 Corea del Sur", fecha: "24 Jun", sede: "BBVA, Monterrey" },
    ]
  },
  {
    nombre: "B", partidos: [
      { local: "🇨🇦 Canadá", visita: "🇧🇦 Bosnia", fecha: "12 Jun", sede: "BMO Field, Toronto" },
      { local: "🇶🇦 Qatar", visita: "🇨🇭 Suiza", fecha: "13 Jun", sede: "Levi's Stadium, SF" },
      { local: "🇨🇭 Suiza", visita: "🇧🇦 Bosnia", fecha: "18 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇨🇦 Canadá", visita: "🇶🇦 Qatar", fecha: "18 Jun", sede: "BC Place, Vancouver" },
      { local: "🇨🇭 Suiza", visita: "🇨🇦 Canadá", fecha: "24 Jun", sede: "BC Place, Vancouver" },
      { local: "🇧🇦 Bosnia", visita: "🇶🇦 Qatar", fecha: "24 Jun", sede: "Lumen Field, Seattle" },
    ]
  },
  {
    nombre: "C", partidos: [
      { local: "🇧🇷 Brasil", visita: "🇲🇦 Marruecos", fecha: "13 Jun", sede: "MetLife, NY/NJ" },
      { local: "🇭🇹 Haití", visita: "🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escocia", fecha: "14 Jun", sede: "Gillette, Boston" },
      { local: "🇧🇷 Brasil", visita: "🇭🇹 Haití", fecha: "19 Jun", sede: "Lincoln Financial, Filadelfia" },
      { local: "🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escocia", visita: "🇲🇦 Marruecos", fecha: "19 Jun", sede: "Gillette, Boston" },
      { local: "🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escocia", visita: "🇧🇷 Brasil", fecha: "24 Jun", sede: "Hard Rock, Miami" },
      { local: "🇲🇦 Marruecos", visita: "🇭🇹 Haití", fecha: "24 Jun", sede: "Mercedes-Benz, Atlanta" },
    ]
  },
  {
    nombre: "D", partidos: [
      { local: "🇺🇸 Estados Unidos", visita: "🇵🇾 Paraguay", fecha: "12 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇦🇺 Australia", visita: "🇹🇷 Turquía", fecha: "14 Jun", sede: "NRG, Houston" },
      { local: "🇺🇸 Estados Unidos", visita: "🇦🇺 Australia", fecha: "19 Jun", sede: "Lumen Field, Seattle" },
      { local: "🇹🇷 Turquía", visita: "🇵🇾 Paraguay", fecha: "19 Jun", sede: "Levi's Stadium, SF" },
      { local: "🇹🇷 Turquía", visita: "🇺🇸 Estados Unidos", fecha: "25 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇵🇾 Paraguay", visita: "🇦🇺 Australia", fecha: "25 Jun", sede: "Levi's Stadium, SF" },
    ]
  },
  {
    nombre: "E", partidos: [
      { local: "🇩🇪 Alemania", visita: "🇨🇼 Curaçao", fecha: "14 Jun", sede: "MetLife, NY/NJ" },
      { local: "🇨🇮 Costa de Marfil", visita: "🇪🇨 Ecuador", fecha: "15 Jun", sede: "AT&T, Dallas" },
      { local: "🇩🇪 Alemania", visita: "🇨🇮 Costa de Marfil", fecha: "20 Jun", sede: "BMO Field, Toronto" },
      { local: "🇪🇨 Ecuador", visita: "🇨🇼 Curaçao", fecha: "20 Jun", sede: "Arrowhead, Kansas City" },
      { local: "🇪🇨 Ecuador", visita: "🇩🇪 Alemania", fecha: "25 Jun", sede: "MetLife, NY/NJ" },
      { local: "🇨🇼 Curaçao", visita: "🇨🇮 Costa de Marfil", fecha: "25 Jun", sede: "Lincoln Financial, Filadelfia" },
    ]
  },
  {
    nombre: "F", partidos: [
      { local: "🇳🇱 Países Bajos", visita: "🇯🇵 Japón", fecha: "15 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇸🇪 Suecia", visita: "🇹🇳 Túnez", fecha: "15 Jun", sede: "Levi's Stadium, SF" },
      { local: "🇳🇱 Países Bajos", visita: "🇸🇪 Suecia", fecha: "20 Jun", sede: "NRG, Houston" },
      { local: "🇹🇳 Túnez", visita: "🇯🇵 Japón", fecha: "20 Jun", sede: "Akron, Guadalajara" },
      { local: "🇹🇳 Túnez", visita: "🇳🇱 Países Bajos", fecha: "25 Jun", sede: "Arrowhead, Kansas City" },
      { local: "🇯🇵 Japón", visita: "🇸🇪 Suecia", fecha: "25 Jun", sede: "AT&T, Dallas" },
    ]
  },
  {
    nombre: "G", partidos: [
      { local: "🇧🇪 Bélgica", visita: "🇪🇬 Egipto", fecha: "15 Jun", sede: "Lumen Field, Seattle" },
      { local: "🇮🇷 Irán", visita: "🇳🇿 Nueva Zelanda", fecha: "15 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇧🇪 Bélgica", visita: "🇮🇷 Irán", fecha: "21 Jun", sede: "SoFi, Los Ángeles" },
      { local: "🇳🇿 Nueva Zelanda", visita: "🇪🇬 Egipto", fecha: "21 Jun", sede: "BC Place, Vancouver" },
      { local: "🇳🇿 Nueva Zelanda", visita: "🇧🇪 Bélgica", fecha: "26 Jun", sede: "BC Place, Vancouver" },
      { local: "🇪🇬 Egipto", visita: "🇮🇷 Irán", fecha: "26 Jun", sede: "Lumen Field, Seattle" },
    ]
  },
  {
    nombre: "H", partidos: [
      { local: "🇪🇸 España", visita: "🇨🇻 Cabo Verde", fecha: "15 Jun", sede: "Mercedes-Benz, Atlanta" },
      { local: "🇸🇦 Arabia Saudita", visita: "🇺🇾 Uruguay", fecha: "15 Jun", sede: "Hard Rock, Miami" },
      { local: "🇪🇸 España", visita: "🇸🇦 Arabia Saudita", fecha: "21 Jun", sede: "Mercedes-Benz, Atlanta" },
      { local: "🇺🇾 Uruguay", visita: "🇨🇻 Cabo Verde", fecha: "21 Jun", sede: "Hard Rock, Miami" },
      { local: "🇺🇾 Uruguay", visita: "🇪🇸 España", fecha: "26 Jun", sede: "Akron, Guadalajara" },
      { local: "🇨🇻 Cabo Verde", visita: "🇸🇦 Arabia Saudita", fecha: "26 Jun", sede: "NRG, Houston" },
    ]
  },
  {
    nombre: "I", partidos: [
      { local: "🇫🇷 Francia", visita: "🇸🇳 Senegal", fecha: "16 Jun", sede: "MetLife, NY/NJ" },
      { local: "🇮🇶 Irak", visita: "🇳🇴 Noruega", fecha: "16 Jun", sede: "Gillette, Boston" },
      { local: "🇫🇷 Francia", visita: "🇮🇶 Irak", fecha: "22 Jun", sede: "Lincoln Financial, Filadelfia" },
      { local: "🇳🇴 Noruega", visita: "🇸🇳 Senegal", fecha: "22 Jun", sede: "BMO Field, Toronto" },
      { local: "🇳🇴 Noruega", visita: "🇫🇷 Francia", fecha: "26 Jun", sede: "Gillette, Boston" },
      { local: "🇸🇳 Senegal", visita: "🇮🇶 Irak", fecha: "26 Jun", sede: "BMO Field, Toronto" },
    ]
  },
  {
    nombre: "J", partidos: [
      { local: "🇦🇷 Argentina", visita: "🇩🇿 Argelia", fecha: "16 Jun", sede: "Arrowhead, Kansas City" },
      { local: "🇦🇹 Austria", visita: "🇯🇴 Jordania", fecha: "17 Jun", sede: "Levi's Stadium, SF" },
      { local: "🇦🇷 Argentina", visita: "🇦🇹 Austria", fecha: "22 Jun", sede: "AT&T, Dallas" },
      { local: "🇯🇴 Jordania", visita: "🇩🇿 Argelia", fecha: "22 Jun", sede: "Levi's Stadium, SF" },
      { local: "🇯🇴 Jordania", visita: "🇦🇷 Argentina", fecha: "27 Jun", sede: "AT&T, Dallas" },
      { local: "🇩🇿 Argelia", visita: "🇦🇹 Austria", fecha: "27 Jun", sede: "Arrowhead, Kansas City" },
    ]
  },
  {
    nombre: "K", partidos: [
      { local: "🇵🇹 Portugal", visita: "🇨🇩 RD Congo", fecha: "17 Jun", sede: "NRG, Houston" },
      { local: "🇺🇿 Uzbekistán", visita: "🇨🇴 Colombia", fecha: "17 Jun", sede: "Azteca, CDMX" },
      { local: "🇵🇹 Portugal", visita: "🇺🇿 Uzbekistán", fecha: "22 Jun", sede: "NRG, Houston" },
      { local: "🇨🇩 RD Congo", visita: "🇨🇴 Colombia", fecha: "22 Jun", sede: "BC Place, Vancouver" },
      { local: "🇨🇴 Colombia", visita: "🇵🇹 Portugal", fecha: "27 Jun", sede: "Lumen Field, Seattle" },
      { local: "🇨🇩 RD Congo", visita: "🇺🇿 Uzbekistán", fecha: "27 Jun", sede: "Mercedes-Benz, Atlanta" },
    ]
  },
  {
    nombre: "L", partidos: [
      { local: "🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", visita: "🇭🇷 Croacia", fecha: "17 Jun", sede: "AT&T, Dallas" },
      { local: "🇬🇭 Ghana", visita: "🇵🇦 Panamá", fecha: "17 Jun", sede: "BMO Field, Toronto" },
      { local: "🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", visita: "🇬🇭 Ghana", fecha: "23 Jun", sede: "Gillette, Boston" },
      { local: "🇵🇦 Panamá", visita: "🇭🇷 Croacia", fecha: "23 Jun", sede: "Gillette, Boston" },
      { local: "🇵🇦 Panamá", visita: "🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", fecha: "27 Jun", sede: "MetLife, NY/NJ" },
      { local: "🇭🇷 Croacia", visita: "🇬🇭 Ghana", fecha: "27 Jun", sede: "Lincoln Financial, Filadelfia" },
    ]
  },
];


const state = grupos.map(g => g.partidos.map(() => ({ res: null, gl: '', gv: '' })));

let totalPartidos = grupos.reduce((a, g) => a + g.partidos.length, 0);

async function cargarResultadosReales() {

  try {

    const response = await fetch('/obtener_resultados.php');

    resultadosReales = await response.json();

    renderGrupos();

  } catch (error) {

    console.error('Error cargando resultados:', error);

  }

}

function renderGrupos() {
  const cont = document.getElementById('grupos-container');
  cont.innerHTML = '';

  // Diccionario que vincula el nombre limpio del país con su código ISO correcto
  const mapaBanderas = {
    "méxico": "mx", "sudáfrica": "za", "corea del sur": "kr", "chequia": "cz",
    "canadá": "ca", "bosnia": "ba", "qatar": "qa", "suiza": "ch",
    "brasil": "br", "marruecos": "ma", "haití": "ht", "escocia": "gb-sct",
    "estados unidos": "us", "paraguay": "py", "australia": "au", "turquía": "tr",
    "alemania": "de", "curaçao": "cw", "costa de marfil": "ci", "ecuador": "ec",
    "países bajos": "nl", "japón": "jp", "suecia": "se", "túnez": "tn",
    "bélgica": "be", "egipto": "eg", "irán": "ir", "nueva zelanda": "nz",
    "españa": "es", "cabo verde": "cv", "arabia saudita": "sa", "uruguay": "uy",
    "francia": "fr", "senegal": "sn", "irak": "iq", "noruega": "no",
    "argentina": "ar", "argelia": "dz", "austria": "at", "jordania": "jo",
    "portugal": "pt", "rd congo": "cd", "uzbekistán": "uz", "colombia": "co",
    "inglaterra": "gb-eng", "croacia": "hr", "ghana": "gh", "panamá": "pa"
  };

  grupos.forEach((grupo, gi) => {
    const card = document.createElement('div');
    card.className = 'grupo-card';
    card.innerHTML = `
      <div class="grupo-header">
        <span class="grupo-nombre">Grupo ${grupo.nombre}</span>
        <span class="grupo-count">${grupo.partidos.length} partidos</span>
      </div>
      ${grupo.partidos.map((p, pi) => {
      const s = state[gi][pi];

      const key = grupo.nombre + "_" + pi;
      const resultadoReal = resultadosReales[key];

      // EXPLICACIÓN: Esta expresión regular elimina CUALQUIER emoji o bandera rota de Windows,
      // dejando únicamente las letras del nombre listas para buscar en el diccionario.
      const nombreLocalLimpio = p.local.replace(/([\uE000-\uF8FF]|\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDE4F]|\uD83D[\uDE80-\uDEFF]|\uD83E[\uDD00-\uDDFF]|\uD83C[\uDDE6-\uDDFF])/g, '').replace(/[^\w\sáéíóúñÁÉÍÓÚÑ]/g, '').trim().toLowerCase();
      const nombreVisitaLimpio = p.visita.replace(/([\uE000-\uF8FF]|\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDE4F]|\uD83D[\uDE80-\uDEFF]|\uD83E[\uDD00-\uDDFF]|\uD83C[\uDDE6-\uDDFF])/g, '').replace(/[^\w\sáéíóúñÁÉÍÓÚÑ]/g, '').trim().toLowerCase();

      const codeLocal = mapaBanderas[nombreLocalLimpio] || 'un';
      const codeVisita = mapaBanderas[nombreVisitaLimpio] || 'un';

      return `
        <div class="partido-item">
          <div class="partido-meta">
            <span class="partido-fecha">${p.fecha}</span>
            <span class="partido-sede">${p.sede}</span>
          </div>
          <div class="partido-equipos">
            
            <div class="equipo-local">
              <span class="equipo-nombre">${nombreLocalLimpio.charAt(0).toUpperCase() + nombreLocalLimpio.slice(1)}</span>
              <img class="bandera" src="https://flagcdn.com/w40/${codeLocal}.png" alt="" />
            </div>
 
        <div class="score-block">
 
        ${resultadoReal ? `
 
          <div style="
            background:#e8f5e9;
            border:1px solid #4caf50;
            border-radius:8px;
            padding:10px;
            text-align:center;
            font-weight:bold;
            color:#2e7d32;
          ">
            FINAL<br>
            ${resultadoReal.goles_local} - ${resultadoReal.goles_visita}
          </div>
 
        ` : `
 
          <div class="score-inputs">
            <input class="score-input" type="number" min="0" max="20" step="1"
              placeholder="–" value="${s.gl}"
              oninput="setScore(${gi},${pi},'gl',this.value)" />
            <span class="score-sep">:</span>
            <input class="score-input" type="number" min="0" max="20" step="1"
              placeholder="–" value="${s.gv}"
              oninput="setScore(${gi},${pi},'gv',this.value)" />
          </div>
 
          <div class="res-btns">
            <button class="rbtn r1 ${s.res === '1' ? 'active' : ''}" onclick="setRes(${gi},${pi},'1')">1</button>
            <button class="rbtn rx ${s.res === 'x' ? 'active' : ''}" onclick="setRes(${gi},${pi},'x')">X</button>
            <button class="rbtn r2 ${s.res === '2' ? 'active' : ''}" onclick="setRes(${gi},${pi},'2')">2</button>
          </div>
 
        `}
 
        </div>
 
            <div class="equipo-visita">
              <img class="bandera" src="https://flagcdn.com/w40/${codeVisita}.png" alt="" />
              <span class="equipo-nombre">${nombreVisitaLimpio.charAt(0).toUpperCase() + nombreVisitaLimpio.slice(1)}</span>
            </div>
 
          </div>
        </div>`;
    }).join('')}
    `;
    cont.appendChild(card);
  });
}


function setRes(gi, pi, val) {

  const s = state[gi][pi];

  s.res = s.res === val ? null : val;

  if (s.res === '1' && s.gl === '' && s.gv === '') { s.gl = '1'; s.gv = '0'; }

  if (s.res === 'x' && s.gl === '' && s.gv === '') { s.gl = '0'; s.gv = '0'; }

  if (s.res === '2' && s.gl === '' && s.gv === '') { s.gl = '0'; s.gv = '1'; }

  renderGrupos();

  updateStats();

}



function setScore(gi, pi, field, val) {

  const s = state[gi][pi];

  s[field] = val === '' ? '' : String(Math.max(0, Math.min(20, parseInt(val) || 0)));

  const gl = parseInt(s.gl);

  const gv = parseInt(s.gv);

  if (!isNaN(gl) && !isNaN(gv)) {

    s.res = gl > gv ? '1' : gl < gv ? '2' : 'x';

  } else {

    s.res = null;

  }

  updateStats();

}



function updateStats() {

  let total = 0, c1 = 0, cx = 0, c2 = 0;

  state.forEach(g => g.forEach(s => {

    if (s.res) { total++; if (s.res === '1') c1++; if (s.res === 'x') cx++; if (s.res === '2') c2++; }

  }));

  document.getElementById('cnt-total').textContent = total;

  document.getElementById('cnt-1').textContent = c1;

  document.getElementById('cnt-x').textContent = cx;

  document.getElementById('cnt-2').textContent = c2;

  const pct = Math.round((total / totalPartidos) * 100);

  document.getElementById('progreso-fill').style.width = pct + '%';

  document.getElementById('progreso-txt').textContent = `${total} / ${totalPartidos} partidos`;

}



function showTab(id) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

  document.getElementById('tab-' + id).classList.add('active');
  event.target.classList.add('active');

  if (id === 'mi-quiniela') renderTabla();

  // ── Cargar/refrescar eliminatorias al entrar al nuevo tab unificado ──
  if (id === 'eliminatorias') cargarEliminatorias();
}



function renderTabla() {

  const tbody = document.getElementById('tabla-body');

  const filas = [];

  grupos.forEach((grupo, gi) => {

    grupo.partidos.forEach((p, pi) => {

      const s = state[gi][pi];

      if (s.res) {

        const resultado = s.gl !== '' && s.gv !== '' ? `${s.gl} - ${s.gv}` : '—';

        const badge = s.res === '1' ? `<span class="badge-1">Local</span>` :

          s.res === 'x' ? `<span class="badge-x">Empate</span>` :

            `<span class="badge-2">Visitante</span>`;

        filas.push(`<tr>
 
          <td><strong>Grupo ${grupo.nombre}</strong></td>
 
          <td>${p.local.split(' ').slice(1).join(' ')} vs ${p.visita.split(' ').slice(1).join(' ')}</td>
 
          <td>${resultado}</td>
 
          <td>${badge}</td>
 
        </tr>`);

      }

    });

  });

  tbody.innerHTML = filas.length

    ? filas.join('')

    : '<tr><td colspan="4" style="text-align:center;color:var(--texto-sec);padding:30px">Aún no has hecho ningún pronóstico</td></tr>';

}



async function abrirModal() {
  const nombreInput = document.getElementById('nombre');
  const correoInput = document.getElementById('correo');
  const empresaInput = document.getElementById('empresa');
  const telefonoInput = document.getElementById('telefono');
  const aceptoInput = document.getElementById('acepto');

  const nombre = nombreInput.value.trim();
  const correo = correoInput.value.trim();
  const empresa = empresaInput.value.trim();
  const telefono = telefonoInput.value.trim();

  if (!nombre || !correo) {
    alert("Ingrese los campos obligatorios (Nombre y Correo)");
    return;
  }

  if (!aceptoInput.checked) {
    alert("Debe aceptar los términos");
    return;
  }

  const btn = document.querySelector('.btn-enviar');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    const response = await fetch('/guardar_quinielas.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        nombre,
        correo,
        empresa,
        telefono,
        quiniela: state
      })
    });

    const data = await response.json();

    if (!data.ok) {
      alert("Error al guardar en la base de datos.");
      btn.disabled = false;
      btn.textContent = 'Enviar Quiniela →';
      return;
    }

    // 1. ASIGNAR DATOS ANTES DE LIMPIAR EL FORMULARIO
    ultimoIdInsertado = data.id || 'N/A';
    datosParticipante = { nombre, correo, empresa, telefono };

    // Actualizar estadísticas del modal
    document.getElementById('m-1').textContent = document.getElementById('cnt-1').textContent;
    document.getElementById('m-x').textContent = document.getElementById('cnt-x').textContent;
    document.getElementById('m-2').textContent = document.getElementById('cnt-2').textContent;

    // Mostrar modal
    document.getElementById('modal').classList.add('open');

    // Limpiar formulario de forma segura
    nombreInput.value = '';
    correoInput.value = '';
    empresaInput.value = '';
    telefonoInput.value = '';
    aceptoInput.checked = false;

  } catch (error) {
    console.error(error);
    alert("Error de conexión al enviar el formulario.");
    btn.disabled = false;
    btn.textContent = 'Enviar Quiniela →';
  }
}

// ===============================
// LOGO IMEX PARA PDF
// ===============================
let logoIMEX = null;

function cargarLogoIMEX() {
  return new Promise((resolve) => {
    const img = new Image();

    img.crossOrigin = "Anonymous";

    img.onload = function () {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");

      canvas.width = img.width;
      canvas.height = img.height;

      ctx.drawImage(img, 0, 0);

      logoIMEX = canvas.toDataURL("image/png");
      resolve();
    };

    img.onerror = function () {
      console.error("No se pudo cargar el logo");
      resolve();
    };

    img.src = "https://isselmexico.com.mx/logos/Logotipo_IMEX_Lean_Project_2.png";
  });
}

document.addEventListener('DOMContentLoaded', async () => {

  await cargarLogoIMEX();
  cargarEliminatorias();
  cargarResultadosReales();
  updateStats();

  const btnPDF = document.getElementById('btn-descargar-pdf');

  if (btnPDF) {
    btnPDF.addEventListener('click', descargarPDF);
  }

});

function descargarPDF() {
  // Verificar que haya pronósticos
  let hayPronosticos = false;
  state.forEach(g => g.forEach(s => { if (s.res) hayPronosticos = true; }));
  if (!hayPronosticos) {
    alert("No hay pronósticos para descargar.");
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });

  const azul = [0, 60, 105];
  const amarillo = [249, 168, 37];
  const blanco = [255, 255, 255];
  const grisClaro = [244, 244, 244];
  const bordeGris = [224, 224, 224];
  const textoOscuro = [26, 26, 26];

  const margen = 15;
  const anchoPage = 210;
  const ancho = anchoPage - margen * 2;
  let y = 15;

  // ── ENCABEZADO CON LOGO IMEX ──
  doc.setFillColor(...azul);
  doc.rect(margen, y, ancho, 24, 'F');

  doc.setFillColor(255, 255, 255);
  doc.roundedRect(margen + 1, y + 1, 38, 22, 2, 2, 'F');

  if (logoIMEX) {
    doc.addImage(logoIMEX, 'PNG', margen + 3, y + 2, 34, 20);
  }

  doc.setTextColor(...blanco);
  doc.setFontSize(18);
  doc.setFont('helvetica', 'bold');
  doc.text('QUINIELA MUNDIAL 2026', 120, y + 9, { align: 'center' });

  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.text('IMEX LEAN PROJECT', 120, y + 16, { align: 'center' });

  y += 28;

  // Folio
  doc.setFillColor(...grisClaro);
  doc.rect(margen, y, ancho, 8, 'F');
  doc.setTextColor(100, 100, 100);
  doc.setFontSize(9);
  doc.text(`Folio de Registro: #${ultimoIdInsertado || 'N/A'}`, anchoPage / 2, y + 5.5, { align: 'center' });
  y += 12;

  // ── DATOS DEL PARTICIPANTE ──
  doc.setFillColor(...grisClaro);
  doc.rect(margen, y, ancho, 7, 'F');
  doc.setTextColor(...azul);
  doc.setFontSize(9);
  doc.setFont('helvetica', 'bold');
  doc.text('DATOS DEL PARTICIPANTE', margen + 3, y + 5);
  y += 10;

  const campos = [
    ['Nombre:', datosParticipante.nombre || '—'],
    ['Correo:', datosParticipante.correo || '—'],
    ['Empresa :', datosParticipante.empresa || '—'],
    ['Teléfono:', datosParticipante.telefono || '—'],
  ];
  doc.setFontSize(9);
  campos.forEach(([etiqueta, valor]) => {
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...textoOscuro);
    doc.text(etiqueta, margen + 2, y);
    doc.setFont('helvetica', 'normal');
    doc.text(valor, margen + 28, y);
    y += 6;
  });
  y += 4;

  // ── TABLA DE PRONÓSTICOS ──
  doc.setFillColor(...azul);
  doc.rect(margen, y, ancho, 7, 'F');
  doc.setTextColor(...blanco);
  doc.setFontSize(9);
  doc.setFont('helvetica', 'bold');
  doc.text('PRONOSTICOS REGISTRADOS', margen + 3, y + 5);
  y += 9;

  // Cabecera de tabla
  const cols = [25, 90, 25, 40]; // anchos de columna
  const cabeceras = ['Grupo', 'Partido', 'Resultado', 'Pronostico'];
  doc.setFillColor(220, 230, 240);
  doc.rect(margen, y, ancho, 7, 'F');
  doc.setTextColor(...azul);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'bold');
  let xCol = margen + 2;
  cabeceras.forEach((cab, i) => {
    doc.text(cab, xCol, y + 5);
    xCol += cols[i];
  });
  y += 8;

  // Filas
  let fila = 0;
  grupos.forEach((grupo, gi) => {
    grupo.partidos.forEach((p, pi) => {
      const s = state[gi][pi];
      if (!s.res) return;

      // Salto de página
      if (y > 270) {
        doc.addPage();
        y = 15;
      }

      const resultado = s.gl !== '' && s.gv !== '' ? `${s.gl} - ${s.gv}` : '—';
      const pronostico = s.res === '1' ? 'Local' : s.res === 'x' ? 'Empate' : 'Visitante';
      const local = p.local.split(' ').slice(1).join(' ');
      const visita = p.visita.split(' ').slice(1).join(' ');

      // Fondo alternado
      if (fila % 2 === 0) {
        doc.setFillColor(250, 250, 250);
        doc.rect(margen, y - 1, ancho, 7, 'F');
      }

      doc.setTextColor(...textoOscuro);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8);

      xCol = margen + 2;
      doc.text(`Grupo ${grupo.nombre}`, xCol, y + 4); xCol += cols[0];
      doc.text(`${local} vs ${visita}`, xCol, y + 4); xCol += cols[1];
      doc.text(resultado, xCol, y + 4); xCol += cols[2];

      // Badge de pronóstico con color
      const badgeColor = s.res === '1' ? [230, 247, 238] : s.res === 'x' ? [255, 253, 231] : [253, 234, 234];
      const badgeTextoColor = s.res === '1' ? [0, 100, 60] : s.res === 'x' ? [122, 92, 0] : [211, 47, 47];
      doc.setFillColor(...badgeColor);
      doc.roundedRect(xCol, y + 0.5, 28, 5.5, 1, 1, 'F');
      doc.setTextColor(...badgeTextoColor);
      doc.setFont('helvetica', 'bold');
      doc.text(pronostico, xCol + 14, y + 4.5, { align: 'center' });

      // Línea separadora
      doc.setDrawColor(...bordeGris);
      doc.line(margen, y + 6, margen + ancho, y + 6);

      y += 7;
      fila++;
    });
  });

  // ── PIE DE PÁGINA ──
  y += 6;
  doc.setDrawColor(...azul);
  doc.line(margen, y, margen + ancho, y);
  y += 5;
  doc.setTextColor(150, 150, 150);
  doc.setFontSize(7);
  doc.setFont('helvetica', 'normal');
  doc.text('IMEX Lean Project • Felipe Villanueva 232, Peralvillo • CDMX', anchoPage / 2, y, { align: 'center' });
  if (logoIMEX) {
    doc.addImage(logoIMEX, 'PNG', margen, y + 2, 18, 10);
  }

  doc.save(`Quiniela_2026_${(datosParticipante.nombre || 'Registro').replace(/\s+/g, '_')}.pdf`);
}

function cerrarModal() {
  document.getElementById('modal').classList.remove('open');
  const btn = document.querySelector('.btn-enviar');
  if (btn) {
    btn.disabled = false;
    btn.textContent = 'Enviar Quiniela →';
  }
}

// funcion para los datos de mas informacion terminos y coindiciones
function toggleTerms() {
  const box = document.getElementById('terms-content');
  box.classList.toggle('open');
}

async function consultarQuiniela() {

  const folio = document.getElementById('folioConsulta').value;

  if (!folio) {
    alert("Ingrese un folio");
    return;
  }

  try {

    const response = await fetch(`/obtener_quiniela.php?folio=${folio}`);
    const data = await response.json();

    if (!data.ok) {
      alert("No se encontró ese folio");
      return;
    }

    state.length = 0;
    data.quiniela.forEach(g => state.push(g));

    renderGrupos();
    renderTabla();
    showTab('mi-quiniela');

  } catch (error) {
    console.error(error);
    alert("Error al consultar");
  }

}

// ===========================================
// ELIMINATORIAS
// ===========================================

// Carga los partidos activos de eliminatoria (con su marcador real si el
// admin ya lo capturó en el panel) y arma el estado de pronóstico local.
async function cargarEliminatorias() {

  try {

    const response = await fetch("/obtener_partidos_eliminatorias.php");
    partidosEliminatoria = await response.json();

    // Solo reconstruye el estado de pronóstico si cambió el número de partidos
    // (evita perder lo que el usuario ya iba escribiendo si se vuelve a llamar).
    if (stateEliminatoria.length !== partidosEliminatoria.length) {

      stateEliminatoria = partidosEliminatoria.map(p => ({
        partido_id: parseInt(p.id),
        gl: "",
        gv: "",
        res: null
      }));

    }

    renderEliminatorias();

  } catch (error) {
    console.error("Error cargando eliminatorias", error);
  }

}

// Actualiza el pronóstico del usuario para un partido de eliminatoria.
function setScoreEliminatoria(i, field, val) {

  const s = stateEliminatoria[i];
  if (!s) return;

  s[field] = val === '' ? '' : String(Math.max(0, Math.min(20, parseInt(val) || 0)));

  const gl = parseInt(s.gl);
  const gv = parseInt(s.gv);

  s.res = (!isNaN(gl) && !isNaN(gv)) ? (gl > gv ? '1' : gl < gv ? '2' : 'x') : null;

}

function setResEliminatoria(i, val) {

  const s = stateEliminatoria[i];

  s.res = s.res === val ? null : val;

  if (s.res === "1") {

    s.gl = "1";
    s.gv = "0";

  }

  if (s.res === "x") {

    s.gl = "0";
    s.gv = "0";

  }

  if (s.res === "2") {

    s.gl = "0";
    s.gv = "1";

  }

  renderEliminatorias();

}

function nombreFase(fase) {

  const f = parseInt(fase);

  switch (f) {

    case 32:
      return "🏆 Dieciseisavos";

    case 16:
      return "🏆 Octavos";

    case 8:
      return "🏆 Cuartos";

    case 4:
      return "🏆 Semifinal";

    case 3:
      return "🥉 Tercer Lugar";

    case 2:
      return "🏆 Final";

    default:
      return "🏆 Eliminatorias";
  }
}
function obtenerBandera(nombre) {

  const mapa = {

    "Sudafricá": "za",
    "Sudáfrica": "za",
    "Canadá": "ca",
    "Brasil": "br",
    "Japón": "jp",
    "Alemania": "de",
    "Paraguay": "py",
    "Países Bajos": "nl",
    "Marruecos": "ma",
    "Costa de Marfil": "ci",
    "Noruega": "no",
    "México": "mx",
    "Corea del Sur": "kr",
    "Chequia": "cz",
    "Bosnia": "🇧🇦",
    "Qatar": "qa",
    "Suiza": "ch",
    "Haití": "ht",
    "Escocia": "gb-sct",
    "Estados Unidos": "us",
    "Australia": "au",
    "Turquía": "tr",
    "Curaçao": "cw",
    "Ecuador": "ec",
    "Túnez": "tn",
    "Suecia": "se",
    "Bélgica": "be",
    "Egipto": "eg",
    "Irán": "ir",
    "Nueva Zelanda": "nz",
    "España": "es",
    "Cabo Verde": "cv",
    "Arabia Saudita": "sa",
    "Uruguay": "uy",
    "Francia": "fr",
    "Senegal": "sn",
    "Irak": "iq",
    "Argentina": "ar",
    "Argelia": "dz",
    "Austria": "at",
    "Jordania": "jo",
    "Portugal": "pt",
    "RD Congo": "cd",
    "Uzbekistán": "uz",
    "Colombia": "co",
    "Inglaterra": "gb-eng",
    "Croacia": "hr",
    "Ghana": "gh",
    "Panamá": "pa"

  };

  const limpio = nombre.trim();

  return mapa[limpio]
    ? `https://flagcdn.com/w40/${mapa[limpio]}.png`
    : "https://flagcdn.com/w40/un.png";

}
// Pinta las tarjetas de eliminatoria agrupadas por fase. Si el partido ya
// tiene marcador real (capturado por el admin), se muestra el resultado
// FINAL en vez de los inputs de pronóstico.
// Pinta las tarjetas de eliminatoria agrupadas por fase.
function renderEliminatorias() {
  const cont = document.getElementById("eliminatorias-container");
  if (!cont) return;

  cont.innerHTML = "";

  if (!partidosEliminatoria || partidosEliminatoria.length === 0) {
    cont.innerHTML = `<p style="text-align:center;color:#999;padding:20px;">
      Aún no hay partidos de eliminatorias disponibles.
    </p>`;
    return;
  }

  // 1. Agrupar por fase
  const fases = {};
  partidosEliminatoria.forEach((p, i) => {
    const fase = p.fase || "Eliminatorias";
    if (!fases[fase]) fases[fase] = [];
    fases[fase].push({ ...p, index: i });
  });

  // 2. Contenedor bracket
  const bracket = document.createElement("div");
  bracket.className = "bracket-grid-form";

  // 3. Render por fase
  Object.keys(fases).forEach(fase => {

    // Título de la fase usando tu función nombreFase()
    const titulo = document.createElement("div");
    titulo.className = "bracket-seccion-titulo16";
    titulo.textContent = nombreFase(fase).toUpperCase();

    bracket.appendChild(titulo);

    fases[fase].forEach(p => {
      // Obtenemos el estado de pronóstico guardado para este índice
      const s = stateEliminatoria[p.index];

      const card = document.createElement("div");
      card.className = "partido16-card";

      card.innerHTML = `

<div class="partido16-num">
PARTIDO ${p.partido_idx}
</div>


<div class="partido16-teams">


<div class="partido16-team local">

  <img class="bandera" src="${obtenerBandera(p.local)}">

  <span class="partido16-team-name">
    ${p.local}
  </span>

</div>



<div class="partido16-score">

${p.goles_local !== null

          ?

          `
<div class="partido16-resultado-final">
${p.goles_local} - ${p.goles_visita}
</div>
`

          :

          `

<input 
class="partido16-gol-inp"
type="number"
min="0"
placeholder="–"
value="${s ? s.gl : ''}"
oninput="
setScoreEliminatoria(${p.index},'gl',this.value);
renderEliminatorias();
">

<span class="partido16-vs">
VS
</span>

<input 
class="partido16-gol-inp"
type="number"
min="0"
placeholder="–"
value="${s ? s.gv : ''}"
oninput="
setScoreEliminatoria(${p.index},'gv',this.value);
renderEliminatorias();
">

`

        }

</div>


<div class="partido16-team visita">

<img class="bandera" src="${obtenerBandera(p.visita)}">

<span class="partido16-team-name">
${p.visita}
</span>

</div>


</div>



<div class="partido16-resultado">


${p.goles_local !== null

          ?

          `
<div style="
background:#e8f5e9;
border:1px solid #4caf50;
border-radius:8px;
padding:10px;
color:#2e7d32;
font-weight:bold;
text-align:center;
width:100%;
">

FINAL<br>

${p.goles_local} - ${p.goles_visita}

</div>
`

          :

          `

<button class="r-btn sel-1 ${s && s.res === '1' ? 'active' : ''}"
onclick="setResEliminatoria(${p.index},'1')">
1
</button>


<button class="r-btn sel-x ${s && s.res === 'x' ? 'active' : ''}"
onclick="setResEliminatoria(${p.index},'x')">
X
</button>


<button class="r-btn sel-2 ${s && s.res === '2' ? 'active' : ''}"
onclick="setResEliminatoria(${p.index},'2')">
2
</button>

`

        }


</div>

`;

      bracket.appendChild(card);
    });
  });

  cont.appendChild(bracket);


  // Crear botones según las fases disponibles
  crearBotonesFases();

}


// ===========================================
// CREAR BOTONES DE GUARDADO POR FASE
// ===========================================

function crearBotonesFases() {

  const contenedor = document.getElementById(
    "botones-fases-eliminatoria"
  );


  if (!contenedor) return;


  contenedor.innerHTML = "";



  const fasesDisponibles = [...new Set(

    partidosEliminatoria.map(p => parseInt(p.fase))

  )];



  fasesDisponibles.forEach(fase => {



    // No mostramos 16avos ni 8vos
    // porque esos se manejan con resultados reales

    if (fase === 32 || fase === 16) {

      return;

    }



    let texto = "";



    switch (fase) {


      case 8:
        texto = "💾 Guardar Cuartos de Final";
        break;


      case 4:
        texto = "💾 Guardar Semifinal";
        break;


      case 3:
        texto = "💾 Guardar Tercer Lugar";
        break;


      case 2:
        texto = "💾 Guardar Final";
        break;


      default:
        texto = "💾 Guardar Eliminatoria";

    }



    const boton = document.createElement("button");


    boton.className = "btn-enviar";


    boton.textContent = texto;



    boton.onclick = function () {


      enviarEliminatoriaFase(fase);


    };



    contenedor.appendChild(boton);



  });



}

async function buscarFolioEliminatoria() {

  const folio = document.getElementById("folioEliminatoria").value;


  if (!folio) {

    alert("Ingrese su folio");
    return;

  }


  try {


    const response = await fetch(
      `/buscar_folio.php?folio=${folio}`
    );


    const data = await response.json();



    if (!data.ok) {

      alert(data.error);
      return;

    }



    folioEliminatoriaActual = data.participante.id;
    participanteEliminatoria = data.participante;



    document.getElementById(
      "datos-participante-eliminatoria"
    ).style.display = "block";



    document.getElementById(
      "dato-folio"
    ).textContent = data.participante.id;



    document.getElementById(
      "dato-nombre"
    ).textContent = data.participante.nombre;



    document.getElementById(
      "dato-empresa"
    ).textContent = data.participante.empresa;



    document.getElementById(
      "eliminatorias-container"
    ).style.display = "block";



    cargarEliminatorias();



  } catch (error) {

    console.error(error);

    alert("Error buscando folio");

  }

}

async function enviarEliminatoriaFase(fase) {


  if (!folioEliminatoriaActual) {

    alert("Primero ingrese su folio");
    return;

  }


  let pronosticos = [];


  partidosEliminatoria.forEach((p, i) => {


    if (parseInt(p.fase) === fase) {


      const s = stateEliminatoria[i];


      if (s && s.res !== null) {


        pronosticos.push({

          partido_id: p.id,
          gl: s.gl,
          gv: s.gv,
          res: s.res

        });


      }


    }


  });



  if (pronosticos.length === 0) {

    alert("No existen pronósticos para esta fase");
    return;

  }



  const datos = {


    folio: folioEliminatoriaActual,

    fase: fase,

    partidos: pronosticos


  };



  try {


    const response = await fetch(
      "/guardar_eliminatoria.php",
      {

        method: "POST",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify(datos)

      }
    );



    const data = await response.json();



    if (data.ok) {


      alert(
        "Pronósticos guardados correctamente"
      );


      generarPDFEliminatoria(fase);



    } else {


      alert(data.error);


    }



  } catch (error) {


    console.error(error);

    alert("Error guardando");

  }


}

function textoPDF(texto){

    if(!texto) return "";

    return texto
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g,"");

}
// ===========================================
// PDF ELIMINATORIAS POR FASE
// ===========================================

async function generarPDFEliminatoria(fase) {



  if (!folioEliminatoriaActual) {

    alert("No existe folio");
    return;

  }

  if (!logoIMEX) {
    await cargarLogoIMEX();
  }



  const partidosPDF = [];


  partidosEliminatoria.forEach((p, i) => {


    if (parseInt(p.fase) === fase) {


      const s = stateEliminatoria[i];


      if (s && s.res !== null) {


        partidosPDF.push({

          partido: p.partido_idx,

          local: p.local,

          visita: p.visita,

          fecha: p.fecha || "",

          sede: p.sede || "",

          gl: s.gl,

          gv: s.gv,

          res: s.res

        });

      }

    }

  });



  if (partidosPDF.length === 0) {

    alert("No hay partidos registrados para esta fase");

    return;

  }



  const { jsPDF } = window.jspdf;


  const doc = new jsPDF({

    orientation: "portrait",

    unit: "mm",

    format: "a4"

  });



  let y = 40;



  // ============================
  // ENCABEZADO
  // ============================


  doc.setFillColor(0, 60, 105);

  doc.rect(
    0,
    0,
    210,
    35,
    "F"
  );


  doc.setTextColor(
    255,
    255,
    255
  );
// ===============================
// ENCABEZADO CON LOGO
// ===============================

if(logoIMEX){

    // Fondo blanco detrás del logo
    doc.setFillColor(255,255,255);

    doc.rect(
        15,
        7,
        45,
        25,
        "F"
    );


    // Logo IMEX
    doc.addImage(
        logoIMEX,
        "PNG",
        20,
        10,
        35,
        18
    );

}


  doc.setFontSize(18);

  doc.text(
    "QUINIELA MUNDIAL 2026",
    105,
    20,
    {
      align: "center"
    }
  );


  doc.setFontSize(13);


  let tituloPDF = "";

  switch (parseInt(fase)) {

    case 32:
      tituloPDF = "DIECISEISAVOS DE FINAL";
      break;

    case 16:
      tituloPDF = "OCTAVOS DE FINAL";
      break;

    case 8:
      tituloPDF = "CUARTOS DE FINAL";
      break;

    case 4:
      tituloPDF = "SEMIFINALES";
      break;

    case 3:
      tituloPDF = "TERCER LUGAR";
      break;

    case 2:
      tituloPDF = "FINAL";
      break;

    default:
      tituloPDF = "ELIMINATORIAS";
  }



  doc.text(

    tituloPDF,

    105,

    25,

    { align: "center" }

  );



  y = 45;



  // ============================
  // DATOS PARTICIPANTE
  // ============================


  doc.setTextColor(0, 0, 0);


  doc.setFontSize(11);


  doc.text(

    "Folio: " + folioEliminatoriaActual,

    20,

    y

  );


  y += 7;


  doc.text(

    "Participante: " + participanteEliminatoria.nombre,

    20,

    y

  );


  y += 7;


  if (participanteEliminatoria.empresa) {

    doc.text(

      "Empresa: " + participanteEliminatoria.empresa,

      20,

      y

    );

    y += 7;

  }



  y += 8;



  // ============================
  // TABLA
  // ============================


  partidosPDF.forEach((p, index) => {


    if (y > 260) {

      doc.addPage();

      y = 20;

    }



    // tarjeta partido

    doc.setFillColor(
      245,
      245,
      245
    );


    doc.rect(
      15,
      y - 5,
      180,
      35
    );



    doc.setFontSize(10);



    doc.text(

      "PARTIDO " + p.partido,

      20,

      y + 7

    );



    doc.setFontSize(12);



    doc.text(

      textoPDF(p.local) + "  vs  " + textoPDF(p.visita),

      20,

      y + 15

    );



    let resultado = "";


    if (p.res === "1") {

      resultado = "Gana Local (1)";

    }
    else if (p.res === "2") {

      resultado = "Gana Visitante (2)";

    }
    else {

      resultado = "Empate";

    }


    doc.setFontSize(10);


    doc.text(

      "Pronóstico: " + p.gl + " - " + p.gv + "  " + resultado,

      20,

      y + 24

    );


    y += 38;



  });



  // ============================
  // PIE
  // ============================


  doc.setFontSize(9);


  doc.text(

    "Documento generado como comprobante de registro.",

    20,

    285

  );



  let nombre = "Eliminatoria";


  switch (fase) {


    case 32:

      nombre = "Dieciseisavos";

      break;


    case 16:

      nombre = "Octavos";

      break;


    case 8:

      nombre = "Cuartos";

      break;


    case 4:

      nombre = "Semifinal";

      break;


    case 3:

      nombre = "Tercer_Lugar";

      break;


    case 2:

      nombre = "Final";

      break;


  }



  doc.save(

    `Quiniela_${nombre}_Folio_${folioEliminatoriaActual}.pdf`

  );


}