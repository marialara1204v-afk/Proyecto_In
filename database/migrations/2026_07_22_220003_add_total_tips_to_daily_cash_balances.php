<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_cash_balances', function (Blueprint $table) {
            $table->decimal('total_tips', 10, 2)->default(0)->after('total_other');
        });
    }

    public function down(): void
    {
        Schema::table('daily_cash_balances', function (Blueprint $table) {
            $table->dropColumn('total_tips');
        });
    }
};
