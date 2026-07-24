<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CleanupTestOrders extends Command
{
    protected $signature = 'cleanup:test-orders';
    protected $description = 'Elimina órdenes de prueba que causan inconsistencias';

    public function handle()
    {
        $this->info('🧹 Limpiando órdenes de prueba...\n');

        // Eliminar todas las órdenes cuyo total no coincide con sus items
        $inconsistentes = Order::all()->filter(function ($order) {
            $calculado = $order->items()->sum('subtotal');
            return abs($order->total - $calculado) > 0.01;
        });

        if ($inconsistentes->count() > 0) {
            $this->line("Eliminando {$inconsistentes->count()} órdenes inconsistentes:");
            foreach ($inconsistentes as $order) {
                $this->line("  - Orden #{$order->id} (Total: {$order->total}, Items: {$order->items()->sum('subtotal')})");
                $order->delete();
            }
        } else {
            $this->line("> No hay ordenes inconsistentes");
        }

        // Limpiar mesas que tengan órdenes pero estén marcadas como libre
        $mesas = \App\Models\Table::all();
        foreach ($mesas as $mesa) {
            $ordenesPendientes = $mesa->orders()
                ->whereIn('status', ['pendiente', 'preparando', 'listo', 'entregado'])
                ->count();

            if ($ordenesPendientes > 0 && $mesa->status === 'libre') {
                $this->warn("Mesa #{$mesa->number}: tiene {$ordenesPendientes} órdenes pendientes pero está libre");
                // Marcar mesa como ocupada si tiene órdenes pendientes
                $mesa->update(['status' => 'ocupada']);
            }
        }

        // Eliminar órdenes sin items
        $sinItems = Order::doesntHave('items')->get();
        if ($sinItems->count() > 0) {
            $this->line("\nEliminando {$sinItems->count()} órdenes sin items:");
            foreach ($sinItems as $order) {
                $this->line("  - Orden #{$order->id}");
                $order->delete();
            }
        }

        $this->info('\n✨ Limpieza completada');
    }
}
