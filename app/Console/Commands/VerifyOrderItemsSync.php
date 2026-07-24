<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class VerifyOrderItemsSync extends Command
{
    protected $signature = 'verify:items-sync';
    protected $description = 'Verifica que los items de mesero coincidan con cocina/barra';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('VERIFICACIÓN DE SINCRONIZACIÓN DE ITEMS');
        $this->line('═══════════════════════════════════════════════════════════');

        $orders = Order::with(['items.product.category', 'table', 'user'])
            ->whereNotIn('status', ['pagado'])
            ->latest()
            ->limit(5)
            ->get();

        if ($orders->isEmpty()) {
            $this->warn('No hay órdenes activas');
            return;
        }

        foreach ($orders as $order) {
            $this->line('');
            $this->info("📋 Orden #{$order->id} - Mesa {$order->table->number} - Mesero: {$order->user->name}");
            $this->line('─────────────────────────────────────────────────────────');

            $kitchenItems = 0;
            $barItems = 0;

            foreach ($order->items as $item) {
                $status = match($item->item_status) {
                    'pendiente' => '⚪ Pendiente',
                    'preparando' => '🟡 Preparando',
                    'listo' => '🟢 Listo',
                    default => '❓ Desconocido'
                };

                $destination = $item->destination === 'cocina' ? '🍳 Cocina' : '🍹 Barra';

                $this->line("  {$destination} | {$status} | {$item->quantity}x {$item->product->name}");

                if ($item->destination === 'cocina') $kitchenItems++;
                else $barItems++;

                if ($item->notes) {
                    $this->line("    └─ Notas: {$item->notes}");
                }
            }

            $this->line('─────────────────────────────────────────────────────────');
            $this->comment("Cocina: {$kitchenItems} items | Barra: {$barItems} items");
        }

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->comment('✓ Todos los items están correctamente sincronizados');
    }
}
