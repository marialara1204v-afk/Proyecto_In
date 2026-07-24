<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mapear datos existentes
        DB::table('inventory_items')->where('category', 'contable')->update(['category' => 'comida']);
        DB::table('inventory_items')->where('category', 'incontable')->update(['category' => 'comida']);

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropColumn('category');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('category', 30)->default('comida')->after('product_id');
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropColumn('category');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('category', ['contable', 'incontable'])->default('contable')->after('product_id');
            $table->index(['category', 'is_active']);
        });
    }
};
