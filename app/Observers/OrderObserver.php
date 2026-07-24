<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'table_id' => $order->table_id,
                'user_id'  => $order->user_id,
                'status'   => $order->status,
                'total'    => $order->total,
                'notes'    => $order->notes,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
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
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function deleted(Order $order): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'event'          => 'deleted',
            'old_values'     => $order->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
