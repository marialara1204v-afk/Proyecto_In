<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Patrona — Reporte Diario</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-patrona-950 min-h-screen text-white">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 bg-copper-600 hover:bg-copper-700 text-white font-bold py-2 px-4 rounded-xl transition">
                ← Volver al Dashboard
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <p class="text-patrona-500 text-sm uppercase tracking-[0.25em]">Reporte administrativo</p>
                <h1 class="text-3xl font-black text-white mt-1">Cierre diario</h1>
                <p class="text-patrona-500 text-sm mt-1">{{ $date->format('d/m/Y') }}</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="bg-patrona-900 border border-patrona-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-copper-500">
                <button class="bg-copper-500 hover:bg-copper-400 text-white font-bold px-4 py-2.5 rounded-xl">Actualizar</button>
                <a href="{{ route('admin.report.export', ['date' => $date->toDateString()]) }}" class="bg-green-600 hover:bg-green-500 text-white font-bold px-4 py-2.5 rounded-xl">Exportar CSV</a>
                <a href="{{ route('admin.report.pdf', ['date' => $date->toDateString()]) }}" class="bg-wine-600 hover:bg-wine-500 text-white font-bold px-4 py-2.5 rounded-xl">Generar PDF</a>
                <a href="{{ route('admin.inventory') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl">Cargar Insumos</a>
                <button type="button" onclick="window.print()" class="bg-patrona-800 hover:bg-patrona-700 text-patrona-200 font-bold px-4 py-2.5 rounded-xl">Imprimir</button>
            </form>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5"><div class="text-patrona-500 text-sm">Ventas</div><div class="text-2xl font-black text-copper-400 mt-1">${{ number_format($totalSales, 2) }}</div></div>
            <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5"><div class="text-patrona-500 text-sm">Órdenes</div><div class="text-2xl font-black text-white mt-1">{{ $totalOrders }}</div></div>
            <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5"><div class="text-patrona-500 text-sm">Ticket prom.</div><div class="text-2xl font-black text-white mt-1">${{ number_format($avgTicket, 2) }}</div></div>
            <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5"><div class="text-patrona-500 text-sm">Propinas</div><div class="text-2xl font-black text-green-400 mt-1">${{ number_format($totalTips ?? 0, 2) }}</div></div>
            <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5"><div class="text-patrona-500 text-sm">Mesas activas</div><div class="text-2xl font-black text-white mt-1">{{ $activeTables }}</div></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <section class="lg:col-span-2 bg-patrona-900 border border-patrona-800 rounded-2xl p-5">
                <h2 class="font-bold text-white mb-4">Productos más vendidos</h2>
                <div class="space-y-3">
                    @forelse ($topProducts as $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-patrona-800 px-4 py-3">
                            <div>
                                <div class="font-semibold text-white">{{ $item->product->name }}</div>
                                <div class="text-xs text-patrona-500">{{ $item->total_sold }} vendidos</div>
                            </div>
                            <div class="text-copper-400 font-bold">${{ number_format($item->revenue, 2) }}</div>
                        </div>
                    @empty
                        <p class="text-gray-600 text-sm py-6 text-center">Sin ventas para esta fecha</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5">
                <h2 class="font-bold text-white mb-4">Inventario crítico</h2>
                <div class="space-y-3">
                    @forelse ($criticalInventory as $item)
                        <div class="rounded-xl bg-wine-500/10 border border-wine-500/20 px-4 py-3">
                            <div class="font-semibold text-white">{{ $item->name }}</div>
                            <div class="text-xs text-wine-300 mt-1">{{ number_format($item->stock, 2) }} {{ $item->unit }} / mínimo {{ number_format($item->min_stock, 2) }}</div>
                        </div>
                    @empty
                        <p class="text-gray-600 text-sm py-6 text-center">Sin insumos críticos</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5 mb-6">
            <h2 class="font-bold text-white mb-4">Tabla de Inventario</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-patrona-800 text-left">
                            <th class="py-3 pr-4 text-patrona-500 font-semibold">Insumo</th>
                            <th class="py-3 pr-4 text-patrona-500 font-semibold">Categoría</th>
                            <th class="py-3 pr-4 text-patrona-500 font-semibold text-right">Stock Actual</th>
                            <th class="py-3 pr-4 text-patrona-500 font-semibold text-right">Stock Mínimo</th>
                            <th class="py-3 pr-4 text-patrona-500 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-patrona-800">
                        @forelse ($inventoryItems as $item)
                            @php
                                $statusClass = $item->is_active
                                    ? ($item->isCritical() ? 'bg-wine-500 text-white' : 'bg-green-500 text-white')
                                    : 'bg-yellow-500 text-black';
                            @endphp
                            <tr>
                                <td class="py-3 pr-4 text-white font-medium">{{ $item->name }}</td>
                                <td class="py-3 pr-4 text-gray-400 capitalize">{{ $item->category }}</td>
                                <td class="py-3 pr-4 text-right text-white">{{ number_format($item->stock, 2) }} {{ $item->unit }}</td>
                                <td class="py-3 pr-4 text-right text-gray-300">{{ number_format($item->min_stock, 2) }} {{ $item->unit }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                        <span class="inline-block w-2 h-2 rounded-full bg-white/90"></span>
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-gray-600">No hay insumos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5 mb-6">
            <h2 class="font-bold text-white mb-4">Ventas por hora</h2>
            <div class="grid grid-cols-8 gap-2" style="grid-template-columns: repeat(15, minmax(0, 1fr));">
                @foreach (range(8, 22) as $hour)
                    @php $data = $salesByHour->get($hour); @endphp
                    <div class="text-center">
                        <div class="h-24 flex items-end justify-center bg-patrona-800 rounded-lg overflow-hidden">
                            <div class="w-full bg-copper-500" style="height: {{ $data ? min(100, max(8, ($data->total / max(1, $salesByHour->max('total') ?: 1)) * 100)) : 0 }}%"></div>
                        </div>
                        <div class="text-xs text-patrona-500 mt-2">{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <a href="{{ route('admin.dashboard') }}" class="bg-patrona-900 border border-patrona-700 hover:border-copper-500 text-gray-300 hover:text-white font-bold px-5 py-2.5 rounded-xl transition-colors">Volver al dashboard</a>
        </div>
    </main>
</body>
</html>