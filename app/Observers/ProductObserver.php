<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => Product::class,
            'auditable_id'   => $product->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'name'         => $product->name,
                'category_id'  => $product->category_id,
                'price'        => $product->price,
                'is_available' => $product->is_available,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
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
            'auditable_type' => Product::class,
            'auditable_id'   => $product->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function deleted(Product $product): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => Product::class,
            'auditable_id'   => $product->id,
            'event'          => 'deleted',
            'old_values'     => $product->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
