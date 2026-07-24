<div class="p-6">

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 z-50 bg-green-500 text-white px-5 py-3
                    rounded-xl shadow-lg font-semibold text-sm">
            <span class="inline-flex items-center gap-1"><x-icon name="check" class="w-4 h-4"/>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 z-50 bg-wine-500 text-white px-5 py-3
                    rounded-xl shadow-lg font-semibold text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-white">Gestión de Usuarios</h2>
            <p class="text-patrona-500 text-sm">{{ $this->users->count() }} usuarios registrados</p>
        </div>
        <button wire:click="openCreate"
                class="bg-copper-500 hover:bg-copper-400 text-white font-bold
                       px-5 py-2.5 rounded-xl transition-colors">
            + Nuevo Usuario
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-patrona-900 border border-patrona-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-patrona-800">
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold">Nombre</th>
                    <th class="text-left px-5 py-3 text-patrona-500 font-semibold hidden sm:table-cell">Email</th>
                    <th class="text-center px-5 py-3 text-patrona-500 font-semibold">Rol</th>
                    <th class="text-center px-5 py-3 text-patrona-500 font-semibold">Estado</th>
                    <th class="text-right px-5 py-3 text-patrona-500 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-patrona-800">
                @foreach ($this->users as $user)
                    <tr class="hover:bg-patrona-800/50 transition-colors"
                        wire:key="user-{{ $user->id }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-copper-500/20 border border-copper-500/30
                                            flex items-center justify-center text-copper-400 font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-semibold text-white">{{ $user->name }}</span>
                                @if ($user->id === auth()->id())
                                    <span class="text-xs bg-patrona-700 text-patrona-400 px-2 py-0.5 rounded-full">Tú</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 hidden sm:table-cell text-patrona-400">
                            {{ $user->email }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @php
                                $roleColors = [
                                    'admin'     => 'bg-purple-500/20 text-purple-400',
                                    'mesero'    => 'bg-copper-500/20 text-copper-400',
                                    'cocina'    => 'bg-wine-500/20 text-wine-400',
                                    'bartender' => 'bg-blue-500/20 text-blue-400',
                                ];
                                $roleLabels = [
                                    'admin'     => 'Admin',
                                    'mesero'    => 'Mesero',
                                    'cocina'    => 'Cocina',
                                    'bartender' => 'Barra',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                         {{ $roleColors[$user->role] ?? 'bg-patrona-700 text-patrona-400' }}">
                                {{ $roleLabels[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                         {{ $user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-patrona-600/30 text-patrona-500' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button wire:click="openEdit({{ $user->id }})"
                                        class="text-patrona-500 hover:text-copper-400 transition-colors p-1">
                                    <x-icon name="edit" class="w-4 h-4"/>
                                </button>
                                @if ($user->id !== auth()->id())
                                    <button
                                        wire:click="toggleActive({{ $user->id }})"
                                        wire:confirm="¿{{ $user->is_active ? 'Desactivar' : 'Activar' }} a {{ $user->name }}?"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none
                                               {{ $user->is_active ? 'bg-green-500' : 'bg-patrona-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200
                                                     {{ $user->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                 wire:click="$set('showForm', false)"></div>

            <div class="relative z-10 bg-patrona-900 border border-patrona-700 rounded-2xl
                        w-full max-w-md shadow-2xl">

                <div class="p-5 border-b border-patrona-800 flex items-center justify-between">
                    <h3 class="font-bold text-white text-lg">
                        {{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h3>
                    <button wire:click="$set('showForm', false)"
                            class="text-patrona-500 hover:text-white text-2xl">&times;</button>
                </div>

                <div class="p-5 space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">Nombre *</label>
                        <input wire:model="name" type="text"
                               class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                      text-white focus:outline-none focus:border-copper-500 text-sm"
                               placeholder="Nombre completo">
                        @error('name') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">Email *</label>
                        <input wire:model="email" type="email"
                               class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                      text-white focus:outline-none focus:border-copper-500 text-sm"
                               placeholder="correo@lapatrona.com">
                        @error('email') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Contraseña {{ $editingId ? '(dejar vacío para no cambiar)' : '*' }}
                        </label>
                        <input wire:model="password" type="password"
                               class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                      text-white focus:outline-none focus:border-copper-500 text-sm"
                               placeholder="••••••••">
                        @error('password') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">
                            Confirmar contraseña {{ $editingId ? '' : '*' }}
                        </label>
                        <input wire:model="password_confirmation" type="password"
                               class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                      text-white focus:outline-none focus:border-copper-500 text-sm"
                               placeholder="••••••••">
                        @error('password_confirmation') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-patrona-400 mb-1">Rol *</label>
                        <select wire:model="role"
                                class="w-full bg-patrona-800 border border-patrona-700 rounded-xl px-4 py-2.5
                                       text-white focus:outline-none focus:border-copper-500 text-sm">
                            <option value="mesero">Mesero</option>
                            <option value="cocina">Cocina</option>
                            <option value="bartender">Bartender</option>
                            <option value="admin">Administrador</option>
                        </select>
                        @error('role') <p class="text-wine-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-patrona-800 flex gap-3">
                    <button wire:click="$set('showForm', false)"
                            class="flex-1 bg-patrona-800 hover:bg-patrona-700 text-patrona-400
                                   font-bold py-3 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="flex-1 bg-copper-500 hover:bg-copper-400 text-white
                                   font-bold py-3 rounded-xl transition-colors">
                        <span wire:loading.remove>{{ $editingId ? 'Guardar' : 'Crear' }}</span>
                        <span wire:loading>Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>