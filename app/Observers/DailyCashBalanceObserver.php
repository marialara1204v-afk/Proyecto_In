<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\DailyCashBalance;

class DailyCashBalanceObserver
{
    public function created(DailyCashBalance $balance): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => DailyCashBalance::class,
            'auditable_id'   => $balance->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'date'           => $balance->date,
                'total_sales'    => $balance->total_sales,
                'total_cash'     => $balance->total_cash,
                'total_card'     => $balance->total_card,
                'total_transfer' => $balance->total_transfer,
                'total_tips'     => $balance->total_tips,
                'orders_count'   => $balance->orders_count,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(DailyCashBalance $balance): void
    {
        $changes = $balance->getChanges();
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
            'auditable_type' => DailyCashBalance::class,
            'auditable_id'   => $balance->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
