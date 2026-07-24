<?php

namespace App\Livewire\Admin;

use App\Models\DailyCashBalance;
use App\Models\Order;
use App\Models\OrderPayment;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CashClosingPanel extends Component
{
    public bool $showCloseModal = false;
    public string $notes = '';

    #[Computed]
    public function today()
    {
        return today();
    }

    #[Computed]
    public function todayBalance()
    {
        return DailyCashBalance::where('date', today())->first();
    }

    #[Computed]
    public function todayStats()
    {
        $orders = Order::today()->where('status', 'pagado')->get();
        $payments = OrderPayment::today()->get();

        return [
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('total'),
            'total_payments' => $payments->sum('amount'),
            'total_tips'   => $payments->sum('tip'),
            'cash'      => $payments->where('payment_method', 'efectivo')->sum('amount'),
            'card'      => $payments->where('payment_method', 'tarjeta')->sum('amount'),
            'transfer'  => $payments->where('payment_method', 'transferencia')->sum('amount'),
            'other'     => $payments->where('payment_method', 'otro')->sum('amount'),
        ];
    }

    #[Computed]
    public function balances()
    {
        return DailyCashBalance::orderBy('date', 'desc')->paginate(10);
    }

    public function openCloseModal(): void
    {
        if ($this->todayBalance && $this->todayBalance->isClosed()) {
            session()->flash('error', 'La caja de hoy ya fue cerrada');
            return;
        }

        $this->showCloseModal = true;
    }

    public function closeCash(): void
    {
        $stats = $this->todayStats();

        try {
            DB::transaction(function () use ($stats) {
                DailyCashBalance::updateOrCreate(
                    ['date' => today()],
                    [
                        'total_sales'    => $stats['total_sales'],
                        'total_cash'     => $stats['cash'],
                        'total_card'     => $stats['card'],
                        'total_transfer' => $stats['transfer'],
                        'total_other'    => $stats['other'],
                        'total_payments' => $stats['total_payments'],
                        'total_tips'     => $stats['total_tips'],
                        'orders_count'   => $stats['total_orders'],
                        'notes'          => $this->notes,
                        'closed_at'      => now(),
                        'closed_by'      => auth()->id(),
                    ]
                );
            });

            session()->flash('success', '¡Caja cerrada correctamente!');
            $this->showCloseModal = false;
            $this->notes = '';
            unset($this->todayBalance, $this->todayStats, $this->balances);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Error al cerrar la caja');
        }
    }

    public function render()
    {
        return view('livewire.admin.cash-closing-panel');
    }
}
