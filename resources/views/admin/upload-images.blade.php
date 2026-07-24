@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <h1 class="text-3xl font-bold mb-6">Cargar Imagenes de Productos</h1>

    @if ($errors->any())
        <div class="bg-wine-100 border border-wine-400 text-wine-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-wine-100 border border-wine-400 text-wine-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        <!-- Carga Individual -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Cargar Imagen Individual</h2>
            
            <form action="{{ route('admin.cargar-imagen') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="product_id" class="block font-semibold mb-2">Seleccionar Producto:</label>
                    <select name="product_id" id="product_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">-- Selecciona un producto --</option>
                        @foreach ($products->groupBy('category.name') as $category => $items)
                            <optgroup label="{{ $category }}">
                                @foreach ($items as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->price }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image" class="block font-semibold mb-2">Seleccionar Imagen:</label>
                    <input type="file" name="image" id="image" class="w-full border border-gray-300 rounded px-3 py-2" accept="image/*" required>
                    <p class="text-sm text-gray-600 mt-1">Formatos: JPG, PNG, GIF (Máx. 2MB)</p>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Cargar Imagen
                </button>
            </form>
        </div>

        <!-- Guía de Asignación -->
        <div class="bg-blue-50 rounded-lg shadow-md p-6 border-l-4 border-blue-600">
            <h2 class="text-xl font-bold mb-4">Guia de Productos a Cargar</h2>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <h3 class="font-bold text-blue-700 mb-2">COMIDAS</h3>
                    <ul class="space-y-1 text-gray-700">
                        <li>Birria (caldo con carne)</li>
                        <li>Birriamen (fideos con birria)</li>
                        <li>Quesabirria (quesadilla dorada)</li>
                        <li>Taco Dorado (taco en harina)</li>
                        <li>Nachos (con y sin birria)</li>
                        <li>Sincronizada (quesadilla)</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-bold text-blue-700 mb-2">BEBIDAS</h3>
                    <ul class="space-y-1 text-gray-700">
                        <li>Mojito (bebida verde)</li>
                        <li>Piña Colada (bebida cremosa)</li>
                        <li>Cantaritos (decorados con borde rojo)</li>
                        <li>Sangría Preparada (roja)</li>
                        <li>Agua Horchata</li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-gray-700">
                    <strong>Nota:</strong> Las imágenes se guardarán automáticamente con nombres que correspondan a cada producto.
                </p>
            </div>
        </div>

        <!-- Lista de Productos Sin Imagen -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Productos Sin Imagen ({{ $products->whereNull('image')->count() }})</h2>
            
            @if ($products->whereNull('image')->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($products->whereNull('image') as $product)
                        <div class="border border-gray-200 rounded p-3 flex justify-between items-center">
                            <div>
                                <p class="font-semibold">{{ $product->name }}</p>
                                <p class="text-sm text-gray-600">{{ $product->category->name }}</p>
                            </div>
                            <span class="bg-wine-100 text-wine-700 px-3 py-1 rounded text-sm">Pendiente</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-green-600">Todos los productos tienen imagen</p>
            @endif
        </div>

        <!-- Productos Con Imagen -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Productos Con Imagen ({{ $products->whereNotNull('image')->count() }})</h2>
            
            @if ($products->whereNotNull('image')->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($products->whereNotNull('image') as $product)
                        <div class="border border-gray-200 rounded overflow-hidden hover:shadow-lg transition">
                            @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-32 object-cover">
                            @endif
                            <div class="p-3">
                                <p class="font-semibold text-sm">{{ $product->name }}</p>
                                <p class="text-xs text-gray-600">{{ $product->category->name }}</p>
                                <p class="text-xs text-green-600 mt-1">✅ Imagen cargada</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">Aún no hay productos con imagen</p>
            @endif
        </div>
    </div>
</div>

<style>
    select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        padding-right: 28px;
    }
</style>
@endsection
