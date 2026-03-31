document.addEventListener("DOMContentLoaded", () => {
  // === 1. LINKS EXTERNOS ===
  const currentHost = window.location.hostname;
  document.querySelectorAll('a[href]').forEach(link => {
    try {
      const url = new URL(link.href, window.location.origin);
      if (url.hostname && url.hostname !== currentHost) {
        link.target = "_blank";
        link.rel = "noopener noreferrer external";
      }
    } catch (e) {}
  });

  // === 2. MOTOR DE PARTÍCULAS ===
  const bgContainer = document.querySelector('.bg-shapes');
  if (!bgContainer) {
    console.error("No se encontró .bg-shapes");
    return;
  }

  const canvas = document.createElement('canvas');
  canvas.id = 'canvas-particles';
  bgContainer.appendChild(canvas);
  
  const ctx = canvas.getContext('2d');
  let particles = [];

  const initParticles = () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
      particles = [];

      // DETERMINAR DENSIDAD SEGÚN PANTALLA
      // En PC (más de 768px) usamos 60, en móvil solo 25 para no saturar
      const isMobile = window.innerWidth < 768;
      const particleCount = isMobile ? 35 : 60; 
      const connectionDist = isMobile ? 120 : 180; // Líneas más cortas en móvil

      for (let i = 0; i < particleCount; i++) {
        particles.push({
          x: Math.random() * canvas.width,
          y: Math.random() * canvas.height,
          vx: (Math.random() - 0.5) * (isMobile ? 0.3 : 0.6), // Más lento en móvil
          vy: (Math.random() - 0.5) * (isMobile ? 0.3 : 0.6),
          r: Math.random() * 1.5 + 0.5,
          distLimit: connectionDist // Guardamos el límite de conexión
        });
      }
    };

    const animate = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      // Color de partículas y líneas (sutil para no afectar lectura)
      ctx.fillStyle = "rgba(0, 60, 105, 0.8)"; 
      ctx.strokeStyle = "rgba(0, 60, 105, 0.08)"; 
      ctx.lineWidth = 0.5;

      particles.forEach((p, i) => {
        p.x += p.vx;
        p.y += p.vy;

        if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();

        for (let j = i + 1; j < particles.length; j++) {
          const p2 = particles[j];
          const dist = Math.hypot(p.x - p2.x, p.y - p2.y);
          // Usamos el límite dinámico
          if (dist < p.distLimit) {
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.stroke();
          }
        }
      });
      requestAnimationFrame(animate);
    };

  initParticles();
  animate();
  window.addEventListener('resize', initParticles);
});