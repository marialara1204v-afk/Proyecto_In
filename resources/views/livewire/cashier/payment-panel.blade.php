<div>
    @if ($showModal && $this->order)
        <div class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">

                {{-- PAGO COMPLETADO --}}
                @if ($paymentSuccess)
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-green-600 mb-1">Pago Completado</h2>
                        <p class="text-gray-600">Cuenta pagada exitosamente</p>
                    </div>

                    <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 mb-1">Monto Pagado</p>
                        <p class="text-4xl font-bold text-green-600">${{ number_format($this->totalWithTip, 2) }}</p>
                    </div>

                    @if ($this->tipAmount > 0)
                        <div class="bg-copper-100 border-2 border-copper-300 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600 mb-1">Propina</p>
                            <p class="text-2xl font-bold text-copper-600">${{ number_format($this->tipAmount, 2) }}</p>
                            @if ($this->splitTip && $this->tipPerWorker > 0)
                                <p class="text-xs text-copper-700 mt-1">
                                    Dividida entre {{ $this->workerCount }} trabajadores: ${{ number_format($this->tipPerWorker, 2) }} c/u
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-2">Metodo de Pago</p>
                        <p class="text-lg font-bold text-blue-600">
                            @switch($paymentMethod)
                                @case('efectivo') Efectivo @break
                                @case('tarjeta') Tarjeta @break
                                @case('transferencia') Transferencia @break
                                @default Otro
                            @endswitch
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <a href="{{ route('ticket.print', $this->order->id) }}"
                           target="_blank"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-4 rounded-lg text-xl transition text-center">
                            Imprimir Ticket
                        </a>
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold px-4 py-4 rounded-lg text-lg transition">
                            Cerrar
                        </button>
                    </div>

                {{-- FORMULARIO DE PAGO --}}
                @else
                    <h2 class="text-2xl font-bold mb-4">Pagar Cuenta</h2>

                    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                        <p class="text-3xl font-bold text-blue-600">${{ number_format($this->totalAmount, 2) }}</p>
                    </div>

                    {{-- Campo de Propina --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Propina (opcional)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">$</span>
                            <input type="number"
                                   wire:model.live="tipAmount"
                                   step="0.01" min="0"
                                   placeholder="0.00"
                                   class="w-full border-2 border-gray-300 rounded-lg pl-8 pr-4 py-2.5 text-lg font-medium focus:border-copper-500 focus:outline-none">
                        </div>

                        {{-- Propinas rapidas --}}
                        <div class="flex gap-2 mt-2">
                            @foreach([20, 50, 100] as $quickTip)
                                <button type="button"
                                        wire:click="$set('tipAmount', {{ $quickTip }})"
                                        class="flex-1 bg-gray-100 hover:bg-copper-100 border border-gray-300 hover:border-copper-400 text-gray-700 hover:text-copper-700 text-xs font-bold py-2 rounded-lg transition">
                                    ${{ $quickTip }}
                                </button>
                            @endforeach
                            <button type="button"
                                    wire:click="$set('tipAmount', 0)"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-500 text-xs font-bold py-2 rounded-lg transition">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    {{-- Dividir propina --}}
                    @if ($this->tipAmount > 0)
                        <div class="mb-4 p-3 bg-copper-100 border border-copper-200 rounded-lg">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="splitTip"
                                       class="w-5 h-5 rounded accent-copper-1000">
                                <span class="text-sm font-semibold text-gray-700">
                                    Dividir propina entre trabajadores
                                </span>
                            </label>

                            @if ($splitTip)
                                <div class="mt-3 flex items-center gap-3">
                                    <label class="text-sm text-gray-600">Trabajadores:</label>
                                    <select wire:model.live="workerCount"
                                            class="border-2 border-gray-300 rounded px-3 py-1.5 text-sm font-medium focus:border-copper-500 focus:outline-none">
                                        @for ($i = 1; $i <= $this->activeWorkersToday; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <span class="text-sm text-gray-500">
                                        (max. {{ $this->activeWorkersToday }})
                                    </span>
                                </div>
                                <div class="mt-2 p-2 bg-white rounded border border-copper-200">
                                    <p class="text-sm text-gray-600">
                                        Propina por trabajador:
                                        <strong class="text-copper-700 text-lg">${{ number_format($this->tipPerWorker, 2) }}</strong>
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Total con propina --}}
                    @if ($this->tipAmount > 0)
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-semibold">Total a pagar:</span>
                                <span class="text-2xl font-bold text-green-700">${{ number_format($this->totalWithTip, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Metodo de Pago --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Metodo de Pago:</label>
                        <select wire:model.live="paymentMethod"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 font-medium focus:border-copper-500 focus:outline-none">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 rounded-xl transition">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="submitPayment"
                            wire:loading.attr="disabled"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition">
                            <span wire:loading.remove>
                                @if ($this->tipAmount > 0)
                                    Pagar ${{ number_format($this->totalWithTip, 2) }}
                                @else
                                    Pagar ${{ number_format($this->totalAmount, 2) }}
                                @endif
                            </span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
