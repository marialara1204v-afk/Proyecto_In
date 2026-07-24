<div
    class="p-4 sm:p-5 min-h-screen"
    @if (! $showOrderModal && ! $showBillModal)
        wire:poll.5s="refreshTables"
    @endif
>

    {{-- ── FLASH ── --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3500)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-5 right-5 z-50 bg-green-500 text-white px-5 py-3.5
                   rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2"
        >
            <x-icon name="check" class="w-4 h-4"/>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4500)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-red-500 text-white px-5 py-3.5
                   rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2"
        >
            <x-icon name="warning" class="w-4 h-4"/>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── RESUMEN RÁPIDO ── --}}
    @php
        $freeCount = $this->tables->where('status', 'libre')->count();
        $occupiedCount = $this->tables->where('status', 'ocupada')->count();
        $readyCount = $this->tables->where('status', 'pedido_listo')->count();
        $hasTables = $this->tables->isNotEmpty();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Mesas Libres</p>
            <p class="text-2xl font-black text-emerald-600 leading-none mt-1">{{ $freeCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Mesas Ocupadas</p>
            <p class="text-2xl font-black text-amber-500 leading-none mt-1">{{ $occupiedCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Pedidos Listos</p>
            <p class="text-2xl font-black text-rose-500 leading-none mt-1">{{ $readyCount }}</p>
        </div>
    </div>

    {{-- ── ENCABEZADO ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center
                justify-between gap-4 mb-4">
        <div>
            <h1 class="font-display text-3xl text-[#1F2937] font-bold">
                Mapa de Mesas
            </h1>
            <p class="text-[#6B7280] text-sm mt-0.5">
                Toca una mesa para gestionar el pedido
            </p>
        </div>
        <div class="flex items-center gap-4">
            {{-- Leyenda --}}
            <div class="flex gap-3 text-xs font-semibold text-[#6B7280]">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Libre
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Ocupada
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span>Esperando cuenta
                </span>
            </div>
            {{-- Contador de mesas libres --}}
            <div class="bg-[#4B2E18] rounded-xl px-4 py-2 text-center shadow-sm">
                <div class="text-lg font-bold text-green-400">
                    {{ $freeCount }}
                </div>
                <div class="text-xs text-[#D1D5DB]">libres</div>
            </div>

            <button
                type="button"
                wire:click="startNewOrder"
                @class([
                    'bg-[#f59e0b] text-white rounded-xl px-4 py-2.5 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-colors',
                    'hover:bg-[#ea8c00]' => $hasTables,
                    'bg-gray-300 cursor-not-allowed pointer-events-none' => ! $hasTables,
                ])
                aria-disabled="{{ $hasTables ? 'false' : 'true' }}"
            >
                <x-icon name="cart" class="w-4 h-4"/> Nuevo Pedido
            </button>
        </div>
    </div>

    @if ($showTablePickerModal)
        @php
            $allTables = $this->tables->sortBy('number');
        @endphp

        <div class="fixed inset-0 z-40 flex items-center justify-center p-4" x-data @keydown.escape.window="$wire.closeTablePickerModal()">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" wire:click="closeTablePickerModal"></div>

            <div class="relative z-50 w-full max-w-2xl bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-2xl text-[#1f2937] font-bold leading-tight">Elegir mesa</h2>
                        <p class="text-xs text-[#6b7280] font-semibold">Selecciona la mesa para crear el pedido</p>
                    </div>
                    <button type="button" wire:click="closeTablePickerModal" class="text-[#6B7280] hover:text-[#111827] text-3xl leading-none transition-colors">&times;</button>
                </div>

                <div class="p-5">
                    @if ($allTables->isEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 font-semibold">
                            No hay mesas registradas.
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($allTables as $table)
                                @php
                                    $tableStyle = match ($table->status) {
                                        'libre' => ['border' => 'border-emerald-300', 'bg' => 'bg-emerald-50', 'hover' => 'hover:bg-emerald-100', 'title' => 'Libre', 'number' => 'text-emerald-600', 'label' => 'text-emerald-700'],
                                        'pedido_listo' => ['border' => 'border-rose-300', 'bg' => 'bg-rose-50', 'hover' => 'hover:bg-rose-100', 'title' => 'Esperando cuenta', 'number' => 'text-rose-600', 'label' => 'text-rose-700'],
                                        default => ['border' => 'border-amber-300', 'bg' => 'bg-amber-50', 'hover' => 'hover:bg-amber-100', 'title' => 'Ocupada', 'number' => 'text-amber-600', 'label' => 'text-amber-700'],
                                    };
                                @endphp
                                <button
                                    type="button"
                                    wire:click="selectTableForOrder({{ $table->id }})"
                                    class="rounded-xl border {{ $tableStyle['border'] }} {{ $tableStyle['bg'] }} {{ $tableStyle['hover'] }} px-4 py-4 text-left transition-colors"
                                >
                                    <div class="text-xs {{ $tableStyle['label'] }} font-semibold">Mesa {{ $tableStyle['title'] }}</div>
                                    <div class="font-display text-4xl leading-none {{ $tableStyle['number'] }} font-bold mt-1">{{ $table->number }}</div>
                                    <div class="text-xs text-[#6b7280] font-semibold mt-2">{{ $table->capacity }} personas</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="px-5 pb-5">
                    <button type="button" wire:click="closeTablePickerModal" class="w-full bg-[#F5EFE8] hover:bg-[#E8D5C4] text-[#5C3D26] font-semibold py-3 rounded-xl transition-colors text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── GRID DE MESAS ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-8">
        @foreach ($this->tables as $table)
            @php
                $statusConfig = match($table->status) {
                    'libre'        => ['bg' => '#0fcf5f', 'border' => '#0aae4f', 'text' => '#ffffff', 'badgeBg' => 'rgba(255,255,255,.22)', 'badgeText' => '#ffffff', 'label' => 'Libre'],
                    'pedido_listo' => ['bg' => '#f18a97', 'border' => '#df7280', 'text' => '#ffffff', 'badgeBg' => 'rgba(255,255,255,.22)', 'badgeText' => '#ffffff', 'label' => 'Pedido listo'],
                    default        => ['bg' => '#f59e0b', 'border' => '#df8d05', 'text' => '#ffffff', 'badgeBg' => 'rgba(255,255,255,.22)', 'badgeText' => '#ffffff', 'label' => 'Ocupada'],
                };
            @endphp

            <div
                wire:key="table-card-{{ $table->id }}"
                class="relative rounded-xl border transition-all duration-200 cursor-pointer group
                      hover:-translate-y-0.5 hover:shadow-lg hover:shadow-black/10
                       {{ $table->isOrderReady() ? 'animate-pulse' : '' }}"
                style="background-color: {{ $statusConfig['bg'] }}; border-color: {{ $statusConfig['border'] }};"
            >
                {{-- Botón principal: abrir pedido --}}
                <button
                    type="button"
                    wire:click="openOrderModal({{ $table->id }})"
                    class="w-full p-4 text-center focus:outline-none"
                >
                    {{-- Número de mesa --}}
                    <div class="font-display text-5xl font-bold
                                leading-none mb-2">
                        <span style="color: {{ $statusConfig['text'] }};">{{ $table->number }}</span>
                    </div>

                    {{-- Capacidad --}}
                    <div class="text-xs mb-2 font-semibold" style="color: rgba(255,255,255,0.92);">
                        {{ $table->capacity }} personas
                    </div>

                    {{-- Estado --}}
                    <span
                        class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                        style="background-color: {{ $statusConfig['badgeBg'] }}; color: {{ $statusConfig['badgeText'] }};"
                    >{{ $statusConfig['label'] }}</span>

                    {{-- Total de la orden activa --}}
                    @if ($table->activeOrder)
                        <div class="mt-2 text-xs font-bold" style="color: {{ $statusConfig['text'] }};">
                            ${{ number_format($table->activeOrder->total, 2) }}
                        </div>
                    @endif
                </button>

                {{-- Badge de ítems en la orden --}}
                @if ($table->activeOrder && $table->activeOrder->items->count() > 0)
                    <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs
                                 font-bold rounded-full w-6 h-6 flex items-center justify-center
                                 shadow-lg">
                        {{ $table->activeOrder->items->count() }}
                    </span>
                @endif

                {{-- Botón de cuenta (solo si está ocupada o lista) --}}
                @if (! $table->isFree())
                    <button
                        type="button"
                        wire:click.stop="openBillModal({{ $table->id }})"
                        class="absolute bottom-2 right-2 bg-[#2C1A0E] hover:bg-[#3D2410]
                               text-[#9B7B5E] hover:text-white text-xs px-2 py-1
                               rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                        title="Ver cuenta"
                    >
                        <x-icon name="receipt" class="w-4 h-4"/>
                    </button>
                @endif

                {{-- Botón QR del menú público de la mesa --}}
                <a
                    href="{{ route('menu', $table->number) }}"
                    target="_blank"
                    class="absolute bottom-2 left-2 bg-[#2C1A0E] hover:bg-[#3D2410]
                           text-[#9B7B5E] hover:text-white text-xs px-2 py-1
                           rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                    title="Ver menú QR de esta mesa"
                >
                    QR
                </a>
            </div>
        @endforeach
    </div>


    {{-- ══════════════════════════════════════════════════════════
         MODAL DE NUEVO PEDIDO
    ══════════════════════════════════════════════════════════ --}}
    @if ($showOrderModal && $this->selectedTable)
        <div
            class="fixed inset-0 z-40 flex"
            x-data
            @keydown.escape.window="$wire.closeOrderModal()"
        >
            <a
                href="{{ route('waiter.tables') }}"
                wire:click.prevent="closeOrderModal"
                class="absolute inset-0 bg-black/75 backdrop-blur-sm"
                aria-label="Cerrar modal de pedido"
            ></a>

            <div class="relative z-50 flex w-full max-w-5xl mx-auto my-4 lg:my-6
                        bg-[#f9fafb] rounded-2xl overflow-hidden shadow-2xl
                        border border-gray-200" style="max-height:90vh">

                {{-- ─── COLUMNA IZQUIERDA: Catálogo ─── --}}
                <div class="flex-1 flex flex-col overflow-hidden border-r border-gray-200">

                    {{-- Header --}}
                    <div class="bg-white px-5 py-4 flex items-center justify-between shrink-0 border-b border-gray-200">
                        <div>
                            <h2 class="font-display text-2xl text-[#1f2937] font-bold leading-tight">
                                Nuevo Pedido
                            </h2>
                            <p class="text-xs text-[#6b7280] font-semibold">
                                Mesa {{ $this->selectedTable->number }}
                                ({{ $this->selectedTable->capacity }} personas)
                            </p>
                        </div>
                        <a href="{{ route('waiter.tables') }}" wire:click.prevent="closeOrderModal"
                                class="text-[#6B7280] hover:text-[#111827] text-3xl leading-none transition-colors">
                            &times;
                        </a>
                    </div>

                    {{-- Buscador --}}
                    <div class="px-4 py-3 border-b border-gray-200 shrink-0 bg-white">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]"><x-icon name="search" class="w-4 h-4"/></span>
                            <input
                                wire:model.live.debounce.150ms="search"
                                type="text"
                                placeholder="Buscar productos..."
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-300
                                       rounded-xl text-sm text-[#2C1A0E] focus:outline-none
                                       focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30
                                       transition-colors"
                                autofocus
                            >
                        </div>
                    </div>

                    {{-- Filtros de categoría --}}
                    <div class="flex gap-2 px-4 py-2.5 border-b border-gray-200 shrink-0
                                bg-white overflow-x-auto">
                        <button
                            type="button"
                            wire:click="$set('activeCategoryId', null)"
                            class="shrink-0 px-4 py-1.5 rounded-full text-xs font-bold
                                   transition-all border
                                   {{ is_null($activeCategoryId)
                                        ? 'bg-orange-500 border-orange-500 text-white'
                                        : 'bg-[#f3f4f6] border-transparent text-[#4b5563] hover:border-orange-300' }}"
                        >
                            Todos
                        </button>
                        @foreach ($this->categories as $cat)
                            <button
                                type="button"
                                wire:click="$set('activeCategoryId', {{ $cat->id }})"
                                class="shrink-0 px-4 py-1.5 rounded-full text-xs font-bold
                                       transition-all border
                                       {{ $activeCategoryId === $cat->id
                                            ? 'bg-orange-500 border-orange-500 text-white'
                                            : 'bg-[#f3f4f6] border-transparent text-[#4b5563] hover:border-orange-300' }}"
                            >
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Grid de Productos --}}
                    <div class="flex-1 overflow-y-auto p-4 bg-[#f9fafb]">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @forelse ($this->products as $product)
                                <a
                                    href="{{ route('waiter.tables', ['newOrder' => 1, 'table' => $this->selectedTable->id, 'action' => 'add', 'item' => $product->id]) }}"
                                    wire:key="product-card-{{ $product->id }}"
                                    wire:click.stop.prevent="addToCart({{ $product->id }})"
                                    class="relative text-left bg-white rounded-xl border
                                           {{ isset($cart[$product->id]) ? 'border-orange-500' : 'border-gray-200' }}
                                           hover:border-orange-400 hover:shadow-sm p-3
                                           transition-all group active:scale-95 block"
                                >
                                    @if ($product->image)
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-16 object-cover rounded-lg mb-2">
                                    @endif
                                    <div class="font-semibold text-sm text-[#2C1A0E]
                                                group-hover:text-orange-600 transition-colors leading-tight">
                                        {{ $product->name }}
                                    </div>
                                    <div class="text-orange-600 font-bold text-base mt-1.5">
                                        ${{ number_format($product->price, 2) }}
                                    </div>
                                    <div class="text-xs text-[#6B7280] mt-0.5">
                                        {{ $product->category->name }}
                                    </div>

                                    @if (isset($cart[$product->id]))
                                        <span class="absolute -top-2.5 -right-2.5 bg-orange-500 text-white
                                                     text-xs font-bold rounded-full w-6 h-6 flex
                                                     items-center justify-center shadow-md">
                                            {{ $cart[$product->id]['quantity'] }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <div class="col-span-2 text-center py-12 text-[#9B7B5E]">
                                    <div class="text-4xl mb-2 flex justify-center text-[#9B7B5E]"><x-icon name="search" class="w-8 h-8"/></div>
                                    <p class="text-sm">No se encontraron productos</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ─── COLUMNA DERECHA: Carrito ─── --}}
                <div class="w-80 flex flex-col bg-white shrink-0">

                    <div class="px-4 py-3.5 bg-white border-b border-gray-200 shrink-0">
                        <h3 class="font-semibold text-[#111827] flex items-center gap-2">
                            <x-icon name="cart" class="w-5 h-5"/> Pedido
                            @if ($this->cartCount > 0)
                                <span class="bg-gray-100 text-gray-500 text-xs font-bold
                                             px-2 py-0.5 rounded-full">
                                    {{ $this->cartCount }}
                                </span>
                            @endif
                        </h3>
                    </div>

                    {{-- Items del carrito --}}
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-white">
                        @forelse ($cart as $productId => $item)
                            <div wire:key="cart-item-{{ $productId }}" class="bg-white rounded-xl border border-gray-200 p-3">
                                <div class="flex items-start justify-between gap-1 mb-2">
                                    <span class="text-xs font-semibold text-[#2C1A0E] leading-tight flex-1">
                                        {{ $item['name'] }}
                                    </span>
                                    <a
                                        href="{{ route('waiter.tables', ['newOrder' => 1, 'table' => $this->selectedTable->id, 'action' => 'remove', 'item' => $productId]) }}"
                                        wire:click.prevent="removeItem({{ $productId }})"
                                        class="text-[#C4A08A] hover:text-red-500 text-lg
                                               leading-none shrink-0 transition-colors"
                                    >
                                        &times;
                                    </a>
                                </div>

                                {{-- Controles de cantidad --}}
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('waiter.tables', ['newOrder' => 1, 'table' => $this->selectedTable->id, 'action' => 'dec', 'item' => $productId]) }}"
                                            wire:click.prevent="decrementItem({{ $productId }})"
                                                class="w-7 h-7 rounded-lg bg-[#F5EFE8] hover:bg-[#E8D5C4]
                                                       text-[#2C1A0E] font-bold text-sm flex items-center
                                                       justify-center transition-colors">
                                            −
                                        </a>
                                        <span class="text-[#1F2937] font-bold text-sm w-4 text-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <a
                                            href="{{ route('waiter.tables', ['newOrder' => 1, 'table' => $this->selectedTable->id, 'action' => 'inc', 'item' => $productId]) }}"
                                            wire:click.prevent="incrementItem({{ $productId }})"
                                                class="w-7 h-7 rounded-lg bg-[#F5EFE8] hover:bg-[#E8D5C4]
                                                       text-[#2C1A0E] font-bold text-sm flex items-center
                                                       justify-center transition-colors">
                                            +
                                        </a>
                                    </div>
                                    <span class="text-orange-600 font-bold text-sm">
                                        ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </span>
                                </div>

                                {{-- Notas --}}
                                @if ($editingNoteFor === (int)$productId)
                                    <div x-data="{ n: '{{ addslashes($item['notes']) }}' }" class="mt-2">
                                        <input
                                            x-model="n"
                                            @keydown.enter="$wire.updateNote({{ $productId }}, n)"
                                            @keydown.escape="$wire.set('editingNoteFor', null)"
                                            type="text"
                                            placeholder="Sin cebolla, extra salsa..."
                                            autofocus
                                            class="w-full bg-[#FFFAF7] border border-orange-400 rounded-lg
                                                   px-2.5 py-1.5 text-xs text-[#2C1A0E] focus:outline-none"
                                        >
                                        <p class="text-[10px] text-[#6B7280] mt-0.5">Enter para guardar</p>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        wire:click="$set('editingNoteFor', {{ $productId }})"
                                        class="mt-1.5 text-xs transition-colors
                                               {{ $item['notes']
                                                    ? 'text-orange-500 font-medium'
                                                    : 'text-[#C4A08A] hover:text-orange-500' }}"
                                    >
                                        {{ $item['notes'] ? $item['notes'] : '+ Nota especial' }}
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-[#C4A08A] text-xs py-10">
                                <div class="text-3xl mb-2 flex justify-center text-[#9CA3AF]"><x-icon name="cart" class="w-8 h-8"/></div>
                                Toca un producto para añadirlo
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer del carrito --}}
                    <div class="p-4 border-t border-gray-200 bg-white shrink-0">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[#4b5563] text-sm font-medium">Total:</span>
                            <span class="font-display text-2xl font-bold text-orange-600">
                                ${{ number_format($this->cartTotal, 2) }}
                            </span>
                        </div>
                        <a
                            href="{{ route('waiter.tables', ['newOrder' => 1, 'table' => $this->selectedTable->id, 'action' => 'submit']) }}"
                            wire:click.prevent="submitOrder"
                            wire:loading.attr="aria-disabled"
                            wire:target="submitOrder"
                            @class([
                                'w-full bg-[#ea7a00] hover:bg-[#d86f00] text-white font-bold py-4 rounded-xl transition-all text-base tracking-wide shadow-sm active:scale-95 text-center block',
                                'bg-[#d1d5db] cursor-not-allowed pointer-events-none' => empty($cart),
                            ])
                            aria-disabled="{{ empty($cart) ? 'true' : 'false' }}"
                        >
                            <span wire:loading.remove wire:target="submitOrder">
                                Enviar a Cocina
                            </span>
                            <span wire:loading wire:target="submitOrder">
                                Enviando...
                            </span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════
         MODAL DE CUENTA / CIERRE
    ══════════════════════════════════════════════════════════ --}}
    @if ($showBillModal && $this->billOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm"
                  wire:click="closeBillModal"></div>

            <div class="relative z-10 bg-[#FFF8F2] border border-[#E8D5C4]
                        rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">

                <div class="bg-[#2C1A0E] px-5 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg text-[#FFF5EE] font-bold">
                            Cuenta — Mesa {{ $this->billOrder->table->number }}
                        </h3>
                        <p class="text-xs text-[#9B7B5E]">Pedido #{{ $this->billOrder->id }}</p>
                    </div>
                    <button type="button" wire:click="closeBillModal"
                            class="text-[#9B7B5E] hover:text-white text-3xl leading-none">
                        &times;
                    </button>
                </div>

                {{-- Desglose --}}
                <div class="p-5 space-y-2 max-h-64 overflow-y-auto">
                    @foreach ($this->billOrder->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <span class="text-[#2C1A0E] font-medium">
                                    {{ $item->quantity }}× {{ $item->product->name }}
                                </span>
                                @if ($item->notes)
                                    <span class="text-[#9B7B5E] text-xs block">
                                        <span class="inline-flex items-center gap-1"><x-icon name="warning" class="w-3 h-3"/>{{ $item->notes }}</span>
                                    </span>
                                @endif
                            </div>
                            <span class="text-[#2C1A0E] font-bold">
                                ${{ number_format($item->subtotal, 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-[#E8D5C4] px-5 py-4">
                    <div class="flex items-center justify-between">
                            <span class="text-[#6B7280] font-medium">Total a cobrar</span>
                        <span class="font-display text-3xl font-bold text-orange-600">
                            ${{ number_format($this->billOrder->total, 2) }}
                        </span>
                    </div>
                </div>

                <div class="px-5 pb-5 flex gap-3">
                    <button type="button" wire:click="closeBillModal"
                            class="flex-1 bg-[#F5EFE8] hover:bg-[#E8D5C4] text-[#5C3D26]
                                   font-semibold py-3 rounded-xl transition-colors text-sm">
                        Cancelar
                    </button>
                    @php
                        $canMarkDelivered = $this->billOrder->status === 'listo';
                        $alreadyDelivered = $this->billOrder->status === 'entregado';
                    @endphp
                    <button
                        type="button"
                        wire:click="markDelivered"
                        @disabled(! $canMarkDelivered || $alreadyDelivered)
                        class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white
                               font-bold py-3 rounded-xl transition-colors text-sm"
                    >
                        @if ($alreadyDelivered)
                            Entregado
                        @elseif ($canMarkDelivered)
                            Entregar y Cobrar
                        @else
                            Esperando cocina
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
