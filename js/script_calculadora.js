/* ================================================
   script_calculadora.js – IMEX Lean Project
   ================================================ */

const _eficiencia = {
    automotriz:    0.55,
    alimentos:     0.48,
    farmaceutico:  0.60,
    electronica:   0.52,
    plasticos:     0.45,
    metalmecanica: 0.50,
    otro:          0.40
};

const _sectorNombre = {
    automotriz:    'automotriz',
    alimentos:     'alimentos y bebidas',
    farmaceutico:  'farmacéutico',
    electronica:   'electrónica',
    plasticos:     'plásticos y hule',
    metalmecanica: 'metalmecánica',
    otro:          'manufactura general'
};

function calcROI() {
    const sector    = document.getElementById('calc-sector').value;
    const turnos    = parseInt(document.getElementById('calc-turnos').value);
    const lineas    = Math.max(1, parseInt(document.getElementById('calc-lineas').value) || 1);
    const paros     = parseInt(document.getElementById('calc-paros').value);
    const duracion  = parseInt(document.getElementById('calc-duracion').value);
    const costoHora = parseInt(document.getElementById('calc-costo').value);

    const reduccion          = _eficiencia[sector];
    const horasParo_sem      = (paros * duracion / 60) * lineas;
    const horasAhorradas_sem = horasParo_sem * reduccion;
    const horasAhorradas_anio = Math.round(horasAhorradas_sem * 52);
    const ahorro_sem         = horasAhorradas_sem * costoHora * turnos;
    const ahorro_mes         = ahorro_sem * 4.33;
    const ahorro_anio        = ahorro_sem * 52;

    const invEstimada   = lineas * 48000 + turnos * 14000;
    const paybackMeses  = ahorro_mes > 0 ? Math.round(invEstimada / ahorro_mes) : 0;
    const paybackText   = paybackMeses <= 1
        ? 'menos de 1 mes'
        : paybackMeses <= 12
            ? paybackMeses + ' meses'
            : (Math.round(paybackMeses / 1.2) / 10).toFixed(1) + ' años';

    const fmt = n => '$' + Math.round(n).toLocaleString('es-MX');

    document.getElementById('calc-r-mensual').textContent = fmt(ahorro_mes);
    document.getElementById('calc-r-anual').textContent   = fmt(ahorro_anio);
    document.getElementById('calc-r-payback').textContent = paybackText;
    document.getElementById('calc-r-horas').textContent   = horasAhorradas_anio.toLocaleString('es-MX') + ' hrs';

    const pct = Math.round(reduccion * 100);
    document.getElementById('calc-insight').innerHTML =
        'Plantas del sector <strong>' + _sectorNombre[sector] + '</strong> logran reducir paros hasta un ' +
        '<strong>' + pct + '%</strong> con sensores y monitoreo Banner Engineering. ' +
        'En tus <strong>' + lineas + ' línea' + (lineas > 1 ? 's' : '') + '</strong> eso equivale a recuperar ' +
        '<strong>' + horasAhorradas_anio.toLocaleString('es-MX') + ' horas productivas</strong> al año.';
}

// Reveal animation
document.addEventListener('DOMContentLoaded', function () {
    calcROI();

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) e.target.classList.add('show');
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.calc-reveal').forEach(function (el) {
        observer.observe(el);
    });

    initCircuits();
});

/* ================================================
   CIRCUITOS ANIMADOS – fondo de la calculadora
   ================================================ */
function initCircuits() {
    var canvas = document.getElementById('calc-circuits-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var W, H;

    function resize() {
        W = canvas.width  = canvas.offsetWidth;
        H = canvas.height = canvas.offsetHeight;
    }
    resize();
    window.addEventListener('resize', function () { resize(); buildNodes(); });

    /* --- Colores --- */
    var C_LINE   = 'rgba(252, 217, 0, 0.10)';   /* amarillo muy sutil */
    var C_PULSE  = 'rgba(252, 217, 0, 0.75)';   /* pulso brillante    */
    var C_NODE   = 'rgba(0, 180, 130, 0.30)';   /* nodo verde-teal    */
    var C_NODE_B = 'rgba(252, 217, 0, 0.55)';   /* nodo activo        */

    /* --- Nodos de circuito --- */
    var nodes = [];
    var COLS = 10, ROWS = 14;

    function buildNodes() {
        nodes = [];
        var gx = W / COLS, gy = H / ROWS;
        for (var r = 0; r <= ROWS; r++) {
            for (var c = 0; c <= COLS; c++) {
                /* pequeño jitter para que no parezca cuadrícula perfecta */
                var jx = (Math.random() - 0.5) * gx * 0.55;
                var jy = (Math.random() - 0.5) * gy * 0.55;
                nodes.push({
                    x:  c * gx + jx,
                    y:  r * gy + jy,
                    r:  Math.random() < 0.25 ? 3 : 1.5,
                    active: false
                });
            }
        }
        buildEdges();
    }

    /* --- Aristas (conexiones entre nodos cercanos) --- */
    var edges = [];
    var MAX_DIST = 180;

    function buildEdges() {
        edges = [];
        for (var i = 0; i < nodes.length; i++) {
            for (var j = i + 1; j < nodes.length; j++) {
                var dx = nodes[i].x - nodes[j].x;
                var dy = nodes[i].y - nodes[j].y;
                var d  = Math.sqrt(dx*dx + dy*dy);
                if (d < MAX_DIST) {
                    /* solo conexiones horizontales o verticales (look de PCB) */
                    if (Math.abs(dx) < 12 || Math.abs(dy) < 12) {
                        edges.push({ a: i, b: j, len: d });
                    }
                }
            }
        }
    }

    buildNodes();

    /* --- Pulsos viajando por las aristas --- */
    var pulses = [];

    function spawnPulse() {
        if (edges.length === 0) return;
        var e = edges[Math.floor(Math.random() * edges.length)];
        /* dirección aleatoria */
        var fwd = Math.random() < 0.5;
        pulses.push({
            edge: e,
            t: 0,                          /* 0 → 1 progreso */
            speed: 0.004 + Math.random() * 0.006,
            forward: fwd,
            alpha: 0
        });
    }

    /* Lanza un pulso cada ~700ms */
    setInterval(spawnPulse, 700);

    /* --- Loop de animación --- */
    function draw() {
        ctx.clearRect(0, 0, W, H);

        /* Dibujar aristas */
        ctx.strokeStyle = C_LINE;
        ctx.lineWidth   = 0.8;
        for (var i = 0; i < edges.length; i++) {
            var e  = edges[i];
            var na = nodes[e.a], nb = nodes[e.b];
            ctx.beginPath();
            ctx.moveTo(na.x, na.y);
            /* líneas en L estilo PCB */
            var mx = (Math.abs(na.x - nb.x) > Math.abs(na.y - nb.y)) ? nb.x : na.x;
            var my = (Math.abs(na.x - nb.x) > Math.abs(na.y - nb.y)) ? na.y : nb.y;
            ctx.lineTo(mx, my);
            ctx.lineTo(nb.x, nb.y);
            ctx.stroke();
        }

        /* Dibujar nodos */
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fillStyle = n.active ? C_NODE_B : C_NODE;
            ctx.fill();
        }

        /* Dibujar y avanzar pulsos */
        for (var i = pulses.length - 1; i >= 0; i--) {
            var p  = pulses[i];
            var e  = p.edge;
            var na = nodes[e.a], nb = nodes[e.b];

            p.t += p.speed;
            p.alpha = p.t < 0.1 ? p.t / 0.1
                    : p.t > 0.85 ? (1 - p.t) / 0.15
                    : 1;

            var ax = p.forward ? na.x : nb.x;
            var ay = p.forward ? na.y : nb.y;
            var bx = p.forward ? nb.x : na.x;
            var by = p.forward ? nb.y : na.y;

            /* posición actual en la ruta en L */
            var px, py;
            var mx = (Math.abs(ax - bx) > Math.abs(ay - by)) ? bx : ax;
            var my = (Math.abs(ax - bx) > Math.abs(ay - by)) ? ay : by;
            var seg1 = Math.sqrt((mx-ax)*(mx-ax)+(my-ay)*(my-ay));
            var seg2 = Math.sqrt((bx-mx)*(bx-mx)+(by-my)*(by-my));
            var total = seg1 + seg2 || 1;
            var dist  = p.t * total;

            if (dist <= seg1) {
                var r = seg1 > 0 ? dist / seg1 : 0;
                px = ax + (mx - ax) * r;
                py = ay + (my - ay) * r;
            } else {
                var r = seg2 > 0 ? (dist - seg1) / seg2 : 0;
                r = Math.min(r, 1);
                px = mx + (bx - mx) * r;
                py = my + (by - my) * r;
            }

            /* glow del pulso */
            var grd = ctx.createRadialGradient(px, py, 0, px, py, 8);
            grd.addColorStop(0, 'rgba(252,217,0,' + (p.alpha * 0.9) + ')');
            grd.addColorStop(1, 'rgba(252,217,0,0)');
            ctx.beginPath();
            ctx.arc(px, py, 8, 0, Math.PI * 2);
            ctx.fillStyle = grd;
            ctx.fill();

            /* punto central */
            ctx.beginPath();
            ctx.arc(px, py, 2, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + p.alpha + ')';
            ctx.fill();

            /* activar nodo al llegar al final */
            if (p.t >= 0.92) {
                var dest = p.forward ? e.b : e.a;
                nodes[dest].active = true;
                setTimeout(function(d){ nodes[d].active = false; }, 300, dest);
            }

            if (p.t >= 1) pulses.splice(i, 1);
        }

        requestAnimationFrame(draw);
    }

    draw();
}
