<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuarios ───────────────────────────────────────────────
        $users = [
            ['name' => 'Francisco',    'email' => 'admin@lapatrona.com',   'role' => 'admin'],
            ['name' => 'Jorge',        'email' => 'mesero@lapatrona.com',  'role' => 'mesero'],
            ['name' => 'César',        'email' => 'mesero2@lapatrona.com', 'role' => 'mesero'],
            ['name' => 'Alejandro',    'email' => 'cocina@lapatrona.com',  'role' => 'cocina'],
            ['name' => 'Talia',        'email' => 'barra@lapatrona.com',   'role' => 'bartender'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => Hash::make('password')])
            );
        }

        // ── Mesas ──────────────────────────────────────────────────
        $capacities = [2, 2, 4, 4, 4, 4, 4, 6, 6, 6];
        foreach (range(1, 10) as $i) {
            Table::updateOrCreate(
                ['number' => $i],
                ['capacity' => $capacities[$i - 1], 'status' => 'libre']
            );
        }

        // ── Categorías ─────────────────────────────────────────────
        $entradas = Category::updateOrCreate(['name' => 'Entradas'], ['icon' => '🥗', 'is_active' => true]);
        $fuertes  = Category::updateOrCreate(['name' => 'Fuertes'],  ['icon' => '🍽️', 'is_active' => true]);
        $bebidas  = Category::updateOrCreate(['name' => 'Bebidas'],  ['icon' => '🥤', 'is_active' => true]);
        $postres  = Category::updateOrCreate(['name' => 'Postres'],  ['icon' => '🍮', 'is_active' => true]);

        // ── Productos ──────────────────────────────────────────────
        $products = [
            [$entradas->id, 'Quesadillas (2)',         'Quesadillas de queso con tortilla de harina',        65.00, 'lácteos, gluten'],
            [$entradas->id, 'Caldo de Res',            'Caldo tradicional con verduras y carne',             110.00, 'apio'],
            [$fuertes->id,  'Tacos de Carnitas (3)',   'Tortillas de maíz con carnitas, cebolla y cilantro', 85.00, 'picante'],
            [$fuertes->id,  'Enchiladas Verdes',       'Tres enchiladas bañadas en salsa verde con crema',   95.00, 'lácteos, gluten'],
            [$fuertes->id,  'Torta de Milanesa',       'Pan telera con milanesa, frijoles y aguacate',       75.00, 'gluten, huevo'],
            [$bebidas->id,  'Agua Fresca del Día',     'Horchata, jamaica o tamarindo',                      30.00, null],
            [$bebidas->id,  'Refresco 600ml',          'Coca-Cola, Sprite o Fanta',                          35.00, null],
            [$bebidas->id,  'Cerveza Modelo Especial', 'Botella fría 355ml',                                 55.00, 'alcohol'],
            [$bebidas->id,  'Café de Olla',            'Café con canela y piloncillo',                       40.00, null],
            [$postres->id,  'Flan Napolitano',         'Flan de cajeta con crema batida',                    50.00, 'lácteos, huevo'],
            [$postres->id,  'Pay de Limón',            'Pay frío con base de galleta',                       55.00, 'lácteos, gluten'],
        ];

        foreach ($products as [$cat, $name, $desc, $price, $allergens]) {
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $cat,
                    'description' => $desc,
                    'allergens'   => $allergens,
                    'price'       => $price,
                    'is_available' => true,
                ]
            );
        }

        // ── Inventario básico ─────────────────────────────────────
        \App\Models\InventoryItem::insert([
            ['name' => 'Tortillas de maíz', 'category' => 'comida', 'stock' => 120, 'min_stock' => 40, 'unit' => 'pzas', 'notes' => 'Base para tacos y quesadillas', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carne de cerdo', 'category' => 'comida', 'stock' => 18, 'min_stock' => 20, 'unit' => 'kg', 'notes' => 'Carnitas y otros guisos', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Queso', 'category' => 'comida', 'stock' => 12, 'min_stock' => 15, 'unit' => 'kg', 'notes' => 'Platillos y entradas', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servilletas', 'category' => 'verdura', 'stock' => 260, 'min_stock' => 100, 'unit' => 'pzas', 'notes' => 'Uso diario en salón', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hielo', 'category' => 'bebida', 'stock' => 8, 'min_stock' => 10, 'unit' => 'bolsas', 'notes' => 'Bebidas y barra', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}