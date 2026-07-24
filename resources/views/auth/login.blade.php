<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Patrona — Acceso</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-patrona-900 p-4 relative overflow-hidden">

    {{-- Elementos decorativos de fondo --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-copper-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-copper-400/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-copper-500/3 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">

        {{-- Logo + Titulo --}}
        <div class="text-center mb-8">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-copper-400 to-copper-500 mx-auto flex items-center justify-center shadow-2xl shadow-copper-500/30 overflow-hidden ring-4 ring-copper-400/20">
                <x-brand-logo class="w-20 h-20 object-contain" />
            </div>
            <h1 class="font-display text-white text-5xl font-bold mt-5 tracking-tight">La Patrona</h1>
            <div class="flex items-center justify-center gap-3 mt-3">
                <span class="h-px w-12 bg-gradient-to-r from-transparent to-copper-400/50"></span>
                <p class="text-copper-300 text-sm font-semibold uppercase tracking-[0.2em]">Sistema de Gestión</p>
                <span class="h-px w-12 bg-gradient-to-l from-transparent to-copper-400/50"></span>
            </div>
        </div>

        {{-- Card de login --}}
        <div class="bg-patrona-800/80 backdrop-blur-xl rounded-2xl border border-patrona-700/50 p-7 sm:p-8 shadow-2xl shadow-black/40">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <h2 class="font-display text-3xl font-bold text-patrona-100 tracking-tight text-center">
                    Iniciar Sesión
                </h2>

                {{-- Error Easter Egg --}}
                @error('email')
                    <div x-data="{ show: true }" x-show="show" x-transition
                         class="bg-wine-500/10 border border-wine-500/30 rounded-xl p-4 text-center relative">
                        <button @click="show = false" class="absolute top-2 right-3 text-wine-400 hover:text-white text-lg">&times;</button>
                        <img src="{{ asset('images/jurassic-denied.jpg') }}"
                             alt="Dinosaurio"
                             class="w-12 h-12 mx-auto rounded-full object-cover border border-wine-500/40 mb-2
                                    shadow-lg shadow-wine-500/20">
                        <p class="text-red-300 font-semibold text-sm">
                            "¡Ah, ah, ah! No dijiste la palabra mágica..."
                        </p>
                        <p class="text-wine-400/70 text-xs mt-1">{{ $message }}</p>
                    </div>
                @enderror

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-patrona-400 mb-2 uppercase tracking-wider">
                        Correo electrónico
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required autofocus
                        class="w-full bg-patrona-700/50 border border-patrona-600 rounded-xl px-4 py-3.5
                               text-patrona-100 placeholder-patrona-500 focus:outline-none
                               focus:border-copper-400/60 focus:ring-2 focus:ring-copper-400/20 focus:bg-patrona-700/70
                               transition-all duration-200"
                        placeholder="usuario@lapatrona.com"
                    >
                    @error('email')
                        <p class="text-wine-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contrasena --}}
                <div>
                    <label class="block text-xs font-semibold text-patrona-400 mb-2 uppercase tracking-wider">
                        Contraseña
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full bg-patrona-700/50 border border-patrona-600 rounded-xl px-4 py-3.5
                               text-patrona-100 placeholder-patrona-500 focus:outline-none
                               focus:border-copper-400/60 focus:ring-2 focus:ring-copper-400/20 focus:bg-patrona-700/70
                               transition-all duration-200"
                        placeholder="••••••••"
                    >
                </div>

                {{-- Boton --}}
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-copper-500 to-copper-400 hover:from-copper-400 hover:to-copper-300
                           text-patrona-900 font-bold py-3.5 rounded-xl transition-all duration-200 text-base tracking-wide
                           shadow-lg shadow-copper-500/25 hover:shadow-copper-400/35 active:scale-[0.98]"
                >
                    Iniciar Sesión
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-patrona-500 text-xs mt-6 tracking-wide">
            &copy; {{ date('Y') }} La Patrona — Todos los derechos reservados
        </p>
    </div>

</body>
</html>
