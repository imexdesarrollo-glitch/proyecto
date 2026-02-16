//para volver al inicio presionando el logo
document.addEventListener('DOMContentLoaded', () => {
  const imexLogo = document.getElementById('imexLogo');

  if (imexLogo) {
    imexLogo.style.cursor = 'pointer';
    imexLogo.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = 'index.html';
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {

  // =========================
  // Logo → volver a inicio
  // =========================
  const imexLogo = document.getElementById('imexLogo');
  if (imexLogo) {
    imexLogo.style.cursor = 'pointer';
    imexLogo.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = 'index.html';
    });
  }



  // =========================
  // Buscador con dropdown custom
  // =========================
  const searchInput = document.getElementById('searchInput');
  const categoriesDropdown = document.getElementById('categoriesDropdown');
  const searchInDropdown = document.getElementById('searchInDropdown');
  const searchWrapper = document.querySelector('.search-wrapper');

  if (searchInput && categoriesDropdown && searchInDropdown && searchWrapper) {

    function openDropdown(e) {
      e.stopPropagation();
      categoriesDropdown.classList.add('active');
      setTimeout(() => searchInDropdown.focus(), 100);
    }

    function closeDropdown() {
      categoriesDropdown.classList.remove('active');
    }

    searchInput.addEventListener('click', openDropdown);
    searchInput.addEventListener('focus', openDropdown);

    searchInDropdown.addEventListener('input', () => {
      searchInput.value = searchInDropdown.value;
    });

    searchInput.addEventListener('input', () => {
      searchInDropdown.value = searchInput.value;
    });

    document.addEventListener('click', e => {
      if (!searchWrapper.contains(e.target)) {
        closeDropdown();
      }
    });
  }

  // =========================
  // Hero: cambio de fondo con productos
  // =========================
  const hero = document.querySelector('.hero');
  const productCards = document.querySelectorAll('.product-card');
  const preview = document.querySelector('.products-preview');
  const heroLogo = document.getElementById('heroLogo');

  productCards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      const imageUrl = this.dataset.image;
      hero?.style.setProperty('--hero-image', `url('${imageUrl}')`);
      hero?.classList.add('show-bg');
      heroLogo?.classList.add('hidden');
    });

    card.addEventListener('mouseleave', function () {
      hero?.classList.remove('show-bg');
      heroLogo?.classList.remove('hidden');
    });
  });

  preview?.addEventListener('mouseleave', () => {
    hero?.classList.remove('show-bg');
    heroLogo?.classList.remove('hidden');
  });

});


(function(){
  const carousel = document.getElementById('aboutCarousel');
  if (!carousel) return;

  const track = carousel.querySelector('.carousel-track');
  const items = carousel.querySelectorAll('.imex-carousel-item');
  const prev = carousel.querySelector('.prev');
  const next = carousel.querySelector('.next');

  if (!track || !items.length) return;

  let index = 0;
  let timer = null;
  const interval = 4000;

  function update(){
    track.style.transform = `translateX(${-index * 100}%)`;
  }

  function nextSlide(){
    index = (index + 1) % items.length;
    update();
  }

  function prevSlide(){
    index = (index - 1 + items.length) % items.length;
    update();
  }

  function startAuto(){
    stopAuto();
    timer = setInterval(nextSlide, interval);
  }

  function stopAuto(){
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  prev?.addEventListener('click', () => {
    prevSlide();
    startAuto();
  });

  next?.addEventListener('click', () => {
    nextSlide();
    startAuto();
  });

  carousel.addEventListener('mouseenter', stopAuto);
  carousel.addEventListener('mouseleave', startAuto);
  carousel.addEventListener('touchstart', stopAuto);
  carousel.addEventListener('touchend', startAuto);

  update();
  startAuto();
})();


// Mejora de accesibilidad: pausa/reanuda con teclado y controla velocidad
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('testimonialCards');
  if (!container) return;

  container.addEventListener('touchstart', () => {
    container.classList.add('paused');
  });

  container.addEventListener('touchend', () => {
    container.classList.remove('paused');
  });
});



// === BUSCADOR QUE REDIRIGE A ODOO ===
const searchInput = document.getElementById("searchInput");
const searchBtn = document.querySelector(".search-btn");

function redirectToOdooSearch() {
  const query = searchInput.value.trim();
  if (!query) return;

  const url = "https://issel-mexico.odoo.com/shop?search=" + encodeURIComponent(query);
  window.location.href = url;
}

// Click en botón Buscar
searchBtn.addEventListener("click", redirectToOdooSearch);

// Presionar Enter en el input principal
searchInput.addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    redirectToOdooSearch();
  }
});

// Busqueda en el dropdown
const searchInDropdown = document.getElementById("searchInDropdown");
if (searchInDropdown) {
  searchInDropdown.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      searchInput.value = searchInDropdown.value;
      redirectToOdooSearch();
    }
  });
}

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
