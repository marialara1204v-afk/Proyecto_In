<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'table_id', 'user_id', 'status',
        'total', 'tip', 'tip_split_workers',
        'notes', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'total'     => 'decimal:2',
            'tip'       => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function user(): BelongsTo   // mesero
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['pagado']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeForKitchen($query)
    {
        return $query->whereIn('status', ['pendiente', 'preparando']);
    }

    // ── Helpers ─────────────────────────────────────────────
    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');
        // Asegurar que el total sea siempre positivo
        $this->total = max(0, $total);
        $this->save();
    }

    /** Color de estado para la vista de cocina */
    public function kitchenStatusColor(): string
    {
        return match ($this->status) {
            'pendiente'  => 'bg-red-500',
            'preparando' => 'bg-yellow-400',
            'listo'      => 'bg-green-500',
            'entregado'  => 'bg-blue-400',
            default      => 'bg-gray-300',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pendiente'  => 'Pendiente',
            'preparando' => 'Preparando',
            'listo'      => 'Listo',
            'entregado'  => 'Entregado',
            'pagado'     => 'Pagado',
            default      => 'Desconocido',
        };
    }
}