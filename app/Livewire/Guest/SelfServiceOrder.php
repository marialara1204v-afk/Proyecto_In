<?php

namespace App\Livewire\Guest;

use App\Events\NewOrderFromTable;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SelfServiceOrder extends Component
{
    public int $tableId;
    public string $tableName = '';
    public int $tableNumber = 0;
    public string $search = '';
    public ?int $activeCategoryId = null;
    public array $cart = [];
    public bool $showCart = false;
    public bool $orderPlaced = false;
    public ?string $orderError = null;

    public function mount(string $table): void
    {
        $tableModel = Table::where('number', $table)->orWhere('id', $table)->first();

        if (! $tableModel) {
            abort(404, 'Mesa no encontrada');
        }

        $this->tableId = $tableModel->id;
        $this->tableName = $tableModel->number;
        $this->tableNumber = $tableModel->number;

        $this->cart = session()->get("selfservice_cart_{$this->tableId}", []);
    }

    public function updatedSearch(): void
    {
        $this->activeCategoryId = null;
    }

    #[Computed]
    public function table()
    {
        return Table::find($this->tableId);
    }

    #[Computed]
    public function categories()
    {
        return Category::active()->orderBy('name')->get();
    }

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

    #[Computed]
    public function cartItems(): array
    {
        return collect($this->cart)->values()->all();
    }

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

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product || ! $product->is_available) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $productId,
                'name'       => $product->name,
                'price'      => (float) $product->price,
                'quantity'   => 1,
                'notes'      => '',
            ];
        }

        $this->persistCart();
    }

    public function incrementItem(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->persistCart();
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
        $this->persistCart();
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->persistCart();
    }

    public function updateNote(int $productId, string $note): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['notes'] = substr($note, 0, 255);
            $this->persistCart();
        }
    }

    public function toggleCart(): void
    {
        $this->showCart = ! $this->showCart;
    }

    public function submitOrder(): void
    {
        $this->orderError = null;

        if (empty($this->cart)) {
            $this->orderError = 'Agrega al menos un producto al pedido.';
            return;
        }

        try {
            DB::transaction(function () {
                $table = Table::query()->lockForUpdate()->findOrFail($this->tableId);

                $order = Order::create([
                    'table_id' => $table->id,
                    'user_id'  => null,
                    'status'   => 'pendiente',
                    'total'    => 0,
                ]);

                $itemsProcessed = 0;

                foreach ($this->cart as $productId => $item) {
                    $product = Product::find($productId);
                    if (! $product || ! $product->is_available) continue;

                    $price     = (float) $product->price;
                    $quantity  = max(1, (int) $item['quantity']);
                    $notes     = substr($item['notes'] ?? '', 0, 255);
                    $destination = $product->isDrink() ? 'bartender' : 'cocina';

                    $order->items()->create([
                        'product_id'   => $productId,
                        'quantity'     => $quantity,
                        'notes'        => $notes,
                        'unit_price'   => $price,
                        'subtotal'     => $price * $quantity,
                        'destination'  => $destination,
                        'item_status'  => 'pendiente',
                    ]);

                    $itemsProcessed++;
                }

                if ($itemsProcessed === 0) {
                    throw new \RuntimeException('No hay productos disponibles para enviar.');
                }

                $order->recalculateTotal();
                $table->update(['status' => 'ocupada']);

                NewOrderFromTable::dispatch($order->fresh(['table', 'items.product']));
            });

            session()->forget("selfservice_cart_{$this->tableId}");
            $this->cart = [];
            $this->showCart = false;
            $this->orderPlaced = true;
        } catch (\Throwable $e) {
            report($e);
            $this->orderError = 'No se pudo enviar el pedido. Intenta de nuevo.';
        }
    }

    protected function persistCart(): void
    {
        session()->put("selfservice_cart_{$this->tableId}", $this->cart);
    }

    public function render()
    {
        return view('livewire.guest.self-service-order')
            ->layout('layouts.app', ['title' => "Mesa {$this->tableNumber} - Menu"]);
    }
}
