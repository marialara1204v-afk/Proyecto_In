<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar el enum para agregar 'entregado'
        Schema::table('tables', function (Blueprint $table) {
            $table->enum('status', ['libre', 'ocupada', 'pedido_listo', 'entregado'])
                  ->default('libre')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->enum('status', ['libre', 'ocupada', 'pedido_listo'])
                  ->default('libre')
                  ->change();
        });
    }
};
