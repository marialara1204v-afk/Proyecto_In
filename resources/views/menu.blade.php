<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Patrona — Menú Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(200,157,84,0.22),_transparent_35%),linear-gradient(180deg,_#1A0F08_0%,_#120A05_55%,_#0C0704_100%)] text-white antialiased">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 backdrop-blur-xl p-6 sm:p-8 shadow-2xl shadow-black/30 mb-6">
            <div class="absolute -top-16 -right-12 w-40 h-40 rounded-full bg-copper-500/20 blur-3xl"></div>
            <div class="absolute -bottom-12 -left-8 w-32 h-32 rounded-full bg-copper-300/10 blur-3xl"></div>

            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <x-brand-logo class="h-24 w-auto mb-4" />
                    <p class="text-copper-300 uppercase tracking-[0.35em] text-xs font-bold mb-3">Menú digital QR</p>
                    <h1 class="font-display text-4xl sm:text-5xl font-black leading-tight">La Patrona</h1>
                    <p class="text-white/70 mt-3 text-sm sm:text-base max-w-xl">
                        Consulta rápida del catálogo. Esta vista es solo lectura para apoyar la validación del pedido desde mesa.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <div class="text-white/50 text-xs uppercase tracking-wider">Categorías</div>
                        <div class="text-2xl font-black text-copper-300">{{ $categories->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <div class="text-white/50 text-xs uppercase tracking-wider">Platillos</div>
                        <div class="text-2xl font-black text-white">{{ $products->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3 col-span-2 sm:col-span-1">
                        <div class="text-white/50 text-xs uppercase tracking-wider">Mesa</div>
                        <div class="text-2xl font-black text-copper-200">{{ $selectedTable?->number ? 'Mesa '.$selectedTable->number : 'General' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('menu', $selectedTable?->number) }}"
               class="rounded-full px-4 py-2 text-sm font-bold transition-colors {{ $activeCategory === '' ? 'bg-copper-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' }}">
                Todos
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('menu', ['table' => $selectedTable?->number, 'category' => $category->name]) }}"
                   class="rounded-full px-4 py-2 text-sm font-bold transition-colors {{ $activeCategory === $category->name ? 'bg-copper-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($products as $product)
                <article class="group rounded-3xl border border-white/10 bg-white/6 overflow-hidden shadow-xl shadow-black/20 hover:-translate-y-1 hover:border-copper-400/40 transition-all duration-200">
                    <div class="aspect-[4/3] bg-gradient-to-br from-copper-500/25 via-copper-400/10 to-transparent relative">
                        @if ($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-white/60"><x-icon name="kitchen" class="w-14 h-14"/></div>
                        @endif
                        <div class="absolute top-4 left-4 rounded-full bg-black/50 backdrop-blur px-3 py-1 text-xs font-bold text-white/90">
                            {{ $product->category->name }}
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <h2 class="text-lg font-bold text-white leading-tight">{{ $product->name }}</h2>
                            <span class="text-copper-300 font-black text-lg whitespace-nowrap">${{ number_format($product->price, 2) }}</span>
                        </div>
                        @if ($product->description)
                            <p class="text-white/65 text-sm mt-3 leading-relaxed">{{ $product->description }}</p>
                        @endif
                        @if ($product->allergens)
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach (explode(',', $product->allergens) as $allergen)
                                     <span class="rounded-full border border-copper-400/30 bg-copper-500/10 px-3 py-1 text-xs font-semibold text-copper-200">
                                        {{ trim($allergen) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-white/10 bg-white/5 p-10 text-center text-white/60">
                    No hay productos disponibles en este momento.
                </div>
            @endforelse
        </section>
    </main>
</body>
</html>