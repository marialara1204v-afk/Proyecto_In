<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-copper-500">
        <h2 class="text-2xl font-bold text-patrona-800 flex items-center gap-3">
            <span class="text-3xl">📱</span>
            Generador de Códigos QR - Menú Digital
        </h2>
        <p class="text-patrona-600 mt-2">
            Genera códigos QR para compartir el menú digital con tus clientes
        </p>
    </div>

    {{-- Opciones de QR --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- QR del Menú Completo --}}
        <button wire:click="generateMenuQR"
                class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 border-2 border-transparent
                       {{ $qrType === 'menu' ? 'border-copper-500 bg-copper-100' : 'border-gray-200 hover:border-copper-300' }}">
            <div class="text-4xl mb-3">📋</div>
            <h3 class="font-bold text-patrona-800 text-lg">Menú Completo</h3>
            <p class="text-sm text-patrona-600 mt-2">Acceso a todas las categorías y productos</p>
        </button>

        {{-- QR por Mesa (Auto-servicio) --}}
        <div class="bg-white rounded-xl shadow-md p-6 border-2 border-gray-200
                    {{ $qrType === 'table' ? 'border-green-500 bg-green-50' : '' }}">
            <div class="text-4xl mb-3">🪑</div>
            <h3 class="font-bold text-patrona-800 text-lg">Auto-servicio por Mesa</h3>
            <p class="text-sm text-patrona-600 mb-2">El cliente ordena directamente desde su teléfono</p>
            <select wire:change="generateTableQR($event.target.value)"
                    class="w-full px-3 py-2 border border-patrona-300 rounded-lg text-sm focus:outline-none focus:border-green-500">
                <option value="">Selecciona una mesa...</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">Mesa {{ $table->number }}</option>
                @endforeach
            </select>
        </div>

        {{-- QR por Categoría --}}
        <div class="bg-white rounded-xl shadow-md p-6 border-2 border-gray-200
                    {{ $qrType === 'category' ? 'border-copper-500 bg-copper-100' : '' }}">
            <div class="text-4xl mb-3">📂</div>
            <h3 class="font-bold text-patrona-800 text-lg">Por Categoría</h3>
            <select wire:change="generateCategoryQR($event.target.value)"
                    class="mt-3 w-full px-3 py-2 border border-patrona-300 rounded-lg text-sm focus:outline-none focus:border-copper-500">
                <option value="">Selecciona una categoría...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- QR por Producto --}}
        <div class="bg-white rounded-xl shadow-md p-6 border-2 border-gray-200
                    {{ $qrType === 'product' ? 'border-copper-500 bg-copper-100' : '' }}">
            <div class="text-4xl mb-3">🍽️</div>
            <h3 class="font-bold text-patrona-800 text-lg">Por Producto</h3>
            <select wire:change="generateProductQR($event.target.value)"
                    class="mt-3 w-full px-3 py-2 border border-patrona-300 rounded-lg text-sm focus:outline-none focus:border-copper-500">
                <option value="">Selecciona un producto...</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Vista previa y descarga --}}
    @if($showPreview && $generatedQRUrl)
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Vista previa del QR --}}
                <div class="flex flex-col items-center justify-center">
                    <h3 class="font-bold text-patrona-800 mb-4">Vista Previa</h3>
                    <div class="bg-gray-100 rounded-lg p-6 flex items-center justify-center min-h-80 w-full max-w-sm">
                        <img src="{{ $generatedQRUrl }}" 
                             alt="QR Code" 
                             class="max-w-full h-auto rounded-lg shadow-md border-4 border-white">
                    </div>
                    <p class="text-xs text-patrona-500 mt-4 text-center">
                        Tamaño: 500x500 px - Resolución de impresión: 300 DPI
                    </p>
                </div>

                {{-- Información y opciones --}}
                <div class="flex flex-col justify-center">
                    <div class="mb-6">
                        <h3 class="font-bold text-patrona-800 text-lg mb-3">Detalles del Código QR</h3>
                        <div class="space-y-2 text-sm">
                            @if($qrType === 'menu')
                                <p><span class="font-semibold text-patrona-700">Tipo:</span> Menú Completo</p>
                                <p><span class="font-semibold text-patrona-700">Destino:</span> Todas las categorías</p>
                                <p><span class="font-semibold text-patrona-700">URL:</span> {{ route('menu.digital') }}</p>
                            @elseif($qrType === 'table')
                                <p><span class="font-semibold text-patrona-700">Tipo:</span> Auto-servicio por Mesa</p>
                                <p><span class="font-semibold text-patrona-700">Mesa:</span> {{ $tables->find($selectedTableId)->number ?? 'N/A' }}</p>
                                <p><span class="font-semibold text-patrona-700">URL:</span> {{ route('menu.selfservice', $tables->find($selectedTableId)->number ?? '') }}</p>
                                <p class="text-green-700 font-medium">✓ El cliente puede ordenar directamente</p>
                            @elseif($qrType === 'category')
                                <p><span class="font-semibold text-patrona-700">Tipo:</span> Categoría</p>
                                <p><span class="font-semibold text-patrona-700">Destino:</span> {{ $categories->find($selectedCategoryId)->name ?? 'N/A' }}</p>
                                <p><span class="font-semibold text-patrona-700">URL:</span> {{ route('menu.category', $selectedCategoryId) }}</p>
                            @elseif($qrType === 'product')
                                <p><span class="font-semibold text-patrona-700">Tipo:</span> Producto</p>
                                <p><span class="font-semibold text-patrona-700">Destino:</span> {{ $products->find($selectedProductId)->name ?? 'N/A' }}</p>
                                <p><span class="font-semibold text-patrona-700">URL:</span> {{ route('menu.product', $selectedProductId) }}</p>
                            @endif
                            <p><span class="font-semibold text-patrona-700">Generado:</span> {{ now()->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>

                    {{-- Opciones de descarga --}}
                    <div class="space-y-3">
                        <a href="{{ $generatedQRUrl }}" 
                           download 
                           class="block text-center px-6 py-3 bg-copper-500 text-white rounded-lg hover:bg-copper-600 
                                  transition font-semibold shadow-md">
                            ⬇️ Descargar Código QR
                        </a>
                        
                        <button wire:click="closePreview"
                                class="w-full px-6 py-3 bg-gray-200 text-patrona-700 rounded-lg hover:bg-patrona-300 
                                       transition font-semibold">
                            ✕ Cerrar Vista Previa
                        </button>
                    </div>

                    {{-- Info de uso --}}
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                            <span>💡</span>
                            Consejos de uso
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Imprime en resolución máxima</li>
                            <li>• Coloca en mesas, paredes o menús</li>
                            <li>• Prueba con tu teléfono antes</li>
                            <li>• Genera nuevos al cambiar menú</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Información general --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <h3 class="font-bold text-green-900 text-lg mb-3 flex items-center gap-2">
                <span>✅</span>
                Ventajas del Menú Digital
            </h3>
            <ul class="text-sm text-green-800 space-y-2">
                <li>✓ Clientes acceden desde sus teléfonos</li>
                <li>✓ Menú siempre actualizado</li>
                <li>✓ Información de precios y alérgenos</li>
                <li>✓ Imágenes de alta calidad</li>
                <li>✓ Fácil de compartir</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <h3 class="font-bold text-purple-900 text-lg mb-3 flex items-center gap-2">
                <span>📍</span>
                Dónde usar los QR
            </h3>
            <ul class="text-sm text-purple-800 space-y-2">
                <li>📌 En cada mesa (menú completo)</li>
                <li>📱 En redes sociales (enlaces a categorías)</li>
                <li>🎯 En promociones (productos específicos)</li>
                <li>🖼️ En paredes y vitrinas</li>
                <li>📧 En correos a clientes</li>
            </ul>
        </div>
    </div>
</div>
