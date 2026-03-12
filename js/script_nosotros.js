document.addEventListener("DOMContentLoaded", () => {

  const counters = document.querySelectorAll('.counter');
  const speed = 100;
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

// Opcion para poner el simbolo de más +
document.addEventListener("DOMContentLoaded", () => {

  const counters = document.querySelectorAll('.counter');
  let countersStarted = false;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {

      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }

      if (entry.isIntersecting && entry.target.classList.contains("imex-stats") && !countersStarted) {

        counters.forEach(counter => {

          const target = parseInt(counter.getAttribute('data-target'));
          const suffix = counter.getAttribute('data-suffix') || "";
          const speed = 200;
          let count = 0;

          const updateCount = () => {
            const increment = Math.ceil(target / speed);

            if (count < target) {
              count += increment;
              if (count > target) count = target;

              counter.innerText = count;
              setTimeout(updateCount, 10);
            } else {
              counter.innerText = target + suffix;
            }
          };

          updateCount();
        });

        countersStarted = true;
      }

    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.reveal').forEach(el => {
    observer.observe(el);
  });

  const statsSection = document.querySelector('.imex-stats');
  if (statsSection) {
    observer.observe(statsSection);
  }

});