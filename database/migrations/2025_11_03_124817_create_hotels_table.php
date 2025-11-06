<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('hotels', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
			$table->string('email');
			$table->json('name');
			$table->enum('status', [Status::Active, Status::Inactive])->default(Status::Active);
			$table->enum('rating', [1, 2, 3, 4, 5])->default(3);
			$table->string('phone_key')->nullable();
			$table->string('phone')->nullable();
			$table->string('latitude')->nullable();
			$table->string('longitude')->nullable();
			$table->json('description')->nullable();
			$table->json('address');
			$table->json('facilities');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('hotels');
	}
};
