<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UserManager extends Component
{
    public bool   $showForm    = false;
    public ?int   $editingId   = null;

    #[Validate('required|string|max:100')]
    public string $name        = '';

    #[Validate('required|email')]
    public string $email       = '';

    #[Validate('nullable|min:8')]
    public string $password    = '';

    #[Validate('nullable|min:8')]
    public string $password_confirmation = '';

    #[Validate('required|in:admin,mesero,cocina,bartender')]
    public string $role        = 'mesero';

    #[Computed]
    public function users()
    {
        return User::orderBy('role')->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'editingId']);
        $this->role     = 'mesero';
        $this->showForm = true;
    }

    public function openEdit(int $userId): void
    {
        $user            = User::findOrFail($userId);
        $this->editingId = $userId;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->role;
        $this->password  = '';
        $this->password_confirmation = '';
        $this->showForm  = true;
    }

    public function save(): void
    {
        $rules = [
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email' . ($this->editingId ? ",{$this->editingId}" : ''),
            'role'  => 'required|in:admin,mesero,cocina,bartender',
        ];

        if (! $this->editingId || $this->password) {
            $rules['password'] = $this->editingId ? 'nullable|min:8' : 'required|min:8';
            $rules['password_confirmation'] = 'required_with:password|same:password';
        }

        $this->validate($rules);

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Usuario actualizado.');
        } else {
            User::create($data);
            session()->flash('success', 'Usuario creado.');
        }

        $this->showForm = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'editingId']);
        unset($this->users);
    }

    public function toggleActive(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'No puedes desactivar tu propio usuario.');
            return;
        }

        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);

        unset($this->users);
        session()->flash('success', $user->is_active ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function render()
    {
        return view('livewire.admin.user-manager')
            ->layout('layouts.app', ['title' => 'Usuarios']);
    }
}