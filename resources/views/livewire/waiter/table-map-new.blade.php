<div class="p-4 sm:p-5 min-h-screen"
    @if (!$showOrderModal && !$showBillModal)
        wire:poll.5s="refreshTables"
    @endif>

    {{-- ── FLASH MESSAGES ── --}}
    @if (session('success'))
        <div class="fixed top-5 right-5 z-50 bg-green-500 text-white px-5 py-3.5 rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3500)"
             x-transition:leave="transition ease-in duration-300">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- STATS --}}
    @php
        $freeCount = $this->tables->where('status', 'libre')->count();
        $occupiedCount = $this->tables->where('status', 'ocupada')->count();
        $readyCount = $this->tables->where('status', 'pedido_listo')->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Mesas Libres</p>
            <p class="text-2xl font-black text-emerald-600">{{ $freeCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Ocupadas</p>
            <p class="text-2xl font-black text-copper-500">{{ $occupiedCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <p class="text-[11px] text-gray-500 font-semibold">Pedidos Listos</p>
            <p class="text-2xl font-black text-wine-500">{{ $readyCount }}</p>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="font-display text-3xl text-[#1F2937] font-bold">Mapa de Mesas</h1>
            <p class="text-[#6B7280] text-sm">Toca una mesa para gestionar el pedido</p>
        </div>
        <button
            type="button"
            wire:click="startNewOrder"
            class="bg-[#f59e0b] hover:bg-[#ea8c00] text-white rounded-xl px-4 py-2.5 font-bold text-sm flex items-center gap-2">
            🛒 Nuevo Pedido
        </button>
    </div>

    {{-- TABLE PICKER MODAL --}}
    @if ($showTablePickerModal)
        <div class="fixed inset-0 z-40 bg-black/75 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl">
                <h2 class="text-2xl font-bold mb-4">Elegir Mesa</h2>

                @if ($this->tables->isEmpty())
                    <p class="text-red-600">No hay mesas disponibles</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        @foreach ($this->tables as $table)
                            <button
                                type="button"
                                wire:click="selectTableForOrder({{ $table->id }})"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 rounded-lg">
                                Mesa {{ $table->number }} ({{ $table->capacity }} personas)
                            </button>
                        @endforeach
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="closeTablePickerModal"
                    class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    {{-- MESAS GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @foreach ($this->tables as $table)
            @php
                $colors = [
                    'libre' => ['bg' => 'bg-green-500', 'label' => 'Libre'],
                    'ocupada' => ['bg' => 'bg-copper-500', 'label' => 'Ocupada'],
                    'pedido_listo' => ['bg' => 'bg-wine-500', 'label' => 'Listo'],
                ];
                $config = $colors[$table->status] ?? $colors['libre'];
            @endphp

            <button
                type="button"
                wire:click="openOrderModal({{ $table->id }})"
                class="rounded-xl border-2 border-gray-300 p-4 text-center text-white font-bold {{ $config['bg'] }} hover:shadow-lg transition-all">
                <div class="text-4xl font-display font-black mb-1">{{ $table->number }}</div>
                <div class="text-xs mb-1">{{ $table->capacity }} personas</div>
                <div class="text-xs opacity-90">{{ $config['label'] }}</div>
            </button>
        @endforeach
    </div>

    {{-- ORDER MODAL --}}
    @if ($showOrderModal && $this->selectedTable)
        <div class="fixed inset-0 z-40 bg-black/75 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl">
                <div class="flex items-center justify-between p-6 border-b">
                    <div>
                        <h2 class="text-2xl font-bold">Nuevo Pedido</h2>
                        <p class="text-gray-600">Mesa {{ $this->selectedTable->number }}</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeOrderModal"
                        class="text-3xl text-gray-400 hover:text-gray-600">×</button>
                </div>

                <div class="grid grid-cols-3 gap-4 p-6 max-h-96 overflow-y-auto">
                    {{-- PRODUCTOS --}}
                    <div class="col-span-2">
                        <h3 class="font-bold mb-3">Productos ({{ $this->products->count() }})</h3>
                        <div class="space-y-2">
                            @foreach ($this->products as $product)
                                <button
                                    type="button"
                                    wire:click="addToCart({{ $product->id }})"
                                    class="w-full text-left bg-gray-100 hover:bg-blue-100 p-3 rounded border-2 border-gray-300">
                                    <strong>{{ $product->name }}</strong><br>
                                    <span class="text-sm text-gray-600">{{ $product->category->name }}</span>
                                    <span class="float-right font-bold text-copper-600">${{ number_format($product->price, 2) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- CARRITO --}}
                    <div>
                        <h3 class="font-bold mb-3">Carrito ({{ $this->cartCount }})</h3>
                        <div class="space-y-2 mb-4">
                            @foreach ($cart as $productId => $item)
                                <div class="bg-green-100 p-2 rounded">
                                    <strong class="text-sm">{{ $item['name'] }}</strong><br>
                                    <span class="text-xs text-gray-600">{{ $item['quantity'] }}x @ ${{ number_format($item['price'], 2) }}</span>
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $productId }})"
                                        class="float-right text-red-500 hover:text-red-700 font-bold">×</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-blue-50 p-3 rounded border-2 border-blue-200">
                            <p class="text-lg font-bold">Total: ${{ number_format($this->cartTotal, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 p-6 border-t bg-gray-50">
                    <button
                        type="button"
                        wire:click="submitOrder"
                        @disabled(empty($cart))
                        class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 rounded">
                        Enviar a Cocina
                    </button>
                    <button
                        type="button"
                        wire:click="closeOrderModal"
                        class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 rounded">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
