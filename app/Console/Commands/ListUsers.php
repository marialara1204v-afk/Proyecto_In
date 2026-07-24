<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'Lista todos los usuarios del sistema con sus roles';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('USUARIOS DEL SISTEMA');
        $this->line('═══════════════════════════════════════════════════════════');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('No hay usuarios registrados');
            return;
        }

        foreach ($users as $user) {
            $icon = match($user->role) {
                'admin' => '👨‍💼',
                'mesero' => '👨‍🍳',
                'cocina' => '🍳',
                'bartender' => '🍹',
                default => '❓'
            };

            $this->line('');
            $this->info("{$icon} {$user->name}");
            $this->line("   ID: {$user->id}");
            $this->line("   Email: {$user->email}");
            $this->line("   Rol: {$user->role}");
            $this->line("   Creado: {$user->created_at}");
        }

        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
    }
}
