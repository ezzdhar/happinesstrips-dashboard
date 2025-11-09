<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->json('room_price')->nullable(); // {"egp": 0, "usd": 0}
            $table->integer('rooms_count')->default(1); // عدد الغرف من نفس النوع
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_hotels');
    }
};

