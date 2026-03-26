<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Patrocinadores – Estaciones Meteorihuela</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        header {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            padding: 2rem 2.5rem;
            border-bottom: 1px solid #334155;
            display: flex; align-items: center; justify-content: space-between;
        }
        header h1 { font-size: 1.6rem; font-weight: 700; color: #f8fafc; }
        header p { font-size: 0.85rem; color: #94a3b8; margin-top: 0.25rem; }
        .btn-pdf {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.4rem; border-radius: 8px;
            background: #3b82f6; color: #fff; text-decoration: none;
            font-size: 0.875rem; font-weight: 600;
            transition: background 0.2s;
        }
        .btn-pdf:hover { background: #2563eb; }

        .container { max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem; }

        /* Summary totals */
        .totals { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; margin-bottom: 2.5rem; }
        .total-card {
            background: #1e293b; border: 1px solid #334155; border-radius: 12px;
            padding: 1.4rem 1.6rem;
        }
        .total-card span { font-size: 0.78rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; }
        .total-card strong { display: block; font-size: 2.2rem; font-weight: 700; color: #f8fafc; margin-top: 0.3rem; }

        /* Sponsor cards */
        .sponsors { display: flex; flex-direction: column; gap: 1.5rem; }
        .card {
            background: #1e293b; border: 1px solid #334155; border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            display: flex; align-items: center; gap: 1rem;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #334155;
            background: #162032;
        }
        .card-header img {
            width: 48px; height: 48px; border-radius: 8px; object-fit: contain;
            background: #fff; padding: 4px;
        }
        .card-header .avatar {
            width: 48px; height: 48px; border-radius: 8px;
            background: #3b82f6; display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; font-weight: 700; color: #fff;
        }
        .sponsor-name { font-size: 1.1rem; font-weight: 700; color: #f8fafc; }
        .sponsor-link { font-size: 0.78rem; color: #60a5fa; text-decoration: none; margin-top: 2px; display: block; }
        .badge-active { margin-left: auto; padding: 0.2rem 0.8rem; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
        .badge-active.yes { background: #14532d; color: #4ade80; }
        .badge-active.no  { background: #450a0a; color: #f87171; }

        .card-body { padding: 1.2rem 1.5rem; }
        .stats-row { display: flex; gap: 1.5rem; margin-bottom: 1.2rem; }
        .stat-box {
            flex: 1; background: #0f172a; border: 1px solid #334155; border-radius: 10px;
            padding: 1rem 1.2rem;
        }
        .stat-box .label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
        .stat-box .value { font-size: 1.8rem; font-weight: 700; margin-top: 0.2rem; }
        .stat-box.views .value { color: #a78bfa; }
        .stat-box.clicks .value { color: #34d399; }

        .stories-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
        .stories-table th {
            text-align: left; padding: 0.5rem 0.75rem;
            background: #0f172a; color: #94a3b8;
            font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .stories-table td { padding: 0.55rem 0.75rem; border-top: 1px solid #334155; color: #cbd5e1; }
        .stories-table tr:hover td { background: #162032; }

        footer { text-align: center; padding: 2rem; color: #475569; font-size: 0.78rem; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>📊 Informe de Patrocinadores</h1>
            <p>Estaciones Meteorihuela · Generado el {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <a class="btn-pdf" href="{{ route('sponsors.pdf') }}">⬇ Descargar PDF</a>
    </header>

    <div class="container">
        @php
            $totalViews  = $sponsors->sum('story_views');
            $totalClicks = $sponsors->sum('link_clicks');
            $totalSponsors = $sponsors->count();
        @endphp

        <div class="totals">
            <div class="total-card">
                <span>Total patrocinadores</span>
                <strong>{{ $totalSponsors }}</strong>
            </div>
            <div class="total-card">
                <span>Vistas de stories</span>
                <strong>{{ number_format($totalViews) }}</strong>
            </div>
            <div class="total-card">
                <span>Clicks en enlaces</span>
                <strong>{{ number_format($totalClicks) }}</strong>
            </div>
        </div>

        <div class="sponsors">
            @forelse ($sponsors as $sponsor)
            <div class="card">
                <div class="card-header">
                    @if ($sponsor['logo_url'])
                        <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsor['name'] }}">
                    @else
                        <div class="avatar">{{ mb_substr($sponsor['name'], 0, 1) }}</div>
                    @endif
                    <div>
                        <div class="sponsor-name">{{ $sponsor['name'] }}</div>
                        @if ($sponsor['link_url'])
                            <a class="sponsor-link" href="{{ $sponsor['link_url'] }}" target="_blank">{{ $sponsor['link_url'] }}</a>
                        @endif
                    </div>
                    <span class="badge-active {{ $sponsor['active'] ? 'yes' : 'no' }}">
                        {{ $sponsor['active'] ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="stats-row">
                        <div class="stat-box views">
                            <div class="label">👁 Vistas stories</div>
                            <div class="value">{{ number_format($sponsor['story_views']) }}</div>
                        </div>
                        <div class="stat-box clicks">
                            <div class="label">🔗 Clicks enlace</div>
                            <div class="value">{{ number_format($sponsor['link_clicks']) }}</div>
                        </div>
                    </div>

                    @if ($sponsor['stories']->count())
                    <table class="stories-table">
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
                                <td>{{ $story['active'] ? '✅ Activa' : '❌ Inactiva' }}</td>
                                <td>{{ number_format($story['views']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p style="font-size:0.82rem; color:#64748b; margin-top:0.5rem;">Sin stories registradas.</p>
                    @endif
                </div>
            </div>
            @empty
                <p style="text-align:center; color:#64748b; padding: 3rem;">No hay patrocinadores registrados todavía.</p>
            @endforelse
        </div>
    </div>

    <footer>Estaciones Meteorihuela &copy; {{ date('Y') }} · Sistema de patrocinadores</footer>
</body>
</html>
