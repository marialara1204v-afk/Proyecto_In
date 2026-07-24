@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-patrona-50">
    {{-- Header de navegación --}}
    <div class="bg-gradient-to-r from-copper-700 to-copper-500 text-white p-6 shadow-lg">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('menu.category', $product->category) }}" 
               class="inline-flex items-center gap-2 hover:opacity-80 transition mb-4">
                <span class="text-xl">←</span>
                <span>Volver a {{ $product->category->name }}</span>
            </a>
            <h1 class="text-4xl font-display font-bold">{{ $product->name }}</h1>
        </div>
    </div>

    {{-- Contenido del producto --}}
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-patrona-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                {{-- Imagen --}}
                <div class="flex items-center justify-center">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-auto rounded-lg shadow-md object-cover">
                    @else
                        <div class="w-full aspect-square bg-gradient-to-br from-copper-100 to-copper-200 rounded-lg 
                                    flex items-center justify-center">
                            <span class="text-9xl">{{ $product->category->icon ?? '' }}</span>
                        </div>
                    @endif
                </div>

                {{-- Detalles --}}
                <div>
                    <div class="mb-4">
                        <span class="inline-block px-4 py-1 bg-copper-100 text-copper-700 rounded-full text-sm font-medium">
                            {{ $product->category->icon }} {{ $product->category->name }}
                        </span>
                    </div>

                    @if($product->description)
                        <p class="text-patrona-400 text-lg mb-6">
                            {{ $product->description }}
                        </p>
                    @endif

                    <div class="mb-8 p-6 bg-gradient-to-r from-copper-100/50 to-copper-100 rounded-lg border-2 border-copper-200">
                        <p class="text-patrona-400 text-sm mb-2">Precio:</p>
                        <p class="text-5xl font-bold text-copper-600">
                            ${{ number_format($product->price, 2) }}
                        </p>
                    </div>

                    @if($product->allergens)
                        <div class="mb-6">
                            <h3 class="font-bold text-patrona-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">⚠️</span>
                                Contiene Alérgenos
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $product->allergens) as $allergen)
                                    <span class="bg-wine-600/15 text-wine-500 px-3 py-2 rounded-lg text-sm font-medium">
                                        {{ trim($allergen) }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="text-xs text-wine-500 mt-3">
                                Por favor, notifica al personal si eres alérgico a alguno de estos ingredientes.
                            </p>
                        </div>
                    @else
                        <div class="mb-6 p-3 bg-olive-500/10 border border-olive-400/30 rounded-lg">
                            <p class="text-olive-500 text-sm flex items-center gap-2">
                                <span>✅</span>
                                Sin alérgenos conocidos
                            </p>
                        </div>
                    @endif

                    @if($product->is_available)
                        <div class="p-4 bg-olive-500/10 border border-olive-400/30 rounded-lg">
                            <p class="text-olive-500 font-medium flex items-center gap-2">
                                <span class="w-3 h-3 bg-olive-500 rounded-full"></span>
                                Disponible ahora
                            </p>
                        </div>
                    @else
                        <div class="p-4 bg-patrona-100 border border-patrona-200 rounded-lg">
                            <p class="text-patrona-400 font-medium">
                                No disponible en este momento
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($relatedProducts->count() > 0)
            <div class="mt-12">
                <h2 class="text-2xl font-display font-bold text-patrona-800 mb-6">
                    Otros productos en {{ $product->category->name }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('menu.product', $related) }}"
                           class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden
                                  border-2 border-transparent hover:border-copper-400">
                            @if($related->image)
                                <div class="relative w-full h-32 bg-gradient-to-br from-patrona-100 to-patrona-200 overflow-hidden">
                                    <img src="{{ asset('storage/' . $related->image) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @else
                                <div class="w-full h-32 bg-gradient-to-br from-copper-100 to-copper-200 flex items-center justify-center">
                                    <span class="text-3xl">{{ $product->category->icon }}</span>
                                </div>
                            @endif

                            <div class="p-4">
                                <h3 class="font-bold text-patrona-800 group-hover:text-copper-600 transition-colors line-clamp-2">
                                    {{ $related->name }}
                                </h3>
                                <p class="text-copper-600 font-bold text-lg mt-2">
                                    ${{ number_format($related->price, 2) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 flex gap-4 justify-center">
            <a href="{{ route('menu.category', $product->category) }}"
               class="px-6 py-3 bg-patrona-100 text-patrona-600 rounded-lg hover:bg-patrona-200 transition font-medium">
                Ver más productos
            </a>
            <a href="{{ route('menu.digital') }}"
               class="px-6 py-3 bg-copper-500 text-white rounded-lg hover:bg-copper-600 transition font-medium">
                Ver menú completo
            </a>
        </div>
    </div>
</div>
@endsection
