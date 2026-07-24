<?php

namespace App\Livewire\Waiter;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Throwable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TableMap extends Component
{
    public bool   $showTablePickerModal = false;

    // ── Estado del Modal de Pedido ─────────────────────────
    public bool   $showOrderModal   = false;
    public ?int   $selectedTableId  = null;
    public string $search           = '';
    public ?int   $activeCategoryId = null;
    public array  $cart             = [];
    public ?int   $editingNoteFor   = null;

    // ── Estado del Modal de Cuenta ─────────────────────────
    public bool $showBillModal = false;
    public ?int $billOrderId   = null;

    // ── Estado del Modal de Entrega ─────────────────────────
    public bool $showDeliveryModal = false;
    public ?int $deliveryOrderId   = null;

    // ── Estado del Modal de Pago ──────────────────────────────
    public bool   $showPaymentModal = false;
    public ?int   $paymentOrderId   = null;
    public string $paymentMethod    = 'efectivo';
    public float  $amountGiven      = 0;        // Monto dado en efectivo
    public string $notes            = '';       // Notas del pago
    public array  $payments         = [];

    // ── Notificaciones de items listos ─────────────────────
    public bool   $showReadyNotification = false;
    public string $readyItemName    = '';
    public int    $readyTableNumber = 0;
    public int    $readyOrderId     = 0;

    #[Computed]
    public function deliveryOrder()
    {
        return $this->deliveryOrderId 
            ? Order::with('items.product', 'table')->find($this->deliveryOrderId)
            : null;
    }

    #[Computed]
    public function paymentOrder()
    {
        return $this->paymentOrderId 
            ? Order::with('items.product', 'table')->find($this->paymentOrderId)
            : null;
    }

    #[Computed]
    public function totalAmount(): float
    {
        return (float) ($this->paymentOrder?->total ?? 0);
    }

    #[Computed]
    public function amountPerPerson(): float
    {
        return $this->totalAmount > 0 ? $this->totalAmount / $this->splitCount : 0;
    }

    #[Computed]
    public function totalPaid(): float
    {
        if (empty($this->payments)) {
            return 0.0;
        }
        $total = 0;
        foreach ($this->payments as $payment) {
            if (isset($payment['amount'])) {
                $total += floatval($payment['amount']);
            }
        }
        return $total;
    }

    #[Computed]
    public function remainingAmount(): float
    {
        return max(0, $this->totalAmount - $this->totalPaid);
    }

    public function mount(): void
    {
        $tableId = request()->integer('table');
        $productId = request()->integer('item');
        $action = request()->string('action')->toString();

        if (request()->boolean('newOrder') || in_array($action, ['add', 'remove', 'inc', 'dec', 'submit'], true)) {
            if ($tableId > 0) {
                $this->openOrderModal($tableId);
            } else {
                $this->startNewOrder();
            }
        }

        if ($this->selectedTableId && in_array($action, ['add', 'remove', 'inc', 'dec', 'submit'], true)) {
            if (in_array($action, ['add', 'remove', 'inc', 'dec'], true) && $productId > 0) {
                match ($action) {
                    'add' => $this->addToCart($productId),
                    'remove' => $this->removeItem($productId),
                    'inc' => $this->incrementItem($productId),
                    'dec' => $this->decrementItem($productId),
                    default => null,
                };

                $this->redirectRoute('waiter.tables', [
                    'newOrder' => 1,
                    'table' => $this->selectedTableId,
                ]);
                return;
            }

            if ($action === 'submit') {
                $this->submitOrder();
                $this->redirectRoute('waiter.tables');
                return;
            }
        }
    }

    private function cartSessionKey(?int $tableId = null): ?string
    {
        $id = $tableId ?? $this->selectedTableId;

        return $id ? "waiter_cart_table_{$id}" : null;
    }

    private function loadCartFromSession(int $tableId): void
    {
        $this->cart = session()->get($this->cartSessionKey($tableId), []);
    }

    private function persistCartToSession(): void
    {
        $key = $this->cartSessionKey();

        if (! $key) {
            return;
        }

        session()->put($key, $this->cart);
    }

    // ── Mesas ───────────────────────────────────────────────
    #[Computed]
    public function tables()
    {
        return Table::with(['activeOrder.items'])->orderBy('number')->get();
    }

    // ── Categorías ──────────────────────────────────────────
    #[Computed]
    public function categories()
    {
        return Category::active()->get();
    }

    // ── Productos filtrados ─────────────────────────────────
    #[Computed]
    public function products()
    {
        return Product::available()
            ->when($this->activeCategoryId, fn($q) =>
                $q->where('category_id', $this->activeCategoryId)
            )
            ->when($this->search, function ($q) {
                $safe = str_replace(['%', '_'], ['\%', '\_'], $this->search);
                $q->where('name', 'like', "%{$safe}%");
            })
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    // ── Mesa seleccionada ───────────────────────────────────
    #[Computed]
    public function selectedTable()
    {
        return $this->selectedTableId
            ? Table::find($this->selectedTableId)
            : null;
    }

    // ── Orden de la cuenta ──────────────────────────────────
    #[Computed]
    public function billOrder()
    {
        return $this->billOrderId
            ? Order::with('items.product', 'table')->find($this->billOrderId)
            : null;
    }

    // ── Totales del carrito ─────────────────────────────────
    #[Computed]
    public function cartTotal(): float
    {
        return collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    #[Computed]
    public function cartCount(): int
    {
        return collect($this->cart)->sum('quantity');
    }

    // ── Abrir modal de pedido ───────────────────────────────
    public function openOrderModal(int $tableId): void
    {
        $table = Table::with('activeOrder')->find($tableId);

        if (! $table) {
            session()->flash('error', 'La mesa seleccionada no existe.');
            return;
        }

        // Si la orden ya fue entregada, mostrar modal de pago
        if ($table->activeOrder && $table->activeOrder->status === 'entregado') {
            session()->flash('error', 'Esta mesa tiene una cuenta pendiente de pago.');
            $this->openBillModal($tableId);
            return;
        }

        // Si la orden está lista, mostrar modal de entrega
        if ($table->activeOrder && $table->activeOrder->status === 'listo') {
            $this->openDeliveryModal($tableId);
            return;
        }

        $this->selectedTableId  = $tableId;
        $this->loadCartFromSession($tableId);
        $this->search           = '';
        $this->activeCategoryId = null;
        $this->showOrderModal   = true;
    }

    public function startNewOrder(): void
    {
        if (! Table::query()->exists()) {
            session()->flash('error', 'No hay mesas libres para crear un pedido.');
            return;
        }

        $this->showTablePickerModal = true;
    }

    public function closeTablePickerModal(): void
    {
        $this->showTablePickerModal = false;
    }

    public function selectTableForOrder(int $tableId): void
    {
        $table = Table::find($tableId);

        if (! $table) {
            session()->flash('error', 'La mesa seleccionada no existe.');
            return;
        }

        $this->showTablePickerModal = false;
        $this->openOrderModal($tableId);
    }

    public function closeOrderModal(): void
    {
        $this->showOrderModal  = false;
        $this->showTablePickerModal = false;
        $this->selectedTableId = null;
        $this->cart            = [];
        $this->editingNoteFor  = null;
        $this->search          = '';
        $this->activeCategoryId = null;
    }

    public function refreshTables(): void
    {
        unset($this->tables);
    }

    // ────────────────────────────────────────────────────────────
    // NUEVO: Cambiar estado manual de mesa
    // ────────────────────────────────────────────────────────────
    public function changeTableStatus(int $tableId, string $status): void
    {
        $validStatuses = ['libre', 'ocupada', 'pedido_listo', 'entregado'];
        
        if (!in_array($status, $validStatuses)) {
            session()->flash('error', 'Estado inválido');
            return;
        }

        try {
            $table = Table::with('activeOrder')->findOrFail($tableId);
            
            // Si cambias a 'entregado', también actualiza la orden
            if ($status === 'entregado' && $table->activeOrder) {
                $table->activeOrder->update(['status' => 'entregado']);
            }
            
            $table->update(['status' => $status]);
            
            // Refrescar las mesas en el waiter
            unset($this->tables);
            
            // Notificar a la caja que se actualicen las mesas
            $this->dispatch('table-status-updated', tableId: $tableId, status: $status);
            
            session()->flash('success', "Mesa {$table->number} marcada como " . ucfirst(str_replace('_', ' ', $status)));
        } catch (\Exception $e) {
            report($e);
            session()->flash('error', 'Error al cambiar el estado');
        }
    }

    // ── Abrir modal de cuenta ───────────────────────────────
    public function openBillModal(int $tableId): void
    {
        $table = Table::with('activeOrder')->find($tableId);
        if (! $table?->activeOrder) return;

        $this->billOrderId   = $table->activeOrder->id;
        $this->showBillModal = true;
    }

    public function closeBillModal(): void
    {
        $this->showBillModal = false;
        $this->billOrderId   = null;
    }

    // ── Abrir modal de entrega ────────────────────────────
    public function openDeliveryModal(int $tableId): void
    {
        $table = Table::with('activeOrder')->find($tableId);
        if (! $table?->activeOrder) return;

        $this->deliveryOrderId   = $table->activeOrder->id;
        $this->showDeliveryModal = true;
    }

    public function closeDeliveryModal(): void
    {
        $this->showDeliveryModal = false;
        $this->deliveryOrderId   = null;
    }

    // ── Modal de Pago ──────────────────────────────────────
    public function openPaymentModal(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order->status !== 'entregado') {
            session()->flash('error', 'Solo se pueden pagar órdenes entregadas');
            return;
        }

        $this->paymentOrderId = $orderId;
        $this->paymentMethod  = 'efectivo';
        $this->payments       = [];
        $this->amountGiven    = 0;
        $this->notes          = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentOrderId   = null;
        $this->payments         = [];
        $this->amountGiven      = 0;
        $this->notes            = '';
    }

    // NUEVO: Obtener cambio para efectivo
    public function getChange(): float
    {
        if ($this->paymentMethod !== 'efectivo' || $this->amountGiven <= 0) {
            return 0;
        }
        return max(0, $this->amountGiven - $this->totalAmount);
    }

    public function addPayment(): void
    {
        if ($this->paymentMethod === 'efectivo') {
            // Para efectivo: debe cubrir el total
            if ($this->amountGiven < $this->totalAmount) {
                session()->flash('error', 'El monto dado no cubre el total');
                return;
            }
            
            $this->payments = [[
                'amount' => $this->totalAmount,
                'method' => 'efectivo',
                'notes' => $this->notes,
                'given' => $this->amountGiven,
                'change' => $this->getChange(),
            ]];
        } else {
            // Para otros métodos: pagar todo el total
            if ($this->totalAmount <= 0) {
                session()->flash('error', 'Monto inválido');
                return;
            }

            $this->payments = [[
                'amount' => $this->totalAmount,
                'method' => $this->paymentMethod,
                'notes' => $this->notes,
            ]];
        }

        $this->amountGiven = 0;
        $this->notes = '';
    }

    public function removePayment(int $index): void
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function submitPayment(): void
    {
        if (empty($this->payments)) {
            session()->flash('error', 'Registra al menos un pago');
            return;
        }

        try {
            DB::transaction(function () {
                $order = Order::with('table')->findOrFail($this->paymentOrderId);
                $user  = auth()->user();

                // Registrar pagos
                foreach ($this->payments as $payment) {
                    \App\Models\OrderPayment::create([
                        'order_id'       => $order->id,
                        'user_id'        => $user->id,
                        'amount'         => $payment['amount'],
                        'payment_method' => $payment['method'],
                        'notes'          => $payment['notes'] ?? 'Pago registrado',
                    ]);
                }

                // Marcar orden como pagada y liberar mesa
                $order->update(['status' => 'pagado']);
                $order->table->update(['status' => 'libre']);
            });

            session()->flash('success', '✓ Pago registrado. Mesa liberada');
            $this->closePaymentModal();
            unset($this->tables);
        } catch (\Exception $e) {
            report($e);
            session()->flash('error', 'Error al procesar el pago');
        }
    }

    // ── Carrito: agregar ────────────────────────────────────
    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'name'     => $product->name,
                'price'    => (float) $product->price,
                'quantity' => 1,
                'notes'    => '',
            ];
        }

        $this->persistCartToSession();
    }

    public function incrementItem(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->persistCartToSession();
        }
    }

    public function decrementItem(int $productId): void
    {
        if (! isset($this->cart[$productId])) return;

        if ($this->cart[$productId]['quantity'] <= 1) {
            $this->removeItem($productId);
            return;
        }

        $this->cart[$productId]['quantity']--;
        $this->persistCartToSession();
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->persistCartToSession();
    }

    public function updateNote(int $productId, string $note): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['notes'] = substr($note, 0, 255);
            $this->persistCartToSession();
        }
        $this->editingNoteFor = null;
    }

    // ── Enviar pedido a cocina ──────────────────────────────
    public function submitOrder(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Agrega al menos un producto al pedido.');
            return;
        }

        if (! $this->selectedTableId) {
            session()->flash('error', 'Selecciona una mesa antes de enviar el pedido.');
            return;
        }

        try {
            DB::transaction(function () {
                $table = Table::query()->lockForUpdate()->findOrFail($this->selectedTableId);

                $latestUnpaidOrder = $table->orders()
                    ->where('status', '!=', 'pagado')
                    ->latest()
                    ->first();

                if ($latestUnpaidOrder && $latestUnpaidOrder->status === 'entregado') {
                    throw new \RuntimeException('La mesa tiene una cuenta pendiente de pago. Cobrala antes de crear un nuevo pedido.');
                }

                $order = $table->orders()
                    ->whereIn('status', ['pendiente', 'preparando', 'listo'])
                    ->latest()
                    ->first();

                if (! $order) {
                    $order = Order::create([
                        'table_id' => $table->id,
                        'user_id'  => auth()->id(),
                        'status'   => 'pendiente',
                        'total'    => 0,
                    ]);
                } elseif ($order->status !== 'pendiente') {
                    $order->update(['status' => 'pendiente']);
                }

                $itemsProcessed = 0;

                foreach ($this->cart as $productId => $item) {
                    $product = Product::find($productId);
                    if (! $product || ! $product->is_available) {
                        continue;
                    }

                    $price     = (float) $product->price;
                    $quantity  = max(1, (int) $item['quantity']);
                    $notes     = substr($item['notes'] ?? '', 0, 255);
                    $destination = $product->isDrink() ? 'bartender' : 'cocina';

                    $existing = $order->items()->where('product_id', $productId)->first();

                    if ($existing) {
                        $existing->quantity += $quantity;
                        $existing->notes = $notes ?: $existing->notes;
                        $existing->destination = $destination;
                        $existing->subtotal = $existing->unit_price * $existing->quantity;
                        $existing->save();
                    } else {
                        $order->items()->create([
                            'product_id' => $productId,
                            'quantity'   => $quantity,
                            'notes'      => $notes,
                            'unit_price' => $price,
                            'subtotal'   => $price * $quantity,
                            'destination' => $destination,
                            'item_status' => 'pendiente',
                        ]);
                    }

                    $itemsProcessed++;
                }

                if ($itemsProcessed === 0) {
                    throw new \RuntimeException('No hay productos disponibles para enviar.');
                }

                $order->recalculateTotal();
                $table->update(['status' => 'ocupada']);
            });

            $this->dispatch('order-submitted');

            if ($this->selectedTableId) {
                session()->forget($this->cartSessionKey());
            }

            $this->closeOrderModal();
            unset($this->tables);
            session()->flash('success', '¡Pedido enviado a cocina!');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo enviar el pedido. Intenta de nuevo.');
        }
    }

    // ── Marcar como entregado ────────────────────────────────
    public function markDelivered(): void
    {
        //Obtener el orderId de delivery o bill modal
        $orderId = $this->deliveryOrderId ?? $this->billOrderId;
        if (!$orderId) return;

        $order = Order::with('table')->findOrFail($orderId);

        if ($order->status === 'entregado') {
            $this->closeDeliveryModal();
            $this->openPaymentModal($orderId);
            session()->flash('success', 'Comida entregada. Proceda al pago.');
            return;
        }

        if ($order->status !== 'listo') {
            session()->flash('error', 'Solo puedes entregar pedidos cuando estén en estado listo.');
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'entregado']);
            $order->table->update(['status' => 'entregado']);
        });

        $this->closeDeliveryModal();
        $this->openPaymentModal($orderId);
        unset($this->tables);
        
        // Notificar a la caja que hay una nueva orden lista para pagar
        $this->dispatch('table-status-updated', tableId: $order->table_id, status: 'entregado');
        
        session()->flash('success', '¡Cuenta entregada! Aparecerá en caja para cobrar.');
    }

    // ── Escuchar evento de cocina/barra ──────────────────────
    #[On('order-ready')]
    public function handleOrderReady(): void
    {
        unset($this->tables);
    }

    // ── Escuchar pedido desde auto-servicio QR ──────────────
    #[On('new-order-from-table')]
    public function handleNewOrderFromTable($tableNumber, $orderId): void
    {
        unset($this->tables);
        session()->flash('success', "Nuevo pedido auto-servicio de Mesa {$tableNumber}");
    }

    // Listener para cuando se completa un pago desde caja
    #[On('payment-completed')]
    public function handlePaymentCompleted(): void
    {
        unset($this->tables);
        session()->flash('success', 'Mesa fue pagada y liberada');
    }

    // Escuchar notificaciones de items listos (via WebSocket)
    #[On('item-ready')]
    public function handleItemReady($tableNumber, $itemName, $orderId): void
    {
        $this->showReadyNotification = true;
        $this->readyTableNumber = $tableNumber;
        $this->readyItemName = $itemName;
        $this->readyOrderId = $orderId;

        // Auto-cerrar notificación en 5 segundos
        $this->js('setTimeout(() => { Livewire.dispatch("close-notification") }, 5000)');
    }

    #[On('close-notification')]
    public function closeNotification(): void
    {
        $this->showReadyNotification = false;
    }

    public function render()
    {
        return view('livewire.waiter.table-map')
            ->layout('layouts.app', ['title' => 'Mapa de Mesas']);
    }
}