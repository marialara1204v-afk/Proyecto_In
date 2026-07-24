<div class="p-6">
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 z-50 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm">
            <span class="inline-flex items-center gap-1"><x-icon name="check" class="w-4 h-4"/>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-white">Inventario Básico</h2>
            <p class="text-patrona-500 text-sm">Control de insumos: Comida, Bebida, Verdura</p>
        </div>

        <div class="flex gap-3">
            <button wire:click="openCreate"
                    class="bg-copper-500 hover:bg-copper-400 text-white font-bold px-5 py-2.5 rounded-xl transition-colors">
                + Nuevo Insumo
            </button>
            <a href="{{ route('admin.report') }}"
               class="bg-patrona-900 border border-patrona-700 hover:border-copper-500 text-gray-300 hover:text-white font-bold px-5 py-2.5 rounded-xl transition-colors">
                Ver reporte
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5">
            <div class="text-patrona-500 text-sm">Insumos activos</div>
            <div class="text-3xl font-black text-white mt-1">{{ $this->totalItems }}</div>
        </div>
        <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5">
            <div class="text-patrona-500 text-sm">Stock crítico</div>
            <div class="text-3xl font-black text-wine-400 mt-1">{{ $this->criticalCount }}</div>
        </div>
        <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-5">
            <div class="text-patrona-500 text-sm">Disponible</div>
            <div class="text-3xl font-black text-green-400 mt-1">{{ $this->items->where('is_active', true)->count() }}</div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input wire:model.live.debounce.200ms="search" type="text" placeholder="Buscar insumo..."
               class="flex-1 bg-patrona-900 border border-patrona-700 rounded-xl px-4 py-2.5 text-white placeholder-patrona-600 text-sm focus:outline-none focus:border-copper-500">
        <select wire:model.live="filterCategory"
                class="bg-patrona-900 border border-patrona-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-copper-500">
            <option value="">Todas las categorías</option>
            <option value="comida">Comida</option>
            <option value="bebida">Bebida</option>
            <option value="verdura">Verdura</option>
        </select>
    </div>

    <div class="bg-patrona-900 border border-patrona-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-patrona-800">
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold">Insumo</th>
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold hidden md:table-cell">Categoría</th>
                    <th class="text-right px-5 py-3 text-patrona-500 font-semibold">Stock</th>
                    <th class="text-center px-5 py-3 text-patrona-500 font-semibold">Estado</th>
                    <th class="text-right px-5 py-3 text-patrona-500 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-patrona-800">
                @forelse ($this->items as $item)
                    <tr class="hover:bg-patrona-800/50 transition-colors" wire:key="inventory-{{ $item->id }}">
                        <td class="px-5 py-3">
                            <div class="font-semibold text-white">{{ $item->name }}</div>
                            @if ($item->notes)
                                <div class="text-patrona-600 text-xs truncate max-w-sm">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="text-patrona-400 text-xs capitalize">{{ $item->category }}</span>
                        </td>
                        <td class="px-5 py-3 text-right text-white font-bold">
                            {{ number_format($item->stock, 2) }} {{ $item->unit }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold text-white {{ $item->statusColor() }}">
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openAdjust({{ $item->id }})" class="text-patrona-500 hover:text-copper-400 transition-colors p-1" title="Ajustar">+/-</button>
                                <button wire:click="openEdit({{ $item->id }})" class="text-patrona-500 hover:text-copper-400 transition-colors p-1" title="Editar"><x-icon name="edit" class="w-4 h-4"/></button>
                                <button wire:click="toggleActive({{ $item->id }})" class="text-patrona-500 hover:text-green-400 transition-colors p-1" title="Activar/Desactivar">⏻</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="¿Eliminar '{{ $item->name }}'?" class="text-patrona-500 hover:text-wine-400 transition-colors p-1" title="Eliminar"><x-icon name="trash" class="w-4 h-4"/></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-patrona-600 py-12">No hay insumos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative z-10 bg-patrona-900 border border-patrona-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-patrona-800 flex items-center justify-between">
                    <h3 class="font-bold text-white text-lg">{{ $editingId ? 'Editar Insumo' : 'Nuevo Insumo' }}</h3>
                    <button wire:click="closeForm" class="text-patrona-500 hover:text-white text-2xl">&times;</button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">Nombre *</label>
                        <input wire:model="name" type="text" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm" placeholder="Ej: Harina de maíz">
                        @error('name') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Categoría *</label>
                            <select wire:model="category" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm">
                                <option value="comida">Comida</option>
                                <option value="bebida">Bebida</option>
                                <option value="verdura">Verdura</option>
                            </select>
                            @error('category') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Unidad *</label>
                            <input wire:model="unit" type="text" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm" placeholder="kg / pza / l">
                            @error('unit') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Stock actual *</label>
                            <input wire:model="stock" type="number" step="0.01" min="0" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm">
                            @error('stock') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Stock mínimo *</label>
                            <input wire:model="min_stock" type="number" step="0.01" min="0" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm">
                            @error('min_stock') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="3" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm resize-none" placeholder="Observaciones del insumo..."></textarea>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="is_active" type="checkbox" class="w-5 h-5 rounded accent-copper-500">
                        <span class="text-sm text-patrona-400">Insumo activo</span>
                    </label>
                </div>

                <div class="p-4 border-t border-patrona-800 flex gap-3">
                    <button wire:click="closeForm" class="flex-1 bg-patrona-800 hover:bg-patrona-700 text-patrona-400 font-bold py-3 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="flex-1 bg-copper-500 hover:bg-copper-400 text-white font-bold py-3 rounded-xl transition-colors">
                        <span wire:loading.remove>{{ $editingId ? 'Guardar Cambios' : 'Crear Insumo' }}</span>
                        <span wire:loading>Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showAdjustForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeAdjustForm"></div>
            <div class="relative z-10 bg-patrona-900 border border-patrona-700 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-patrona-800">
                    <h3 class="font-bold text-white text-lg">Ajustar stock</h3>
                    <p class="text-patrona-500 text-sm mt-1">{{ $name }}</p>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Tipo</label>
                            <select wire:model="adjustmentType" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm">
                                <option value="add">Sumar</option>
                                <option value="subtract">Restar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-patrona-400 mb-1">Cantidad</label>
                            <input wire:model="adjustment" type="number" step="0.01" min="0" class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-copper-500 text-sm">
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-patrona-800 flex gap-3">
                    <button wire:click="closeAdjustForm" class="flex-1 bg-patrona-800 hover:bg-patrona-700 text-patrona-400 font-bold py-3 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="applyAdjustment" class="flex-1 bg-green-500 hover:bg-green-400 text-white font-bold py-3 rounded-xl transition-colors">Aplicar</button>
                </div>
            </div>
        </div>
    @endif
</div>