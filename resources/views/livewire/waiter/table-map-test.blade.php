<div class="p-4 sm:p-5 min-h-screen"
    @if (! $showOrderModal && ! $showBillModal)
        wire:poll.5s="refreshTables"
    @endif
>
    {{-- Test simple --}}
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Mapa de Mesas - TEST</h1>
        
        {{-- Estado del componente --}}
        <div class="mb-4 p-4 bg-blue-50 rounded border border-blue-200">
            <p><strong>showTablePickerModal:</strong> <span class="text-blue-600">{{ $showTablePickerModal ? 'true' : 'false' }}</span></p>
            <p><strong>showOrderModal:</strong> <span class="text-blue-600">{{ $showOrderModal ? 'true' : 'false' }}</span></p>
            <p><strong>selectedTableId:</strong> <span class="text-blue-600">{{ $selectedTableId ?? 'null' }}</span></p>
            <p><strong>Mesas cargadas:</strong> <span class="text-blue-600">{{ $this->tables->count() }}</span></p>
            <p><strong>Categorías activas:</strong> <span class="text-blue-600">{{ $this->categories->count() }}</span></p>
            <p><strong>Productos disponibles:</strong> <span class="text-blue-600">{{ $this->products->count() }}</span></p>
        </div>

        {{-- Botón de prueba --}}
        <button
            type="button"
            wire:click="startNewOrder"
            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg"
        >
            TEST: Nuevo Pedido
        </button>
    </div>

    {{-- Modal de selector de mesas (simplificado) --}}
    @if ($showTablePickerModal)
        <div class="fixed inset-0 z-40 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full p-6">
                <h2 class="text-2xl font-bold mb-4">Elegir Mesa</h2>
                
                @if($this->tables->isEmpty())
                    <p class="text-red-600 font-bold">No hay mesas cargadas</p>
                @else
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach($this->tables as $table)
                            <button
                                type="button"
                                wire:click="selectTableForOrder({{ $table->id }})"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-3 rounded"
                            >
                                Mesa {{ $table->number }} ({{ $table->capacity }} personas)
                            </button>
                        @endforeach
                    </div>
                @endif
                
                <button
                    type="button"
                    wire:click="closeTablePickerModal"
                    class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded w-full"
                >
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    {{-- Modal de nuevo pedido (simplificado) --}}
    @if ($showOrderModal && $this->selectedTable)
        <div class="fixed inset-0 z-40 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full p-6">
                <h2 class="text-2xl font-bold mb-4">Nuevo Pedido - Mesa {{ $this->selectedTable->number }}</h2>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-bold mb-2">Productos ({{ $this->products->count() }})</h3>
                        <div class="space-y-2 max-h-96 overflow-y-auto border p-3 rounded">
                            @foreach($this->products as $product)
                                <button
                                    type="button"
                                    wire:click="addToCart({{ $product->id }})"
                                    class="w-full text-left bg-gray-100 hover:bg-blue-100 p-3 rounded border border-gray-300"
                                >
                                    <strong>{{ $product->name }}</strong> - ${{ number_format($product->price, 2) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold mb-2">Carrito ({{ $this->cartCount }})</h3>
                        <div class="space-y-2 max-h-96 overflow-y-auto border p-3 rounded">
                            @foreach($cart as $productId => $item)
                                <div class="bg-green-100 p-2 rounded flex justify-between items-center">
                                    <div>
                                        <strong>{{ $item['name'] }}</strong><br>
                                        <span class="text-sm text-gray-600">{{ $item['quantity'] }}x @ ${{ number_format($item['price'], 2) }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $productId }})"
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded text-sm"
                                    >
                                        ✕
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                            <p class="text-lg font-bold">Total: ${{ number_format($this->cartTotal, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="submitOrder"
                        @disabled(empty($cart))
                        class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-3 rounded"
                    >
                        Enviar a Cocina
                    </button>
                    <button
                        type="button"
                        wire:click="closeOrderModal"
                        class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 rounded"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
