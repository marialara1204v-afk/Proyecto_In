<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Console\Command;

class TestItemStatusFlow extends Command
{
    protected $signature = 'test:item-status-flow';
    protected $description = 'Prueba el flujo completo de cambio de estado de items';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('TEST: FLUJO DE ESTADO DE ITEMS');
        $this->line('═══════════════════════════════════════════════════════════');

        // Obtener una orden activa
        $order = Order::whereNotIn('status', ['pagado'])
            ->with('items')
            ->first();

        if (!$order) {
            $this->error('No hay órdenes activas');
            return;
        }

        $this->line('');
        $this->comment("Orden seleccionada: #{$order->id} - Mesa {$order->table->number}");
        $this->line('─────────────────────────────────────────────────────────');

        // Obtener un item pendiente
        $item = $order->items()->where('item_status', 'pendiente')->first();

        if (!$item) {
            $this->warn('No hay items pendientes. Usando primer item.');
            $item = $order->items()->first();
        }

        $this->line("Item seleccionado: {$item->product->name} (ID: {$item->id})");
        $this->line('');

        // Estado inicial
        $this->info('PASO 1: Estado inicial');
        $this->line("  Status: {$item->item_status}");
        $this->line("  Prepared at: {$item->prepared_at}");

        // Cambiar a preparando
        $this->info('PASO 2: Cambiar a PREPARANDO');
        $item->markPreparing();
        $this->line("  ✓ Status actualizado a: {$item->fresh()->item_status}");

        // Cambiar a listo
        $this->info('PASO 3: Cambiar a LISTO');
        $item->markReady();
        $freshItem = $item->fresh();
        $this->line("  ✓ Status actualizado a: {$freshItem->item_status}");
        $this->line("  ✓ Prepared at: {$freshItem->prepared_at}");

        // Verificar que se puede buscar por estado
        $this->info('PASO 4: Verificar queries');
        $readyCount = OrderItem::where('item_status', 'listo')->count();
        $this->line("  ✓ Total items listos en BD: {$readyCount}");

        // Verificar helpers
        $this->info('PASO 5: Verificar helpers');
        $this->line("  isReady(): " . ($freshItem->isReady() ? '✓ true' : '✗ false'));
        $this->line("  isPreparing(): " . ($freshItem->isPreparing() ? '✓ true' : '✗ false'));
        $this->line("  isPending(): " . ($freshItem->isPending() ? '✓ true' : '✗ false'));

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->comment('✓ Test completado exitosamente');
    }
}
