<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Patrona — {{ $title ?? 'Sistema' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-patrona-900 min-h-screen text-patrona-100 antialiased">

    {{-- ── NAVBAR ── --}}
    <nav class="bg-patrona-800/95 backdrop-blur-sm border-b border-patrona-700/50 sticky top-0 z-30">
        <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-4">

            {{-- Logo --}}
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-copper-400 to-copper-500 flex items-center justify-center shadow-lg shadow-copper-500/20 shrink-0 overflow-hidden ring-2 ring-copper-400/30">
                    <x-brand-logo class="h-9 w-9 object-contain" />
                </div>
                <div class="hidden sm:block leading-tight min-w-0">
                    <p class="text-sm font-bold text-patrona-100 truncate">Vista {{ ucfirst(auth()->user()->role ?? 'Usuario') }}</p>
                    <p class="text-[11px] text-patrona-400 truncate tracking-wide uppercase">La Patrona</p>
                </div>
                <span class="hidden md:inline text-[10px] bg-copper-500/15 text-copper-300 px-2.5 py-0.5
                             rounded-full font-semibold uppercase tracking-wider border border-copper-500/20">
                    {{ ucfirst(auth()->user()->role ?? 'Invitado') }}
                </span>
            </div>

            {{-- Navegacion por rol --}}
            <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
                @auth
                    <a href="{{ route('menu.digital') }}"
                       class="nav-link">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="menu" class="w-4 h-4"/>Menú</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="dashboard" class="w-4 h-4"/>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.report') }}"
                           class="nav-link {{ request()->routeIs('admin.report') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="receipt" class="w-4 h-4"/>Reporte</span>
                        </a>
                        <a href="{{ route('admin.inventory') }}"
                           class="nav-link {{ request()->routeIs('admin.inventory') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="box" class="w-4 h-4"/>Inventario</span>
                        </a>
                        <a href="{{ route('admin.products') }}"
                           class="nav-link {{ request()->routeIs('admin.products') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="kitchen" class="w-4 h-4"/>Productos</span>
                        </a>
                        <a href="{{ route('admin.users') }}"
                           class="nav-link {{ request()->routeIs('admin.users') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="users" class="w-4 h-4"/>Usuarios</span>
                        </a>
                        <a href="{{ route('admin.audit-log') }}"
                           class="nav-link {{ request()->routeIs('admin.audit-log') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="receipt" class="w-4 h-4"/>Auditoría</span>
                        </a>
                        <a href="{{ route('waiter.tables') }}"
                           class="nav-link {{ request()->routeIs('waiter.tables') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="map" class="w-4 h-4"/>Mesas</span>
                        </a>
                        <a href="{{ route('cashier.index') }}"
                           class="nav-link {{ request()->routeIs('cashier.*') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="card" class="w-4 h-4"/>Caja</span>
                        </a>
                    @elseif(auth()->user()->isMesero())
                        <a href="{{ route('waiter.tables') }}"
                           class="nav-link {{ request()->routeIs('waiter.tables') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="map" class="w-4 h-4"/>Mis Mesas</span>
                        </a>
                        <a href="{{ route('cashier.index') }}"
                           class="nav-link {{ request()->routeIs('cashier.*') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="card" class="w-4 h-4"/>Caja</span>
                        </a>
                    @elseif(auth()->user()->hasKitchenAccess())
                        <a href="{{ route('kitchen.orders') }}"
                           class="nav-link {{ request()->routeIs('kitchen.orders') ? 'nav-active' : '' }}">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="kitchen" class="w-4 h-4"/>Comandas</span>
                        </a>
                    @endif
                @endauth
            </div>

            {{-- Usuario + Logout --}}
            <div class="flex items-center gap-3">
                <span class="hidden lg:block text-sm text-patrona-300">
                    {{ auth()->user()->name ?? '' }}
                </span>
                @auth
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;" id="logoutForm">
                        @csrf
                        <button type="submit"
                                class="text-sm text-patrona-400 hover:text-copper-400 transition-colors px-3 py-1.5 rounded-lg hover:bg-patrona-700/50"
                                onclick="handleLogout(event)">
                            Salir
                        </button>
                    </form>
                    <script>
                        function handleLogout(e) {
                            e.preventDefault();
                            fetch('{{ route("logout") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                }
                            }).then(() => {
                                window.location.href = '/';
                            }).catch(() => {
                                document.getElementById('logoutForm').submit();
                            });
                        }
                    </script>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-patrona-400 hover:text-copper-400 transition-colors px-3 py-1.5 rounded-lg hover:bg-patrona-700/50">
                        Login
                    </a>
                @endauth
            </div>

        </div>
    </nav>

    {{-- ── CONTENIDO ── --}}
    <main>{{ $slot }}</main>

    @livewireScripts
</body>
</html>

<style>
    .nav-link {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #a89b8c;
        transition: all .2s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .nav-link:hover {
        color: #f5f0eb;
        background: rgba(200, 149, 108, 0.1);
    }
    .nav-active {
        color: #c8956c !important;
        background: rgba(200, 149, 108, 0.12) !important;
    }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
