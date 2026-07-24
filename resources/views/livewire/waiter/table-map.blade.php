<div class="p-4 sm:p-5 min-h-screen"
    @if (!$showOrderModal && !$showBillModal)
        wire:poll.5s="refreshTables"
    @endif>

    {{-- ── FLASH MESSAGES ── --}}
    @if (session('success'))
        <div class="fixed top-5 right-5 z-50 bg-olive-500 text-white px-5 py-3.5 rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3500)"
             x-transition:leave="transition ease-in duration-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── NOTIFICACION DE ITEMS LISTOS ── --}}
    @if ($showReadyNotification)
        <div class="fixed top-24 right-5 z-50 bg-copper-500 text-patrona-950 px-6 py-4 rounded-xl shadow-2xl font-bold text-base flex items-center gap-3"
             wire:key="notification-{{ $readyOrderId }}">
            <div>
                <p class="font-bold">LISTO</p>
                <p class="text-sm">{{ $readyItemName }}</p>
                <p class="text-xs font-normal opacity-80">Mesa #{{ $readyTableNumber }} - Orden #{{ $readyOrderId }}</p>
            </div>
            <button
                type="button"
                wire:click="closeNotification"
                class="ml-2 text-xl hover:opacity-70 transition-opacity">&times;</button>
        </div>
    @endif

    {{-- STATS --}}
    @php
        $freeCount = $this->tables->where('status', 'libre')->count();
        $occupiedCount = $this->tables->where('status', 'ocupada')->count();
        $readyCount = $this->tables->where('status', 'pedido_listo')->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-3">
            <p class="text-[11px] text-patrona-400 font-semibold uppercase tracking-wider">Libres</p>
            <p class="text-2xl font-black text-olive-400">{{ $freeCount }}</p>
        </div>
        <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-3">
            <p class="text-[11px] text-patrona-400 font-semibold uppercase tracking-wider">Ocupadas</p>
            <p class="text-2xl font-black text-copper-400">{{ $occupiedCount }}</p>
        </div>
        <div class="bg-patrona-800 border border-patrona-600/50 rounded-xl p-3">
            <p class="text-[11px] text-patrona-400 font-semibold uppercase tracking-wider">Pedidos Listos</p>
            <p class="text-2xl font-black text-wine-400">{{ $readyCount }}</p>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="font-display text-3xl text-patrona-50 font-bold">Mapa de Mesas</h1>
            <p class="text-patrona-400 text-sm">Toca una mesa para gestionar el pedido</p>
        </div>
        <button
            type="button"
            wire:click="startNewOrder"
            class="bg-copper-500 hover:bg-copper-400 text-patrona-950 rounded-xl px-4 py-2.5 font-bold text-sm transition-all duration-200">
            Nuevo Pedido
        </button>
    </div>

    {{-- TABLE PICKER MODAL --}}
    @if ($showTablePickerModal)
        <div class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-patrona-800 border border-patrona-600/50 rounded-2xl max-w-2xl w-full p-6 shadow-2xl">
                <h2 class="text-2xl font-bold text-patrona-50 mb-4">Elegir Mesa</h2>

                @if ($this->tables->isEmpty())
                    <p class="text-wine-400">No hay mesas disponibles</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        @foreach ($this->tables as $table)
                            <button
                                type="button"
                                wire:click="selectTableForOrder({{ $table->id }})"
                                class="bg-copper-500 hover:bg-copper-400 text-patrona-950 font-bold py-4 rounded-lg transition-all duration-200">
                                Mesa {{ $table->number }} ({{ $table->capacity }})
                            </button>
                        @endforeach
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="closeTablePickerModal"
                    class="w-full bg-patrona-600 hover:bg-patrona-500 text-patrona-200 font-bold py-2 rounded-lg transition-all duration-200">
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
                    'libre'        => ['bg' => 'bg-olive-500',   'label' => 'Libre'],
                    'ocupada'      => ['bg' => 'bg-wine-500',    'label' => 'Ocupada'],
                    'pedido_listo' => ['bg' => 'bg-copper-500',  'label' => 'Pedido Listo'],
                    'entregado'    => ['bg' => 'bg-patrona-500', 'label' => 'Entregado'],
                ];
                $config = $colors[$table->status] ?? $colors['libre'];
            @endphp

            <div class="rounded-xl border border-patrona-600/50 overflow-hidden bg-patrona-800 shadow-lg" wire:key="table-{{ $table->id }}">
                {{-- Mesa principal --}}
                <button
                    type="button"
                    wire:click="openOrderModal({{ $table->id }})"
                    class="w-full p-4 text-center text-white font-bold {{ $config['bg'] }} hover:shadow-lg transition-all duration-200"
                >
                    <div class="text-4xl font-display font-black mb-1">{{ $table->number }}</div>
                    <div class="text-xs mb-2 opacity-80">{{ $table->capacity }} personas</div>
                    <div class="text-sm">{{ $config['label'] }}</div>
                </button>

                {{-- Botones de estado --}}
                <div class="grid grid-cols-2 gap-1 p-2 bg-patrona-700/50 border-t border-patrona-600/30">
                    <button
                        type="button"
                        wire:click="changeTableStatus({{ $table->id }}, 'libre')"
                        class="text-xs py-1 px-2 rounded font-bold transition-all duration-200
                               {{ $table->status === 'libre'
                                    ? 'bg-olive-500 text-white'
                                    : 'bg-patrona-600 text-patrona-300 hover:bg-olive-500/20' }}"
                    >
                        Libre
                    </button>

                    <button
                        type="button"
                        wire:click="changeTableStatus({{ $table->id }}, 'ocupada')"
                        class="text-xs py-1 px-2 rounded font-bold transition-all duration-200
                               {{ $table->status === 'ocupada'
                                    ? 'bg-wine-500 text-white'
                                    : 'bg-patrona-600 text-patrona-300 hover:bg-wine-500/20' }}"
                    >
                        Ocupada
                    </button>

                    <button
                        type="button"
                        wire:click="changeTableStatus({{ $table->id }}, 'pedido_listo')"
                        class="text-xs py-1 px-2 rounded font-bold transition-all duration-200
                               {{ $table->status === 'pedido_listo'
                                    ? 'bg-copper-500 text-patrona-950'
                                    : 'bg-patrona-600 text-patrona-300 hover:bg-copper-500/20' }}"
                    >
                        Listo
                    </button>

                    <button
                        type="button"
                        wire:click="changeTableStatus({{ $table->id }}, 'entregado')"
                        class="text-xs py-1 px-2 rounded font-bold transition-all duration-200
                               {{ $table->status === 'entregado'
                                    ? 'bg-patrona-400 text-white'
                                    : 'bg-patrona-600 text-patrona-300 hover:bg-patrona-400/20' }}"
                    >
                        Entregado
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ORDER MODAL --}}
    @if ($showOrderModal && $this->selectedTable)
        <div class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-patrona-800 border border-patrona-600/50 rounded-2xl w-full max-w-4xl shadow-2xl">
                <div class="flex items-center justify-between p-6 border-b border-patrona-600/30">
                    <div>
                        <h2 class="text-2xl font-bold text-patrona-50">Nuevo Pedido</h2>
                        <p class="text-patrona-400">Mesa {{ $this->selectedTable->number }}</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeOrderModal"
                        class="text-patrona-400 hover:text-patrona-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-4 p-6 max-h-96 overflow-y-auto">
                    {{-- PRODUCTOS --}}
                    <div class="col-span-2">
                        <h3 class="font-bold text-patrona-100 mb-3">Productos ({{ $this->products->count() }})</h3>
                        <div class="space-y-2">
                            @foreach ($this->products as $product)
                                <button
                                    type="button"
                                    wire:click="addToCart({{ $product->id }})"
                                    class="w-full text-left bg-patrona-700 hover:bg-copper-500/10 p-3 rounded-lg border border-patrona-600/40 hover:border-copper-500/30 transition-all duration-200">
                                    <strong class="text-patrona-100">{{ $product->name }}</strong><br>
                                    <span class="text-sm text-patrona-400">{{ $product->category->name }}</span>
                                    <span class="float-right font-bold text-copper-400">${{ number_format($product->price, 2) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- CARRITO --}}
                    <div>
                        <h3 class="font-bold text-patrona-100 mb-3">Carrito ({{ $this->cartCount }})</h3>
                        <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                            @foreach ($cart as $productId => $item)
                                <div class="bg-olive-500/10 p-3 rounded-lg border border-olive-500/20">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex-1">
                                            <strong class="text-sm block text-patrona-100">{{ $item['name'] }}</strong>
                                            <span class="text-xs text-patrona-400">${{ number_format($item['price'], 2) }}</span>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="removeItem({{ $productId }})"
                                            class="text-wine-400 hover:text-wine-500 font-bold text-lg transition-colors">&times;</button>
                                    </div>

                                    <div class="flex items-center gap-1.5 mb-2 bg-patrona-700 rounded p-1.5 border border-patrona-600/40">
                                        <button
                                            type="button"
                                            wire:click="decrementItem({{ $productId }})"
                                            class="bg-wine-500 hover:bg-wine-400 text-white font-bold w-5 h-5 rounded flex items-center justify-center text-xs transition-colors">
                                            &minus;
                                        </button>
                                        <span class="flex-1 text-center font-bold text-sm text-patrona-100">{{ $item['quantity'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="incrementItem({{ $productId }})"
                                            class="bg-olive-500 hover:bg-olive-400 text-white font-bold w-5 h-5 rounded flex items-center justify-center text-xs transition-colors">
                                            +
                                        </button>
                                    </div>

                                    <span class="block text-sm font-bold text-copper-400 mb-2 text-center">
                                        ${{ number_format($item['quantity'] * $item['price'], 2) }}
                                    </span>

                                    <input
                                        type="text"
                                        wire:model="cart.{{ $productId }}.notes"
                                        placeholder="Sin cilantro, con cebolla..."
                                        class="w-full text-xs border border-patrona-600/40 rounded px-2 py-1 bg-patrona-700 text-patrona-100 placeholder-patrona-500 focus:outline-none focus:border-copper-500/50 transition-colors">
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-copper-500/10 p-3 rounded-lg border border-copper-500/20">
                            <p class="text-lg font-bold text-copper-400">Total: ${{ number_format($this->cartTotal, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 p-6 border-t border-patrona-600/30 bg-patrona-700/30">
                    <button
                        type="button"
                        wire:click="submitOrder"
                        @disabled(empty($cart))
                        class="flex-1 bg-olive-500 hover:bg-olive-400 disabled:bg-patrona-500 text-white font-bold py-3 rounded-lg transition-all duration-200">
                        Enviar a Cocina
                    </button>
                    <button
                        type="button"
                        wire:click="closeOrderModal"
                        class="flex-1 bg-patrona-600 hover:bg-patrona-500 text-patrona-200 font-bold py-3 rounded-lg transition-all duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- DELIVERY MODAL --}}
    @if ($showDeliveryModal && $this->deliveryOrder)
        <div class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-patrona-800 border border-patrona-600/50 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="flex items-center justify-between p-6 border-b border-patrona-600/30 bg-copper-500/5">
                    <div>
                        <h2 class="text-2xl font-bold text-copper-400">Pedido Listo</h2>
                        <p class="text-patrona-400 text-sm">Entregar comida al cliente</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeDeliveryModal"
                        class="text-patrona-400 hover:text-patrona-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="bg-copper-500/10 border border-copper-500/20 rounded-lg p-4 mb-6">
                        <p class="text-sm text-patrona-400 mb-1">Mesa</p>
                        <p class="text-4xl font-display font-black text-copper-400">{{ $this->deliveryOrder->table->number }}</p>
                    </div>

                    <h3 class="font-bold text-patrona-100 mb-3">Productos:</h3>
                    <div class="space-y-2 mb-6 max-h-48 overflow-y-auto">
                        @forelse ($this->deliveryOrder->items as $item)
                            <div class="bg-patrona-700/50 p-3 rounded-lg flex justify-between border border-patrona-600/30">
                                <div>
                                    <strong class="block text-patrona-100">{{ $item->product->name }}</strong>
                                    <span class="text-xs text-patrona-400">x{{ $item->quantity }}</span>
                                    @if ($item->notes)
                                        <p class="text-xs text-copper-400 italic mt-1">{{ $item->notes }}</p>
                                    @endif
                                </div>
                                <span class="font-bold text-copper-400">${{ number_format($item->subtotal, 2) }}</span>
                            </div>
                        @empty
                            <p class="text-patrona-400 text-sm text-center py-3">Sin productos</p>
                        @endforelse
                    </div>

                    <div class="bg-olive-500/10 border border-olive-500/20 rounded-lg p-3 mb-6">
                        <p class="text-sm text-patrona-400">Total</p>
                        <p class="text-3xl font-bold text-olive-400">${{ number_format($this->deliveryOrder->total, 2) }}</p>
                    </div>
                </div>

                <div class="flex gap-2 p-6 border-t border-patrona-600/30 bg-patrona-700/30">
                    <button
                        type="button"
                        wire:click="markDelivered"
                        class="flex-1 bg-olive-500 hover:bg-olive-400 text-white font-bold py-3 rounded-lg text-lg transition-all duration-200">
                        Entregar Comida
                    </button>
                    <button
                        type="button"
                        wire:click="closeDeliveryModal"
                        class="flex-1 bg-patrona-600 hover:bg-patrona-500 text-patrona-200 font-bold py-3 rounded-lg transition-all duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- PAYMENT MODAL --}}
    @if ($showPaymentModal && $this->paymentOrder)
        <div class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-patrona-800 border border-patrona-600/50 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="flex items-center justify-between p-6 border-b border-patrona-600/30 bg-olive-500/5">
                    <div>
                        <h2 class="text-2xl font-bold text-olive-400">Cobrar Cuenta</h2>
                        <p class="text-patrona-400 text-sm">Mesa {{ $this->paymentOrder->table->number }}</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closePaymentModal"
                        class="text-patrona-400 hover:text-patrona-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="bg-copper-500/10 border border-copper-500/20 rounded-lg p-4 mb-6">
                        <p class="text-sm text-patrona-400 mb-1">Total a Pagar</p>
                        <p class="text-4xl font-bold text-copper-400">${{ number_format($this->totalAmount, 2) }}</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-patrona-200 mb-2">Metodo de Pago:</label>
                        <select
                            wire:model="paymentMethod"
                            class="w-full border border-patrona-600 rounded-lg px-3 py-2 font-medium bg-patrona-700 text-patrona-100 focus:outline-none focus:border-copper-500/50">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    @if ($paymentMethod === 'efectivo')
                        <div class="mb-6 space-y-3 p-4 bg-copper-500/5 border border-copper-500/20 rounded-lg">
                            <div>
                                <label class="block text-sm font-bold text-patrona-200 mb-1">Monto Dado ($):</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model.live="amountGiven"
                                    placeholder="Ingresa el monto"
                                    class="w-full border border-patrona-600 rounded-lg px-3 py-2 font-bold text-lg bg-patrona-700 text-patrona-100 focus:outline-none focus:border-copper-500/50">
                            </div>

                            @if ($amountGiven > $this->totalAmount)
                                <div class="bg-olive-500/10 border border-olive-500/20 rounded p-3">
                                    <p class="text-sm text-patrona-400 mb-1">Cambio:</p>
                                    <p class="text-2xl font-bold text-olive-400">${{ number_format($this->getChange(), 2) }}</p>
                                </div>
                            @elseif ($amountGiven > 0 && $amountGiven < $this->totalAmount)
                                <div class="bg-wine-500/10 border border-wine-500/20 rounded p-3">
                                    <p class="text-sm text-wine-400">Falta: ${{ number_format($this->totalAmount - $amountGiven, 2) }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-patrona-200 mb-2">Notas:</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            placeholder="Sin cambio, etc."
                            class="w-full border border-patrona-600 rounded-lg px-3 py-2 text-sm bg-patrona-700 text-patrona-100 placeholder-patrona-500 focus:outline-none focus:border-copper-500/50"></textarea>
                    </div>

                    @if (!empty($this->payments))
                        <div class="mb-6 p-4 bg-olive-500/5 rounded-lg border border-olive-500/20">
                            <h3 class="font-bold text-patrona-100 mb-3">Pago Registrado</h3>
                            @foreach ($this->payments as $payment)
                                <div class="text-sm space-y-1">
                                    <p class="text-patrona-200"><strong>${{ number_format($payment['amount'], 2) }}</strong> - {{ ucfirst($payment['method']) }}</p>
                                    @if ($payment['method'] === 'efectivo')
                                        <p class="text-patrona-400">Dado: ${{ number_format($payment['given'], 2) }}</p>
                                        <p class="text-olive-400 font-bold">Cambio: ${{ number_format($payment['change'], 2) }}</p>
                                    @endif
                                    @if (!empty($payment['notes']))
                                        <p class="text-patrona-400 italic">{{ $payment['notes'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 p-6 border-t border-patrona-600/30 bg-patrona-700/30">
                    @if (empty($this->payments))
                        <button
                            type="button"
                            wire:click="addPayment"
                            @disabled($paymentMethod === 'efectivo' && $amountGiven < $this->totalAmount)
                            class="flex-1 bg-olive-500 hover:bg-olive-400 disabled:bg-patrona-500 text-white font-bold py-3 rounded-lg transition-all duration-200">
                            Registrar Pago
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="submitPayment"
                            class="flex-1 bg-copper-500 hover:bg-copper-400 text-patrona-950 font-bold py-3 rounded-lg transition-all duration-200">
                            Cobrar
                        </button>
                        <button
                            type="button"
                            wire:click="removePayment(0)"
                            class="flex-1 bg-wine-500 hover:bg-wine-400 text-white font-bold py-3 rounded-lg transition-all duration-200">
                            Limpiar
                        </button>
                    @endif
                    <button
                        type="button"
                        wire:click="closePaymentModal"
                        class="flex-1 bg-patrona-600 hover:bg-patrona-500 text-patrona-200 font-bold py-3 rounded-lg transition-all duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
