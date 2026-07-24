<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'event'          => 'created',
            'old_values'     => null,
            'new_values'     => [
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'is_active' => $user->is_active,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']);

        // No auditar cambios de password hasheado
        unset($changes['password']);

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
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'event'          => 'updated',
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function deleted(User $user): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'event'          => 'deleted',
            'old_values'     => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function restored(User $user): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'user_name'      => auth()->user()->name ?? 'Sistema',
            'user_role'      => auth()->user()->role ?? 'N/A',
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'event'          => 'updated',
            'old_values'     => ['deleted_at' => null],
            'new_values'     => ['deleted_at' => $user->deleted_at],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
