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
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('weekly_prices');
            $table->json('price_periods')->nullable()->comment('Array of price periods with start_date, end_date, adult_price_egp, adult_price_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('price_periods');
            $table->json('weekly_prices')->nullable();
        });
    }
};
