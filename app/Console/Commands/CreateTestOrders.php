<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Table;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestOrders extends Command
{
    protected $signature = 'test:create-orders';
    protected $description = 'Create test orders in listo status';

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

        foreach ([1, 6] as $tableNum) {
            $table = $tables->where('number', $tableNum)->first();
            if (!$table) continue;

            // Eliminar orden anterior si existe
            Order::where('table_id', $table->id)->delete();

            $order = Order::create([
                'table_id' => $table->id,
                'user_id' => $user->id,
                'status' => 'listo',
                'total' => 0
            ]);

            $selectedProducts = $products->random(min(2, $products->count()));
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 2);
                $subtotal = $product->price * $qty;
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'notes' => $tableNum === 1 ? 'Sin cebolla' : ''
                ]);
            }

            $order->recalculateTotal();
            $this->line("> Orden #{$order->id} creada para Mesa {$table->number} - Status: listo");
        }

        // Actualizar tabla status a pedido_listo
        Table::whereIn('number', [1, 6])->update(['status' => 'pedido_listo']);
        
        $this->info('✓ Órdenes de prueba creadas exitosamente');
    }
}
