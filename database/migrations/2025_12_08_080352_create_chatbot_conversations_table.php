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
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->text('user_message');
            $table->text('bot_response');
            $table->json('api_calls')->nullable();
            $table->json('api_results')->nullable();
            $table->json('suggested_actions')->nullable();
            $table->string('intent')->nullable(); // نوع السؤال: hotel_search, trip_search, price_inquiry, etc.
            $table->boolean('was_helpful')->nullable(); // هل كانت الإجابة مفيدة
            $table->text('feedback')->nullable(); // ملاحظات المستخدم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};

