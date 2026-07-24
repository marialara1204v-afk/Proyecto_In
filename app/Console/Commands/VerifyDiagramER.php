<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyDiagramER extends Command
{
    protected $signature = 'verify:diagram-er';
    protected $description = 'Verifica que el proyecto cumpla con el diagrama Entidad-Relación';

    public function handle()
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('VERIFICACIÓN: DIAGRAMA ENTIDAD-RELACIÓN');
        $this->line('═══════════════════════════════════════════════════════════');

        $checks = [];

        // 1. Verificar tabla ROLES
        $this->line('');
        $this->info('1 - TABLA ROLES');
        try {
            $rolesCount = DB::table('roles')->count();
            $roles = DB::table('roles')->pluck('name')->toArray();
            $this->line("   ✓ Tabla roles existe");
            $this->line("   ✓ Total de roles: {$rolesCount}");
            $this->line("   ✓ Roles: " . implode(', ', $roles));
            $checks[] = true;
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $checks[] = false;
        }

        // 2. Verificar FK users.role_id
        $this->line('');
        $this->info('2 - TABLA USERS - Relación con ROLES');
        try {
            $usersWithRole = DB::table('users')->whereNotNull('role_id')->count();
            $totalUsers = DB::table('users')->count();
            $this->line("   ✓ Columna role_id existe");
            $this->line("   ✓ Usuarios con role_id: {$usersWithRole}/{$totalUsers}");
            $checks[] = true;
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $checks[] = false;
        }

        // 3. Verificar FK inventory_items.product_id
        $this->line('');
        $this->info('3 - TABLA INVENTORY_ITEMS - Relación con PRODUCTS');
        try {
            $itemsWithProduct = DB::table('inventory_items')->whereNotNull('product_id')->count();
            $totalItems = DB::table('inventory_items')->count();
            $this->line("   ✓ Columna product_id existe");
            $this->line("   ✓ Items con product_id: {$itemsWithProduct}/{$totalItems}");
            $checks[] = true;
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $checks[] = false;
        }

        // 4. Verificar relaciones en Modelos
        $this->line('');
        $this->info('4 - MODELOS - Relaciones');
        try {
            $roleModel = class_exists(\App\Models\Role::class);
            $this->line("   ✓ Modelo Role existe");
            $checks[] = $roleModel;
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $checks[] = false;
        }

        // 5. Verificar estructura de tablas principales
        $this->line('');
        $this->info('5 - ESTRUCTURA DE TABLAS');
        $tables = [
            'roles' => ['id', 'name', 'description'],
            'users' => ['id', 'name', 'email', 'role', 'role_id'],
            'categories' => ['id', 'name'],
            'products' => ['id', 'category_id', 'name', 'price'],
            'tables' => ['id', 'number', 'status'],
            'orders' => ['id', 'table_id', 'user_id', 'status', 'total'],
            'order_items' => ['id', 'order_id', 'product_id', 'quantity', 'unit_price'],
            'inventory_items' => ['id', 'product_id', 'name', 'stock'],
        ];

        foreach ($tables as $table => $expectedColumns) {
            try {
                $columns = DB::getSchemaBuilder()->getColumnListing($table);
                $missingColumns = array_diff($expectedColumns, $columns);

                if (empty($missingColumns)) {
                    $this->line("   ✓ {$table}");
                    $checks[] = true;
                } else {
                    $this->warn("   ⚠ {$table} - Faltan: " . implode(', ', $missingColumns));
                    $checks[] = false;
                }
            } catch (\Exception $e) {
                $this->error("   ✗ {$table} - No existe");
                $checks[] = false;
            }
        }

        // Resumen
        $this->line('');
        $this->line('═══════════════════════════════════════════════════════════');
        $totalChecks = count($checks);
        $passedChecks = array_sum($checks);

        if ($passedChecks === $totalChecks) {
            $this->info(\"> DIAGRAMA ER COMPLETAMENTE IMPLEMENTADO ({$passedChecks}/{$totalChecks})\");
        } else {
            $this->warn(\"  {$passedChecks}/{$totalChecks} verificaciones pasadas\");
        }

        $this->line('');
    }
}
