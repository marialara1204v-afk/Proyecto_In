<div class="p-6 min-h-screen" x-data="cashierPanel()" x-init="startAutoRefresh()">

    {{-- Boton Volver al Dashboard --}}
    <div class="mb-4">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="inline-flex items-center gap-2 bg-copper-500 hover:bg-copper-400 text-patrona-950 font-bold py-2 px-4 rounded-xl transition-all duration-200">
            &larr; Volver al Dashboard
        </a>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="fixed top-5 right-5 z-50 bg-olive-500 text-white px-5 py-3.5
                    rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2">
            <x-icon name="check" class="w-4 h-4"/> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="fixed top-5 right-5 z-50 bg-wine-500 text-white px-5 py-3.5
                    rounded-xl shadow-2xl font-semibold text-sm flex items-center gap-2">
            <x-icon name="warning" class="w-4 h-4"/> {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="font-display text-2xl text-patrona-50 font-bold inline-flex items-center gap-2"><x-icon name="card" class="w-6 h-6 text-copper-400"/>Caja</h1>
        <p class="text-patrona-400 text-sm mt-0.5">Selecciona una mesa con cuenta entregada para cerrar la cuenta</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Lista de mesas con cuenta ─── --}}
        <div class="lg:col-span-1">
            <h2 class="text-sm font-bold text-patrona-400 uppercase tracking-wider mb-3">
                Mesas por Cobrar
            </h2>

            @if ($this->tables->isEmpty())
                <div class="bg-patrona-800 rounded-2xl p-8 text-center border border-patrona-600/50">
                    <div class="text-olive-400 mb-2 flex justify-center"><x-icon name="check" class="w-10 h-10"/></div>
                    <p class="text-patrona-400 text-sm">No hay cuentas entregadas pendientes de cobro</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($this->tables as $table)
                        <button
                            wire:click="selectTable({{ $table->id }})"
                            class="w-full text-left rounded-2xl border-2 p-4 transition-all duration-200
                                   {{ $selectedTableId === $table->id
                                        ? 'border-copper-500 bg-copper-500/10'
                                        : 'border-patrona-600/50 bg-patrona-800 hover:border-copper-500/30' }}"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="font-display text-3xl font-bold text-copper-400">
                                        {{ $table->number }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-semibold text-patrona-100">
                                            Mesa {{ $table->number }}
                                        </div>
                                        <div class="text-xs text-patrona-400">
                                            {{ $table->statusLabel() }}
                                        </div>
                                    </div>
                                </div>
                                @if ($table->activeOrder)
                                    <span class="text-lg font-bold text-copper-400">
                                        ${{ number_format(abs($table->activeOrder->total), 2) }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── Detalle de la cuenta ─── --}}
        <div class="lg:col-span-2">
            @if ($this->selectedOrder)
                <div class="bg-patrona-800 rounded-2xl overflow-hidden border border-patrona-600/50">

                    {{-- Header --}}
                    <div class="bg-patrona-700/50 px-6 py-5 border-b border-patrona-600/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="font-display text-xl font-bold text-patrona-50">
                                    Mesa {{ $this->selectedOrder->table->number }}
                                </h2>
                                <p class="text-patrona-400 text-xs mt-0.5">
                                    Atendida por {{ $this->selectedOrder->user->name }}
                                    &middot; {{ $this->selectedOrder->created_at->format('H:i') }}
                                </p>
                            </div>
                            <x-status-badge :status="$this->selectedOrder->status" />
                        </div>
                    </div>

                    {{-- Tabla de productos --}}
                    <div class="p-6">
                        <table class="w-full text-sm mb-6">
                            <thead>
                                <tr class="border-b border-patrona-600/30">
                                    <th class="text-left pb-2 text-patrona-400 font-semibold text-xs uppercase">
                                        Producto
                                    </th>
                                    <th class="text-center pb-2 text-patrona-400 font-semibold text-xs uppercase">
                                        Cant.
                                    </th>
                                    <th class="text-right pb-2 text-patrona-400 font-semibold text-xs uppercase">
                                        Precio
                                    </th>
                                    <th class="text-right pb-2 text-patrona-400 font-semibold text-xs uppercase">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-patrona-600/20">
                                @foreach ($this->selectedOrder->items as $item)
                                    <tr>
                                        <td class="py-2.5">
                                            <div class="font-medium text-patrona-100">
                                                {{ $item->product->name }}
                                            </div>
                                            @if ($item->notes)
                                                <div class="text-xs text-copper-400 mt-0.5">
                                                    {{ $item->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-center text-patrona-300 font-semibold">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="py-2.5 text-right text-patrona-400">
                                            ${{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="py-2.5 text-right font-bold text-patrona-100">
                                            ${{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Total --}}
                        <div class="flex items-center justify-between pt-4
                                    border-t-2 border-copper-500/30 mb-6">
                            <span class="text-patrona-300 font-bold text-lg">Total</span>
                            <span class="font-display text-4xl font-bold text-copper-400">
                                ${{ number_format(abs($this->selectedOrder->total), 2) }}
                            </span>
                        </div>

                        {{-- Boton de confirmacion --}}
                        @if (! $showConfirm)
                            <button
                                wire:click="openPayment"
                                class="w-full bg-olive-500 hover:bg-olive-400 text-white
                                       font-black py-5 rounded-xl transition-all duration-200 text-lg
                                       tracking-wide shadow-lg shadow-olive-500/20 active:scale-95"
                            >
                                Registrar Pago
                            </button>
                        @else
                            <div class="bg-olive-500/10 border-2 border-olive-500/30 rounded-xl p-4 text-center">
                                <p class="text-patrona-100 font-semibold text-sm mb-3">
                                    Confirmar pago de
                                    <strong class="text-olive-400">
                                        ${{ number_format(abs($this->selectedOrder->total), 2) }}
                                    </strong>
                                    de Mesa {{ $this->selectedOrder->table->number }}?
                                </p>
                                <div class="flex gap-3">
                                    <button
                                        wire:click="$set('showConfirm', false)"
                                        class="flex-1 bg-patrona-600 hover:bg-patrona-500 text-patrona-200
                                               font-bold py-3 rounded-xl transition-all duration-200 text-sm"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        wire:click="confirmPayment"
                                        wire:loading.attr="disabled"
                                        class="flex-1 bg-olive-500 hover:bg-olive-400 text-white
                                               font-black py-3 rounded-xl transition-all duration-200 text-sm"
                                    >
                                        <span wire:loading.remove class="inline-flex items-center gap-1"><x-icon name="money" class="w-4 h-4"/>Confirmar Pago</span>
                                        <span wire:loading>Procesando...</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-patrona-800 rounded-2xl p-16 text-center h-full border border-patrona-600/50
                            flex flex-col items-center justify-center">
                    <div class="text-patrona-500 mb-4"><x-icon name="map" class="w-12 h-12"/></div>
                    <h3 class="font-display text-xl text-patrona-100 font-bold mb-2">
                        Selecciona una mesa
                    </h3>
                    <p class="text-patrona-400 text-sm">
                        Elige una mesa de la lista para ver el desglose de su cuenta
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Componente de pago ─── --}}
    @livewire('cashier.payment-panel')

    <script>
        function cashierPanel() {
            return {
                autoRefreshInterval: null,
                startAutoRefresh() {
                    this.autoRefreshInterval = setInterval(() => {
                        Livewire.dispatch('refresh-tables');
                    }, 3000);
                },
                destroy() {
                    if (this.autoRefreshInterval) {
                        clearInterval(this.autoRefreshInterval);
                    }
                }
            };
        }
    </script>

</div>
