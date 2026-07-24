<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // mesero
            $table->enum('status', [
                'pendiente',
                'preparando',
                'listo',
                'entregado',
                'pagado',
            ])->default('pendiente');
            $table->decimal('total', 10, 2)->unsigned()->default(0);
            $table->text('notes')->nullable(); // notas generales del pedido
            $table->timestamp('closed_at')->nullable(); // cuando se paga
            $table->timestamps();

            // Índices para las vistas más consultadas
            $table->index(['status', 'created_at']);
            $table->index(['table_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};