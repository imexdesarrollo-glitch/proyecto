// === ABRIR LINKS EXTERNOS EN NUEVA PESTAÑA ===
document.addEventListener("DOMContentLoaded", () => {
  const currentHost = window.location.hostname;

  document.querySelectorAll('a[href]').forEach(link => {
    try {
      const url = new URL(link.href, window.location.origin);

      // Si es link externo
      if (url.hostname && url.hostname !== currentHost) {
        link.target = "_blank";
        link.rel = "noopener noreferrer external";
      }
    } catch (e) {
      // Ignorar links mal formados (mailto, tel, #, javascript:)
    }
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const moreBrands = document.getElementById("moreBrands");
  const toggleBtn = document.querySelector('[data-bs-target="#moreBrands"]');

  if (moreBrands && toggleBtn) {
    moreBrands.addEventListener('shown.bs.collapse', () => {
      toggleBtn.textContent = "Ocultar marcas";
    });

    moreBrands.addEventListener('hidden.bs.collapse', () => {
      toggleBtn.textContent = "Conoce más marcas";
    });
  }
});
