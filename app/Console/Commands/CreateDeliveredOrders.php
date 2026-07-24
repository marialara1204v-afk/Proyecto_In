<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class CreateDeliveredOrders extends Command
{
    protected $signature = 'test:create-delivered-orders';
    protected $description = 'Create test orders in entregado status for payment testing';

    public function handle()
    {
        $user = User::where('role', 'mesero')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Mesero Test',
                'email' => 'mesero' . time() . '@test.com',
                'password' => bcrypt('password'),
                'role' => 'mesero'
            ]);
        }

        $tables = Table::all();
        $products = Product::available()->limit(5)->get();

        if ($products->isEmpty()) {
            $this->error('No hay productos disponibles');
            return;
        }

        foreach ([3, 7] as $tableNum) {
            $table = $tables->where('number', $tableNum)->first();
            if (!$table) continue;

            // Eliminar orden anterior si existe
            Order::where('table_id', $table->id)->delete();

            $order = Order::create([
                'table_id' => $table->id,
                'user_id' => $user->id,
                'status' => 'entregado',  // ← Ya entregado, listo para pagar
                'total' => 0
            ]);

            $selectedProducts = $products->random(min(2, $products->count()));
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $subtotal = $product->price * $qty;
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'notes' => ''
                ]);
            }

            $order->recalculateTotal();
            $this->line("> Orden #{$order->id} creada para Mesa {$table->number} - Status: entregado - Total: \${$order->total}");
        }

        // Actualizar tabla status a pedido_listo
        Table::whereIn('number', [3, 7])->update(['status' => 'pedido_listo']);
        
        $this->info('✓ Órdenes entregadas creadas exitosamente');
    }
}
