<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'icon', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Métodos de Clasificación ────────────────────────────
    public function getTypeAttribute(): string
    {
        $beverageCats = [
            'Bebidas Calientes',
            'Bebidas Frías',
            'Coctelería Sin Alcohol',
            'Coctelería Con Alcohol',
            'Cantaritos',
            'Refrescos',
            'Cerveza',
        ];

        return in_array($this->name, $beverageCats) ? 'bebida' : 'comida';
    }

    public function isBeverage(): bool
    {
        return $this->type === 'bebida';
    }

    public function isFood(): bool
    {
        return $this->type === 'comida';
    }
}