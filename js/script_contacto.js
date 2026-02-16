let map;
let geocoder;

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 19.4326, lng: -99.1332 },
    zoom: 12,
  });

  geocoder = new google.maps.Geocoder();
}

function buscarCodigoPostal() {
  const postalCode = document.getElementById("postalCode").value;

  if (!postalCode) {
    alert("Ingresa un código postal");
    return;
  }

  geocoder.geocode({ address: postalCode }, (results, status) => {
    if (status === "OK") {
      map.setCenter(results[0].geometry.location);

      new google.maps.Marker({
        map,
        position: results[0].geometry.location
      });
    } else {
      alert("No se encontró el código postal");
    }
  });
}

  function verificarEstado() {
    const hoy = new Date();
    const dia = hoy.getDay(); // 0=Domingo, 1=Lunes...
    const hora = hoy.getHours();

    let abierto = false;

    // Lunes a Viernes de 8 a 19 hrs
    if (dia >= 1 && dia <= 5 && hora >= 8 && hora < 19) {
      abierto = true;
    }

    const estado = document.getElementById("estado");
    estado.textContent = abierto ? "✅ Actualmente estamos ABIERTOS" : "❌ Actualmente estamos CERRADOS";
  }

  verificarEstado();


  function verificarEstado() {
    const hoy = new Date();
    const dia = hoy.getDay(); // 0=Domingo, 1=Lunes...
    const hora = hoy.getHours();
    let abierto = false;

    if (dia >= 1 && dia <= 5 && hora >= 8 && hora < 19) {
      abierto = true;
    }

    const estado = document.getElementById("estado");
    estado.textContent = abierto ? "✅ Actualmente estamos ABIERTOS" : "❌ Actualmente estamos CERRADOS";
    estado.style.color = abierto ? "green" : "red";
  }
  verificarEstado();

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