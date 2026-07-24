<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ─────────────────────────────────────────────
    public function auditable(): ?Model
    {
        $class = $this->auditable_type;
        if (! class_exists($class)) {
            return null;
        }
        return $class::find($this->auditable_id);
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            default   => $this->event,
        };
    }

    public function eventColor(): string
    {
        return match ($this->event) {
            'created' => 'bg-green-500/20 text-green-400',
            'updated' => 'bg-yellow-500/20 text-yellow-400',
            'deleted' => 'bg-red-500/20 text-red-400',
            default   => 'bg-gray-700 text-gray-400',
        };
    }

    public function modelName(): string
    {
        $class = class_basename($this->auditable_type);
        return match ($class) {
            'Order'             => 'Orden',
            'OrderPayment'      => 'Pago',
            'OrderItem'         => 'Item de Orden',
            'InventoryItem'     => 'Inventario',
            'User'              => 'Usuario',
            'DailyCashBalance'  => 'Cierre de Caja',
            'Product'           => 'Producto',
            default             => $class,
        };
    }

    /** Traducir campo técnico a español legible */
    public static function translateField(string $field): string
    {
        return match ($field) {
            'status'        => 'Estado',
            'total'         => 'Total',
            'tip'           => 'Propina',
            'tip_split_workers' => 'Propina repartida entre',
            'notes'         => 'Notas',
            'closed_at'     => 'Cerrado el',
            'amount'        => 'Monto',
            'payment_method'=> 'Método de pago',
            'quantity'      => 'Cantidad',
            'unit_price'    => 'Precio unitario',
            'subtotal'      => 'Subtotal',
            'item_status'   => 'Estado del item',
            'destination'   => 'Destino',
            'prepared_at'   => 'Preparado el',
            'stock'         => 'Stock',
            'min_stock'     => 'Stock mínimo',
            'is_active'     => 'Activo',
            'is_available'  => 'Disponible',
            'category'      => 'Categoría',
            'name'          => 'Nombre',
            'email'         => 'Email',
            'role'          => 'Rol',
            'password'      => 'Contraseña',
            'price'         => 'Precio',
            'description'   => 'Descripción',
            'allergens'     => 'Alérgenos',
            'image'         => 'Imagen',
            'variants'      => 'Variantes',
            'total_sales'   => 'Ventas totales',
            'total_cash'    => 'Efectivo',
            'total_card'    => 'Tarjeta',
            'total_transfer'=> 'Transferencia',
            'total_other'   => 'Otros',
            'total_tips'    => 'Propinas total',
            'total_payments'=> 'Pagos total',
            'orders_count'  => 'Ordenes',
            'closed_by'     => 'Cerrado por',
            default         => $field,
        };
    }
}
