<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mapa de estaciones</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    html,body { height: 100%; margin: 0; padding: 0; font-family: system-ui, -apple-system, Roboto, "Helvetica Neue", Arial; }
    #map { width: 100%; height: 100vh; }

    /* Controles dentro del mapa */
    .leaflet-bottom.leaflet-left .map-filters { margin: 0 0 14px 14px; }
    .leaflet-top.leaflet-right .map-counter { margin: 14px 14px 0 0; }

    .map-filters {
      background: rgba(255,255,255,0.92);
      padding: 8px 10px;
      border-radius: 10px;
      box-shadow: 0 3px 14px rgba(0,0,0,0.15);
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      align-items: center;
    }

    .map-counter {
      background: rgba(255,255,255,0.92);
      padding: 8px 12px;
      border-radius: 10px;
      box-shadow: 0 3px 14px rgba(0,0,0,0.15);
      font-weight: 600;
      font-size: 0.95rem;
    }

    .btn {
      background:#fff;
      border:1px solid #cbd5e1;
      padding:0.35rem 0.55rem;
      border-radius:6px;
      font-size:0.85rem;
      cursor:pointer;
      transition: all 0.15s ease-in-out;
    }
    .btn:hover { background:#f1f5f9; }
    .btn.active {
      background:#2563eb;
      border-color:#1e3a8a;
      color:#fff;
      font-weight:600;
      box-shadow: 0 2px 8px rgba(37,99,235,0.25);
    }

    /* Marcadores personalizados */
    .custom-marker {
      text-align: center;
      transform: translate(-50%, -50%);
    }

    .marker-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 0.9rem;
      border: 2px solid rgba(255,255,255,0.9);
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }

    .popup-title { font-size:1.02rem; margin-bottom:4px; display:block; font-weight:700; }
    .muted { color:#6b7280; font-size:0.9rem; }

    /* Colores de los datos */
    .temp-actual { color: #16a34a; font-weight: 600; }   /* verde */
    .temp-min { color: #2563eb; font-weight: 600; }      /* azul */
    .temp-max { color: #dc2626; font-weight: 600; }      /* rojo */
    .humidity { color: #0ea5e9; }                        /* azul claro */
    .wind { color: #6b7280; }                            /* gris */
    .rain { color: #7c3aed; }                            /* morado */
  </style>
</head>
<body>

  <div id="map"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    const stations = {!! json_encode($stations) !!} || [];

    const map = L.map('map').setView([38.085, -0.945], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);
    let displayMetric = 'temp';

    // Configuración de métricas y colores
    const metricConfig = {
      temp: { min: -10, max: 40, lowColor: [25,120,200], highColor: [217,83,79], unit: '°C' },
      temp_max: { min: -5, max: 45, lowColor: [25,120,200], highColor: [217,83,79], unit: '°C' },
      temp_min: { min: -20, max: 30, lowColor: [25,120,200], highColor: [217,83,79], unit: '°C' },
      temp_amplitude: { min: 0, max: 25, lowColor: [200,200,200], highColor: [217,83,79], unit: '°C' },
      humidity: { min: 0, max: 100, lowColor: [230,245,255], highColor: [10,80,20], unit: '%' },
      wind_gust: { min: 0, max: 120, lowColor: [240,240,240], highColor: [120,20,10], unit: 'km/h' },
      precip_total: { min: 0, max: 50, lowColor: [255,255,255], highColor: [0,80,200], unit: 'mm' },
    };

    function metricValue(s, metric) {
      switch (metric) {
        case 'temp': return s.temp;
        case 'temp_max': return s.temp_max;
        case 'temp_min': return s.temp_min;
        case 'temp_amplitude': return s.temp_max - s.temp_min;
        case 'humidity': return s.humidity;
        case 'wind_gust': return s.wind_gust;
        case 'precip_total': return s.precip_total;
        default: return null;
      }
    }

    function interpolateColor(rgb1, rgb2, t) {
      const r = Math.round(rgb1[0] + (rgb2[0] - rgb1[0]) * t);
      const g = Math.round(rgb1[1] + (rgb2[1] - rgb1[1]) * t);
      const b = Math.round(rgb1[2] + (rgb2[2] - rgb1[2]) * t);
      return `rgb(${r},${g},${b})`;
    }

    function computeDynamicRange(metric) {
      const vals = stations.map(s => metricValue(s, metric)).filter(v => v != null);
      if (!vals.length) return null;
      const min = Math.min(...vals);
      const max = Math.max(...vals);
      const pad = (max - min) * 0.08 || 1;
      return { min: min - pad, max: max + pad };
    }

    function colorFor(s, metric) {
      const cfg = metricConfig[metric] || metricConfig.temp;
      const dyn = computeDynamicRange(metric);
      const min = dyn ? dyn.min : cfg.min;
      const max = dyn ? dyn.max : cfg.max;
      const val = metricValue(s, metric);
      if (val == null) return '#888';
      const t = Math.max(0, Math.min(1, (val - min) / (max - min || 1)));
      return interpolateColor(cfg.lowColor, cfg.highColor, t);
    }

    function getTextColor(bgColor) {
      const rgb = bgColor.match(/\d+/g).map(Number);
      const brightness = (rgb[0]*299 + rgb[1]*587 + rgb[2]*114) / 1000;
      return brightness > 150 ? '#111' : '#fff';
    }

    function renderMarkers() {
      markersLayer.clearLayers();

      stations.forEach(s => {
        if (!s.lat || !s.lon) return;

        const val = metricValue(s, displayMetric);
        const txt = (val == null || isNaN(val)) ? '—' : Math.round(val);
        const color = colorFor(s, displayMetric);
        const textColor = getTextColor(color);

        const icon = L.divIcon({
          className: 'custom-marker',
          html: `<div class="marker-circle" style="background:${color};color:${textColor};">${txt}</div>`,
          iconSize: [36,36],
          iconAnchor: [18,18],
        });

        const popup = `
          <span class="popup-title">${s.name}</span>
          <div>
            <span class="temp-actual">🌡️ Actual: ${s.temp ?? 'N/D'} °C</span><br>
            <span class="temp-max">🔺 Máx: ${s.temp_max ?? 'N/D'} °C</span><br>
            <span class="temp-min">🔻 Mín: ${s.temp_min ?? 'N/D'} °C</span><br>
            <span class="humidity">💧 Humedad: ${s.humidity ?? 'N/D'} %</span><br>
            <span class="wind">🌬️ Viento: ${s.wind_speed ?? 'N/D'} km/h</span><br>
            <span class="rain">🌧️ Lluvia: ${s.precip_total ?? 'N/D'} mm</span>
          </div>
        `;

        L.marker([s.lat, s.lon], { icon }).bindPopup(popup).addTo(markersLayer);
      });
    }

    renderMarkers();

    // Control: contador arriba derecha
    const counter = L.control({ position: 'topright' });
    counter.onAdd = function() {
      const div = L.DomUtil.create('div', 'map-counter');
      div.innerHTML = `Estaciones: <strong>${stations.length}</strong>`;
      return div;
    };
    counter.addTo(map);

    // Control: filtros abajo izquierda
    const filters = L.control({ position: 'bottomleft' });
    filters.onAdd = function() {
      const div = L.DomUtil.create('div', 'map-filters');
      div.innerHTML = `
        <button class="btn active" data-metric="temp">Temp actual</button>
        <button class="btn" data-metric="temp_max">Temp máx</button>
        <button class="btn" data-metric="temp_min">Temp mín</button>
        <button class="btn" data-metric="temp_amplitude">Amplitud</button>
        <button class="btn" data-metric="humidity">Humedad</button>
        <button class="btn" data-metric="wind_gust">Racha</button>
        <button class="btn" data-metric="precip_total">Precip</button>
      `;
      return div;
    };
    filters.addTo(map);

    // Activar botones dentro del mapa
    const mapContainer = map.getContainer();
    mapContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('.map-filters .btn');
      if (!btn) return;
      mapContainer.querySelectorAll('.map-filters .btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      displayMetric = btn.getAttribute('data-metric');
      renderMarkers();
    });
  </script>
</body>
</html>
