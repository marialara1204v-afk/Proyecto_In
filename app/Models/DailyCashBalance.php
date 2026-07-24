<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCashBalance extends Model
{
    protected $fillable = [
        'date',
        'total_sales',
        'total_cash',
        'total_card',
        'total_transfer',
        'total_other',
        'total_tips',
        'total_payments',
        'orders_count',
        'notes',
        'closed_at',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'date'              => 'date',
            'total_sales'       => 'decimal:2',
            'total_cash'        => 'decimal:2',
            'total_card'        => 'decimal:2',
            'total_transfer'    => 'decimal:2',
            'total_other'       => 'decimal:2',
            'total_tips'        => 'decimal:2',
            'total_payments'    => 'decimal:2',
            'closed_at'         => 'datetime',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // ── Helpers ─────────────────────────────────────────────
    public function isClosed(): bool
    {
        return !is_null($this->closed_at);
    }

    public function difference(): float
    {
        return (float) ($this->total_sales - $this->total_payments);
    }
}
