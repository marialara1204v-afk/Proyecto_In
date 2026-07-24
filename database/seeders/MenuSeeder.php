<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // COMIDA - BIRRIA
        $birria = Category::firstOrCreate(['name' => 'Birria']);
        Product::updateOrCreate(['name' => 'Birria', 'category_id' => $birria->id], ['price' => 110.0, 'variants' => [['name' => 'Plato', 'price' => 110.0], ['name' => 'Litro', 'price' => 170.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Consomé', 'category_id' => $birria->id], ['price' => 50.0, 'variants' => [['name' => 'Plato', 'price' => 50.0], ['name' => 'Litro', 'price' => 80.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Birriamen', 'category_id' => $birria->id], ['price' => 150.0, 'variants' => [['name' => 'Sin Queso', 'price' => 150.0], ['name' => 'Con Queso', 'price' => 170.0]], 'is_available' => true]);

        // TACOS
        $tacos = Category::firstOrCreate(['name' => 'Tacos']);
        Product::updateOrCreate(['name' => 'Taco Dorado', 'category_id' => $tacos->id], ['price' => 45.0, 'variants' => [['name' => 'Maíz', 'price' => 45.0], ['name' => 'Harina', 'price' => 55.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Taco Blando', 'category_id' => $tacos->id], ['price' => 45.0, 'variants' => [['name' => 'Maíz', 'price' => 45.0], ['name' => 'Harina', 'price' => 55.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Quesabirria', 'category_id' => $tacos->id], ['price' => 50.0, 'variants' => [['name' => 'Maíz', 'price' => 50.0], ['name' => 'Harina', 'price' => 65.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Mulita', 'category_id' => $tacos->id], ['price' => 75.0, 'variants' => [['name' => 'Maíz', 'price' => 75.0], ['name' => 'Harina', 'price' => 90.0]], 'is_available' => true]);

        // SNAKS
        $snaks = Category::firstOrCreate(['name' => 'Snaks']);
        Product::updateOrCreate(['name' => 'Costra', 'category_id' => $snaks->id], ['description' => 'Con queso + papa + guacamole', 'price' => 150.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Wrap', 'category_id' => $snaks->id], ['description' => 'Con queso + papa + guacamole', 'price' => 150.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Nachos', 'category_id' => $snaks->id], ['description' => 'Opciones: Rajas, Guacamole, Queso, Catsup, Salsa, Mayonesa', 'price' => 100.0, 'variants' => [['name' => 'Solos', 'price' => 100.0], ['name' => 'Con Birria', 'price' => 190.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Papas Fritas', 'category_id' => $snaks->id], ['description' => 'Opciones: Rajas, Guacamole, Queso, Catsup, Salsa, Mayonesa', 'price' => 70.0, 'variants' => [['name' => 'Solos', 'price' => 70.0], ['name' => 'Con Birria', 'price' => 150.0]], 'is_available' => true]);

        // MENÚ INFANTIL
        $infantil = Category::firstOrCreate(['name' => 'Menú Infantil']);
        Product::updateOrCreate(['name' => 'Sincronizada', 'category_id' => $infantil->id], ['price' => 45.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Nuggets con papas fritas', 'category_id' => $infantil->id], ['description' => 'Complementos: Catsup, Salsa, Queso, Mayonesa', 'price' => 130.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Salchipulpos con papas fritas', 'category_id' => $infantil->id], ['description' => 'Complementos: Catsup, Salsa, Queso, Mayonesa', 'price' => 130.0, 'is_available' => true]);

        // PAQUETES
        $paquetes = Category::firstOrCreate(['name' => 'Paquetes']);
        Product::updateOrCreate(['name' => '1kg Birria + 2lt consomé', 'category_id' => $paquetes->id], ['price' => 750.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Pizza + 1lt consomé', 'category_id' => $paquetes->id], ['price' => 280.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => '3 QBM + consomé + refresco', 'category_id' => $paquetes->id], ['price' => 160.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Quesabirria Jumbo + Consomé + Refresco', 'category_id' => $paquetes->id], ['price' => 175.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Burrito + consomé + papas + guacamole + Refresco', 'category_id' => $paquetes->id], ['price' => 195.0, 'is_available' => true]);

        // POSTRES
        $postres = Category::firstOrCreate(['name' => 'Postres']);
        Product::updateOrCreate(['name' => 'Flan Napolitano', 'category_id' => $postres->id], ['price' => 50.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Fresas con Crema', 'category_id' => $postres->id], ['price' => 45.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Carlota de Limón', 'category_id' => $postres->id], ['price' => 45.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Pay Queso con Zarzamora', 'category_id' => $postres->id], ['price' => 50.0, 'is_available' => true]);

        // BEBIDAS CALIENTES
        $calientes = Category::firstOrCreate(['name' => 'Bebidas Calientes']);
        Product::updateOrCreate(['name' => 'Café / Té', 'category_id' => $calientes->id], ['price' => 25.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Ponche de Temporada', 'category_id' => $calientes->id], ['price' => 30.0, 'is_available' => true]);

        // BEBIDAS FRÍAS
        $frias = Category::firstOrCreate(['name' => 'Bebidas Frías']);
        Product::updateOrCreate(['name' => 'Tehuacán Preparado', 'category_id' => $frias->id], ['price' => 50.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Sangría Preparada', 'category_id' => $frias->id], ['price' => 50.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Agua Horchata', 'category_id' => $frias->id], ['price' => 25.0, 'variants' => [['name' => 'Vaso', 'price' => 25.0], ['name' => 'Litro', 'price' => 40.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Limonada', 'category_id' => $frias->id], ['price' => 50.0, 'variants' => [['name' => 'Natural', 'price' => 50.0], ['name' => 'Mineral', 'price' => 60.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Naranjada', 'category_id' => $frias->id], ['price' => 50.0, 'variants' => [['name' => 'Natural', 'price' => 50.0], ['name' => 'Mineral', 'price' => 60.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Agua', 'category_id' => $frias->id], ['price' => 20.0, 'variants' => [['name' => 'Natural', 'price' => 20.0], ['name' => 'Mineral', 'price' => 35.0]], 'is_available' => true]);

        // COCTELERÍA SIN ALCOHOL
        $coctelSin = Category::firstOrCreate(['name' => 'Coctelería Sin Alcohol']);
        Product::updateOrCreate(['name' => 'Piña Colada', 'category_id' => $coctelSin->id], ['price' => 70.0, 'variants' => [['name' => '450ml', 'price' => 70.0], ['name' => '900ml', 'price' => 130.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Mojito', 'category_id' => $coctelSin->id], ['price' => 50.0, 'variants' => [['name' => '450ml', 'price' => 50.0], ['name' => '900ml', 'price' => 95.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Pitufo', 'category_id' => $coctelSin->id], ['price' => 50.0, 'variants' => [['name' => '450ml', 'price' => 50.0], ['name' => '900ml', 'price' => 95.0]], 'is_available' => true]);

        // COCTELERÍA CON ALCOHOL
        $coctelCon = Category::firstOrCreate(['name' => 'Coctelería Con Alcohol']);
        Product::updateOrCreate(['name' => 'Piña Colada', 'category_id' => $coctelCon->id], ['price' => 80.0, 'variants' => [['name' => '450ml', 'price' => 80.0], ['name' => '900ml', 'price' => 140.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Mojito', 'category_id' => $coctelCon->id], ['price' => 60.0, 'variants' => [['name' => '450ml', 'price' => 60.0], ['name' => '900ml', 'price' => 105.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Pitufo', 'category_id' => $coctelCon->id], ['price' => 60.0, 'variants' => [['name' => '450ml', 'price' => 60.0], ['name' => '900ml', 'price' => 105.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Whisky', 'category_id' => $coctelCon->id], ['price' => 70.0, 'variants' => [['name' => '450ml', 'price' => 70.0], ['name' => '900ml', 'price' => 120.0]], 'is_available' => true]);

        // CANTARITOS
        $cantaritos = Category::firstOrCreate(['name' => 'Cantaritos']);
        Product::updateOrCreate(['name' => 'Cantarito Caballito', 'category_id' => $cantaritos->id], ['price' => 40.0, 'variants' => [['name' => 'Tequila', 'price' => 40.0], ['name' => 'Mezcal', 'price' => 60.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Chico 450ml', 'category_id' => $cantaritos->id], ['price' => 65.0, 'variants' => [['name' => 'Tequila', 'price' => 65.0], ['name' => 'Mezcal', 'price' => 85.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Mediano 900ml', 'category_id' => $cantaritos->id], ['price' => 100.0, 'variants' => [['name' => 'Tequila', 'price' => 100.0], ['name' => 'Mezcal', 'price' => 130.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Cazuela 1.2lt', 'category_id' => $cantaritos->id], ['price' => 130.0, 'variants' => [['name' => 'Tequila', 'price' => 130.0], ['name' => 'Mezcal', 'price' => 150.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Grande 5lt', 'category_id' => $cantaritos->id], ['price' => 550.0, 'variants' => [['name' => 'Tequila', 'price' => 550.0], ['name' => 'Mezcal', 'price' => 600.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Extra Grande 10lt', 'category_id' => $cantaritos->id], ['price' => 1050.0, 'variants' => [['name' => 'Tequila', 'price' => 1050.0], ['name' => 'Mezcal', 'price' => 1150.0]], 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cantarito Jumbo 15lt', 'category_id' => $cantaritos->id], ['price' => 1600.0, 'variants' => [['name' => 'Tequila', 'price' => 1600.0], ['name' => 'Mezcal', 'price' => 1700.0]], 'is_available' => true]);

        // REFRESCOS
        $refrescos = Category::firstOrCreate(['name' => 'Refrescos']);
        Product::updateOrCreate(['name' => 'Refresco', 'category_id' => $refrescos->id], ['description' => 'Coca, Coca Light, Fanta, Fresca, Sidral, Sprite, Guayaba, Mango, Delaware', 'price' => 30.0, 'is_available' => true]);

        // CERVEZA
        $cerveza = Category::firstOrCreate(['name' => 'Cerveza']);
        Product::updateOrCreate(['name' => 'Cerveza Corona / Victoria 375ml', 'category_id' => $cerveza->id], ['description' => 'Extras: Chelada +$5, Cubana +$10, Clamato +$15, Frutichela +$25', 'price' => 45.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cerveza Negra Modelo / Modelo Especial 375ml', 'category_id' => $cerveza->id], ['description' => 'Extras: Chelada +$5, Cubana +$10, Clamato +$15, Frutichela +$25', 'price' => 50.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cerveza Corona / Victoria 1.2lt', 'category_id' => $cerveza->id], ['description' => 'Extras: Chelada +$15, Cubana +$20, Clamato +$25, Frutichela +$45', 'price' => 75.0, 'is_available' => true]);
        Product::updateOrCreate(['name' => 'Cerveza Negra Modelo / Modelo Especial 1.2lt', 'category_id' => $cerveza->id], ['description' => 'Extras: Chelada +$15, Cubana +$20, Clamato +$25, Frutichela +$45', 'price' => 80.0, 'is_available' => true]);
    }
}
