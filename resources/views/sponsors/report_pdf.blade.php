<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Patrocinadores</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        .header {
            background: #1e3a5f; color: #fff; padding: 18px 24px;
            border-radius: 6px; margin-bottom: 20px;
        }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header p { font-size: 10px; color: #94a3b8; margin-top: 4px; }

        .totals { display: flex; gap: 10px; margin-bottom: 20px; }
        .total-box {
            flex: 1; border: 1px solid #e2e8f0; border-radius: 6px;
            padding: 10px 14px; background: #f8fafc;
        }
        .total-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .total-box .value { font-size: 20px; font-weight: 700; color: #1e293b; margin-top: 2px; }

        .sponsor-block { margin-bottom: 18px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; page-break-inside: avoid; }
        .sponsor-head {
            background: #f1f5f9; padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            display: block;
        }
        .sponsor-head-name { font-size: 13px; font-weight: 700; color: #0f172a; }
        .sponsor-head-link { font-size: 9px; color: #3b82f6; }
        .sponsor-head-badge {
            display: inline-block; margin-left: 8px;
            padding: 1px 8px; border-radius: 99px; font-size: 9px; font-weight: 600;
        }
        .badge-yes { background: #dcfce7; color: #16a34a; }
        .badge-no  { background: #fee2e2; color: #dc2626; }

        .sponsor-stats { display: flex; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; }
        .stat { flex: 1; }
        .stat .slabel { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .stat .svalue { font-size: 16px; font-weight: 700; margin-top: 2px; }
        .stat.views .svalue { color: #7c3aed; }
        .stat.clicks .svalue { color: #059669; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table th {
            text-align: left; padding: 5px 10px;
            background: #f8fafc; color: #64748b;
            font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
        }
        table td { padding: 5px 10px; border-bottom: 1px solid #f1f5f9; color: #374151; }

        .no-stories { padding: 8px 14px; font-size: 10px; color: #94a3b8; }
        footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informe de Patrocinadores</h1>
        <p>Estaciones Meteorihuela &bull; Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $totalViews  = $sponsors->sum('story_views');
        $totalClicks = $sponsors->sum('link_clicks');
        $totalSponsors = $sponsors->count();
    @endphp

    <div class="totals">
        <div class="total-box">
            <div class="label">Patrocinadores</div>
            <div class="value">{{ $totalSponsors }}</div>
        </div>
        <div class="total-box">
            <div class="label">Vistas de stories</div>
            <div class="value">{{ number_format($totalViews) }}</div>
        </div>
        <div class="total-box">
            <div class="label">Clicks en enlaces</div>
            <div class="value">{{ number_format($totalClicks) }}</div>
        </div>
    </div>

    @forelse ($sponsors as $sponsor)
        <div class="sponsor-block">
            <div class="sponsor-head">
                <span class="sponsor-head-name">{{ $sponsor['name'] }}</span>
                <span class="sponsor-head-badge {{ $sponsor['active'] ? 'badge-yes' : 'badge-no' }}">
                    {{ $sponsor['active'] ? 'Activo' : 'Inactivo' }}
                </span>
                @if($sponsor['link_url'])
                    <br><span class="sponsor-head-link">{{ $sponsor['link_url'] }}</span>
                @endif
            </div>
            <div class="sponsor-stats">
                <div class="stat views">
                    <div class="slabel">Vistas stories</div>
                    <div class="svalue">{{ number_format($sponsor['story_views']) }}</div>
                </div>
                <div class="stat clicks">
                    <div class="slabel">Clicks enlace</div>
                    <div class="svalue">{{ number_format($sponsor['link_clicks']) }}</div>
                </div>
            </div>
            @if ($sponsor['stories']->count())
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
        </div>
    @empty
        <p style="text-align:center;color:#94a3b8;padding:40px 0;">No hay patrocinadores registrados.</p>
    @endforelse

    <footer>Estaciones Meteorihuela &copy; {{ date('Y') }} &bull; Sistema de patrocinadores</footer>
</body>
</html>
