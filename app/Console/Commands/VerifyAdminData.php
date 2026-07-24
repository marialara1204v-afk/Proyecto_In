<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Console\Command;

class VerifyAdminData extends Command
{
    protected $signature = 'verify:admin-data';
    protected $description = 'Verifica que el admin pueda ver los datos correctamente';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('VERIFICACIÓN DE DATOS DEL ADMINISTRADOR');
        $this->line('═══════════════════════════════════════════════════════════');

        // Verificar mesas activas
        $this->verifyActiveTables();

        // Verificar órdenes pagadas
        $this->verifyPaidOrders();

        // Verificar órdenes pendientes de pago
        $this->verifyPendingPayment();

        // Verificar productos más vendidos
        $this->verifyTopProducts();

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->comment('✓ VERIFICACIÓN COMPLETADA');
        $this->line('═══════════════════════════════════════════════════════════');
    }

    private function verifyActiveTables()
    {
        $this->line('');
        $this->info('MESAS ACTIVAS');
        $this->line('─────────────────────────────────────────────────────────');

        $activeTables = Table::whereIn('status', ['ocupada', 'pedido_listo'])->count();
        $totalTables = Table::count();
        $freeTables = Table::where('status', 'libre')->count();

        $this->line("Total de mesas: {$totalTables}");
        $this->line("Mesas libres: {$freeTables}");
        $this->line("Mesas ocupadas/esperando: {$activeTables}");

        if ($activeTables > 0) {
            $this->comment('✓ OK: Hay mesas activas siendo rastreadas');
        } else {
            $this->warn('⚠️  No hay mesas activas actualmente');
        }
    }

    private function verifyPaidOrders()
    {
        $this->line('');
        $this->info('ORDENES PAGADAS (HOY)');
        $this->line('─────────────────────────────────────────────────────────');

        $paidOrders = Order::where('status', 'pagado')
            ->whereDate('created_at', today())
            ->count();

        $totalSales = Order::where('status', 'pagado')
            ->whereDate('created_at', today())
            ->sum('total');

        $avgTicket = $paidOrders > 0 ? $totalSales / $paidOrders : 0;

        $this->line("Órdenes pagadas: {$paidOrders}");
        $this->line("Total de ventas: \${$totalSales}");
        $this->line("Ticket promedio: \${$avgTicket}");

        if ($paidOrders > 0) {
            $this->comment('✓ OK: Se está registrando correctamente las ventas');
        } else {
            $this->warn('⚠️  No hay órdenes pagadas hoy');
        }
    }

    private function verifyPendingPayment()
    {
        $this->line('');
        $this->info('ORDENES PENDIENTES DE PAGO');
        $this->line('─────────────────────────────────────────────────────────');

        $pendingOrders = Order::with(['table', 'items'])
            ->where('status', 'entregado')
            ->orderBy('created_at')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->comment('✓ OK: No hay órdenes pendientes de pago');
        } else {
            $this->warn("⚠️  Hay {$pendingOrders->count()} órdenes pendientes de pago:");
            foreach ($pendingOrders as $order) {
                $this->line("  - Orden #{$order->id} (Mesa {$order->table->number}): \${$order->total}");
            }
        }
    }

    private function verifyTopProducts()
    {
        $this->line('');
        $this->info('PRODUCTOS MAS VENDIDOS');
        $this->line('─────────────────────────────────────────────────────────');

        $topProducts = OrderItem::select('product_id', \DB::raw('SUM(quantity) as total_sold'), \DB::raw('SUM(subtotal) as revenue'))
            ->with('product.category')
            ->whereHas('order', function ($q) {
                $q->where('status', 'pagado')
                  ->whereDate('created_at', today());
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        if ($topProducts->isEmpty()) {
            $this->warn('⚠️  No hay datos de productos vendidos hoy');
        } else {
            $this->comment('✓ TOP 5 productos vendidos:');
            foreach ($topProducts as $item) {
                $this->line("  - {$item->product->name}: {$item->total_sold} unidades (\${$item->revenue})");
            }
        }
    }
}
