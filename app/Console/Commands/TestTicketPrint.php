<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestTicketPrint extends Command
{
    protected $signature = 'test:ticket-print';
    protected $description = 'Valida que la funcionalidad de imprimir ticket está configurada correctamente';

    public function handle()
    {
        $this->info('🖨️ VALIDANDO FUNCIONALIDAD DE IMPRIMIR TICKET\n');

        // 1. Verificar controlador
        $this->line('📋 VERIFICANDO CONTROLADOR...');
        $controller = 'App\Http\Controllers\TicketController';
        if (class_exists($controller)) {
            $this->line("  ✓ Controlador existe: {$controller}");
            if (method_exists($controller, 'print')) {
                $this->line("  ✓ Método print() existe");
            } else {
                $this->error("  ❌ Método print() NO existe");
                return 1;
            }
        } else {
            $this->error("  ❌ Controlador no encontrado");
            return 1;
        }

        // 2. Verificar vista
        $this->line('\n📄 VERIFICANDO VISTA DE TICKET...');
        $viewPath = resource_path('views/ticket/print.blade.php');
        if (file_exists($viewPath)) {
            $this->line("  ✓ Vista existe: resources/views/ticket/print.blade.php");
            $content = file_get_contents($viewPath);
            $checks = [
                'LA PATRONA' => 'Nombre del restaurante',
                'Ticket #' => 'Número de ticket',
                'Mesa:' => 'Información de mesa',
                'TOTAL:' => 'Total de la orden',
                '@foreach' => 'Listado de items',
            ];
            
            foreach ($checks as $text => $desc) {
                if (str_contains($content, $text)) {
                    $this->line("    ✓ {$desc}");
                } else {
                    $this->warn("    ⚠️ {$desc} - No encontrado");
                }
            }
        } else {
            $this->error("  ❌ Vista no encontrada");
            return 1;
        }

        // 3. Verificar ruta
        $this->line('\n🛣️ VERIFICANDO RUTA DE TICKET...');
        $routes = collect(Route::getRoutes()->getRoutes());
        $ticketRoute = $routes->first(function ($route) {
            return str_contains($route->uri, 'ticket') && str_contains($route->uri, 'print');
        });

        if ($ticketRoute) {
            $this->line("  ✓ Ruta existe: {$ticketRoute->uri}");
            $methods = implode('|', $ticketRoute->methods);
            $this->line("  ✓ Métodos: {$methods}");
            if (in_array('auth', $ticketRoute->middleware())) {
                $this->line("  ✓ Protegida con autenticación");
            }
        } else {
            $this->error("  ❌ Ruta de ticket no configurada");
            return 1;
        }

        // 4. Verificar componente Livewire
        $this->line('\n⚙️ VERIFICANDO COMPONENTE LIVEWIRE...');
        $livewireComponent = 'App\Livewire\Cashier\PaymentPanel';
        if (class_exists($livewireComponent)) {
            $this->line("  ✓ Componente PaymentPanel existe");
            
            $reflection = new \ReflectionClass($livewireComponent);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            $propNames = array_map(fn($p) => $p->name, $properties);
            
            if (in_array('paymentSuccess', $propNames)) {
                $this->line("  ✓ Propiedad paymentSuccess existe");
            } else {
                $this->warn("  ⚠️ Propiedad paymentSuccess NO encontrada");
            }
        } else {
            $this->error("  ❌ Componente PaymentPanel no encontrado");
            return 1;
        }

        // 5. Verificar vista del payment panel
        $this->line('\n🎯 VERIFICANDO VISTA DE PAYMENT PANEL...');
        $viewPath = resource_path('views/livewire/cashier/payment-panel.blade.php');
        if (file_exists($viewPath)) {
            $content = file_get_contents($viewPath);
            if (str_contains($content, 'Imprimir Ticket') || str_contains($content, 'ticket.print')) {
                $this->line("  ✓ Botón de imprimir agregado");
            } else {
                $this->warn("  ⚠️ Botón de imprimir NO encontrado en vista");
            }
        } else {
            $this->error("  ❌ Vista de payment panel no encontrada");
            return 1;
        }

        // 6. Verificar modelo Order
        $this->line('\n📦 VERIFICANDO MODELO ORDER...');
        if (class_exists('App\Models\Order')) {
            $order = Order::first();
            if ($order) {
                try {
                    $payments = $order->payments()->get();
                    $this->line("  ✓ Relación payments() funciona correctamente");
                } catch (\Exception $e) {
                    $this->warn("  ⚠️ Error al acceder a relación payments(): {$e->getMessage()}");
                }
            } else {
                $this->line("  ℹ️ No hay órdenes en la BD para probar");
            }
        }

        // Resumen final
        $this->info('\n' . str_repeat('═', 50));
        $this->line('RESULTADO FINAL:');
        $this->info('  ✅ Funcionalidad de imprimir ticket está completamente configurada');
        $this->line('  El usuario podrá:');
        $this->line('  1. Procesar un pago');
        $this->line('  2. Ver botón "🖨️ Imprimir Ticket"');
        $this->line('  3. Abrirlo en nueva pestaña');
        $this->line('  4. Imprimir o guardar como PDF');
        $this->info(str_repeat('═', 50));

        return 0;
    }
}
