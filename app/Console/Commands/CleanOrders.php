<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use Illuminate\Console\Command;

class CleanOrders extends Command
{
    protected $signature = 'orders:clean';
    protected $description = 'Limpia órdenes antiguas y resetea estados de mesas';

    public function handle()
    {
        $this->info('🧹 Limpiando órdenes...');

        // Eliminar órdenes incompletas de hace más de 1 día
        $deleted = Order::where('status', '!=', 'pagado')
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $this->info("❌ Eliminadas $deleted órdenes antiguas incompletas");

        // Resetear todas las mesas a 'libre' que no tengan órdenes sin pagar
        Table::all()->each(function (Table $table) {
            $hasUnpaidOrders = $table->orders()
                ->where('status', '!=', 'pagado')
                ->exists();

            if (!$hasUnpaidOrders && $table->status !== 'libre') {
                $table->update(['status' => 'libre']);
                $this->line("> Mesa {$table->number} resetizada a libre");
            }
        });

        // Recalcular totales de todas las órdenes activas
        Order::where('status', '!=', 'pagado')->each(function (Order $order) {
            $order->recalculateTotal();
            if ($order->total < 0) {
                $this->warn("⚠️  Orden {$order->id} tiene total negativo: {$order->total}");
            }
        });

        $this->info('✨ Limpieza completada');
    }
}
