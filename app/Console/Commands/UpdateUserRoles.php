<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserRoles extends Command
{
    protected $signature = 'app:update-user-roles';
    protected $description = 'Actualizar roles de usuarios del sistema';

    public function handle()
    {
        $this->info('🔄 ACTUALIZANDO ROLES DE USUARIOS\n');

        $users = [
            [
                'name' => 'Cesar',
                'email' => 'cesar@restaurante.local',
                'role' => 'admin',
                'password' => '123456',
            ],
            [
                'name' => 'Francisco',
                'email' => 'francisco@restaurante.local',
                'role' => 'bartender',
                'password' => '123456',
            ],
            [
                'name' => 'Talia',
                'email' => 'talia@restaurante.local',
                'role' => 'mesera',
                'password' => '123456',
            ],
            [
                'name' => 'Alejandro',
                'email' => 'alejandro@restaurante.local',
                'role' => 'mesero',
                'password' => '123456',
            ],
        ];

        foreach ($users as $userData) {
            // Buscar o crear usuario
            $user = User::where('email', $userData['email'])->first();

            if ($user) {
                // Actualizar
                $user->update([
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                ]);
                $this->line("✓ Actualizado: {$userData['name']} ({$userData['role']})");
            } else {
                // Crear
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'password' => bcrypt($userData['password']),
                ]);
                $this->line("✓ Creado: {$userData['name']} ({$userData['role']})");
            }
        }

        $this->info("\n✅ Usuarios actualizado correctamente!\n");
        $this->info('📝 CREDENCIALES:');
        $this->line('  • Cesar (Admin)       - cesar@restaurante.local / 123456');
        $this->line('  • Francisco (Barman)   - francisco@restaurante.local / 123456');
        $this->line('  • Talia (Mesera)       - talia@restaurante.local / 123456');
        $this->line('  • Alejandro (Mesero)   - alejandro@restaurante.local / 123456');
        
        return 0;
    }
}
