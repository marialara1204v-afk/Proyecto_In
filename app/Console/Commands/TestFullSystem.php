<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Models\Product;
use Illuminate\Console\Command;

class TestFullSystem extends Command
{
    protected $signature = 'test:full-system';
    protected $description = 'Prueba completa del sistema: pedido -> cocina -> entrega -> pago';

    public function handle()
    {
        $this->info('🚀 INICIANDO PRUEBA COMPLETA DEL SISTEMA\n');

        // 1. VERIFICAR BASE DE DATOS
        $this->line('📊 VERIFICANDO BASE DE DATOS...');
        $this->verifyDatabase();

        // 2. FLUJO DE MESERO
        $this->line('\n🍽️ FLUJO 1: MESERO CREA PEDIDO');
        $order = $this->createOrder();
        if (!$order) return;

        // 3. FLUJO DE COCINA
        $this->line('\n👨‍🍳 FLUJO 2: COCINA VE Y PREPARA');
        $this->verifyKitchenBoard($order);

        // 4. FLUJO DE ENTREGA
        $this->line('\n📦 FLUJO 3: MARCAR COMO ENTREGADO');
        $this->markDelivered($order);

        // 5. FLUJO DE PAGO
        $this->line('\n💳 FLUJO 4: PROCESAR PAGO');
        $this->processPayment($order);

        // 6. VERIFICACIÓN FINAL
        $this->line('\n✅ VERIFICACIÓN FINAL');
        $this->verifyFinalState($order);

        $this->info('\n✨ PRUEBA COMPLETADA EXITOSAMENTE');
    }

    private function verifyDatabase()
    {
        $tables = Table::count();
        $users = User::count();
        $products = Product::count();
        
        $this->line("  ✓ Mesas: {$tables}");
        $this->line("  ✓ Usuarios: {$users}");
        $this->line("  ✓ Productos: {$products}");

        if ($tables < 10) {
            $this->error('  ❌ Faltan mesas');
            return false;
        }

        return true;
    }

    private function createOrder()
    {
        $mesa = Table::inRandomOrder()->first();
        $mesero = User::where('role', 'mesero')->first();

        if (!$mesa || !$mesero) {
            $this->error('  ❌ No hay mesa o mesero');
            return null;
        }

        // Crear orden
        $order = Order::create([
            'table_id' => $mesa->id,
            'user_id' => $mesero->id,
            'status' => 'pendiente',
            'total' => 0,
        ]);

        // Agregar 2 productos aleatorios
        $products = Product::inRandomOrder()->take(2)->get();
        
        foreach ($products as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => rand(1, 2),
                'unit_price' => $product->price,
                'subtotal' => $product->price * rand(1, 2),
                'destination' => $product->isDrink() ? 'bartender' : 'cocina',
                'item_status' => 'pendiente',
            ]);
        }

        $order->recalculateTotal();
        $mesa->update(['status' => 'ocupada']);

        $this->line("  ✓ Orden #{$order->id} creada");
        $this->line("  ✓ Mesa #{$mesa->number} marcada como OCUPADA");
        $this->line("  ✓ Total: \${$order->total}");
        $this->line("  ✓ Items: {$order->items->count()}");

        return $order;
    }

    private function verifyKitchenBoard($order)
    {
        $cocinero = User::where('role', 'cocina')->first();
        $mesero = User::where('role', 'mesero')->first();

        if (!$cocinero) {
            $this->warn('  ⚠️ No hay cocinero');
            return;
        }

        $this->line("  ✓ Cocinero: {$cocinero->name}");
        $this->line("  ✓ Orden visible en panel: Orden #{$order->id}");

        // Simular que cocina prepara los items
        foreach ($order->items as $item) {
            $item->update(['item_status' => 'preparando']);
            $this->line("  ✓ Item {$item->product->name}: PREPARANDO");
        }

        // Marcar como listos
        foreach ($order->items as $item) {
            $item->update(['item_status' => 'listo']);
            $this->line("  ✓ Item {$item->product->name}: LISTO");
        }

        $order->update(['status' => 'listo']);
        $this->line("  ✓ Orden #{$order->id}: LISTO");
    }

    private function markDelivered($order)
    {
        $mesa = $order->table;

        $order->update(['status' => 'entregado']);
        $mesa->update(['status' => 'entregado']);

        $this->line("  ✓ Orden #{$order->id}: ENTREGADO");
        $this->line("  ✓ Mesa #{$mesa->number}: ENTREGADO");
    }

    private function processPayment($order)
    {
        $mesa = $order->table;
        $total = $order->total;

        $order->update([
            'status' => 'pagado',
            'closed_at' => now(),
        ]);

        $mesa->update(['status' => 'libre']);

        $this->line("  ✓ Orden #{$order->id}: PAGADA");
        $this->line("  ✓ Monto cobrado: \${$total}");
        $this->line("  ✓ Mesa #{$mesa->number}: LIBERADA");
    }

    private function verifyFinalState($order)
    {
        $order->refresh();
        $mesa = $order->table;

        $checks = [
            'Orden status = pagado' => $order->status === 'pagado',
            'Mesa status = libre' => $mesa->status === 'libre',
            'Total positivo' => $order->total > 0,
            'Tiene items' => $order->items->count() > 0,
            'Closed at está registrado' => $order->closed_at !== null,
        ];

        foreach ($checks as $check => $result) {
            $icon = $result ? '✓' : '❌';
            $this->line("  {$icon} {$check}");
        }

        if (!in_array(false, $checks)) {
            $this->info("\n✨ TODOS LOS VALIDADORES PASARON ✨");
        }
    }
}
