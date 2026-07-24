<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __invoke(Request $request, ?string $table = null)
    {
        $selectedTable = null;

        if ($table !== null) {
            $selectedTable = Table::where('number', $table)->first() ?? Table::find($table);
        }

        $categories = Category::active()->with(['products' => function ($query) {
            $query->available()->orderBy('name');
        }])->orderBy('name')->get();

        $products = Product::available()
            ->with('category')
            ->when($request->string('category')->toString(), function ($query, $category) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', $category));
            })
            ->orderBy('name')
            ->get();

        return view('menu', [
            'selectedTable' => $selectedTable,
            'categories' => $categories,
            'products' => $products,
            'activeCategory' => $request->string('category')->toString(),
        ]);
    }
}