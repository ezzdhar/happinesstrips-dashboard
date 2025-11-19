<?php

use App\Enums\Status;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->json('name');
            $table->integer('adults_count')->default(1);
            $table->integer('children_count')->default(0);
            $table->json('price_periods')->nullable()->comment('Array of price periods with start_date, end_date, adult_price_egp, adult_price_usd');
            $table->json('includes')->nullable();
	        $table->boolean('is_featured')->default(false);
            $table->enum('status', [Status::Active, Status::Inactive])->default(Status::Active);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
