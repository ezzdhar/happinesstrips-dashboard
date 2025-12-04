<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('booking_ratings', function (Blueprint $table) {
			$table->id();
			$table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->unsignedTinyInteger('rating'); // من 1 لـ 5
			$table->timestamps();
			$table->unique(['booking_id', 'user_id']); // نفس اليوزر يقيم نفس الحجز مرة واحدة
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('booking_ratings');
	}
};
