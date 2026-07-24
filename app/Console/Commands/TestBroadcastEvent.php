<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use App\Events\ItemReady;
use Illuminate\Console\Command;

class TestBroadcastEvent extends Command
{
    protected $signature = 'test:broadcast-event';
    protected $description = 'Prueba que el evento ItemReady se dispara correctamente';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('TEST: BROADCAST EVENT ITEMREADY');
        $this->line('═══════════════════════════════════════════════════════════');

        // Obtener un item
        $item = OrderItem::with('order.table', 'product')
            ->whereNotIn('item_status', ['listo'])
            ->first();

        if (!$item) {
            $this->error('No hay items para probar');
            return;
        }

        $this->line('');
        $this->comment("Item seleccionado: {$item->product->name} (ID: {$item->id})");
        $this->comment("Orden: #{$item->order->id}, Mesa: {$item->order->table->number}");

        // Actualizar a listo
        $this->info('PASO 1: Marcando item como LISTO...');
        $item->update([
            'item_status' => 'listo',
            'prepared_at' => now(),
        ]);
        $this->line('  ✓ Item actualizado a listo en BD');

        // Disparar evento
        $this->info('PASO 2: Disparando evento ItemReady...');
        try {
            ItemReady::dispatch(
                $item->order->id,
                $item->order->table->number,
                $item->product->name
            );
            $this->line('  ✓ Evento disparado correctamente');
        } catch (\Exception $e) {
            $this->error('  ✗ Error al disparar evento: ' . $e->getMessage());
            return;
        }

        // Información del evento
        $this->info('PASO 3: Detalles del evento');
        $this->line("  Channel: 'waiter-notifications'");
        $this->line("  Event name: 'item-ready'");
        $this->line("  Payload:");
        $this->line("    - orderId: {$item->order->id}");
        $this->line("    - tableNumber: {$item->order->table->number}");
        $this->line("    - itemName: {$item->product->name}");

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->comment('✓ Evento broadcast completado');
        $this->comment('⚠️  Nota: Verifica en el navegador del mesero si recibe la notificación');
    }
}
