document.addEventListener('DOMContentLoaded', () => {
    // 1. LOGO A INICIO
    // Nota: Asegúrate que tu <img> del logo tenga el id="imexLogo" o cámbialo aquí
    const imexLogo = document.getElementById('imexLogo') || document.querySelector('.navbar-brand img');
    if (imexLogo) {
        imexLogo.style.cursor = 'pointer';
        imexLogo.onclick = () => window.location.href = 'index.html';
    }

    // 2. BUSCADOR ODOO
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.querySelector('.search-btn');
    const categoriesDropdown = document.getElementById('categoriesDropdown');
    const searchInDropdown = document.getElementById('searchInDropdown');
    const searchWrapper = document.querySelector('.search-wrapper');

    function doSearch() {
        const query = searchInput?.value.trim();
        if (query) window.location.href = "https://issel-mexico.odoo.com/shop?search=" + encodeURIComponent(query);
    }

    if (searchWrapper) {
        searchInput?.addEventListener('focus', () => categoriesDropdown?.classList.add('active'));
        document.addEventListener('click', (e) => {
            if (!searchWrapper.contains(e.target)) categoriesDropdown?.classList.remove('active');
        });
        searchBtn?.addEventListener('click', doSearch);
        searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') doSearch(); });
    }

    // 3. HERO HOVER EFECTOS
    const hero = document.querySelector('.hero');
    const productCards = document.querySelectorAll('.product-card');
    const heroLogo = document.getElementById('heroLogo');

    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const imageUrl = this.dataset.image;
            if (imageUrl) {
                hero?.style.setProperty('--hero-image', `url('${imageUrl}')`);
                hero?.classList.add('show-bg');
                heroLogo?.classList.add('hidden');
            }
        });
        card.addEventListener('mouseleave', () => {
            hero?.classList.remove('show-bg');
            heroLogo?.classList.remove('hidden');
        });
    });

    // 4. AUTO-CERRAR MENÚ MÓVIL AL HACER CLIC
    const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle), .dropdown-item');
    const menuColapsable = document.getElementById('imexNavbar');
    if (menuColapsable) {
        const bsCollapse = new bootstrap.Collapse(menuColapsable, { toggle: false });
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) bsCollapse.hide();
            });
        });
    }
});

// AUTO-CERRAR MENÚ MÓVIL AL HACER CLIC FUERA
const menuColapsable = document.getElementById('imexNavbar');
const navbarToggler = document.querySelector('.navbar-toggler');

if (menuColapsable && navbarToggler) {

    document.addEventListener('click', function (event) {

        const isMenuOpen = menuColapsable.classList.contains('show');

        const clickInsideMenu = menuColapsable.contains(event.target);
        const clickOnToggler = navbarToggler.contains(event.target);

        if (isMenuOpen && !clickInsideMenu && !clickOnToggler && window.innerWidth < 992) {

            const bsCollapse = bootstrap.Collapse.getInstance(menuColapsable)
                || new bootstrap.Collapse(menuColapsable, { toggle: false });

            bsCollapse.hide();
        }
    });
}

// 5. CARRUSEEL "SOBRE NOSOTROS" (Simplificado)
(function() {
    const carousel = document.getElementById('aboutCarousel');
    if (!carousel) return;
    const track = carousel.querySelector('.carousel-track');
    const items = carousel.querySelectorAll('.imex-carousel-item');
    let index = 0;
    
    const update = () => track.style.transform = `translateX(${-index * 100}%)`;
    const next = () => { index = (index + 1) % items.length; update(); };
    
    let timer = setInterval(next, 4000);
    carousel.addEventListener('mouseenter', () => clearInterval(timer));
    carousel.addEventListener('mouseleave', () => timer = setInterval(next, 4000));
    
    carousel.querySelector('.next')?.addEventListener('click', next);
    carousel.querySelector('.prev')?.addEventListener('click', () => {
        index = (index - 1 + items.length) % items.length;
        update();
    });
})();

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