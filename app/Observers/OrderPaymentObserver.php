<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\OrderPayment;

class OrderPaymentObserver
{
    public function created(OrderPayment $payment): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => OrderPayment::class,
            'auditable_id'   => $payment->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'order_id'       => $payment->order_id,
                'amount'         => $payment->amount,
                'tip'            => $payment->tip,
                'payment_method' => $payment->payment_method,
                'notes'          => $payment->notes,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(OrderPayment $payment): void
    {
        $changes = $payment->getChanges();
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
            'auditable_type' => OrderPayment::class,
            'auditable_id'   => $payment->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
