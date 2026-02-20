document.addEventListener("DOMContentLoaded", () => {

  const counters = document.querySelectorAll('.counter');
  const speed = 200;
  let countersStarted = false;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {

      // Animación reveal normal
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }

      // Activar contadores solo una vez
      if (entry.isIntersecting && entry.target.classList.contains("imex-stats") && !countersStarted) {

        counters.forEach(counter => {
          const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
              counter.innerText = Math.ceil(count + increment);
              setTimeout(updateCount, 10);
            } else {
              counter.innerText = target;
            }
          };
          updateCount();
        });

        countersStarted = true;
      }

    });
  }, { threshold: 0.3 });

  // Reveal general
  document.querySelectorAll('.reveal').forEach(el => {
    observer.observe(el);
  });

  // Observar sección de estadísticas
  const statsSection = document.querySelector('.imex-stats');
  if (statsSection) {
    observer.observe(statsSection);
  }

});