@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-patrona-50">
    {{-- Header con navegación --}}
    <div class="bg-gradient-to-r from-copper-700 to-copper-500 text-white p-6 shadow-lg sticky top-0 z-10">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div>
                <a href="{{ route('menu.digital') }}" class="inline-flex items-center gap-2 hover:opacity-80 transition">
                    <span class="text-xl">←</span>
                    <span>Volver al Menú</span>
                </a>
                <h1 class="text-3xl font-display font-bold mt-2">{{ $category->icon ?? '' }} {{ $category->name }}</h1>
            </div>
        </div>
    </div>

    {{-- Filtros de categorías --}}
    <div class="bg-white border-b border-patrona-100 sticky top-20 z-10">
        <div class="max-w-6xl mx-auto p-4">
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach($categories as $cat)
                    <a href="{{ route('menu.category', $cat) }}"
                       class="px-4 py-2 rounded-full whitespace-nowrap transition-all font-medium
                              {{ $cat->id === $category->id 
                                  ? 'bg-copper-500 text-white' 
                                  : 'bg-patrona-100 text-patrona-600 hover:bg-copper-100' }}">
                        {{ $cat->icon }} {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Productos --}}
    <div class="max-w-6xl mx-auto p-6">
        @if($category->products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($category->products as $product)
                    <a href="{{ route('menu.product', $product) }}"
                       class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden
                              border-2 border-transparent hover:border-copper-400">
                        @if($product->image)
                            <div class="relative w-full h-48 bg-gradient-to-br from-patrona-100 to-patrona-200 overflow-hidden">
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            </div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-copper-100 to-copper-200 flex items-center justify-center">
                                <span class="text-6xl">{{ $category->icon ?? '' }}</span>
                            </div>
                        @endif

                        <div class="p-5">
                            <h3 class="text-lg font-bold text-patrona-800 group-hover:text-copper-600 transition-colors line-clamp-2">
                                {{ $product->name }}
                            </h3>

                            @if($product->description)
                                <p class="text-patrona-400 text-sm mt-2 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            @endif

                            @if($product->allergens)
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach(explode(',', $product->allergens) as $allergen)
                                        <span class="text-xs bg-wine-600/15 text-wine-500 px-2 py-1 rounded">
                                            {{ trim($allergen) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-2xl font-bold text-copper-600">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                                <span class="text-sm bg-copper-100 text-copper-700 px-3 py-1 rounded-full">
                                    Ver más →
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-2xl font-bold text-patrona-800 mb-2">No hay productos disponibles</h3>
                <p class="text-patrona-400">Esta categoría no tiene productos en este momento.</p>
            </div>
        @endif
    </div>
</div>
@endsection
