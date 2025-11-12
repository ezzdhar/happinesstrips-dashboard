<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_travelers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone_key')->nullable();
            $table->string('phone');
            $table->string('nationality');
            $table->integer('age');
            $table->enum('id_type', ['passport', 'national_id'])->default('passport');
            $table->string('id_number'); // رقم الهوية أو الجواز
            $table->enum('type', ['adult', 'child'])->default('adult');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_travelers');
    }
};
