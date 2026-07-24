<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'role_id', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ── Helpers de Rol ──────────────────────────────────────
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isMesero(): bool   { return $this->role === 'mesero'; }
    public function isCocina(): bool   { return $this->role === 'cocina'; }
    public function isBartender(): bool { return $this->role === 'bartender'; }

    /** Cocina y Bartender comparten el panel de comandas */
    public function hasKitchenAccess(): bool
    {
        return in_array($this->role, ['cocina', 'bartender']);
    }

    /** Ruta de redirección post-login según rol */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin'                    => 'admin.dashboard',
            'cocina', 'bartender'      => 'kitchen.orders',
            default                    => 'waiter.tables',   // mesero
        };
    }
}