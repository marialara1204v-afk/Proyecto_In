<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description',
        'allergens', 'price', 'image', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'is_available' => 'boolean',
            'variants'     => 'array',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryItem(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // ── Helpers ─────────────────────────────────────────────
    public function formattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Determina si el producto es una bebida
     */
    public function isDrink(): bool
    {
        $drinkCategories = [
            'Bebidas Calientes',
            'Bebidas Frías',
            'Coctelería Sin Alcohol',
            'Coctelería Con Alcohol',
            'Cantaritos',
            'Refrescos',
            'Cerveza',
        ];

        return in_array($this->category->name, $drinkCategories);
    }

    /**
     * Determina si el producto es comida
     */
    public function isFood(): bool
    {
        return !$this->isDrink();
    }
}