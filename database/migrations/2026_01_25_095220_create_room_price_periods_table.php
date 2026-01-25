<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إنشاء جدول فترات أسعار الغرف
     * يخزن فترات الأسعار بشكل منفصل لكل عملة (EGP / USD)
     */
    public function up(): void
    {
        Schema::create('room_price_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('currency', ['egp', 'usd'])->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // فهرس مركب للبحث السريع
            $table->index(['room_id', 'currency', 'start_date', 'end_date'], 'room_currency_dates');
        });

        // إزالة عمود price_periods من جدول rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('price_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('price_periods')->nullable();
        });

        Schema::dropIfExists('room_price_periods');
    }
};
