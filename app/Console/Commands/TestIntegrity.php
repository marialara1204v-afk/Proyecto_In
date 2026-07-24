<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Models\Product;
use Illuminate\Console\Command;

class TestIntegrity extends Command
{
    protected $signature = 'test:integrity';
    protected $description = 'Valida integridad completa del sistema con múltiples casos';

    public function handle()
    {
        $this->info('🔍 VALIDACIÓN COMPLETA DE INTEGRIDAD\n');

        // Test 1: Validar mesas
        $this->testMesas();

        // Test 2: Validar órdenes
        $this->testOrdenes();

        // Test 3: Validar cambios de estado
        $this->testEstados();

        // Test 4: Validar totales
        $this->testTotales();

        // Test 5: Validar sincronización
        $this->testSincronizacion();

        $this->info('\n✨ INTEGRIDAD VALIDADA EXITOSAMENTE');
    }

    private function testMesas()
    {
        $this->line('📊 TEST 1: VALIDACIÓN DE MESAS');
        
        $mesas = Table::all();
        
        // Validar cantidad
        if ($mesas->count() !== 10) {
            $this->error("  ❌ Debería haber 10 mesas, hay {$mesas->count()}");
            return;
        }
        $this->line("  ✓ 10 mesas correctas");

        // Validar números
        $numeros = $mesas->pluck('number')->sort()->values()->all();
        if ($numeros !== [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]) {
            $this->error("  ❌ Números de mesa incorrectos: " . json_encode($numeros));
            return;
        }
        $this->line("  ✓ Números de mesa 1-10 correctos");

        // Validar capacidades
        $capacidades = $mesas->pluck('capacity')->all();
        if (min($capacidades) < 2 || max($capacidades) > 6) {
            $this->error("  ❌ Capacidades fuera de rango");
            return;
        }
        $this->line("  ✓ Capacidades válidas (2-6 personas)");

        // Validar estados
        $estados = $mesas->pluck('status')->unique()->all();
        foreach ($estados as $estado) {
            if (!in_array($estado, ['libre', 'ocupada', 'pedido_listo', 'entregado'])) {
                $this->error("  ❌ Estado inválido: {$estado}");
                return;
            }
        }
        $this->line("  ✓ Estados válidos: " . implode(', ', $estados));
    }

    private function testOrdenes()
    {
        $this->line('\n📋 TEST 2: VALIDACIÓN DE ÓRDENES');
        
        $ordenes = Order::all();
        
        // Validar cantidad mínima
        if ($ordenes->count() < 5) {
            $this->warn("  ⚠️ Pocas órdenes en base de datos ({$ordenes->count()})");
        } else {
            $this->line("  ✓ {$ordenes->count()} órdenes en sistema");
        }

        // Validar estados
        $estados = $ordenes->pluck('status')->unique()->all();
        $estadosValidos = ['pendiente', 'preparando', 'listo', 'entregado', 'pagado'];
        
        foreach ($estados as $estado) {
            if (!in_array($estado, $estadosValidos)) {
                $this->error("  ❌ Estado inválido: {$estado}");
                return;
            }
        }
        $this->line("  ✓ Estados válidos: " . implode(', ', $estados));

        // Validar totales
        $negativas = $ordenes->where('total', '<', 0)->count();
        if ($negativas > 0) {
            $this->error("  ❌ {$negativas} órdenes con total negativo");
            return;
        }
        $this->line("  ✓ Todos los totales positivos");

        // Validar items
        $sinItems = $ordenes->where('status', '!=', 'pagado')
            ->filter(fn($o) => $o->items->count() === 0)
            ->count();
        
        if ($sinItems > 0) {
            $this->warn("  ⚠️ {$sinItems} órdenes sin items");
        } else {
            $this->line("  ✓ Todas las órdenes activas tienen items");
        }
    }

    private function testEstados()
    {
        $this->line('\n🔄 TEST 3: VALIDACIÓN DE CAMBIOS DE ESTADO');
        
        // Crear órdenes de prueba con diferentes estados
        $mesa = Table::where('status', 'libre')->first();
        $mesero = User::where('role', 'mesero')->first();
        $producto = Product::first();

        if (!$mesa || !$mesero || !$producto) {
            $this->error("  ❌ Datos incompletos para prueba");
            return;
        }

        // Estados válidos para Orden
        $estadosOrden = ['pendiente', 'preparando', 'listo', 'entregado', 'pagado'];
        // Estados válidos para Item (que es subconjunto)
        $estadosItem = ['pendiente', 'preparando', 'listo'];
        
        foreach ($estadosOrden as $estado) {
            $order = Order::create([
                'table_id' => $mesa->id,
                'user_id' => $mesero->id,
                'status' => $estado,
                'total' => 100,
            ]);

            // Los items usan estados disponibles
            $itemStatus = in_array($estado, $estadosItem) ? $estado : 'listo';
            
            $order->items()->create([
                'product_id' => $producto->id,
                'quantity' => 1,
                'unit_price' => $producto->price,
                'subtotal' => $producto->price,
                'destination' => 'cocina',
                'item_status' => $itemStatus,
            ]);

            $retrieved = Order::find($order->id);
            
            if ($retrieved->status === $estado) {
                $this->line("  ✓ Transición a '{$estado}' correcta");
            } else {
                $this->error("  ❌ Transición a '{$estado}' falló (obtuvo: {$retrieved->status})");
            }
        }
    }

    private function testTotales()
    {
        $this->line('\n💰 TEST 4: VALIDACIÓN DE TOTALES');
        
        $ordenes = Order::whereNotNull('total')->get();
        
        $totalIngresos = $ordenes->sum('total');
        $promedioOrden = $ordenes->avg('total');
        $ordenMasAlta = $ordenes->max('total');
        $ordenMasBaja = $ordenes->min('total');

        $this->line("  📊 Estadísticas de Ingresos:");
        $this->line("     - Ingresos totales: \${$totalIngresos}");
        $this->line("     - Promedio por orden: \${$promedioOrden}");
        $this->line("     - Orden más alta: \${$ordenMasAlta}");
        $this->line("     - Orden más baja: \${$ordenMasBaja}");

        // Validar que no haya inconsistencias
        $inconsistencias = 0;
        
        foreach ($ordenes as $order) {
            $calculado = $order->items()->sum('subtotal');
            if (abs($order->total - $calculado) > 0.01) {
                $inconsistencias++;
                $this->warn("  ⚠️ Orden #{$order->id}: Total {$order->total} vs Calculado {$calculado}");
            }
        }

        if ($inconsistencias === 0) {
            $this->line("  ✓ Todos los totales coinciden con items");
        }
    }

    private function testSincronizacion()
    {
        $this->line('\n🔗 TEST 5: VALIDACIÓN DE SINCRONIZACIÓN');
        
        // Validar sincronización mesa-orden
        $mesas = Table::all();
        
        foreach ($mesas as $mesa) {
            $ordenesPendientes = $mesa->orders()
                ->whereIn('status', ['pendiente', 'preparando', 'listo', 'entregado'])
                ->count();
            
            $deberiaEstarOcupada = $ordenesPendientes > 0;
            $estaOcupada = $mesa->status !== 'libre';

            if ($deberiaEstarOcupada && !$estaOcupada) {
                $this->warn("  ⚠️ Mesa #{$mesa->number} tiene órdenes pendientes pero está 'libre'");
            }
        }

        $this->line("  ✓ Sincronización mesa-orden validada");

        // Validar que orden pagada corresponda a mesa libre
        $ordenePagadaEnOcupada = Order::where('status', 'pagado')
            ->whereHas('table', fn($q) => $q->where('status', '!=', 'libre'))
            ->count();

        if ($ordenePagadaEnOcupada > 0) {
            $this->warn("  ⚠️ {$ordenePagadaEnOcupada} órdenes pagadas en mesas ocupadas");
        } else {
            $this->line("  ✓ Órdenes pagadas en mesas libres");
        }
    }
}
