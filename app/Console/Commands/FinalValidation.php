<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Models\Product;
use Illuminate\Console\Command;

class FinalValidation extends Command
{
    protected $signature = 'test:final-validation';
    protected $description = 'Validación final del sistema completo';

    public function handle()
    {
        $this->info('✅ VALIDACIÓN FINAL DEL SISTEMA\n');

        $tests = [
            'Estructura Base' => fn() => $this->validateStructure(),
            'Flujo Completo' => fn() => $this->validateCompleteFlow(),
            'Estados Válidos' => fn() => $this->validateStates(),
            'Integridad Datos' => fn() => $this->validateDataIntegrity(),
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $name => $test) {
            try {
                $this->line("\n📋 {$name}:");
                if ($test()) {
                    $this->info("   ✅ PASÓ");
                    $passed++;
                } else {
                    $this->error("   ❌ FALLÓ");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ ERROR: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("\n" . str_repeat("═", 50));
        $this->line("RESULTADOS FINALES:");
        $total = $passed + $failed;
        $this->line("  Pruebas Pasadas: {$passed}/{$total}");
        if ($failed > 0) {
            $this->warn("  Pruebas Fallidas: {$failed}/{$total}");
        }
        $this->info(str_repeat("═", 50));

        return $failed === 0 ? 0 : 1;
    }

    private function validateStructure()
    {
        $checks = [
            'Mesas' => ['count' => Table::count(), 'min' => 10],
            'Usuarios' => ['count' => User::count(), 'min' => 5],
            'Productos' => ['count' => Product::count(), 'min' => 50],
        ];

        foreach ($checks as $item => $check) {
            if ($check['count'] < $check['min']) {
                $this->line("   ❌ {$item}: {$check['count']} (esperado: {$check['min']})");
                return false;
            }
            $this->line("   ✓ {$item}: {$check['count']}");
        }

        return true;
    }

    private function validateCompleteFlow()
    {
        $mesa = Table::where('status', 'libre')->first();
        $mesero = User::where('role', 'mesero')->first();
        $producto = Product::inRandomOrder()->first();

        if (!$mesa || !$mesero || !$producto) {
            $this->line("   ❌ Faltan datos para crear orden");
            return false;
        }

        // Crear orden
        $order = Order::create([
            'table_id' => $mesa->id,
            'user_id' => $mesero->id,
            'status' => 'pendiente',
            'total' => 0,
        ]);

        $order->items()->create([
            'product_id' => $producto->id,
            'quantity' => 1,
            'unit_price' => $producto->price,
            'subtotal' => $producto->price,
            'destination' => 'cocina',
            'item_status' => 'pendiente',
        ]);

        $order->recalculateTotal();
        $mesa->update(['status' => 'ocupada']);

        // Transiciones
        $transitions = [
            'listo' => 'listo',
            'entregado' => 'entregado',
            'pagado' => 'pagado',
        ];

        foreach ($transitions as $newStatus => $display) {
            $order->update(['status' => $newStatus]);
            if ($order->fresh()->status !== $newStatus) {
                $this->line("   ❌ Transición a '{$display}' falló");
                return false;
            }
            $this->line("   ✓ {$display}");
        }

        $mesa->update(['status' => 'libre']);

        if ($mesa->fresh()->status !== 'libre') {
            $this->line("   ❌ Mesa no se liberó después de pago");
            return false;
        }

        $this->line("   ✓ Mesa liberada");
        return true;
    }

    private function validateStates()
    {
        $ordenes = Order::all();

        $estadosValidos = ['pendiente', 'preparando', 'listo', 'entregado', 'pagado'];

        foreach ($ordenes as $order) {
            if (!in_array($order->status, $estadosValidos)) {
                $this->line("   ❌ Orden #{$order->id}: estado inválido '{$order->status}'");
                return false;
            }

            foreach ($order->items as $item) {
                if (!in_array($item->item_status, ['pendiente', 'preparando', 'listo'])) {
                    $this->line("   ❌ Item en Orden #{$order->id}: item_status inválido");
                    return false;
                }
            }
        }

        $this->line("   ✓ Todos los estados son válidos");
        return true;
    }

    private function validateDataIntegrity()
    {
        // Totales positivos
        $negativas = Order::where('total', '<', 0)->count();
        if ($negativas > 0) {
            $this->line("   ❌ {$negativas} órdenes con total negativo");
            return false;
        }
        $this->line("   ✓ Totales positivos");

        // Órdenes con items
        $sinItems = Order::doesntHave('items')->count();
        if ($sinItems > 0) {
            $this->line("   ⚠️ {$sinItems} órdenes sin items");
        } else {
            $this->line("   ✓ Todas las órdenes tienen items");
        }

        // Mesas válidas
        $mesasInvalidas = Table::where('status', '!=', 'libre')
            ->where('status', '!=', 'ocupada')
            ->where('status', '!=', 'pedido_listo')
            ->where('status', '!=', 'entregado')
            ->count();

        if ($mesasInvalidas > 0) {
            $this->line("   ❌ {$mesasInvalidas} mesas con estado inválido");
            return false;
        }
        $this->line("   ✓ Estados de mesas válidos");

        return true;
    }
}
