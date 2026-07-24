<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Patrona — Acceso Denegado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        .shake { animation: shake 0.6s ease-in-out; }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .blink { animation: blink 1s step-end infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-patrona-900 p-4 relative overflow-hidden">

    {{-- Fondo decorativo --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-wine-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-wine-400/5 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-lg relative z-10 text-center">

        {{-- Dinosaurio --}}
        <div class="float mb-6">
            <img src="{{ asset('images/jurassic-denied.jpg') }}"
                 alt="Dinosaurio - Acceso Denegado"
                 class="w-52 h-auto mx-auto rounded-2xl shadow-2xl shadow-wine-500/20
                        ring-2 ring-wine-500/30 drop-shadow-[0_0_40px_rgba(239,68,68,0.3)]">
        </div>

        {{-- Card principal --}}
        <div class="bg-patrona-800/80 backdrop-blur-xl rounded-2xl border border-wine-500/20 p-8 sm:p-10 shadow-2xl shadow-black/40">

            {{-- Código de error --}}
            <div class="text-7xl font-black text-wine-500 font-display mb-2 shake">403</div>

            {{-- Easter Egg --}}
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-patrona-100 tracking-tight mb-3">
                "¡Ah, ah, ah! No dijiste la palabra mágica..."
            </h1>

            <div class="flex items-center justify-center gap-3 mb-5">
                <span class="h-px w-12 bg-gradient-to-r from-transparent to-wine-500/50"></span>
                <span class="text-wine-400 text-sm font-semibold uppercase tracking-wider">Dr. Malcolm</span>
                <span class="h-px w-12 bg-gradient-to-l from-transparent to-wine-500/50"></span>
            </div>

            <p class="text-patrona-400 text-sm leading-relaxed mb-6">
                No tienes permiso para acceder a esta sección.<br>
                <span class="text-patrona-500">Si crees que esto es un error, contacta al administrador.</span>
            </p>

            {{-- Botones --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ url()->previous() }}"
                   class="flex-1 bg-patrona-700 hover:bg-patrona-600 text-patrona-200 font-bold py-3 rounded-xl transition-colors text-sm">
                    ← Volver
                </a>
                <a href="{{ route('login') }}"
                   class="flex-1 bg-gradient-to-r from-copper-500 to-copper-400 hover:from-copper-400 hover:to-copper-300
                          text-patrona-900 font-bold py-3 rounded-xl transition-all text-sm">
                    Iniciar Sesión
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-patrona-600 text-xs mt-6 tracking-wide">
            "La vida, uh... encuentra un camino." — Ian Malcolm
        </p>
    </div>

</body>
</html>
