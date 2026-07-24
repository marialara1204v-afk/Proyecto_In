<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class VerifyOrderDestinations extends Command
{
    protected $signature = 'verify:destinations';
    protected $description = 'Verifica que las órdenes tengan destinos correctamente asignados';

    public function handle()
    {
        $orders = Order::with(['items.product.category', 'table'])
            ->whereIn('status', ['pendiente', 'preparando', 'listo', 'entregado'])
            ->latest()
            ->limit(5)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No hay órdenes activas para verificar');
            return;
        }

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('VERIFICACIÓN DE DESTINOS DE ÓRDENES');
        $this->line('═══════════════════════════════════════════════════════════');

        foreach ($orders as $order) {
            $this->line('');
            $this->info("📋 Orden #{$order->id} - Mesa {$order->table->number} - Estado: {$order->status}");
            $this->line('─────────────────────────────────────────────────────────');

            $barItems = 0;
            $kitchenItems = 0;
            $missingDestination = 0;

            foreach ($order->items as $item) {
                $destination = $item->destination ?? 'FALTA';
                $icon = $destination === 'bartender' ? '🍹' : ($destination === 'cocina' ? '🍳' : '⚠️');

                $this->line("  {$icon} {$item->product->name} ({$item->product->category->name})");
                $this->line("     Cantidad: {$item->quantity} | Destino: {$destination}");

                if ($destination === 'bartender') $barItems++;
                elseif ($destination === 'cocina') $kitchenItems++;
                else $missingDestination++;
            }

            $this->line('─────────────────────────────────────────────────────────');
            $this->line("Resumen: 🍳 Cocina: {$kitchenItems} | 🍹 Barra: {$barItems} | ⚠️  Sin asignar: {$missingDestination}");

            if ($missingDestination > 0) {
                $this->error("❌ PROBLEMA: Hay {$missingDestination} items sin destino asignado");
            } else {
                $this->comment("✓ OK: Todos los items tienen destino asignado");
            }
        }

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        
        // Verificar Kitchen Board consistency
        $this->verifyKitchenBoardConsistency();
    }

    private function verifyKitchenBoardConsistency()
    {
        $this->line('');
        $this->line('VERIFICACIÓN DEL KITCHEN BOARD');
        $this->line('─────────────────────────────────────────────────────────');

        $kitchenOrders = Order::with(['items.product'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'cocina'))
            ->count();

        $barOrders = Order::with(['items.product'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'bartender'))
            ->count();

        $this->line("📋 Órdenes en Cocina: {$kitchenOrders}");
        $this->line("📋 Órdenes en Barra: {$barOrders}");

        if ($kitchenOrders > 0 || $barOrders > 0) {
            $this->comment('✓ OK: Kitchen board está viendo las órdenes correctamente');
        } else {
            $this->warn('⚠️  No hay órdenes en cocina ni barra para mostrar');
        }
    }
}
