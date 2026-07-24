<?php

namespace App\Livewire\Admin;

use App\Models\InventoryItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InventoryManager extends Component
{
    public bool $showForm = false;
    public bool $showAdjustForm = false;
    public ?int $editingId = null;
    public ?int $adjustingId = null;

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|in:comida,bebida,verdura')]
    public string $category = 'comida';

    #[Validate('required|numeric|min:0')]
    public string $stock = '0';

    #[Validate('required|numeric|min:0')]
    public string $min_stock = '0';

    #[Validate('required|string|max:30')]
    public string $unit = 'uds';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public bool $is_active = true;

    public string $search = '';
    public string $filterCategory = '';

    #[Validate('required|numeric')]
    public string $adjustment = '0';

    public string $adjustmentType = 'add';

    #[Computed]
    public function items()
    {
        return InventoryItem::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn ($query) => $query->where('category', $this->filterCategory))
            ->groupBy('name')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function criticalItems()
    {
        return InventoryItem::critical()
            ->active()
            ->groupBy('name')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function criticalCount(): int
    {
        return InventoryItem::critical()
            ->active()
            ->groupBy('name')
            ->count();
    }

    #[Computed]
    public function totalItems(): int
    {
        return InventoryItem::active()->count();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(int $itemId): void
    {
        $item = InventoryItem::findOrFail($itemId);

        $this->editingId = $itemId;
        $this->name = $item->name;
        $this->category = $item->category;
        $this->stock = (string) $item->stock;
        $this->min_stock = (string) $item->min_stock;
        $this->unit = $item->unit;
        $this->notes = $item->notes ?? '';
        $this->is_active = $item->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'unit' => $this->unit,
            'notes' => $this->notes ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            InventoryItem::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Insumo actualizado.');
        } else {
            InventoryItem::create($data);
            session()->flash('success', 'Insumo creado.');
        }

        $this->closeForm();
        unset($this->items, $this->criticalItems, $this->criticalCount, $this->totalItems);
    }

    public function openAdjust(int $itemId): void
    {
        $item = InventoryItem::findOrFail($itemId);

        $this->adjustingId = $itemId;
        $this->adjustment = '0';
        $this->adjustmentType = 'add';
        $this->name = $item->name;
        $this->showAdjustForm = true;
    }

    public function applyAdjustment(): void
    {
        $this->validate([
            'adjustment' => 'required|numeric',
            'adjustmentType' => 'required|in:add,subtract',
        ]);

        $item = InventoryItem::findOrFail($this->adjustingId);
        $adjustment = (float) $this->adjustment;
        $newStock = $this->adjustmentType === 'subtract'
            ? max(0, (float) $item->stock - $adjustment)
            : (float) $item->stock + $adjustment;

        $item->update(['stock' => $newStock]);

        $this->closeAdjustForm();
        unset($this->items, $this->criticalItems, $this->criticalCount, $this->totalItems);
        session()->flash('success', 'Inventario ajustado.');
    }

    public function toggleActive(int $itemId): void
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->update(['is_active' => ! $item->is_active]);
        unset($this->items, $this->criticalItems, $this->criticalCount, $this->totalItems);
    }

    public function delete(int $itemId): void
    {
        InventoryItem::findOrFail($itemId)->delete();
        unset($this->items, $this->criticalItems, $this->criticalCount, $this->totalItems);
        session()->flash('success', 'Insumo eliminado.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function closeAdjustForm(): void
    {
        $this->showAdjustForm = false;
        $this->adjustingId = null;
        $this->adjustment = '0';
        $this->adjustmentType = 'add';
        $this->name = '';
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'category', 'stock', 'min_stock', 'unit', 'notes', 'editingId']);
        $this->is_active = true;
        $this->category = 'comida';
        $this->stock = '0';
        $this->min_stock = '0';
        $this->unit = 'uds';
    }

    public function render()
    {
        return view('livewire.admin.inventory-manager')
            ->layout('layouts.app', ['title' => 'Inventario']);
    }
}