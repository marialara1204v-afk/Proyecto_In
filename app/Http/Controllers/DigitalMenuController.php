<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class DigitalMenuController extends Controller
{
    /**
     * Mostrar el menú digital completo (público, sin autenticación)
     * Separado en secciones de Comida y Bebidas
     */
    public function index(): View
    {
        // Categorías de comida
        $foodCategoryNames = [
            'Birria', 'Tacos', 'Snaks', 'Menú Infantil', 
            'Paquetes', 'Postres', 'Entradas', 'Fuertes'
        ];

        // Categorías de bebidas
        $drinkCategoryNames = [
            'Bebidas Calientes', 'Bebidas Frías', 'Coctelería Sin Alcohol',
            'Coctelería Con Alcohol', 'Cantaritos', 'Refrescos', 'Cerveza', 'Bebidas'
        ];

        // Obtener IDs de categorías de comida
        $foodCategoryIds = Category::whereIn('name', $foodCategoryNames)
            ->pluck('id')
            ->unique()
            ->values();

        // Obtener IDs de categorías de bebidas
        $drinkCategoryIds = Category::whereIn('name', $drinkCategoryNames)
            ->pluck('id')
            ->unique()
            ->values();

        // Obtener productos de comida
        $foodProducts = Product::whereIn('category_id', $foodCategoryIds)
            ->where('is_available', true)
            ->orderBy('name')
            ->distinct()
            ->get();

        // Obtener productos de bebidas
        $drinkProducts = Product::whereIn('category_id', $drinkCategoryIds)
            ->where('is_available', true)
            ->orderBy('name')
            ->distinct()
            ->get();

        return view('menu.digital', [
            'foodProducts' => $foodProducts,
            'beverageProducts' => $drinkProducts,
        ]);
    }

    /**
     * Mostrar una categoría específica
     */
    public function category(Category $category): View
    {
        $category->load(['products' => function ($query) {
            $query->where('is_available', true);
        }]);

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('public.menu-category', [
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    /**
     * Mostrar un producto específico con detalles
     */
    public function product(Product $product): View
    {
        $product->load('category');

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return view('public.menu-product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
