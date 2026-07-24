<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class CreateMixedOrdersForTest extends Command
{
    protected $signature = 'test:create-mixed-orders';
    protected $description = 'Crear órdenes de prueba con comida Y bebidas (classified)';

    public function handle()
    {
        echo "\n===== CREANDO ÓRDENES MIXTAS =====\n\n";

        // Obtener productos
        $birria = Product::whereHas('category', fn($q) => $q->where('name', 'Birria'))->first();
        $tacos = Product::whereHas('category', fn($q) => $q->where('name', 'Tacos'))->first();
        $snaks = Product::whereHas('category', fn($q) => $q->where('name', 'Snaks'))->first();

        $cerveza = Product::whereHas('category', fn($q) => $q->where('name', 'Cerveza'))->first();
        $refresco = Product::whereHas('category', fn($q) => $q->where('name', 'Refrescos'))->first();
        $coctel = Product::whereHas('category', fn($q) => $q->where('name', 'Coctelería Sin Alcohol'))->first();

        $user = User::first();

        // Orden en Mesa 5: Comida + Bebidas
        $table5 = Table::find(5);
        $order5 = Order::create([
            'table_id' => $table5->id,
            'user_id'  => $user->id,
            'status'   => 'pendiente',
            'total'    => 0,
        ]);

        // Items: 2 comida, 2 bebidas
        $comida1 = $order5->items()->create([
            'product_id' => $birria->id,
            'quantity'   => 1,
            'unit_price' => $birria->price,
            'subtotal'   => $birria->price,
            'destination' => 'cocina',
            'notes'      => 'Sin cebolla',
        ]);

        $comida2 = $order5->items()->create([
            'product_id' => $snaks->id,
            'quantity'   => 2,
            'unit_price' => $snaks->price,
            'subtotal'   => $snaks->price * 2,
            'destination' => 'cocina',
            'notes'      => '',
        ]);

        $bebida1 = $order5->items()->create([
            'product_id' => $cerveza->id,
            'quantity'   => 2,
            'unit_price' => $cerveza->price,
            'subtotal'   => $cerveza->price * 2,
            'destination' => 'bartender',
            'notes'      => 'Bien fría',
        ]);

        $bebida2 = $order5->items()->create([
            'product_id' => $refresco->id,
            'quantity'   => 1,
            'unit_price' => $refresco->price,
            'subtotal'   => $refresco->price,
            'destination' => 'bartender',
            'notes'      => '',
        ]);

        $total5 = $comida1->subtotal + $comida2->subtotal + $bebida1->subtotal + $bebida2->subtotal;
        $order5->update(['total' => $total5]);
        $table5->update(['status' => 'ocupada']);

        echo "✓ Orden #" . $order5->id . " (Mesa 5) creada - MIXTA\n";
        echo "  → Comida (Cocina): " . $comida1->quantity . "x " . $comida1->product->name . " + " . $comida2->quantity . "x " . $comida2->product->name . "\n";
        echo "  → Bebidas (Bartender): " . $bebida1->quantity . "x " . $bebida1->product->name . " + " . $bebida2->quantity . "x " . $bebida2->product->name . "\n";
        echo "  → Total: \$" . number_format($total5, 2) . "\n\n";

        // Orden en Mesa 9: Solo Comida
        $table9 = Table::find(9);
        if ($table9) {
            $order9 = Order::create([
                'table_id' => $table9->id,
                'user_id'  => $user->id,
                'status'   => 'pendiente',
                'total'    => 0,
            ]);

            $item9 = $order9->items()->create([
                'product_id' => $tacos->id,
                'quantity'   => 4,
                'unit_price' => $tacos->price,
                'subtotal'   => $tacos->price * 4,
                'destination' => 'cocina',
                'notes'      => 'Sin cilantro',
            ]);

            $order9->update(['total' => $item9->subtotal]);
            $table9->update(['status' => 'ocupada']);

            echo "✓ Orden #" . $order9->id . " (Mesa 9) creada - SOLO COMIDA\n";
            echo "  → Cocina: " . $item9->quantity . "x " . $item9->product->name . "\n";
            echo "  → Total: \$" . number_format($item9->subtotal, 2) . "\n\n";
        }

        // Orden en Mesa 10: Solo Bebidas
        $table10 = Table::find(10);
        if ($table10) {
            $order10 = Order::create([
                'table_id' => $table10->id,
                'user_id'  => $user->id,
                'status'   => 'pendiente',
                'total'    => 0,
            ]);

            $item10 = $order10->items()->create([
                'product_id' => $coctel->id,
                'quantity'   => 3,
                'unit_price' => $coctel->price,
                'subtotal'   => $coctel->price * 3,
                'destination' => 'bartender',
                'notes'      => 'Con hielo',
            ]);

            $order10->update(['total' => $item10->subtotal]);
            $table10->update(['status' => 'ocupada']);

            echo "✓ Orden #" . $order10->id . " (Mesa 10) creada - SOLO BEBIDAS\n";
            echo "  → Bartender: " . $item10->quantity . "x " . $item10->product->name . "\n";
            echo "  → Total: \$" . number_format($item10->subtotal, 2) . "\n\n";
        }

        echo "✓ Órdenes de prueba (clasificadas) creadas exitosamente\n";
    }
}
