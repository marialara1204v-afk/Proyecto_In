<?php

namespace App\Livewire\Admin;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdminDashboard extends Component
{
    public string $period = 'today'; // today | week | month

    private function hourExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql'  => 'EXTRACT(HOUR FROM created_at)',
            default  => 'HOUR(created_at)',
        };
    }

    // ── Ventas totales ───────────────────────────────────────
    #[Computed]
    public function totalSales(): float
    {
        return Order::where('status', 'pagado')
            ->when($this->period === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($this->period === 'week',  fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'month', fn($q) => $q->whereMonth('created_at', now()->month))
            ->sum('total');
    }

    // ── Órdenes del período ──────────────────────────────────
    #[Computed]
    public function totalOrders(): int
    {
        return Order::where('status', 'pagado')
            ->when($this->period === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($this->period === 'week',  fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'month', fn($q) => $q->whereMonth('created_at', now()->month))
            ->count();
    }

    // ── Ticket promedio ──────────────────────────────────────
    #[Computed]
    public function avgTicket(): float
    {
        return $this->totalOrders > 0
            ? round($this->totalSales / $this->totalOrders, 2)
            : 0;
    }

    // ── Mesas activas ahora ──────────────────────────────────
    #[Computed]
    public function activeTables(): int
    {
        return Table::whereIn('status', ['ocupada', 'pedido_listo'])->count();
    }

    // ── Inventario crítico ──────────────────────────────────
    #[Computed]
    public function criticalStockCount(): int
    {
        return InventoryItem::critical()->active()->groupBy('name')->count();
    }

    #[Computed]
    public function criticalInventory()
    {
        return InventoryItem::critical()->active()->groupBy('name')->orderBy('name')->get();
    }

    // ── Productos más vendidos ───────────────────────────────
    #[Computed]
    public function topProducts()
    {
        return OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
            ->with('product.category')
            ->whereHas('order', function ($q) {
                $q->where('status', 'pagado')
                  ->when($this->period === 'today', fn($q) => $q->whereDate('created_at', today()))
                  ->when($this->period === 'week',  fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                  ->when($this->period === 'month', fn($q) => $q->whereMonth('created_at', now()->month));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();
    }

    // ── Ventas por hora (hoy) ────────────────────────────────
    #[Computed]
    public function salesByHour()
    {
        $hourExpr = $this->hourExpression();

        return Order::select(
            DB::raw("{$hourExpr} as hour"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'pagado')
            ->whereDate('created_at', today())
            ->groupBy(DB::raw($hourExpr))
            ->orderBy(DB::raw($hourExpr))
            ->get()
            ->keyBy('hour');
    }

    // ── Órdenes pendientes de pago ───────────────────────────
    #[Computed]
    public function pendingPayment()
    {
        return Order::with(['table', 'items', 'user'])
            ->where('status', 'entregado')
            ->orderBy('created_at')
            ->get();
    }

    // ── Cerrar cuenta (pagar) ────────────────────────────────
    public function closeOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::with('table')->findOrFail($orderId);

            // Registrar pago en efectivo (flujo rápido desde dashboard)
            OrderPayment::create([
                'order_id'       => $order->id,
                'user_id'        => auth()->id(),
                'amount'         => $order->total,
                'payment_method' => 'efectivo',
                'notes'          => 'Cobrado desde dashboard admin',
            ]);

            $order->update([
                'status'    => 'pagado',
                'closed_at' => now(),
            ]);

            // Liberar la mesa
            $order->table->update(['status' => 'libre']);
        });

        // Refrescar métricas
        unset($this->totalSales, $this->totalOrders, $this->avgTicket,
              $this->activeTables, $this->pendingPayment, $this->criticalStockCount, $this->criticalInventory);
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard Admin']);
    }
}