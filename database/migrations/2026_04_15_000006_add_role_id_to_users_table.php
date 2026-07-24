<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar columna role_id como FK (sin eliminar la columna role enum existente)
            $table->foreignId('role_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('roles')
                  ->restrictOnDelete();
        });

        // Sincronizar valores: mapear enum role a role_id en roles
        $roleMapping = [
            'admin' => 1,
            'mesero' => 2,
            'cocina' => 3,
            'bartender' => 4,
        ];

        foreach ($roleMapping as $roleName => $roleId) {
            DB::table('users')
                ->where('role', $roleName)
                ->update(['role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor('roles');
            $table->dropColumn('role_id');
        });
    }
};
