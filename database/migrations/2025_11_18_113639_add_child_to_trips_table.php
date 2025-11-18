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
        Schema::table('trips', function (Blueprint $table) {
	        $table->decimal('first_child_price_percentage', 50, 2)->default(50);
	        $table->decimal('second_child_price_percentage', 5, 2)->default(50);
	        $table->decimal('third_child_price_percentage', 5, 2)->default(50);
	        $table->decimal('additional_child_price_percentage', 5, 2)->default(100);
	        $table->unsignedTinyInteger('free_child_age')->default(4)->comment('Age under which children are free');
	        $table->unsignedTinyInteger('adult_age')->default(12)->comment('Age at which a child is considered an adult');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            //
        });
    }
};
