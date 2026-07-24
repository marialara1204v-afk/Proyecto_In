<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generar un QR code y guardarlo en storage
     *
     * @param string $data - Datos a codificar (ej: URL)
     * @param string $name - Nombre del archivo (sin extensión)
     * @return string - URL pública del QR
     */
    public static function generate(string $data, string $name = 'qrcode'): string
    {
        // Crear el directorio si no existe
        $directory = storage_path('app/public/qrcodes');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = "{$name}_" . now()->format('Y_m_d') . '.png';
        $filepath = storage_path("app/public/qrcodes/{$filename}");

        $options = new QROptions([
            'version'      => Version::AUTO,
            'outputType'   => QRGdImagePNG::class,
            'imageBase64'  => false,
            'scale'        => 8,
            'quietZone'    => 1,
            'eccLevel'     => EccLevel::H, // Máxima corrección de errores (30%)
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($data, $filepath); // Pasa la data en render() y la ruta del archivo

        return Storage::disk('public')->url('qrcodes/' . $filename);
    }

    /**
     * Generar un único QR para el menú digital
     * Este QR apunta a la URL del menú que contiene dos secciones: Comida y Bebidas
     *
     * @return string - URL pública del QR
     */
    public static function generateMenuQR(): string
    {
        $url = route('menu.digital');
        return self::generate($url, 'menu-digital');
    }

    /**
     * Generar un QR para auto-servicio de una mesa específica
     *
     * @param int $tableNumber - Número de la mesa
     * @return string - URL pública del QR
     */
    public static function generateTableQR(int $tableNumber): string
    {
        $url = route('menu.selfservice', $tableNumber);
        return self::generate($url, "qr-mesa-{$tableNumber}");
    }

    /**
     * Generar un QR para una categoría específica del menú
     *
     * @param int $categoryId - ID de la categoría
     * @return string - URL pública del QR
     */
    public static function generateCategoryQR(int $categoryId): string
    {
        $url = route('menu.category', $categoryId);
        return self::generate($url, "menu-categoria-{$categoryId}");
    }

    /**
     * Generar un QR para un producto específico del menú
     *
     * @param int $productId - ID del producto
     * @return string - URL pública del QR
     */
    public static function generateProductQR(int $productId): string
    {
        $url = route('menu.product', $productId);
        return self::generate($url, "menu-producto-{$productId}");
    }
}

