<?php

namespace App\Livewire\Cashier;

use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CashierPanel extends Component
{
    public ?int  $selectedTableId = null;
    public bool  $showConfirm     = false;

    #[Computed]
    public function tables()
    {
        // Obtener todas las mesas que tienen al menos una orden en estado 'entregado' y no pagada
        return Table::whereHas('orders', function ($query) {
            $query->where('status', 'entregado')->where('total', '>', 0);
        })
        ->with('activeOrder')
        ->orderBy('number')
        ->get();
    }

    #[Computed]
    public function selectedOrder()
    {
        if (! $this->selectedTableId) {
            return null;
        }

        $table = Table::find($this->selectedTableId);

        if (! $table) {
            return null;
        }

        $orderId = $table->orders()
            ->where('status', 'entregado')
            ->latest()
            ->value('id');

        return $orderId
            ? Order::with('items.product', 'table', 'user')->find($orderId)
            : null;
    }

    public function selectTable(int $tableId): void
    {
        $this->selectedTableId = $tableId;
        $this->showConfirm     = false;
    }

    public function confirmPayment(): void
    {
        if (!$this->selectedOrder) {
            session()->flash('error', 'Solo se pueden cobrar cuentas en estado entregado.');
            return;
        }

        // El pago se maneja ahora en PaymentPanel
        $this->dispatch('open-payment', orderId: $this->selectedOrder->id);
    }

    public function openPayment(): void
    {
        if (!$this->selectedOrder) {
            session()->flash('error', 'Selecciona una mesa primero');
            return;
        }

        $this->dispatch('openPaymentModal', orderId: $this->selectedOrder->id);
    }

    #[On('table-status-updated')]
    #[On('payment-completed')]
    #[On('refresh-tables')]
    public function refreshTables(): void
    {
        unset($this->tables);
        // NO limpiar selectedTableId para mantener la mesa visible mientras el usuario trabaja
    }

    public function render()
    {
        return view('livewire.cashier.cashier-panel')
            ->layout('layouts.app', ['title' => 'Caja']);
    }
}