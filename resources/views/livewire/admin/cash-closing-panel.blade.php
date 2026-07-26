<div class="space-y-6">
    {{-- Botón Volver al Dashboard --}}
    <div class="flex gap-2">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}" 
           class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            ← Volver al Dashboard
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-wine-100 border border-wine-400 text-wine-700 px-4 py-3 rounded">
            ✗ {{ session('error') }}
        </div>
    @endif

    {{-- Estadísticas del Día --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6">Resumen del Día — {{ $this->today->format('d/m/Y') }}</h2>

        @php $stats = $this->todayStats; @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded border border-blue-200">
                <p class="text-sm text-gray-600 font-semibold">Órdenes Pagadas</p>
                <p class="text-3xl font-black text-blue-600">{{ $stats['total_orders'] }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded border border-green-200">
                <p class="text-sm text-gray-600 font-semibold">Total Vendido</p>
                <p class="text-3xl font-black text-green-600">${{ number_format($stats['total_sales'], 2) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded border border-purple-200">
                <p class="text-sm text-gray-600 font-semibold">Total Cobrado</p>
                <p class="text-3xl font-black text-purple-600">${{ number_format($stats['total_payments'], 2) }}</p>
            </div>
            <div class="bg-copper-100 p-4 rounded border border-copper-200">
                <p class="text-sm text-gray-600 font-semibold">Diferencia</p>
                <p class="text-3xl font-black {{ $stats['total_sales'] == $stats['total_payments'] ? 'text-green-600' : 'text-wine-600' }}">
                    ${{ number_format($stats['total_sales'] - $stats['total_payments'], 2) }}
                </p>
            </div>
        </div>

        {{-- Desglose por Método de Pago --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-xs text-gray-600 font-semibold">💵 Efectivo</p>
                <p class="text-2xl font-bold">${{ number_format($stats['cash'], 2) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-xs text-gray-600 font-semibold">💳 Tarjeta</p>
                <p class="text-2xl font-bold">${{ number_format($stats['card'], 2) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-xs text-gray-600 font-semibold">🏦 Transferencia</p>
                <p class="text-2xl font-bold">${{ number_format($stats['transfer'], 2) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-xs text-gray-600 font-semibold">📝 Otro</p>
                <p class="text-2xl font-bold">${{ number_format($stats['other'], 2) }}</p>
            </div>
        </div>

        {{-- Botón Cerrar Caja --}}
        @if ($this->todayBalance && $this->todayBalance->isClosed())
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-center font-bold">
                ✓ Caja Cerrada a las {{ $this->todayBalance->closed_at->format('H:i:s') }} por {{ $this->todayBalance->closedBy->name }}
            </div>
        @else
            <button
                type="button"
                wire:click="openCloseModal"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded w-full">
                Cerrar Caja del Día
            </button>
        @endif
    </div>

    {{-- Historial de Cierres --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">Historial de Cierres</h3>

        <table class="w-full text-sm">
            <thead class="bg-gray-100 border-b-2">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-right">Órdenes</th>
                    <th class="px-3 py-2 text-right">Vendido</th>
                    <th class="px-3 py-2 text-right">Cobrado</th>
                    <th class="px-3 py-2 text-right">Cerrado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->balances as $balance)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2">{{ $balance->date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-right font-bold">{{ $balance->orders_count }}</td>
                        <td class="px-3 py-2 text-right">${{ number_format($balance->total_sales, 2) }}</td>
                        <td class="px-3 py-2 text-right">${{ number_format($balance->total_payments, 2) }}</td>
                        <td class="px-3 py-2 text-right">{{ $balance->closedBy?->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal Cerrar Caja --}}
    @if ($showCloseModal)
        <div class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
                <h3 class="text-2xl font-bold mb-4">Confirmar Cierre de Caja</h3>

                <div class="bg-blue-50 p-4 rounded mb-4 border border-blue-200">
                    <p class="text-sm text-gray-600 mb-1">Total a Cerrar</p>
                    <p class="text-3xl font-bold text-blue-600">${{ number_format($stats['total_payments'], 2) }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">Notas (opcional):</label>
                    <textarea
                        wire:model="notes"
                        rows="3"
                        placeholder="Detalle cualquier discrepancia o nota importante..."
                        class="w-full border-2 border-gray-300 rounded px-3 py-2"></textarea>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="closeCash"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded">
                        Cerrar Caja
                    </button>
                    <button
                        type="button"
                        wire:click="$set('showCloseModal', false)"
                        class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 rounded">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
