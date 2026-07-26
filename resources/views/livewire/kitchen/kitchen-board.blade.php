<div class="p-4 sm:p-5 min-h-screen" wire:poll.5s>

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="font-display text-3xl text-patrona-50 font-bold">
                {{ auth()->user()->role === 'bartender' ? 'Barra' : 'Cocina' }}
            </h1>
            <p class="text-patrona-400 text-sm">
                Pedidos activos
            </p>
        </div>

        {{-- Filtros: solo admin --}}
        @if ($this->canSeeAllFilters)
            <div class="flex gap-2">
                @foreach ($this->filterOrder as $val => $label)
                    <button
                        wire:click="$set('filter', '{{ $val }}')"
                        class="px-4 py-2 rounded-xl font-bold text-sm transition-all duration-200 border
                               {{ $filter === $val
                                    ? 'bg-copper-500 border-copper-500 text-patrona-950'
                                    : 'bg-patrona-700 border-patrona-600 text-patrona-300 hover:border-copper-500/50 hover:text-patrona-100' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── CONTADORES ── --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-4">
            <p class="text-xs text-patrona-400 font-semibold mb-1 uppercase tracking-wider">Activos</p>
            <div class="font-display text-4xl font-bold text-patrona-50">
                {{ $this->stats['pending'] + $this->stats['preparing'] }}
            </div>
        </div>
        <div class="bg-wine-600/10 border border-wine-500/30 rounded-xl p-4">
            <p class="text-xs text-wine-400 font-semibold mb-1 uppercase tracking-wider">Pendientes</p>
            <div class="font-display text-4xl font-bold text-wine-400">
                {{ $this->stats['pending'] }}
            </div>
        </div>
        <div class="bg-copper-500/10 border border-copper-500/30 rounded-xl p-4">
            <p class="text-xs text-copper-400 font-semibold mb-1 uppercase tracking-wider">Preparando</p>
            <div class="font-display text-4xl font-bold text-copper-400">
                {{ $this->stats['preparing'] }}
            </div>
        </div>
    </div>

    {{-- ── COMANDAS ── --}}
    @if ($this->orders->isEmpty())
        <div class="flex flex-col items-center justify-center py-28 text-center">
            <div class="text-olive-500 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="font-display text-3xl text-olive-400 font-bold">Todo al dia</h2>
            <p class="text-patrona-400 mt-2">No hay pedidos pendientes</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($this->orders as $order)
                @php
                    $elapsed = $order->created_at->diffForHumans();
                    $mins = now()->diffInMinutes($order->created_at);
                    $urgent = $mins >= 15;
                    $isPending = $order->status === 'pendiente';
                @endphp

                <div
                    class="rounded-xl border overflow-hidden flex flex-col bg-patrona-800 shadow-lg
                           {{ $urgent
                                ? 'border-wine-500/40'
                                : ($isPending ? 'border-patrona-600/50' : 'border-copper-500/30') }}"
                    wire:key="order-{{ $order->id }}"
                >
                    {{-- Header de la comanda --}}
                    <div class="px-4 py-3 flex items-center justify-between border-b
                                {{ $urgent
                                    ? 'border-wine-500/20 bg-wine-600/10'
                                    : ($isPending ? 'border-patrona-600/30 bg-patrona-700/50' : 'border-copper-500/20 bg-copper-500/5') }}">
                        <div class="flex items-center gap-3">
                            <span class="font-display text-4xl font-bold
                                         {{ $urgent
                                            ? 'text-wine-400'
                                            : ($isPending ? 'text-patrona-50' : 'text-copper-400') }}
                                         leading-none">
                                {{ $order->table->number }}
                            </span>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider
                                            {{ $urgent
                                                ? 'text-wine-400'
                                                : ($isPending ? 'text-patrona-300' : 'text-copper-400') }}">
                                    Mesa
                                </div>
                                <div class="text-xs text-patrona-400">
                                    #{{ $order->id }}
                                </div>
                                <div class="text-xs text-patrona-400 mt-0.5">
                                    {{ $order->user->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        {{-- Tiempo --}}
                        <div class="text-right">
                            <div class="font-bold text-xl {{ $urgent ? 'text-wine-400' : 'text-patrona-300' }}">
                                {{ $elapsed }}
                            </div>
                            @if ($urgent)
                                <div class="text-xs text-wine-400 font-bold uppercase">Retraso</div>
                            @endif
                        </div>
                    </div>

                    {{-- Items de la comanda --}}
                    <div class="flex-1 p-4 space-y-3">
                        @foreach ($order->items as $item)
                            @if (
                                $filter === 'all' ||
                                ($filter === 'cocina' && $item->destination === 'cocina') ||
                                ($filter === 'bartender' && $item->destination === 'bartender')
                            )
                                <div class="border border-patrona-600/40 rounded-lg p-3 bg-patrona-700/30" wire:key="item-{{ $item->id }}">
                                    <div class="flex items-start gap-3 mb-2">
                                         {{-- Cantidad --}}
                                        @php
                                            $qtyColor = match($item->item_status) {
                                                'pendiente'  => 'bg-wine-500',
                                                'preparando' => 'bg-copper-500',
                                                default      => 'bg-olive-500',
                                            };
                                        @endphp
                                        <span class="shrink-0 w-10 h-10 rounded-xl font-black text-xl
                                                     flex items-center justify-center {{ $qtyColor }} text-white">
                                            {{ $item->quantity }}
                                        </span>

                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-base text-patrona-50 leading-tight">
                                                {{ $item->product?->name ?? 'Sin producto' }}
                                            </p>
                                            @if ($item->notes)
                                                <p class="text-wine-400 text-sm mt-0.5 font-medium
                                                          bg-wine-600/10 rounded-lg px-2 py-0.5 inline-block border border-wine-500/20">
                                                    {{ $item->notes }}
                                                </p>
                                            @endif
                                            <p class="text-patrona-400 text-xs mt-0.5">
                                                {{ $item->product?->category?->name ?? '' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Botones de estado --}}
                                    <div class="flex gap-2 mt-2">
                                        <button
                                            type="button"
                                            wire:click="updateItemStatus({{ $item->id }}, 'preparando')"
                                            wire:loading.attr="disabled"
                                            wire:target="updateItemStatus({{ $item->id }}, 'preparando')"
                                            class="flex-1 text-xs py-1.5 px-2 rounded font-bold transition-all duration-200
                                                   {{ $item->item_status === 'preparando'
                                                        ? 'bg-copper-500 text-patrona-950'
                                                        : 'bg-patrona-600 text-patrona-300 hover:bg-copper-500/20 hover:text-copper-300' }}
                                                   disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span wire:loading.remove wire:target="updateItemStatus({{ $item->id }}, 'preparando')">En preparacion</span>
                                            <span wire:loading wire:target="updateItemStatus({{ $item->id }}, 'preparando')" class="inline-flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="15.7 47.1"></circle></svg>
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="updateItemStatus({{ $item->id }}, 'listo')"
                                            wire:loading.attr="disabled"
                                            wire:target="updateItemStatus({{ $item->id }}, 'listo')"
                                            class="flex-1 text-xs py-1.5 px-2 rounded font-bold transition-all duration-200
                                                   {{ $item->item_status === 'listo'
                                                        ? 'bg-olive-500 text-white'
                                                        : 'bg-patrona-600 text-patrona-300 hover:bg-olive-500/20 hover:text-olive-400' }}
                                                   disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span wire:loading.remove wire:target="updateItemStatus({{ $item->id }}, 'listo')">Listo</span>
                                            <span wire:loading wire:target="updateItemStatus({{ $item->id }}, 'listo')" class="inline-flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="15.7 47.1"></circle></svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Boton de accion --}}
                    <div class="p-3">
                        <button
                            wire:click="advanceStatus({{ $order->id }})"
                            wire:loading.attr="disabled"
                            wire:target="advanceStatus({{ $order->id }})"
                            class="w-full py-3 rounded-xl font-bold text-sm tracking-wide
                                   transition-all duration-200 active:scale-95
                                   {{ $isPending
                                        ? 'bg-copper-500 hover:bg-copper-400 text-patrona-950 shadow-lg shadow-copper-500/20'
                                        : 'bg-olive-500 hover:bg-olive-400 text-white shadow-lg shadow-olive-500/20' }}
                                   disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="advanceStatus({{ $order->id }})">
                                {{ $isPending ? 'En preparacion' : 'Marcar listo' }}
                            </span>
                            <span wire:loading wire:target="advanceStatus({{ $order->id }})" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="15.7 47.1"></circle></svg>
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
