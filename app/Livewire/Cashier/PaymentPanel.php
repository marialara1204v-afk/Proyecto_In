<?php

namespace App\Livewire\Cashier;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentPanel extends Component
{
    public ?int $orderId = null;
    public string $paymentMethod = 'efectivo';
    public array $payments = [];
    public bool $showModal = false;
    public bool $paymentSuccess = false;

    public float $tipAmount = 0;
    public bool $splitTip = false;
    public int $workerCount = 1;
    public float $tipPerWorker = 0;

    protected $listeners = ['openPaymentModal'];

    #[Computed]
    public function order()
    {
        return $this->orderId
            ? Order::with('items.product', 'table', 'payments')->find($this->orderId)
            : null;
    }

    #[Computed]
    public function totalAmount(): float
    {
        return (float) ($this->order?->total ?? 0);
    }

    #[Computed]
    public function totalWithTip(): float
    {
        return $this->totalAmount + $this->tipAmount;
    }

    #[Computed]
    public function totalPaid(): float
    {
        return collect($this->payments)->sum('amount');
    }

    #[Computed]
    public function remainingAmount(): float
    {
        return $this->totalWithTip - $this->totalPaid;
    }

    #[Computed]
    public function activeWorkersToday(): int
    {
        return User::where('role', '!=', 'admin')->count();
    }

    public function updatedTipAmount(): void
    {
        $maxTip = $this->totalAmount * 0.5;
        $this->tipAmount = max(0, min($this->tipAmount, $maxTip));
        $this->recalculateSplit();
    }

    public function updatedSplitTip(): void
    {
        $this->recalculateSplit();
    }

    public function updatedWorkerCount(): void
    {
        $this->recalculateSplit();
    }

    protected function recalculateSplit(): void
    {
        if ($this->splitTip && $this->workerCount > 0 && $this->tipAmount > 0) {
            $this->tipPerWorker = round($this->tipAmount / $this->workerCount, 2);
        } else {
            $this->tipPerWorker = 0;
        }
    }

    public function openPaymentModal(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order->status === 'pagado') {
            session()->flash('error', 'No se puede pagar esta orden');
            return;
        }

        $this->orderId = $orderId;
        $this->payments = [];
        $this->paymentMethod = 'efectivo';
        $this->tipAmount = 0;
        $this->splitTip = false;
        $this->workerCount = $this->activeWorkersToday;
        $this->tipPerWorker = 0;
        $this->paymentSuccess = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->orderId = null;
        $this->paymentSuccess = false;
    }

    public function submitPayment(): void
    {
        if (!$this->orderId) {
            session()->flash('error', 'No hay orden para pagar');
            return;
        }

        $validMethods = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($this->paymentMethod, $validMethods)) {
            session()->flash('error', 'Método de pago inválido');
            return;
        }

        $maxTip = $this->totalAmount * 0.5;
        if ($this->tipAmount < 0 || $this->tipAmount > $maxTip) {
            session()->flash('error', 'La propina no puede superar el 50% del total');
            return;
        }

        try {
            DB::beginTransaction();

            $order = Order::with('table')->findOrFail($this->orderId);

            OrderPayment::create([
                'order_id'       => $order->id,
                'user_id'        => auth()->id(),
                'amount'         => $order->total,
                'tip'            => $this->tipAmount,
                'payment_method' => $this->paymentMethod,
                'notes'          => null,
            ]);

            $order->update([
                'status'             => 'pagado',
                'closed_at'          => now(),
                'tip'                => $this->tipAmount,
                'tip_split_workers'  => $this->splitTip ? $this->workerCount : null,
            ]);

            if ($order->table) {
                $order->table->update(['status' => 'libre']);
            }

            DB::commit();

            $this->paymentSuccess = true;
            session()->flash('success', 'Pago registrado correctamente!');
            $this->dispatch('payment-completed');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.cashier.payment-panel');
    }
}
