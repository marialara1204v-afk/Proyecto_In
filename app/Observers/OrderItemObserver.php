<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\OrderItem;

class OrderItemObserver
{
    public function created(OrderItem $item): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => OrderItem::class,
            'auditable_id'   => $item->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'order_id'    => $item->order_id,
                'product_id'  => $item->product_id,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'subtotal'    => $item->subtotal,
                'destination' => $item->destination,
                'notes'       => $item->notes,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(OrderItem $item): void
    {
        $changes = $item->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $field => $values) {
            if (is_array($values) && count($values) === 2) {
                $old[$field] = $values[0];
                $new[$field] = $values[1];
            }
        }

        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => OrderItem::class,
            'auditable_id'   => $item->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function deleted(OrderItem $item): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => OrderItem::class,
            'auditable_id'   => $item->id,
            'event'          => 'deleted',
            'old_values'     => $item->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
