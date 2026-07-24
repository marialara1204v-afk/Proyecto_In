<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insertar roles existentes
        DB::table('roles')->insert([
            ['name' => 'admin', 'description' => 'Administrador del sistema', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'mesero', 'description' => 'Personal de meseros', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cocina', 'description' => 'Personal de cocina', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'bartender', 'description' => 'Personal de barra', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
