<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'quantity', 'notes', 'destination',
        'unit_price', 'subtotal', 'item_status', 'prepared_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'   => 'decimal:2',
            'subtotal'     => 'decimal:2',
            'prepared_at'  => 'datetime',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Helpers ─────────────────────────────────────────────
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->unit_price * $this->quantity;
    }

    public function isBartender(): bool
    {
        return $this->destination === 'bartender';
    }

    public function isKitchen(): bool
    {
        return $this->destination === 'cocina';
    }

    public function isPending(): bool
    {
        return $this->item_status === 'pendiente';
    }

    public function isPreparing(): bool
    {
        return $this->item_status === 'preparando';
    }

    public function isReady(): bool
    {
        return $this->item_status === 'listo';
    }

    public function markPreparing(): void
    {
        $this->update(['item_status' => 'preparando']);
    }

    public function markReady(): void
    {
        $this->update([
            'item_status' => 'listo',
            'prepared_at' => now(),
        ]);
    }
}