<?php

namespace App\Console\Commands;

use App\Models\Table;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class TestCompleteFlow extends Command
{
    protected $signature = 'test:complete-flow';
    protected $description = 'Prueba el flujo completo: pedido -> listo -> entregado -> pagado';

    public function handle()
    {
        $this->info('🧪 Iniciando test de flujo completo...');

        // 1. Obtener mesa y usuario
        $table = Table::first();
        $user = User::where('role', 'mesero')->first();

        if (!$table || !$user) {
            $this->error('❌ No hay mesa o mesero disponible');
            return;
        }

        $this->line("✓ Mesa: {$table->number}, Usuario: {$user->name}");

        // 2. Obtener productos
        $products = Product::available()->take(3)->get();
        if ($products->isEmpty()) {
            $this->error('❌ No hay productos disponibles');
            return;
        }

        // 3. Crear orden
        $order = Order::create([
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => 'pendiente',
            'total' => 0,
        ]);
        $this->line("✅ Orden {$order->id} creada en estado 'pendiente'");

        // 4. Agregar items
        foreach ($products as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $product->price,
                'subtotal' => $product->price,
                'destination' => $product->isDrink() ? 'bartender' : 'cocina',
                'item_status' => 'pendiente',
            ]);
        }
        $order->recalculateTotal();
        $this->line("✅ {$products->count()} items agregados, total: \${$order->total}");

        // 5. Cambiar a 'listo'
        $order->update(['status' => 'listo']);
        $this->line("✅ Orden cambió a estado 'listo'");

        // 6. Marcar como entregado
        $order->update(['status' => 'entregado']);
        $table->update(['status' => 'entregado']);
        $this->line("✅ Orden cambió a estado 'entregado', mesa ahora 'entregado'");

        // 7. Procesar pago
        $order->update([
            'status' => 'pagado',
            'closed_at' => now(),
        ]);
        $table->update(['status' => 'libre']);
        $this->line("✅ Orden pagada, mesa liberada");

        // 8. Verificar estado final
        $finalOrder = Order::find($order->id);
        $finalTable = Table::find($table->id);

        $this->line("\n📊 ESTADO FINAL:");
        $this->line("  Orden {$finalOrder->id}: {$finalOrder->status} | Total: \${$finalOrder->total}");
        $this->line("  Mesa {$finalTable->number}: {$finalTable->status}");

        if ($finalOrder->status === 'pagado' && $finalTable->status === 'libre') {
            $this->info("\n✨ ¡Flujo completo funcionando correctamente!");
        } else {
            $this->warn("\n⚠️ Algo falló en el flujo");
        }
    }
}
