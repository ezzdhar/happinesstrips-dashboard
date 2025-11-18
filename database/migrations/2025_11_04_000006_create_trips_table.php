<?php

use App\Enums\Status;
use App\Enums\TripType;
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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_category_id')->constrained('main_categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->constrained('sub_categories')->cascadeOnDelete();
            $table->json('name');
            $table->json('price'); // {"egp": 0, "usd": 0}
            $table->date('duration_from')->nullable();
            $table->date('duration_to')->nullable();
            $table->integer('nights_count')->nullable();
            $table->integer('people_count')->default(1);
            $table->json('notes')->nullable();
            $table->json('program')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('type', [TripType::Fixed, TripType::Flexible])->default(TripType::Fixed);
            $table->enum('status', [Status::Active, Status::Inactive, Status::End, Status::Start])->default(Status::Active);
	        $table->decimal('first_child_price_percentage', 50, 2)->default(50);
	        $table->decimal('second_child_price_percentage', 5, 2)->default(50);
	        $table->decimal('third_child_price_percentage', 5, 2)->default(50);
	        $table->decimal('additional_child_price_percentage', 5, 2)->default(100);
	        $table->unsignedTinyInteger('free_child_age')->default(4)->comment('Age under which children are free');
	        $table->unsignedTinyInteger('adult_age')->default(12)->comment('Age at which a child is considered an adult');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
