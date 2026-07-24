<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function uploadForm()
    {
        $products = Product::all()->sortBy('category_id');
        return view('admin.upload-images', compact('products'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->hasFile('image')) {
            // Generar nombre basado en el producto
            $filename = strtolower(str_replace(' ', '-', $product->name)) . '.' . $request->file('image')->getClientOriginalExtension();

            // Guardar en storage/app/public/productos/
            $path = $request->file('image')->storeAs('productos', $filename, 'public');

            // Actualizar el producto con la ruta de la imagen
            $product->update(['image' => $path]);

            return back()->with('success', "Imagen cargada para {$product->name}");
        }

        return back()->with('error', 'Error al cargar la imagen');
    }

    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_ids.*' => 'required|exists:products,id',
        ]);

        $uploaded = 0;
        foreach ($request->file('images') as $index => $image) {
            $productId = $request->input('product_ids')[$index] ?? null;
            if (!$productId) continue;

            $product = Product::find($productId);
            if ($product) {
                $filename = strtolower(str_replace(' ', '-', $product->name)) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('productos', $filename, 'public');
                $product->update(['image' => $path]);
                $uploaded++;
            }
        }

        return back()->with('success', "$uploaded imágenes cargadas exitosamente");
    }
}
