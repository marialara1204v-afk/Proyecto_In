<?php

namespace App\Console\Commands;

use App\Services\QRCodeService;
use Illuminate\Console\Command;

class GenerateMenuQRCodes extends Command
{
    protected $signature = 'qr:generate-menu';
    protected $description = 'Genera un código QR para el menú digital (Comida y Bebidas)';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('GENERADOR DE CÓDIGO QR - MENÚ DIGITAL');
        $this->line('═══════════════════════════════════════════════════════════');

        try {
            // Generar un único QR para el menú completo
            $this->info('📱 Generando código QR del menú digital...');
            $qrUrl = QRCodeService::generateMenuQR();
            
            $this->line('');
            $this->info('✓ QR Generado Exitosamente');
            $this->line('');
            
            $this->comment('URL del Menú Digital:');
            $this->line("  🔗 " . url('qr-menu'));
            
            $this->comment('Imagen del QR:');
            $this->line("  📷 " . $qrUrl);
            
            $this->line('');
            $this->comment('Estructura del Menú:');
            $this->line("  🍽️  Sección 1: COMIDAS");
            $this->line("  🥤 Sección 2: BEBIDAS");
            
            $this->line('');
            $this->line('═══════════════════════════════════════════════════════════');
            $this->info('✨ LISTO PARA USAR');
            $this->line('Comparte el QR con tus clientes para que accedan al menú');
            $this->line('═══════════════════════════════════════════════════════════');
            $this->line('');

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

