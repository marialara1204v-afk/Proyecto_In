<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class UpdateLaPatronaMenu extends Command
{
    protected $signature = 'menu:update-lapatrona';
    protected $description = 'Update menu with La Patrona restaurant items';

    public function handle()
    {
        // Deshabilitar restricciones de clave foránea
        \DB::statement('PRAGMA foreign_keys = OFF');

        // Limpiar menú anterior
        Product::query()->delete();
        Category::query()->delete();

        // Habilitar restricciones de clave foránea
        \DB::statement('PRAGMA foreign_keys = ON');

        $categories = [
            'Birria' => [
                ['name' => 'Birria', 'price' => 110.0],
                ['name' => 'Birria (Litro)', 'price' => 170.0],
                ['name' => 'Consomé', 'price' => 50.0],
                ['name' => 'Consomé (Litro)', 'price' => 80.0],
                ['name' => 'Birriamen (Sin Queso)', 'price' => 150.0],
                ['name' => 'Birriamen (Con Queso)', 'price' => 170.0],
            ],
            'Tacos' => [
                ['name' => 'Taco Dorado (Maíz)', 'price' => 45.0],
                ['name' => 'Taco Dorado (Harina)', 'price' => 55.0],
                ['name' => 'Taco Blando (Maíz)', 'price' => 45.0],
                ['name' => 'Taco Blando (Harina)', 'price' => 55.0],
                ['name' => 'Quesabirria (Maíz)', 'price' => 50.0],
                ['name' => 'Quesabirria (Harina)', 'price' => 65.0],
                ['name' => 'Mulita (Maíz)', 'price' => 75.0],
                ['name' => 'Mulita (Harina)', 'price' => 90.0],
            ],
            'Snaks' => [
                ['name' => 'Costra c/queso+papa+guacamole', 'price' => 150.0],
                ['name' => 'Wrap c/queso+papa+guacamole', 'price' => 150.0],
                ['name' => 'Nachos (Solos)', 'price' => 100.0],
                ['name' => 'Nachos (Con Birria)', 'price' => 190.0],
                ['name' => 'Papas Fritas (Solos)', 'price' => 70.0],
                ['name' => 'Papas Fritas (Con Birria)', 'price' => 150.0],
            ],
            'Menú Infantil' => [
                ['name' => 'Sincronizada', 'price' => 45.0],
                ['name' => 'Nuggets con papas fritas', 'price' => 130.0],
                ['name' => 'Salchipulpos con papas fritas', 'price' => 130.0],
            ],
            'Paquetes' => [
                ['name' => '1kg Birria + 2lt consomé', 'price' => 750.0],
                ['name' => 'Pizza + 1lt consomé', 'price' => 280.0],
                ['name' => '3 QBM + consomé + refresco', 'price' => 160.0],
                ['name' => 'Quesabirria Jumbo + Consomé + Refresco', 'price' => 175.0],
                ['name' => 'Burrito + consomé + papas + guacamole + Refresco', 'price' => 195.0],
            ],
            'Postres' => [
                ['name' => 'Flan Napolitano', 'price' => 50.0],
                ['name' => 'Fresas con Crema', 'price' => 45.0],
                ['name' => 'Carlota de Limón', 'price' => 45.0],
                ['name' => 'Pay Queso con Zarzamora', 'price' => 50.0],
            ],
            'Bebidas Calientes' => [
                ['name' => 'Café', 'price' => 25.0],
                ['name' => 'Té', 'price' => 25.0],
                ['name' => 'Ponche de Temporada', 'price' => 30.0],
            ],
            'Bebidas Frías' => [
                ['name' => 'Tehuacán Preparado', 'price' => 50.0],
                ['name' => 'Sangría Preparada', 'price' => 50.0],
                ['name' => 'Agua Horchata (vaso)', 'price' => 25.0],
                ['name' => 'Agua Horchata (litro)', 'price' => 40.0],
                ['name' => 'Limonada (Natural)', 'price' => 50.0],
                ['name' => 'Limonada (Mineral)', 'price' => 60.0],
                ['name' => 'Naranjada (Natural)', 'price' => 50.0],
                ['name' => 'Naranjada (Mineral)', 'price' => 60.0],
                ['name' => 'Agua (Natural)', 'price' => 20.0],
                ['name' => 'Agua (Mineral)', 'price' => 35.0],
            ],
            'Coctelería Sin Alcohol' => [
                ['name' => 'Piña Colada (450ml)', 'price' => 70.0],
                ['name' => 'Piña Colada (900ml)', 'price' => 130.0],
                ['name' => 'Mojito (450ml)', 'price' => 50.0],
                ['name' => 'Mojito (900ml)', 'price' => 95.0],
                ['name' => 'Pitufo (450ml)', 'price' => 50.0],
                ['name' => 'Pitufo (900ml)', 'price' => 95.0],
            ],
            'Coctelería Con Alcohol' => [
                ['name' => 'Piña Colada (450ml)', 'price' => 80.0],
                ['name' => 'Piña Colada (900ml)', 'price' => 140.0],
                ['name' => 'Mojito (450ml)', 'price' => 60.0],
                ['name' => 'Mojito (900ml)', 'price' => 105.0],
                ['name' => 'Pitufo (450ml)', 'price' => 60.0],
                ['name' => 'Pitufo (900ml)', 'price' => 105.0],
                ['name' => 'Whisky (450ml)', 'price' => 70.0],
                ['name' => 'Whisky (900ml)', 'price' => 120.0],
            ],
            'Cantaritos' => [
                ['name' => 'Cantarito Caballito (Tequila)', 'price' => 40.0],
                ['name' => 'Cantarito Caballito (Mezcal)', 'price' => 60.0],
                ['name' => 'Cantarito Chico 450ml (Tequila)', 'price' => 65.0],
                ['name' => 'Cantarito Chico 450ml (Mezcal)', 'price' => 85.0],
                ['name' => 'Cantarito Mediano 900ml (Tequila)', 'price' => 100.0],
                ['name' => 'Cantarito Mediano 900ml (Mezcal)', 'price' => 130.0],
                ['name' => 'Cantarito Cazuela 1.2lt (Tequila)', 'price' => 130.0],
                ['name' => 'Cantarito Cazuela 1.2lt (Mezcal)', 'price' => 150.0],
                ['name' => 'Cantarito Grande 5lt (Tequila)', 'price' => 550.0],
                ['name' => 'Cantarito Grande 5lt (Mezcal)', 'price' => 600.0],
                ['name' => 'Cantarito Extra Grande 10lt (Tequila)', 'price' => 1050.0],
                ['name' => 'Cantarito Extra Grande 10lt (Mezcal)', 'price' => 1150.0],
                ['name' => 'Cantarito Jumbo 15lt (Tequila)', 'price' => 1600.0],
                ['name' => 'Cantarito Jumbo 15lt (Mezcal)', 'price' => 1700.0],
            ],
            'Refrescos' => [
                ['name' => 'Coca', 'price' => 30.0],
                ['name' => 'Coca Light', 'price' => 30.0],
                ['name' => 'Fanta', 'price' => 30.0],
                ['name' => 'Fresca', 'price' => 30.0],
                ['name' => 'Sidral', 'price' => 30.0],
                ['name' => 'Sprite', 'price' => 30.0],
                ['name' => 'Guayaba', 'price' => 30.0],
                ['name' => 'Mango', 'price' => 30.0],
                ['name' => 'Delaware', 'price' => 30.0],
            ],
            'Cerveza' => [
                ['name' => 'Corona (375ml)', 'price' => 45.0],
                ['name' => 'Victoria (375ml)', 'price' => 45.0],
                ['name' => 'Negra Modelo (375ml)', 'price' => 50.0],
                ['name' => 'Modelo Especial (375ml)', 'price' => 50.0],
                ['name' => 'Corona (1.2lt)', 'price' => 75.0],
                ['name' => 'Victoria (1.2lt)', 'price' => 75.0],
            ],
        ];

        foreach ($categories as $categoryName => $products) {
            $category = Category::create([
                'name' => $categoryName,
                'is_active' => true,
            ]);

            foreach ($products as $product) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'is_active' => true,
                ]);
            }

            $this->line("✓ Categoría '{$categoryName}' creada con " . count($products) . " productos");
        }

        $this->info("\n✓ Menú de La Patrona actualizado exitosamente!");
        $this->info("Total de categorías: " . count($categories));
        $this->info("Total de productos: " . Product::count());
    }
}
