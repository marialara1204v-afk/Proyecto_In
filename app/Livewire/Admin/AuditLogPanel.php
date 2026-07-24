<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AuditLogPanel extends Component
{
    public string $filterTable = '';
    public string $filterEvent = '';
    public string $filterUser = '';
    public int    $perPage = 25;

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->when($this->filterTable, fn ($q) => $q->where('auditable_type', 'App\\Models\\' . $this->filterTable))
            ->when($this->filterEvent, fn ($q) => $q->where('event', $this->filterEvent))
            ->when($this->filterUser, fn ($q) => $q->where('user_name', 'like', "%{$this->filterUser}%"))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function userOptions(): array
    {
        return AuditLog::distinct()
            ->whereNotNull('user_name')
            ->pluck('user_name')
            ->sort()
            ->values()
            ->toArray();
    }

    public function clearFilters(): void
    {
        $this->filterTable = '';
        $this->filterEvent = '';
        $this->filterUser  = '';
        unset($this->logs);
    }

    public function render()
    {
        return view('livewire.admin.audit-log-panel')
            ->layout('layouts.app', ['title' => 'Auditoría']);
    }
}
