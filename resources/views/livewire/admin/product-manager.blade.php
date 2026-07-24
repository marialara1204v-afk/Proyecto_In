<div class="p-6">

    {{-- Flash --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 z-50 bg-green-500 text-white px-5 py-3 rounded-xl
                    shadow-lg font-semibold text-sm">
            <span class="inline-flex items-center gap-1"><x-icon name="check" class="w-4 h-4"/>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-white">Inventario de Productos</h2>
            <p class="text-patrona-500 text-sm">{{ $this->products->count() }} productos registrados</p>
        </div>
        <button
            wire:click="openCreate"
            class="bg-copper-500 hover:bg-copper-400 text-white font-bold
                   px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2"
        >
            + Nuevo Producto
        </button>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input
            wire:model.live.debounce.200ms="search"
            type="text"
                 placeholder="Buscar producto..."
            class="flex-1 bg-patrona-900 border border-patrona-700 rounded-xl px-4 py-2.5
                   text-white placeholder-patrona-600 text-sm focus:outline-none
                   focus:border-copper-500"
        >
        <select
            wire:model.live="filterCategory"
            class="bg-patrona-900 border border-patrona-700 rounded-xl px-4 py-2.5
                   text-white text-sm focus:outline-none focus:border-copper-500"
        >
            <option value="">Todas las categorías</option>
            @foreach ($this->categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla de Productos --}}
    <div class="bg-patrona-900 border border-patrona-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-patrona-800">
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold">Producto</th>
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold hidden md:table-cell">Categoría</th>
                    <th class="text-right px-5 py-3 text-patrona-500 font-semibold">Precio</th>
                    <th class="text-center px-5 py-3 text-patrona-500 font-semibold">Estado</th>
                    <th class="text-right px-5 py-3 text-patrona-500 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-patrona-800">
                @forelse ($this->products as $product)
                    <tr class="hover:bg-patrona-800/50 transition-colors" wire:key="product-{{ $product->id }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}"
                                         class="w-10 h-10 rounded-lg object-cover shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-patrona-800 flex items-center
                                                justify-center text-lg shrink-0">
                                        <x-icon name="kitchen" class="w-5 h-5 text-patrona-400"/>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-white">{{ $product->name }}</p>
                                    @if ($product->description)
                                        <p class="text-patrona-600 text-xs truncate max-w-xs">
                                            {{ $product->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="text-patrona-400 text-xs">
                                {{ $product->category->icon }} {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <span class="text-copper-400 font-bold">
                                ${{ number_format($product->price, 2) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button
                                wire:click="toggleAvailability({{ $product->id }})"
                                class="px-3 py-1 rounded-full text-xs font-bold transition-colors
                                       {{ $product->is_available
                                            ? 'bg-green-500/20 text-green-400 hover:bg-wine-500/20 hover:text-wine-400'
                                            : 'bg-wine-500/20 text-wine-400 hover:bg-green-500/20 hover:text-green-400' }}"
                            >
                                {{ $product->is_available ? 'Disponible' : 'Agotado' }}
                            </button>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="openEdit({{ $product->id }})"
                                    class="text-patrona-500 hover:text-copper-400 transition-colors p-1"
                                    title="Editar"
                                ><x-icon name="edit" class="w-4 h-4"/></button>
                                <button
                                    wire:click="delete({{ $product->id }})"
                                    wire:confirm="¿Eliminar '{{ $product->name }}'? Esta acción no se puede deshacer."
                                    class="text-patrona-500 hover:text-wine-400 transition-colors p-1"
                                    title="Eliminar"
                                ><x-icon name="trash" class="w-4 h-4"/></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-patrona-600 py-12">
                            No hay productos registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══ MODAL FORMULARIO ══ --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                 wire:click="closeForm"></div>

            <div class="relative z-10 bg-patrona-900 border border-patrona-700 rounded-2xl
                        w-full max-w-lg shadow-2xl overflow-hidden">

                <div class="p-5 border-b border-patrona-800 flex items-center justify-between">
                    <h3 class="font-bold text-white text-lg">
                        {{ $editingId ? 'Editar Producto' : 'Nuevo Producto' }}
                    </h3>
                    <button wire:click="closeForm"
                            class="text-patrona-500 hover:text-white text-2xl">&times;</button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Nombre *
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                   text-white focus:outline-none focus:border-copper-500 text-sm"
                            placeholder="Ej: Tacos de Carnitas"
                        >
                        @error('name')
                            <p class="text-wine-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Categoría + Precio --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">
                                Categoría *
                            </label>
                            <select
                                wire:model="category_id"
                                class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                       text-white focus:outline-none focus:border-copper-500 text-sm"
                            >
                                <option value="">Seleccionar...</option>
                                @foreach ($this->categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->icon }} {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-wine-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">
                                Precio *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2
                                             text-patrona-500 font-bold">$</span>
                                <input
                                    wire:model="price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full bg-patrona-800 border border-patrona-700 rounded-xl
                                           pl-7 pr-4 py-2.5 text-white focus:outline-none
                                           focus:border-copper-500 text-sm"
                                    placeholder="0.00"
                                >
                            </div>
                            @error('price')
                                <p class="text-wine-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Descripción
                        </label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                   text-white focus:outline-none focus:border-copper-500 text-sm resize-none"
                            placeholder="Descripción opcional..."
                        ></textarea>
                    </div>

                    {{-- Alérgenos --}}
                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Alérgenos / Etiquetas del menú
                        </label>
                        <input
                            wire:model="allergens"
                            type="text"
                            class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                   text-white focus:outline-none focus:border-copper-500 text-sm"
                            placeholder="Ej: gluten, lácteos, picante"
                        >
                        <p class="text-patrona-600 text-xs mt-1">
                            Separados por coma para mostrarlos en el menú QR.
                        </p>
                    </div>

                    {{-- Imagen --}}
                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Imagen
                        </label>
                        <input
                            wire:model="photo"
                            type="file"
                            accept="image/*"
                            class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                   text-patrona-400 focus:outline-none focus:border-copper-500 text-sm
                                   file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                                   file:bg-copper-500 file:text-white file:text-xs file:font-bold
                                   file:cursor-pointer"
                        >
                        @error('photo')
                            <p class="text-wine-400 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}"
                                 class="mt-2 h-24 rounded-xl object-cover">
                        @endif
                    </div>

                    {{-- Disponible --}}
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            wire:model="is_available"
                            type="checkbox"
                            class="w-5 h-5 rounded accent-copper-500"
                        >
                        <span class="text-sm text-patrona-400">Disponible en el menú</span>
                    </label>
                </div>

                <div class="p-4 border-t border-patrona-800 flex gap-3">
                    <button wire:click="closeForm"
                            class="flex-1 bg-patrona-800 hover:bg-patrona-700 text-patrona-400
                                   font-bold py-3 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="flex-1 bg-copper-500 hover:bg-copper-400 text-white
                               font-bold py-3 rounded-xl transition-colors"
                    >
                        <span wire:loading.remove>
                            {{ $editingId ? 'Guardar Cambios' : 'Crear Producto' }}
                        </span>
                        <span wire:loading>Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>