<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_id', 'name', 'category', 'stock',
        'min_stock', 'unit', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock'      => 'decimal:2',
            'min_stock'  => 'decimal:2',
            'is_active'  => 'boolean',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->distinct();
    }

    public function scopeCritical($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')->distinct();
    }

    public function isCritical(): bool
    {
        return (float) $this->stock <= (float) $this->min_stock;
    }

    public function isFood(): bool
    {
        return $this->category === 'comida';
    }

    public function isDrink(): bool
    {
        return $this->category === 'bebida';
    }

    public function isVeggie(): bool
    {
        return $this->category === 'verdura';
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactivo';
        }

        if ($this->isCritical()) {
            return 'Stock crítico';
        }

        return 'Disponible';
    }

    public function statusColor(): string
    {
        if (! $this->is_active) {
            return 'bg-gray-500';
        }

        if ($this->isCritical()) {
            return 'bg-red-500';
        }

        return 'bg-green-500';
    }
}