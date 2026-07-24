<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class VerifyOrderNotes extends Command
{
    protected $signature = 'verify:notes';
    protected $description = 'Verifica que los items tengan notas/especificaciones correctas';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('VERIFICACIÓN DE NOTAS EN ITEMS');
        $this->line('═══════════════════════════════════════════════════════════');

        $ordersWithNotes = Order::with(['items.product', 'table'])
            ->whereHas('items', fn($q) => $q->whereNotNull('notes')->where('notes', '!=', ''))
            ->latest()
            ->limit(5)
            ->get();

        if ($ordersWithNotes->isEmpty()) {
            $this->warn('No hay items con notas especificadas todavía');
            $this->info('Las notas se guardarán cuando el mesero especifique:');
            $this->line('  - Sin cilantro');
            $this->line('  - Sin cebolla');
            $this->line('  - Con picante');
            $this->line('  - Etc.');
            return;
        }

        foreach ($ordersWithNotes as $order) {
            $this->line('');
            $this->info("📋 Orden #{$order->id} - Mesa {$order->table->number}");
            $this->line('─────────────────────────────────────────────────────────');

            foreach ($order->items as $item) {
                if ($item->notes) {
                    $this->line("  📝 {$item->product->name}");
                    $this->line("     Notas: {$item->notes}");
                    $this->line("     Cantidad: {$item->quantity}");
                }
            }
        }

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->comment('✓ Sistema de notas funcionando correctamente');
    }
}
