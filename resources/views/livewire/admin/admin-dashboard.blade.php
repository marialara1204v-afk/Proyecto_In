<div class="p-4 sm:p-5 space-y-5">

    {{-- ── ENCABEZADO + SELECTOR PERIODO ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-black text-patrona-50 font-display">Panel Administrador</h2>
            <p class="text-patrona-400 text-sm">La Patrona</p>
        </div>
        <div class="flex gap-2 bg-patrona-800 border border-patrona-600/50 rounded-xl p-1">
            @foreach (['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes'] as $value => $label)
                <button
                    wire:click="$set('period', '{{ $value }}')"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition-all duration-200
                           {{ $period === $value
                                ? 'bg-copper-500 text-patrona-950'
                                : 'bg-patrona-700 text-patrona-400 hover:text-patrona-200 hover:bg-patrona-600' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-copper-500 text-patrona-950">Dashboard</a>
        <a href="{{ route('cashier.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-patrona-700 border border-patrona-600/50 text-patrona-300 hover:text-patrona-100 hover:border-copper-500/30 transition-all duration-200">Cuentas</a>
        <a href="{{ route('admin.report') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-patrona-700 border border-patrona-600/50 text-patrona-300 hover:text-patrona-100 hover:border-copper-500/30 transition-all duration-200">Reportes</a>
        <a href="{{ route('admin.inventory') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-patrona-700 border border-patrona-600/50 text-patrona-300 hover:text-patrona-100 hover:border-copper-500/30 transition-all duration-200">Inventario</a>
    </div>

    {{-- ── KPIs ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @php
            $kpis = [
                ['label' => 'Ventas',         'value' => '$' . number_format($this->totalSales, 2),  'icon' => 'money'],
                ['label' => 'Ordenes',         'value' => $this->totalOrders,                         'icon' => 'receipt'],
                ['label' => 'Ticket Promedio', 'value' => '$' . number_format($this->avgTicket, 2),   'icon' => 'dashboard'],
                ['label' => 'Mesas Activas',   'value' => $this->activeTables,                        'icon' => 'map'],
                ['label' => 'Stock Critico',   'value' => $this->criticalStockCount,                  'icon' => 'warning'],
            ];
        @endphp

        @foreach ($kpis as $kpi)
            <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-4">
                <div class="mb-2 text-copper-400"><x-icon :name="$kpi['icon']" class="w-5 h-5"/></div>
                <div class="text-2xl font-black text-patrona-50">{{ $kpi['value'] }}</div>
                <div class="text-xs text-patrona-400 mt-1">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ── PRODUCTOS MAS VENDIDOS ── --}}
        <div class="lg:col-span-2 bg-patrona-800 border border-patrona-600/50 rounded-xl p-4">
            <h3 class="font-bold text-patrona-100 mb-4">Productos Mas Vendidos</h3>

            @if ($this->topProducts->isEmpty())
                <p class="text-patrona-400 text-sm text-center py-8">Sin datos para este periodo</p>
            @else
                @php $maxSold = $this->topProducts->first()->total_sold; @endphp
                <div class="space-y-3">
                    @foreach ($this->topProducts as $i => $item)
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-center text-sm font-bold
                                         {{ $i === 0 ? 'text-copper-400' : 'text-patrona-400' }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-semibold text-patrona-100 truncate">
                                        {{ $item->product->name }}
                                    </span>
                                    <span class="text-xs text-patrona-400 shrink-0 ml-2">
                                        {{ $item->total_sold }} vendidos
                                    </span>
                                </div>
                                <div class="h-2 bg-patrona-700 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-copper-500 rounded-full transition-all duration-700"
                                        style="width: {{ ($item->total_sold / $maxSold) * 100 }}%"
                                    ></div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-patrona-100 w-20 text-right shrink-0">
                                ${{ number_format($item->revenue, 0) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── VENTAS POR HORA (solo hoy) ── --}}
        <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-4">
            <h3 class="font-bold text-patrona-100 mb-4">Ventas por hora (hoy)</h3>

            @php
                $hours   = range(8, 22);
                $maxHour = $this->salesByHour->max('total') ?: 1;
            @endphp

            <div class="flex items-end gap-1 h-32">
                @foreach ($hours as $h)
                    @php $data = $this->salesByHour->get($h); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div
                            class="w-full rounded-t-sm bg-copper-500/70 hover:bg-copper-500
                                   transition-all cursor-default"
                            style="height: {{ $data ? ($data->total / $maxHour) * 100 : 0 }}%"
                            title="{{ $h }}h — ${{ number_format($data->total ?? 0, 0) }}"
                        ></div>
                        <span class="text-xs text-patrona-400 leading-none">
                            {{ $h < 10 ? '0'.$h : $h }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── CUENTAS PENDIENTES DE PAGO ── --}}
    @if ($this->pendingPayment->isNotEmpty())
        <div class="bg-patrona-800 border border-copper-500/30 rounded-xl p-4">
            <h3 class="font-bold text-patrona-100 mb-4 flex items-center gap-2">
                <x-icon name="card" class="w-5 h-5 text-copper-400"/> Cuentas por Cobrar
                <span class="bg-copper-500 text-patrona-950 text-xs font-black px-2 py-0.5 rounded-full">
                    {{ $this->pendingPayment->count() }}
                </span>
            </h3>

            <div class="space-y-3">
                @foreach ($this->pendingPayment as $order)
                    <div class="flex items-center justify-between bg-patrona-700/50
                                rounded-xl px-4 py-3 gap-4 border border-patrona-600/30">
                        <div class="flex items-center gap-4">
                            <span class="text-3xl font-black text-copper-400 w-10 text-center">
                                {{ $order->table->number }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-patrona-100">
                                    Mesa {{ $order->table->number }}
                                </p>
                                <p class="text-xs text-patrona-400">
                                    {{ $order->items->count() }} productos &middot;
                                    {{ $order->user->name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-xl font-black text-patrona-100">
                                ${{ number_format($order->total, 2) }}
                            </span>
                            <button
                                wire:click="closeOrder({{ $order->id }})"
                                wire:confirm="Confirmar pago de ${{ number_format($order->total, 2) }} de Mesa {{ $order->table->number }}?"
                                class="bg-olive-500 hover:bg-olive-400 text-white font-bold
                                       px-5 py-2.5 rounded-xl transition-all duration-200 whitespace-nowrap"
                            >
                                Cobrado
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── INVENTARIO CRITICO ── --}}
    @if ($this->criticalInventory->isNotEmpty())
        <div class="bg-patrona-800 border border-wine-500/30 rounded-xl p-4">
            <h3 class="font-bold text-patrona-100 mb-4 flex items-center gap-2">
                <x-icon name="warning" class="w-5 h-5 text-wine-400"/> Insumos con stock critico
                <span class="bg-wine-500 text-white text-xs font-black px-2 py-0.5 rounded-full">
                    {{ $this->criticalInventory->count() }}
                </span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($this->criticalInventory as $item)
                    <div class="bg-wine-500/10 rounded-xl px-4 py-3 border border-wine-500/20">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-patrona-100">{{ $item->name }}</p>
                                <p class="text-xs text-patrona-400">{{ ucfirst($item->category) }} &middot; minimo {{ number_format($item->min_stock, 2) }} {{ $item->unit }}</p>
                            </div>
                            <span class="text-wine-400 font-black">
                                {{ number_format($item->stock, 2) }} {{ $item->unit }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── AUTO-REFRESH cada minuto ── --}}
    <div wire:poll.60s></div>

</div>
