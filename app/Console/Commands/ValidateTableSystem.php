<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use Illuminate\Console\Command;

class ValidateTableSystem extends Command
{
    protected $signature = 'system:validate-tables';
    protected $description = 'Valida el estado del sistema de mesas';

    public function handle()
    {
        $this->info('Validando sistema de mesas...');
        
        // Verificar mesas
        $tables = Table::all();
        $this->line("\n📊 MESAS TOTALES: {$tables->count()}");
        
        foreach ($tables as $table) {
            $orders = $table->orders()->count();
            $unpaidOrders = $table->orders()->where('status', '!=', 'pagado')->count();
            $this->line("  Mesa {$table->number}: {$orders} órdenes, {$unpaidOrders} sin pagar | Status: {$table->status}");
        }

        // Verificar órdenes por estado
        $this->line("\n📋 ÓRDENES POR ESTADO:");
        $statuses = ['pendiente', 'preparando', 'listo', 'entregado', 'pagado'];
        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $this->line("  {$status}: {$count}");
        }

        // Validar integridad
        $this->line("\n✅ VALIDACIONES:");
        
        // 1. Mesas con órdenes entregadas pero mesa no marcada como entregado
        $mismatch = Table::where('status', '!=', 'entregado')
            ->whereHas('orders', fn($q) => $q->where('status', 'entregado'))
            ->count();
        
        if ($mismatch > 0) {
            $this->warn("  ⚠️ {$mismatch} mesas tienen órdenes entregadas pero su status no es 'entregado'");
        } else {
            $this->line("  ✓ Estados de mesas sincronizados correctamente");
        }

        // 2. Órdenes con total negativo
        $negative = Order::where('total', '<', 0)->count();
        if ($negative > 0) {
            $this->warn("  ⚠️ {$negative} órdenes con total negativo");
        } else {
            $this->line("  ✓ Todos los totales son positivos");
        }

        // 3. Órdenes sin items pero con total
        $orphaned = Order::whereDoesntHave('items')->where('status', '!=', 'pagado')->count();
        if ($orphaned > 0) {
            $this->warn("  ⚠️ {$orphaned} órdenes sin items");
        } else {
            $this->line("  ✓ Todas las órdenes tienen items");
        }

        $this->info('\n Validación completada');
    }
}
