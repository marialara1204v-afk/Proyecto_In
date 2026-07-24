<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Diario — {{ $date->format('d/m/Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; color: #1a1a1a; font-size: 11px; line-height: 1.5; }

        .page { padding: 30px; }

        .header { border-bottom: 3px solid #c89d54; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #1a1a1a; margin-bottom: 2px; }
        .header .subtitle { font-size: 12px; color: #666; }
        .header .date { font-size: 14px; color: #c89d54; font-weight: bold; margin-top: 4px; }

        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { font-size: 13px; font-weight: bold; color: #c89d54; text-transform: uppercase;
                         letter-spacing: 1px; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px; margin-bottom: 10px; }

        .stats-grid { display: flex; gap: 12px; margin-bottom: 20px; }
        .stat-card { flex: 1; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; text-align: center; }
        .stat-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-top: 4px; }
        .stat-value.gold { color: #c89d54; }
        .stat-value.green { color: #2e7d32; }
        .stat-value.red { color: #c62828; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #f5f5f5; text-align: left; padding: 8px 10px; font-size: 10px; font-weight: bold;
             text-transform: uppercase; letter-spacing: 0.5px; color: #555; border-bottom: 2px solid #e0e0e0; }
        td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; font-size: 11px; }
        tr:nth-child(even) { background: #fafafa; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-gold { color: #c89d54; }
        .text-red { color: #c62828; }
        .text-green { color: #2e7d32; }

        .bar-chart { margin-top: 10px; }
        .bar-row { display: flex; align-items: center; margin-bottom: 6px; }
        .bar-label { width: 40px; font-size: 10px; color: #666; text-align: right; padding-right: 8px; }
        .bar-container { flex: 1; height: 18px; background: #f0f0f0; border-radius: 3px; overflow: hidden; }
        .bar-fill { height: 100%; background: #c89d54; border-radius: 3px; }
        .bar-value { width: 60px; font-size: 10px; padding-left: 8px; font-weight: bold; }

        .footer { border-top: 2px solid #c89d54; padding-top: 10px; margin-top: 20px; text-align: center;
                  font-size: 9px; color: #999; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-critical { background: #ffebee; color: #c62828; }
        .badge-ok { background: #e8f5e9; color: #2e7d32; }
        .badge-warn { background: #fff8e1; color: #f57f17; }
    </style>
</head>
<body>
    <div class="page">

        {{-- Header --}}
        <div class="header">
            <h1>La Patrona</h1>
            <div class="subtitle">Reporte de Cierre Diario</div>
            <div class="date">{{ $date->format('d/m/Y') }}</div>
        </div>

        {{-- Resumen --}}
        <div class="section">
            <div class="section-title">Resumen del Dia</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Ventas Totales</div>
                    <div class="stat-value gold">${{ number_format($totalSales, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ordenes Pagadas</div>
                    <div class="stat-value">{{ $totalOrders }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ticket Promedio</div>
                    <div class="stat-value">${{ number_format($avgTicket, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Propinas</div>
                    <div class="stat-value green">${{ number_format($totalTips ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Desglose por metodo de pago --}}
        @if(isset($paymentBreakdown))
        <div class="section">
            <div class="section-title">Desglose por Metodo de Pago</div>
            <table>
                <thead>
                    <tr>
                        <th>Metodo</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Efectivo</td>
                        <td class="text-right font-bold">${{ number_format($paymentBreakdown['cash'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ $totalSales > 0 ? number_format(($paymentBreakdown['cash'] ?? 0) / $totalSales * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Tarjeta</td>
                        <td class="text-right font-bold">${{ number_format($paymentBreakdown['card'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ $totalSales > 0 ? number_format(($paymentBreakdown['card'] ?? 0) / $totalSales * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Transferencia</td>
                        <td class="text-right font-bold">${{ number_format($paymentBreakdown['transfer'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ $totalSales > 0 ? number_format(($paymentBreakdown['transfer'] ?? 0) / $totalSales * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Otro</td>
                        <td class="text-right font-bold">${{ number_format($paymentBreakdown['other'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ $totalSales > 0 ? number_format(($paymentBreakdown['other'] ?? 0) / $totalSales * 100, 1) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- Productos mas vendidos --}}
        <div class="section">
            <div class="section-title">Productos Mas Vendidos</div>
            @if($topProducts->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th class="text-center">Vendidos</th>
                            <th class="text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $i => $item)
                            <tr>
                                <td class="font-bold">{{ $i + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-center font-bold">{{ $item->total_sold }}</td>
                                <td class="text-right text-gold font-bold">${{ number_format($item->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #999; text-align: center; padding: 15px;">Sin ventas para esta fecha</p>
            @endif
        </div>

        {{-- Ventas por hora --}}
        <div class="section">
            <div class="section-title">Ventas por Hora</div>
            <div class="bar-chart">
                @php $maxSale = max(1, $salesByHour->max('total') ?: 1); @endphp
                @foreach(range(8, 22) as $hour)
                    @php $data = $salesByHour->get($hour); @endphp
                    <div class="bar-row">
                        <div class="bar-label">{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}:00</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $data ? min(100, max(2, ($data->total / $maxSale) * 100)) : 0 }}%"></div>
                        </div>
                        <div class="bar-value">${{ number_format($data->total ?? 0, 0) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Inventario --}}
        <div class="section">
            <div class="section-title">Inventario</div>
            <table>
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Categoria</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Minimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryItems as $item)
                        <tr>
                            <td class="font-bold">{{ $item->name }}</td>
                            <td style="text-transform: capitalize;">{{ $item->category }}</td>
                            <td class="text-right">{{ number_format($item->stock, 2) }} {{ $item->unit }}</td>
                            <td class="text-right">{{ number_format($item->min_stock, 2) }} {{ $item->unit }}</td>
                            <td>
                                @if($item->isCritical())
                                    <span class="badge badge-critical">Critico</span>
                                @elseif($item->is_active)
                                    <span class="badge badge-ok">OK</span>
                                @else
                                    <span class="badge badge-warn">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="color: #999; padding: 15px;">No hay insumos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>La Patrona</strong> — Reporte generado el {{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin-top: 3px;">Este documento es confidencial y de uso interno.</p>
        </div>

    </div>
</body>
</html>
