<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Console\Command;

class CreatePendingOrders extends Command
{
    protected $signature = 'test:create-pending-orders';
    protected $description = 'Create pending orders for testing';

    public function handle()
    {
        $mesas = [2, 4, 8]; // Mesas que mostrar órdenes pendientes

        foreach ($mesas as $tableId) {
            $table = Table::find($tableId);
            if (!$table) continue;

            // Obtener 3-5 productos aleatorios
            $products = Product::inRandomOrder()->limit(rand(3, 5))->get();

            $order = Order::create([
                'table_id' => $tableId,
                'user_id' => 2, // Mesero de prueba
                'status' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total' => $total]);
            $table->update(['status' => 'pedido_listo']);

            $this->line("> Orden #{$order->id} creada para Mesa {$tableId} - Status: pendiente - Total: \${$total}");
        }

        $this->info("\n✓ Órdenes pendientes creadas exitosamente");
    }
}
