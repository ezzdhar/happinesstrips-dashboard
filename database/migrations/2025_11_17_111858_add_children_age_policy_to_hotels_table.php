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
            $table->unsignedTinyInteger('free_child_age')->default(4)->after('additional_child_price_percentage')->comment('Age under which children are free');
            $table->unsignedTinyInteger('adult_age')->default(12)->after('free_child_age')->comment('Age at which a child is considered an adult');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['free_child_age', 'adult_age']);
        });
    }
};
