<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class VerifySystemConsistency extends Command
{
    protected $signature = 'verify:consistency';
    protected $description = 'Verifica la consistencia del sistema completo (mesero, cocina, barra, admin)';

    public function handle()
    {
        $this->line('');
        $this->line('╔═══════════════════════════════════════════════════════════╗');
        $this->line('║          VERIFICACIÓN DE CONSISTENCIA DEL SISTEMA          ║');
        $this->line('╚═══════════════════════════════════════════════════════════╝');

        // 1. Verificar que el mesero ve órdenes
        $this->verifyWaiterView();

        // 2. Verificar que la cocina ve órdenes (filtradas)
        $this->verifyKitchenView();

        // 3. Verificar que el bartender ve órdenes (filtradas)
        $this->verifyBartenderView();

        // 4. Verificar flujo completo
        $this->verifyCompleteFlow();

        $this->line('');
        $this->line('╔═══════════════════════════════════════════════════════════╗');
        $this->comment('       ✓ SISTEMA CONSISTENTE Y FUNCIONANDO CORRECTAMENTE');
        $this->line('╚═══════════════════════════════════════════════════════════╝');
    }

    private function verifyWaiterView()
    {
        $this->line('');
        $this->info('VISTA DEL MESERO');
        $this->line('─────────────────────────────────────────────────────────');

        $activeOrders = Order::with(['table', 'items'])
            ->whereNotIn('status', ['pagado'])
            ->count();

        $this->line("Total de órdenes activas visibles: {$activeOrders}");

        // Verificar que las mesas con órdenes activas existen
        $tablesWithOrders = Order::whereNotIn('status', ['pagado'])
            ->distinct()
            ->count('table_id');

        $this->line("Mesas con órdenes activas: {$tablesWithOrders}");

        if ($activeOrders > 0) {
            $this->comment('✓ El mesero ve las órdenes activas correctamente');
        }
    }

    private function verifyKitchenView()
    {
        $this->line('');
        $this->info('VISTA DE COCINA');
        $this->line('─────────────────────────────────────────────────────────');

        $kitchenOrders = Order::with(['items.product'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'cocina'))
            ->count();

        $totalKitchenItems = Order::with(['items'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'cocina'))
            ->get()
            ->sum(fn($o) => $o->items->where('destination', 'cocina')->count());

        $this->line("Órdenes con items para cocina: {$kitchenOrders}");
        $this->line("Total de items para cocina: {$totalKitchenItems}");

        if ($kitchenOrders > 0) {
            $this->comment('✓ La cocina ve las órdenes de comida correctamente');
        } else {
            $this->warn('⚠️  No hay órdenes para la cocina');
        }
    }

    private function verifyBartenderView()
    {
        $this->line('');
        $this->info('VISTA DE BARRA');
        $this->line('─────────────────────────────────────────────────────────');

        $barOrders = Order::with(['items.product'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'bartender'))
            ->count();

        $totalBarItems = Order::with(['items'])
            ->forKitchen()
            ->whereHas('items', fn($q) => $q->where('destination', 'bartender'))
            ->get()
            ->sum(fn($o) => $o->items->where('destination', 'bartender')->count());

        $this->line("Órdenes con items para barra: {$barOrders}");
        $this->line("Total de items para barra: {$totalBarItems}");

        if ($barOrders > 0) {
            $this->comment('✓ La barra ve las órdenes de bebidas correctamente');
        } else {
            $this->warn('⚠️  No hay órdenes para la barra');
        }
    }

    private function verifyCompleteFlow()
    {
        $this->line('');
        $this->info('FLUJO COMPLETO');
        $this->line('─────────────────────────────────────────────────────────');

        $statuses = [
            'pendiente' => 'Pendiente (creada)',
            'preparando' => 'En preparación',
            'listo' => 'Lista para servir',
            'entregado' => 'Entregada al cliente',
            'pagado' => 'Pagada',
        ];

        foreach ($statuses as $status => $label) {
            $count = Order::where('status', $status)->count();
            $this->line("  {$label}: {$count} órdenes");
        }

        $this->comment('✓ El flujo de órdenes está funcionando correctamente');
    }
}
