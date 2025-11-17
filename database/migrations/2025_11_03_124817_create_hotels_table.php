<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->decimal('first_child_price_percentage', 50, 2)->default(50)->after('facilities');
            $table->decimal('second_child_price_percentage', 5, 2)->default(50)->after('first_child_price_percentage');
            $table->decimal('third_child_price_percentage', 5, 2)->default(50)->after('second_child_price_percentage');
            $table->decimal('additional_child_price_percentage', 5, 2)->default(100)->after('third_child_price_percentage');
            $table->unsignedTinyInteger('free_child_age')->default(4)->after('additional_child_price_percentage')->comment('Age under which children are free');
            $table->unsignedTinyInteger('adult_age')->default(12)->after('free_child_age')->comment('Age at which a child is considered an adult');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
