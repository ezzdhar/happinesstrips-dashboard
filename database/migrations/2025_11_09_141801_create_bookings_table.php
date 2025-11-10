<?php

use App\Enums\BookingStatus;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();

            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('nights_count')->default(1);

            $table->integer('adults_count')->default(1);
            $table->integer('children_count')->default(0);

            $table->decimal('price',2);
            $table->decimal('total_price',2);
	        $table->string('currency')->default('egp');;

            $table->text('notes')->nullable();
            $table->enum('status', [Status::Pending, Status::UnderPayment, Status::UnderCancellation, Status::Cancelled, Status::Completed])->default(Status::Pending);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

