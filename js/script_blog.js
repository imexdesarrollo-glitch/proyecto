


/* =========================
   VIDEOS - NO AUTOPLAY EN MOBILE
========================= */
document.addEventListener('DOMContentLoaded', () => {
  const isMobile = window.matchMedia("(max-width: 768px)").matches;

  document.querySelectorAll('.blog-videos video').forEach(video => {
    video.muted = true;

    if (!isMobile) {
      video.play().catch(() => {});
    } else {
      // En móvil solo preload metadata
      video.preload = "metadata";
    }
  });
});


/* =========================
   LAZY LOAD FUERTE
========================= */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("img").forEach(img => {
    if (!img.hasAttribute("loading")) {
      img.setAttribute("loading", "lazy");
    }
    img.setAttribute("decoding", "async");
  });
});

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

document.addEventListener("DOMContentLoaded", function () {

  const images = document.querySelectorAll(".gallery-img");
  const modalImage = document.getElementById("modalImage");
  const imageModal = new bootstrap.Modal(document.getElementById("imageModal"));

  images.forEach(img => {
    img.addEventListener("click", function () {
      modalImage.src = this.src;
      imageModal.show();
    });
  });

});

/* PARA MOSTRAR LAS IMG DE LA GALERIA EXTENSA */
document.addEventListener("DOMContentLoaded", function () {

  const button = document.getElementById("toggleGallery");
  const hiddenItems = document.querySelectorAll(".hidden-item");

  let expanded = false;

  button.addEventListener("click", function () {

    expanded = !expanded;

    hiddenItems.forEach(item => {
      if (expanded) {
        item.classList.add("show");
      } else {
        item.classList.remove("show");
      }
    });

    button.textContent = expanded ? "Ver menos" : "Ver más";

  });

});

/* SWIPER */
const swiper = new Swiper(".videoSwiper", {
  loop: false,
  spaceBetween: 30,
  grabCursor: true,

  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },

  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  breakpoints: {
    0: { slidesPerView: 1 },
    992: { slidesPerView: 1 }
  },

  // EVENTO 
  on: {
    slideChange: function () {
      const videos = document.querySelectorAll(".videoSwiper video");

      videos.forEach(video => {
        video.pause();
        video.currentTime = 0; // (reinicia el video)
      });
    }
  }

});

const videos = document.querySelectorAll("video");

const videoObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const video = entry.target;
      if (video.dataset.src) {
        video.src = video.dataset.src;
        video.load();
        video.removeAttribute("data-src");
      }
      videoObserver.unobserve(video);
    }
  });
});

/* lazyy load real  */

videos.forEach(video => {
  const source = video.querySelector("source");
  video.dataset.src = source.getAttribute("src");
  source.removeAttribute("src");
  videoObserver.observe(video);
});


document.addEventListener("DOMContentLoaded", function () {

  const videos = document.querySelectorAll("video");

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {

      if (entry.isIntersecting) {

        const video = entry.target;
        const source = video.querySelector("source");

        if (source.dataset.src) {
          source.src = source.dataset.src;
          video.load();
        }

        observer.unobserve(video);
      }

    });
  }, { threshold: 0.3 });

  videos.forEach(video => observer.observe(video));

});

