<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class LoadProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga las imágenes de productos desde storage/temp/productos/';

    /**
     * Mapeo de imagen a producto según la identificación del usuario
     * IMPORTANTE: Los nombres deben coincidir EXACTAMENTE con los de la BD
     */
    protected $imageMapping = [
        '1' => 'Cantarito Caballito',
        '2' => 'Quesabirria',
        '3' => 'Cantarito Chico 450ml',
        '4' => 'Mojito',
        '5' => 'Mulita (Maíz)',
        '6' => 'Nachos (Con Birria)',
        '7' => 'Nachos',
        '8' => 'Birriamen',
        '9' => 'Taco Dorado',
        '10' => 'Birria',
        '11' => 'Piña Colada',
        '12' => 'Sincronizada',
        '13' => 'Burrito + consomé + papas + guacamole + Refresco',
        '14' => 'Cantarito Grande 5lt',
        '15' => 'Sangría Preparada',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tempPath = storage_path('temp/productos');

        // Verificar que la carpeta temporal existe
        if (!is_dir($tempPath)) {
            $this->error("❌ La carpeta storage/temp/productos/ no existe.");
            $this->info("📁 Por favor, coloca tus imágenes en: " . $tempPath);
            return 1;
        }

        // Obtener archivos de imagen
        $files = glob($tempPath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

        if (empty($files)) {
            $this->error("❌ No se encontraron imágenes en storage/temp/productos/");
            return 1;
        }

        $this->info("📸 Iniciando carga de " . count($files) . " imágenes...\n");

        $loaded = 0;
        $failed = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            // Extraer el número del nombre del archivo (ej: "1.jpg" → "1")
            preg_match('/^(\d+)/', $filename, $matches);
            $imageNumber = $matches[1] ?? null;

            if (!$imageNumber || !isset($this->imageMapping[$imageNumber])) {
                $this->warn("⚠️  No se puede identificar la imagen: $filename");
                $failed++;
                continue;
            }

            $productName = $this->imageMapping[$imageNumber];
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                $this->warn("⚠️  Producto no encontrado: $productName (imagen: $filename)");
                $failed++;
                continue;
            }

            try {
                // Generar nombre del archivo de destino
                $destFilename = strtolower(str_replace(' ', '-', $product->name)) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

                // Copiar archivo a storage/app/public/productos/
                $content = file_get_contents($filePath);
                Storage::disk('public')->put('productos/' . $destFilename, $content);

                // Actualizar base de datos
                $product->update(['image' => 'productos/' . $destFilename]);

                $this->info("✅ [$imageNumber] $productName → $destFilename");
                $loaded++;
            } catch (\Exception $e) {
                $this->error("❌ Error cargando $filename: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("🎉 Resumen:");
        $this->info("   ✅ Cargadas: $loaded imágenes");
        if ($failed > 0) {
            $this->warn("   ⚠️  Fallidas: $failed imágenes");
        }

        return 0;
    }
}
