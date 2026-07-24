<?php

namespace Tests\Feature;

use App\Livewire\Waiter\TableMap;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WaiterTableMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_mesero_puede_agregar_productos_al_carrito(): void
    {
        $mesero = User::factory()->create(['role' => 'mesero']);
        $table = Table::create(['number' => 1, 'capacity' => 4, 'status' => 'libre']);
        $category = Category::create(['name' => 'Entradas']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Quesadilla',
            'price' => 50,
            'is_available' => true,
        ]);

        Livewire::actingAs($mesero)
            ->test(TableMap::class)
            ->call('openOrderModal', $table->id)
            ->call('addToCart', $product->id)
            ->call('addToCart', $product->id)
            ->assertSet("cart.{$product->id}.quantity", 2)
            ->assertSet("cart.{$product->id}.name", 'Quesadilla');
    }

    public function test_mesero_puede_enviar_pedido_y_guardar_items(): void
    {
        $mesero = User::factory()->create(['role' => 'mesero']);
        $table = Table::create(['number' => 1, 'capacity' => 4, 'status' => 'libre']);
        $category = Category::create(['name' => 'Fuertes']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Tacos',
            'price' => 85,
            'is_available' => true,
        ]);

        Livewire::actingAs($mesero)
            ->test(TableMap::class)
            ->call('openOrderModal', $table->id)
            ->call('addToCart', $product->id)
            ->call('submitOrder');

        $this->assertDatabaseHas('orders', [
            'table_id' => $table->id,
            'user_id' => $mesero->id,
            'status' => 'pendiente',
        ]);

        $order = Order::first();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => 'ocupada',
        ]);
    }

    public function test_mesero_puede_elegir_una_mesa_especifica_en_el_selector(): void
    {
        $mesero = User::factory()->create(['role' => 'mesero']);
        $table = Table::create(['number' => 7, 'capacity' => 6, 'status' => 'ocupada']);

        Livewire::actingAs($mesero)
            ->test(TableMap::class)
            ->call('selectTableForOrder', $table->id)
            ->assertSet('showOrderModal', true)
            ->assertSet('selectedTableId', $table->id);
    }
}
