<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-white">Registro de Auditoría</h2>
            <p class="text-patrona-500 text-sm">Historial de cambios en el sistema</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="bg-patrona-800 hover:bg-patrona-700 text-gray-300 font-bold px-4 py-2.5 rounded-xl transition-colors text-sm">
            ← Volver
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-patrona-900 border border-patrona-800 rounded-2xl p-4 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            {{-- Tabla --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-patrona-500 mb-1 uppercase tracking-wider">Módulo</label>
                <select wire:model.live="filterTable"
                        class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:border-copper-500">
                    <option value="">Todos los módulos</option>
                    <option value="Order">Órdenes</option>
                    <option value="OrderPayment">Pagos</option>
                    <option value="OrderItem">Items de Orden</option>
                    <option value="InventoryItem">Inventario</option>
                    <option value="User">Usuarios</option>
                    <option value="DailyCashBalance">Cierre de Caja</option>
                    <option value="Product">Productos</option>
                </select>
            </div>

            {{-- Operación --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-patrona-500 mb-1 uppercase tracking-wider">Operación</label>
                <select wire:model.live="filterEvent"
                        class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:border-copper-500">
                    <option value="">Todas</option>
                    <option value="created">Creación</option>
                    <option value="updated">Actualización</option>
                    <option value="deleted">Eliminación</option>
                </select>
            </div>

            {{-- Usuario --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-patrona-500 mb-1 uppercase tracking-wider">Usuario</label>
                <select wire:model.live="filterUser"
                        class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:border-copper-500">
                    <option value="">Todos</option>
                    @foreach ($this->userOptions as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Limpiar --}}
            <button wire:click="clearFilters"
                    class="bg-patrona-800 hover:bg-patrona-700 text-patrona-400 font-bold px-4 py-2.5 rounded-xl text-sm transition-colors">
                Limpiar
            </button>
        </div>
    </div>

    {{-- Tabla de auditoría --}}
    <div class="bg-patrona-900 border border-patrona-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-patrona-800">
                        <th class="text-left px-4 py-3 text-patrona-500 font-semibold">Fecha / Hora</th>
                        <th class="text-left px-4 py-3 text-patrona-500 font-semibold">Usuario</th>
                        <th class="text-center px-4 py-3 text-patrona-500 font-semibold">Operación</th>
                        <th class="text-left px-4 py-3 text-patrona-500 font-semibold">Módulo</th>
                        <th class="text-left px-4 py-3 text-patrona-500 font-semibold">Registro</th>
                        <th class="text-left px-4 py-3 text-patrona-500 font-semibold">Cambios</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-patrona-800">
                    @forelse ($this->logs as $log)
                        <tr class="hover:bg-patrona-800/50 transition-colors">
                            {{-- Fecha/Hora --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-white text-xs font-mono">
                                    {{ $log->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-patrona-500 text-xs font-mono">
                                    {{ $log->created_at->format('H:i:s') }}
                                </div>
                            </td>

                            {{-- Usuario --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-copper-500/20 border border-copper-500/30
                                                flex items-center justify-center text-copper-400 font-bold text-xs">
                                        {{ strtoupper(substr($log->user_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-white text-xs font-semibold">{{ $log->user_name ?? 'Sistema' }}</div>
                                        <div class="text-patrona-500 text-[10px] uppercase">{{ $log->user_role }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Operación --}}
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                             {{ $log->eventColor() }}">
                                    {{ $log->eventLabel() }}
                                </span>
                            </td>

                            {{-- Módulo --}}
                            <td class="px-4 py-3">
                                <span class="text-gray-300 text-xs font-semibold">{{ $log->modelName() }}</span>
                                <span class="text-patrona-600 text-[10px] ml-1">#{{ $log->auditable_id }}</span>
                            </td>

                            {{-- Registro (resumen) --}}
                            <td class="px-4 py-3">
                                @if ($log->event === 'created')
                                    <span class="text-green-400 text-xs">
                                        @foreach ($log->new_values ?? [] as $key => $val)
                                            @if ($loop->first) @break @endif
                                        @endforeach
                                        Nuevo registro creado
                                    </span>
                                @elseif ($log->event === 'deleted')
                                    <span class="text-wine-400 text-xs">Registro eliminado</span>
                                @else
                                    <span class="text-yellow-400 text-xs">
                                        {{ count($log->old_values ?? []) }} campo(s) modificado(s)
                                    </span>
                                @endif
                            </td>

                            {{-- Cambios --}}
                            <td class="px-4 py-3 max-w-xs">
                                @if ($log->event === 'updated' && $log->old_values)
                                    <div class="space-y-0.5">
                                        @foreach ($log->old_values as $field => $oldVal)
                                            @php
                                                $newVal = $log->new_values[$field] ?? '?';
                                                $fieldLabel = \App\Models\AuditLog::translateField($field);
                                            @endphp
                                            <div class="text-[11px] leading-tight">
                                                <span class="text-patrona-500">{{ $fieldLabel }}:</span>
                                                <span class="text-wine-400 line-through">{{ is_bool($oldVal) ? ($oldVal ? 'Sí' : 'No') : $oldVal }}</span>
                                                <span class="text-patrona-600">→</span>
                                                <span class="text-green-400 font-semibold">{{ is_bool($newVal) ? ($newVal ? 'Sí' : 'No') : $newVal }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($log->event === 'created' && $log->new_values)
                                    <div class="text-[11px] text-patrona-500">
                                        @foreach ($log->new_values as $field => $val)
                                            @php $fieldLabel = \App\Models\AuditLog::translateField($field); @endphp
                                            <span class="text-patrona-600">{{ $fieldLabel }}:</span>
                                            <span class="text-patrona-400">{{ is_bool($val) ? ($val ? 'Sí' : 'No') : $val }}</span>
                                            @if (! $loop->last)<span class="text-patrona-700"> · </span>@endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-patrona-600 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-patrona-600">
                                No hay registros de auditoría
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($this->logs->hasPages())
            <div class="px-4 py-3 border-t border-patrona-800">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>
</div>
