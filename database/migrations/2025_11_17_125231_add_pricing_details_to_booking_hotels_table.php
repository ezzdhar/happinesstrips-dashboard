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
        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->decimal('adults_price', 10, 2)->default(0)->after('room_includes');
            $table->decimal('children_price', 10, 2)->default(0)->after('adults_price');
            $table->json('children_breakdown')->nullable()->after('children_price');
            $table->json('pricing_details')->nullable()->after('children_breakdown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->dropColumn(['adults_price', 'children_price', 'children_breakdown', 'pricing_details']);
        });
    }
};
