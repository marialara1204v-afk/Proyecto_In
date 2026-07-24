<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductManager extends Component
{
    use WithFileUploads;

    // ── Formulario ───────────────────────────────────────────
    public bool    $showForm     = false;
    public ?int    $editingId    = null;

    #[Validate('required|string|max:100')]
    public string  $name         = '';

    #[Validate('nullable|string|max:255')]
    public string  $description  = '';

    #[Validate('nullable|string|max:255')]
    public string  $allergens    = '';

    #[Validate('required|numeric|min:0')]
    public string  $price        = '';

    #[Validate('required|exists:categories,id')]
    public string  $category_id  = '';

    #[Validate('nullable|image|max:2048')]
    public $photo = null;

    public bool    $is_available = true;

    // ── Filtros ──────────────────────────────────────────────
    public string $search           = '';
    public string $filterCategory   = '';

    // ── Computed: Productos ──────────────────────────────────
    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
    }

    // ── Computed: Categorías ─────────────────────────────────
    #[Computed]
    public function categories()
    {
        return Category::active()->get();
    }

    // ── Abrir formulario de creación ─────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm   = true;
        $this->editingId  = null;
    }

    // ── Abrir formulario de edición ──────────────────────────
    public function openEdit(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->editingId    = $productId;
        $this->name         = $product->name;
        $this->description  = $product->description ?? '';
        $this->allergens    = $product->allergens ?? '';
        $this->price        = $product->price;
        $this->category_id  = $product->category_id;
        $this->is_available = $product->is_available;
        $this->photo        = null;
        $this->showForm     = true;
    }

    // ── Guardar (crear o actualizar) ─────────────────────────
    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'description'  => $this->description,
            'allergens'    => $this->allergens,
            'price'        => $this->price,
            'category_id'  => $this->category_id,
            'is_available' => $this->is_available,
        ];

        if ($this->photo) {
            $data['image'] = $this->photo->store('products', 'public');
        }

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);

            // Eliminar imagen anterior si se sube una nueva
            if ($this->photo && $product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->update($data);
            session()->flash('success', 'Producto actualizado.');
        } else {
            Product::create($data);
            session()->flash('success', 'Producto creado.');
        }

        $this->closeForm();
        unset($this->products);
    }

    // ── Toggle disponibilidad ────────────────────────────────
    public function toggleAvailability(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_available' => ! $product->is_available]);
        unset($this->products);
    }

    // ── Eliminar ─────────────────────────────────────────────
    public function delete(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        unset($this->products);
        session()->flash('success', 'Producto eliminado.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'description', 'allergens', 'price',
            'category_id', 'photo', 'editingId',
        ]);
        $this->is_available = true;
    }

    public function render()
    {
        return view('livewire.admin.product-manager')
            ->layout('layouts.app', ['title' => 'Inventario']);
    }
}