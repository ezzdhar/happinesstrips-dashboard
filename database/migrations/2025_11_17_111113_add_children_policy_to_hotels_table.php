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
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('first_child_price_percentage', 50, 2)->default(50)->after('facilities');
            $table->decimal('second_child_price_percentage', 5, 2)->default(50)->after('first_child_price_percentage');
            $table->decimal('third_child_price_percentage', 5, 2)->default(50)->after('second_child_price_percentage');
            $table->decimal('additional_child_price_percentage', 5, 2)->default(100)->after('third_child_price_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'first_child_price_percentage',
                'second_child_price_percentage',
                'third_child_price_percentage',
                'additional_child_price_percentage',
            ]);
        });
    }
};
