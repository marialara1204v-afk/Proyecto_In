<?php

namespace App\Livewire\Kitchen;

use App\Events\OrderStatusChanged;
use App\Events\ItemReady;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class KitchenBoard extends Component
{
    public string $filter = '';

    public function mount(): void
    {
        $this->filter = match (auth()->user()?->role) {
            'bartender' => 'bartender',
            'cocina'    => 'cocina',
            default     => 'all',
        };
    }

    #[Computed]
    public function canSeeAllFilters(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    #[Computed]
    public function filterOrder(): array
    {
        return match (auth()->user()?->role) {
            'bartender' => ['bartender' => 'Barra'],
            'cocina'    => ['cocina'    => 'Cocina'],
            default     => ['all' => 'Todo', 'cocina' => 'Cocina', 'bartender' => 'Barra'],
        };
    }

    #[Computed]
    public function orders()
    {
        $query = Order::with(['items.product.category', 'table', 'user'])
            ->forKitchen();

        // Filtrar órdenes basándose en el destino de sus items
        if ($this->filter !== 'all') {
            $destination = $this->filter === 'bartender' ? 'bartender' : 'cocina';
            $query->whereHas('items', function ($q) use ($destination) {
                $q->where('destination', $destination);
            });
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    #[Computed]
    public function stats(): array
    {
        $query = Order::forKitchen();

        if ($this->filter !== 'all') {
            $destination = $this->filter === 'bartender' ? 'bartender' : 'cocina';
            $query->whereHas('items', fn ($q) => $q->where('destination', $destination));
        }

        return [
            'pending'   => (clone $query)->where('status', 'pendiente')->count(),
            'preparing' => (clone $query)->where('status', 'preparando')->count(),
        ];
    }

    public function advanceStatus(int $orderId): void
    {
        $order = Order::with(['table', 'items'])->findOrFail($orderId);

        $userRole = auth()->user()?->role;

        if (! in_array($userRole, ['admin', 'cocina', 'bartender'])) {
            session()->flash('error', 'No tienes permiso para modificar el estado.');
            return;
        }

        if ($userRole === 'cocina') {
            $hasBartenderItems = $order->items->contains('destination', 'bartender');
            $hasCocinaItems    = $order->items->contains('destination', 'cocina');
            if ($hasBartenderItems && ! $hasCocinaItems) {
                session()->flash('error', 'Esta orden contiene exclusivamente bebidas (barra).');
                return;
            }
        }

        if ($userRole === 'bartender') {
            $hasCocinaItems    = $order->items->contains('destination', 'cocina');
            $hasBartenderItems = $order->items->contains('destination', 'bartender');
            if ($hasCocinaItems && ! $hasBartenderItems) {
                session()->flash('error', 'Esta orden contiene exclusivamente cocina.');
                return;
            }
        }

        $next = match ($order->status) {
            'pendiente'  => 'preparando',
            'preparando' => 'listo',
            default      => null,
        };

        if (! $next) return;

        DB::transaction(function () use ($order, $next, $userRole) {
            $order->update(['status' => $next]);

            if ($next === 'listo') {
                $userDestination = $userRole === 'bartender' ? 'bartender' : 'cocina';

                $order->items()
                    ->where('destination', $userDestination)
                    ->where('item_status', '!=', 'listo')
                    ->update([
                        'item_status' => 'listo',
                        'prepared_at' => now(),
                    ]);

                $order->refresh()->load('items');

                if ($order->items->every('item_status', 'listo')) {
                    $order->table->update(['status' => 'pedido_listo']);
                    $this->dispatch('order-ready', tableNumber: $order->table->number, orderId: $order->id);
                }
            }
        });

        OrderStatusChanged::dispatch($order->fresh(['table']));
        unset($this->orders, $this->stats);
    }

    public function completeOrder(int $orderId): void
    {
        $this->advanceStatus($orderId);
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $item = OrderItem::with('order.table', 'product')->findOrFail($itemId);

        if (! in_array($status, ['preparando', 'listo'])) {
            session()->flash('error', 'Estado inválido');
            return;
        }

        $userRole = auth()->user()?->role;
        $itemDestination = $item->destination ?? 'cocina';

        if ($userRole === 'bartender' && $itemDestination !== 'bartender') {
            session()->flash('error', 'Solo puedes modificar items de la barra.');
            return;
        }
        if ($userRole === 'cocina' && $itemDestination !== 'cocina') {
            session()->flash('error', 'Solo puedes modificar items de cocina.');
            return;
        }

        try {
            DB::transaction(function () use ($item, $status) {
                $item->update(['item_status' => $status]);

                if ($status === 'listo') {
                    $item->update(['prepared_at' => now()]);

                    ItemReady::dispatch(
                        $item->order->id,
                        $item->order->table->number,
                        $item->product->name
                    );

                    $this->autoAdvanceOrder($item->order);
                }
            });

            unset($this->orders, $this->stats);

            $message = $status === 'listo' ? 'Listo' : 'En preparacion';
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    protected function autoAdvanceOrder(Order $order): void
    {
        $order->load('items');

        $allReady = $order->items->every('item_status', 'listo');

        if ($allReady && $order->status !== 'listo') {
            $order->update(['status' => 'listo']);
            $order->table->update(['status' => 'pedido_listo']);
            $this->dispatch('order-ready', tableNumber: $order->table->number, orderId: $order->id);
            OrderStatusChanged::dispatch($order->fresh(['table']));
        }
    }

    #[On('order-submitted')]
    public function refresh(): void
    {
        unset($this->orders, $this->stats);
    }

    #[On('new-order-from-table')]
    public function handleNewOrder(): void
    {
        unset($this->orders, $this->stats);
    }

    public function render()
    {
        $title = match (auth()->user()?->role) {
            'bartender' => 'Panel Barra',
            default     => 'Panel Cocina',
        };

        return view('livewire.kitchen.kitchen-board')
            ->layout('layouts.app', ['title' => $title]);
    }
}