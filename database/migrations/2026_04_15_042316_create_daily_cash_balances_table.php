<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_cash_balances', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // fecha del cuadre
            $table->decimal('total_sales', 12, 2)->default(0); // total de vendido
            $table->decimal('total_cash', 10, 2)->default(0); // total en efectivo
            $table->decimal('total_card', 10, 2)->default(0); // total en tarjeta
            $table->decimal('total_transfer', 10, 2)->default(0); // total en transferencia
            $table->decimal('total_other', 10, 2)->default(0); // total en otras formas
            $table->decimal('total_payments', 12, 2)->default(0); // total cobrado
            $table->integer('orders_count')->default(0); // cantidad de órdenes
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_balances');
    }
};
