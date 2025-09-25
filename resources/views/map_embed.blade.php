<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mapa de estaciones</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    html,body,#map { height: 100%; margin: 0; padding: 0; }
    /* Info box */
    .info-box { position: absolute; right: 10px; top: 10px; z-index: 1000; background: rgba(255,255,255,0.94); padding: 8px 12px; border-radius: 8px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); font-family: system-ui, -apple-system, Roboto, "Helvetica Neue", Arial; }
    .info-box b { display:block; font-size: 0.9rem; }

    /* Marker styles */
    .custom-marker { text-align: center; }
    .marker-label { display:block; font-weight:700; color:#111; background: rgba(255,255,255,0.95); padding: 3px 8px; border-radius: 999px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); font-size:0.85rem; }
    .marker-circle { width:22px; height:22px; border-radius:50%; margin:6px auto 0; box-shadow: 0 2px 6px rgba(0,0,0,0.18); border: 2px solid rgba(255,255,255,0.9); }

    /* Popup temperature highlights */
    .temp-max { color: #c9302c; font-weight:700; }
    .temp-min { color: #0b62d6; font-weight:700; }
    .popup-title { font-size:1.02rem; margin-bottom:4px; display:block; }
  </style>
</head>
<body>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
  const stations = {!! json_encode($stations) !!};
  console.log('Stations payload for map:', stations);

  // Mostrar recuadro con total de estaciones en la esquina superior derecha
  const infoBox = document.createElement('div');
  infoBox.className = 'info-box';
  infoBox.innerHTML = `<b>Estaciones totales</b><span>${stations.length}</span>`;
  document.body.appendChild(infoBox);

    // Comprobar que Leaflet se ha cargado
    if (typeof L === 'undefined') {
      document.getElementById('map').innerHTML = '<p style="padding:1rem;font-family:system-ui, -apple-system, Roboto, "Helvetica Neue", Arial;">Error cargando la librería de mapas (Leaflet). Comprueba la conexión a internet o prueba a remover atributos SRI si los hay.</p>';
      console.error('Leaflet no se ha cargado: L is undefined');
    } else {

    // Centrar por defecto en Orihuela (Alicante) con zoom para ver la ciudad/entorno
    const orihuelaCenter = [38.085, -0.945];
    let center = orihuelaCenter;
    const defaultZoom = 12; // nivel de zoom centrado en las latitudes de Orihuela

    const map = L.map('map').setView(center, defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Preparar escala de color entre azul (frío) y rojo (calor)
    const temps = stations.map(st => st.temp).filter(t => t !== null && !isNaN(t));
    const scaleMin = temps.length ? Math.min(...temps) : 0;
    const scaleMax = temps.length ? Math.max(...temps) : 30;

    function tempToColor(temp) {
      if (temp === null || isNaN(temp)) return '#888';
      // Normalizar según scaleMin/scaleMax
      const t = Math.max(Math.min((temp - scaleMin) / (scaleMax - scaleMin || 1), 1), 0);
      // Interpolar RGB entre azul (#1978c8) y rojo (#d9534f)
      const r1 = 25, g1 = 120, b1 = 200; // azul
      const r2 = 217, g2 = 83, b2 = 79;  // rojo
      const r = Math.round(r1 + (r2 - r1) * t);
      const g = Math.round(g1 + (g2 - g1) * t);
      const b = Math.round(b1 + (b2 - b1) * t);
      return `rgb(${r},${g},${b})`;
    }

  stations.forEach(s => {
      if (s.lat == null || s.lon == null) return;

      // Crear un icono simple con la temperatura
      const tempLabel = s.temp !== null ? `${s.temp}°C` : 'N/A';
      const circleColor = tempToColor(s.temp);
      const divIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="text-align:center"><div class='marker-label'>${tempLabel}</div><div class='marker-circle' style='background:${circleColor}'></div></div>`,
        iconSize: [48, 48],
        iconAnchor: [24, 24]
      });

      const marker = L.marker([s.lat, s.lon], { icon: divIcon }).addTo(map);

      const popupHtml = `<span class='popup-title'>${s.name}</span>
        Temperatura actual: ${s.temp !== null ? s.temp + ' °C' : 'N/D'}<br/>
        Mínima diaria: <span class='temp-min'>${s.temp_min !== null ? s.temp_min + ' °C' : 'N/D'}</span><br/>
        Máxima diaria: <span class='temp-max'>${s.temp_max !== null ? s.temp_max + ' °C' : 'N/D'}</span>`;

      marker.bindPopup(popupHtml);
    });
    }
  </script>
</body>
</html>