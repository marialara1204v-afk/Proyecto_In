@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-patrona-50">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-copper-700 to-copper-500 text-white p-6 shadow-lg">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-4xl font-display font-bold mb-2">La Patrona</h1>
            <p class="text-copper-100">Menú Digital - Acceso desde Código QR</p>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="max-w-6xl mx-auto p-6">
        {{-- Información --}}
        <div class="bg-white rounded-xl shadow-md p-8 mb-8 border border-patrona-100">
            <div class="flex items-start gap-4 mb-6">
                <div class="text-4xl">🍽️</div>
                <div>
                    <h2 class="text-2xl font-bold text-patrona-800">Bienvenido a nuestro Menú Digital</h2>
                    <p class="text-patrona-400 mt-2">
                        Explora todos nuestros platos, bebidas y especialidades. Haz tu pedido directamente desde tu teléfono.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-copper-100/50 to-copper-100 rounded-lg p-4 border border-copper-200">
                    <div class="text-3xl mb-2">🍽️</div>
                    <p class="font-bold text-patrona-800">{{ $totalProducts }} Productos</p>
                    <p class="text-sm text-patrona-400">Disponibles ahora</p>
                </div>
                <div class="bg-gradient-to-br from-copper-100/30 to-copper-100/60 rounded-lg p-4 border border-copper-200">
                    <div class="text-3xl mb-2">📂</div>
                    <p class="font-bold text-patrona-800">{{ $categories->count() }} Categorías</p>
                    <p class="text-sm text-patrona-400">Organizadas por tipo</p>
                </div>
                <div class="bg-gradient-to-br from-copper-100/20 to-copper-200/40 rounded-lg p-4 border border-copper-300">
                    <div class="text-3xl mb-2">⭐</div>
                    <p class="font-bold text-patrona-800">Premium</p>
                    <p class="text-sm text-patrona-400">Especialidades de la casa</p>
                </div>
            </div>
        </div>

        {{-- Categorías --}}
        <h2 class="text-3xl font-display font-bold text-patrona-800 mb-6">Nuestras Categorías</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categories as $category)
                <a href="{{ route('menu.category', $category) }}"
                   class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden
                          border-2 border-transparent hover:border-copper-400">
                    <div class="p-6">
                        <div class="text-5xl mb-3">
                            {{ $category->icon ?? '' }}
                        </div>
                        <h3 class="text-xl font-bold text-patrona-800 group-hover:text-copper-600 transition-colors">
                            {{ $category->name }}
                        </h3>
                        <p class="text-patrona-400 mt-2">
                            {{ $category->products->count() }} opciones
                        </p>
                        <div class="mt-4 inline-block px-4 py-2 bg-copper-100 text-copper-700 rounded-lg 
                                    group-hover:bg-copper-500 group-hover:text-white transition-all">
                            Ver →
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-patrona-400 text-lg">No hay categorías disponibles</p>
                </div>
            @endforelse
        </div>

        {{-- Footer info --}}
        <div class="mt-12 bg-gradient-to-r from-copper-700 to-copper-500 text-white rounded-xl p-8 text-center">
            <h3 class="text-2xl font-display font-bold mb-2">¿Listo para pedir?</h3>
            <p class="text-copper-100 mb-4">
                Selecciona una categoría para ver todos los productos disponibles
            </p>
            <p class="text-sm text-copper-200 mt-4">
                © {{ date('Y') }} La Patrona Restaurant. Todos los derechos reservados.
            </p>
        </div>
    </div>
</div>
@endsection
