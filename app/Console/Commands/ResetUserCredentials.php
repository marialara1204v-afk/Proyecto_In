<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserCredentials extends Command
{
    protected $signature = 'app:reset-credentials';
    protected $description = 'Restaurar credenciales originales del sistema';

    public function handle()
    {
        $this->info('🔄 RESTAURANDO CREDENCIALES ORIGINALES\n');

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@lapatrona.com',
                'role' => 'admin',
                'password' => 'password',
            ],
            [
                'name' => 'Mesero',
                'email' => 'mesero@lapatrona.com',
                'role' => 'mesero',
                'password' => 'password',
            ],
            [
                'name' => 'Mesero 2',
                'email' => 'mesero2@lapatrona.com',
                'role' => 'mesero',
                'password' => 'password',
            ],
            [
                'name' => 'Barra',
                'email' => 'barra@lapatrona.com',
                'role' => 'bartender',
                'password' => 'password',
            ],
            [
                'name' => 'Cocina',
                'email' => 'cocina@lapatrona.com',
                'role' => 'cocina',
                'password' => 'password',
            ],
        ];

        // Obtener usuario admin existente y actualizar
        $adminUser = User::whereEmail('admin@lapatrona.com')->first();
        if ($adminUser) {
            $adminUser->update([
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
            $this->line("✓ Admin actualizado");
        } else {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@lapatrona.com',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]);
            $this->line("✓ Admin creado");
        }

        // Crear o actualizar otros usuarios
        foreach (array_slice($users, 1) as $userData) {
            $user = User::whereEmail($userData['email'])->first();
            
            if ($user) {
                $user->update([
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'password' => bcrypt($userData['password']),
                ]);
                $this->line("✓ {$userData['name']} actualizado");
            } else {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'password' => bcrypt($userData['password']),
                ]);
                $this->line("✓ {$userData['name']} creado");
            }
        }

        $this->info("\n✅ Credenciales restauradas correctamente!\n");
        $this->info('📝 CREDENCIALES (Todas con contraseña: password):');
        $this->line('  • admin@lapatrona.com (Admin)');
        $this->line('  • mesero@lapatrona.com (Mesero)');
        $this->line('  • mesero2@lapatrona.com (Mesero 2)');
        $this->line('  • barra@lapatrona.com (Barman/Bartender)');
        $this->line('  • cocina@lapatrona.com (Cocina)');
        
        return 0;
    }
}
