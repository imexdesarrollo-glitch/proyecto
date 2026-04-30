document.addEventListener('DOMContentLoaded', () => {
    // === NUEVO: LÓGICA DE WELCOME OVERLAY (FLYERS) ===
    const welcomeScreen = document.getElementById('welcome-screen');
    const closeWelcome = document.getElementById('close-welcome');

    // Bloqueamos el scroll si el flyer está presente
    if (welcomeScreen) {
        document.body.style.overflow = "hidden";
    }

    // Al cerrar el flyer, iniciamos la validación de cookies
    closeWelcome?.addEventListener('click', () => {
        welcomeScreen.style.opacity = '0';
        setTimeout(() => {
            welcomeScreen.style.display = 'none';
            // Una vez cerrado el flyer, verificamos si hay que mostrar cookies
            initCookieLogic();
        }, 500);
    });

    // 1. LOGO A INICIO
    const imexLogo = document.getElementById('imexLogo') || document.querySelector('.navbar-brand img');
    if (imexLogo) {
        imexLogo.style.cursor = 'pointer';
        imexLogo.onclick = () => window.location.href = 'index.html';
    }

    // 2. BUSCADOR ODOO DINÁMICO
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.querySelector('.search-btn');
    const categoriesDropdown = document.getElementById('categoriesDropdown');
    const searchWrapper = document.querySelector('.search-wrapper');
    const categoriesList = document.querySelector('.categories-list'); 

    let debounceTimer;

    function doSearch() {
        const query = searchInput?.value.trim();
        if (!query) {
            window.location.href = "https://issel-mexico.odoo.com/shop";
            return;
        }
        window.location.href = "https://issel-mexico.odoo.com/shop?search=" + encodeURIComponent(query);
    }

    function fetchOdooProducts(term) {
        if (term.length < 2) return;
        fetch(`https://issel-mexico.odoo.com/shop/products/autocomplete?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                if (data.results && data.results.length > 0) {
                    let html = '';
                    data.results.slice(0, 6).forEach(item => {
                        html += `
                            <li>
                                <a href="${item.website_url}" class="category-item">
                                    <img src="https://issel-mexico.odoo.com/web/image/product.template/${item.id}/image_128" 
                                        alt="${item.name}" class="category-image">
                                    <span>${item.name}</span>
                                </a>
                            </li>`;
                    });
                    categoriesList.innerHTML = html;
                    categoriesDropdown?.classList.add('active');
                }
            })
            .catch(err => console.error("Error buscando en Odoo:", err));
    }

    if (searchWrapper) {
        searchInput?.addEventListener('focus', () => categoriesDropdown?.classList.add('active'));
        searchInput?.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            clearTimeout(debounceTimer);
            if (term !== "") {
                debounceTimer = setTimeout(() => {
                    fetchOdooProducts(term);
                }, 300);
            }
        });

        document.addEventListener('click', (e) => {
            if (!searchWrapper.contains(e.target)) {
                categoriesDropdown?.classList.remove('active');
            }
        });

        searchBtn?.addEventListener('click', (e) => { e.preventDefault(); doSearch(); });
        searchInput?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });
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

    // --- Lógica de Cookies IMEX ---
    const cookieBanner = document.getElementById("cookie-banner");
    const cookieOverlay = document.getElementById("cookie-overlay");
    const acceptCookies = document.getElementById("acceptCookies");
    const rejectCookies = document.getElementById("rejectCookies");

    const closeCookies = () => {
        if (cookieBanner) cookieBanner.style.display = "none";
        if (cookieOverlay) cookieOverlay.style.display = "none";
        document.body.style.overflow = "auto"; 
    };

    // Función que inicializa cookies (llamada tras cerrar el flyer)
    function initCookieLogic() {
        if (cookieBanner && !sessionStorage.getItem("cookiesIMEX")) {
            cookieBanner.style.display = "block";
            if (cookieOverlay) cookieOverlay.style.display = "block";
            document.body.style.overflow = "hidden"; 
        } else {
            // Si ya se aceptaron las cookies, devolvemos el scroll al cerrar el flyer
            document.body.style.overflow = "auto";
        }
    }

    // Si NO hay flyer al cargar (por ejemplo en otras páginas), iniciamos cookies directo
    if (!welcomeScreen) {
        initCookieLogic();
    }

    acceptCookies?.addEventListener("click", () => {
        sessionStorage.setItem("cookiesIMEX", "accepted");
        closeCookies();
    });

    rejectCookies?.addEventListener("click", () => {
        sessionStorage.setItem("cookiesIMEX", "rejected");
        closeCookies();
    });
});

// AUTO-CERRAR MENÚ MÓVIL AL HACER CLIC FUERA
(function() {
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
})();

// 5. CARRUSEEL "SOBRE NOSOTROS"
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
      if (url.hostname && url.hostname !== currentHost) {
        link.target = "_blank";
        link.rel = "noopener noreferrer external";
      }
    } catch (e) {}
  });
});

// HIDE HEADER ON SCROLL
document.addEventListener("DOMContentLoaded", function () {
    const header = document.querySelector("header");
    let lastScroll = 0;
    const scrollThreshold = 80;
    window.addEventListener("scroll", function () {
        const currentScroll = window.pageYOffset;
        if (currentScroll <= 0) {
            header.classList.remove("hide-on-scroll");
            return;
        }
        if (currentScroll > lastScroll && currentScroll > scrollThreshold) {
            header.classList.add("hide-on-scroll");
        } else if (currentScroll < lastScroll) {
            header.classList.remove("hide-on-scroll");
        }
        lastScroll = currentScroll;
    });
});