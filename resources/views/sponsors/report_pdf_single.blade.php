<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Patrocinador – {{ $sponsor['name'] }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        .header {
            background: #1e3a5f; color: #fff; padding: 18px 24px;
            border-radius: 6px; margin-bottom: 20px;
        }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header .sub { font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .header .sponsor-title { font-size: 13px; color: #93c5fd; margin-top: 6px; font-weight: 600; }

        .badge {
            display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 9px; font-weight: 600;
        }
        .badge-yes { background: #dcfce7; color: #16a34a; }
        .badge-no  { background: #fee2e2; color: #dc2626; }

        .meta { margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; background: #f8fafc; }
        .meta .row { display: flex; gap: 8px; align-items: center; margin-bottom: 4px; }
        .meta .mlabel { font-size: 9px; color: #64748b; text-transform: uppercase; width: 80px; flex-shrink: 0; }
        .meta .mvalue { font-size: 11px; color: #0f172a; word-break: break-all; }

        .stats { display: flex; gap: 10px; margin-bottom: 20px; }
        .stat-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; background: #f8fafc; }
        .stat-box .slabel { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-box .svalue { font-size: 20px; font-weight: 700; margin-top: 2px; }
        .stat-box.views .svalue   { color: #7c3aed; }
        .stat-box.clicks .svalue  { color: #059669; }
        .stat-box.devices .svalue { color: #ea580c; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #334155; text-transform: uppercase;
            letter-spacing: 0.06em; margin-bottom: 8px; padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table th {
            text-align: left; padding: 5px 10px;
            background: #f8fafc; color: #64748b;
            font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
        }
        table td { padding: 5px 10px; border-bottom: 1px solid #f1f5f9; color: #374151; }

        .no-stories { padding: 8px 0; font-size: 10px; color: #94a3b8; }
        footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Informe de Patrocinador</h1>
        <div class="sub">Estaciones Meteorihuela &bull; Generado el {{ now()->format('d/m/Y H:i') }}</div>
        <div class="sponsor-title">{{ $sponsor['name'] }}</div>
    </div>

    {{-- Meta --}}
    <div class="meta">
        <div class="row">
            <span class="mlabel">Estado</span>
            <span class="mvalue">
                <span class="badge {{ $sponsor['active'] ? 'badge-yes' : 'badge-no' }}">
                    {{ $sponsor['active'] ? 'Activo' : 'Inactivo' }}
                </span>
            </span>
        </div>
        @if ($sponsor['link_url'])
        <div class="row">
            <span class="mlabel">Enlace</span>
            <span class="mvalue">{{ $sponsor['link_url'] }}</span>
        </div>
        @endif
    </div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat-box views">
            <div class="slabel">Vistas stories</div>
            <div class="svalue">{{ number_format($sponsor['story_views']) }}</div>
        </div>
        <div class="stat-box clicks">
            <div class="slabel">Clicks enlace</div>
            <div class="svalue">{{ number_format($sponsor['link_clicks']) }}</div>
        </div>
        <div class="stat-box devices">
            <div class="slabel">Dispositivos unicos</div>
            <div class="svalue">{{ number_format($sponsor['unique_devices']) }}</div>
        </div>
    </div>

    {{-- Stories table --}}
    <div class="section-title">Stories</div>

    @if (count($sponsor['stories']) > 0)
        <table>
            <thead>
                <tr>
                    <th>ID Story</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Vistas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sponsor['stories'] as $story)
                <tr>
                    <td>#{{ $story['id'] }}</td>
                    <td>{{ ucfirst($story['type']) }}</td>
                    <td>{{ $story['active'] ? 'Activa' : 'Inactiva' }}</td>
                    <td>{{ number_format($story['views']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-stories">Sin stories registradas.</div>
    @endif

    <footer>Estaciones Meteorihuela &copy; {{ date('Y') }} &bull; Sistema de patrocinadores</footer>
</body>
</html>
