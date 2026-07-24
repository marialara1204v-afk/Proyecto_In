<?php

namespace Tests\Feature;

use App\Livewire\Cashier\CashierPanel;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashierPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_cobrar_orden_no_entregada(): void
    {
        $mesero = User::factory()->create(['role' => 'mesero']);

        $table = Table::create([
            'number' => 1,
            'capacity' => 4,
            'status' => 'ocupada',
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'user_id' => $mesero->id,
            'status' => 'pendiente',
            'total' => 120,
        ]);

        Livewire::actingAs($mesero)
            ->test(CashierPanel::class)
            ->call('selectTable', $table->id)
            ->call('confirmPayment');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pendiente',
        ]);

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => 'ocupada',
        ]);
    }

    public function test_permite_cobrar_orden_entregada_y_libera_mesa(): void
    {
        $mesero = User::factory()->create(['role' => 'mesero']);

        $table = Table::create([
            'number' => 2,
            'capacity' => 4,
            'status' => 'pedido_listo',
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'user_id' => $mesero->id,
            'status' => 'entregado',
            'total' => 250,
        ]);

        Livewire::actingAs($mesero)
            ->test(CashierPanel::class)
            ->call('selectTable', $table->id)
            ->call('confirmPayment');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pagado',
        ]);

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => 'libre',
        ]);
    }
}
