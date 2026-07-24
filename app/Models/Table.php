<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    protected $fillable = ['number', 'capacity', 'status'];

    // ── Relaciones ──────────────────────────────────────────
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Orden activa (no pagada) de la mesa */
    public function activeOrder(): HasOne
    {
        return $this->hasOne(Order::class)
                    ->whereNotIn('status', ['pagado'])
                    ->latestOfMany();
    }

    // ── Helpers de Estado ───────────────────────────────────
    public function isFree(): bool        { return $this->status === 'libre'; }
    public function isOccupied(): bool    { return $this->status === 'ocupada'; }
    public function isOrderReady(): bool  { return $this->status === 'pedido_listo'; }
    public function isDelivered(): bool   { return $this->status === 'entregado'; }

    /** Color Tailwind para el mapa de mesas */
    public function statusColor(): string
    {
        return match ($this->status) {
            'libre'        => 'bg-green-500',
            'ocupada'      => 'bg-red-500',
            'pedido_listo' => 'bg-yellow-500',
            'entregado'    => 'bg-blue-500',
            default        => 'bg-gray-400',
        };
    }

    /** Etiqueta legible */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'libre'        => 'Libre',
            'ocupada'      => 'Ocupada',
            'pedido_listo' => 'Esperando Cuenta/Limpieza',
            'entregado'    => 'Entregado',
            default        => 'Desconocido',
        };
    }
}