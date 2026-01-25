<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * تعديل الجدول للسماح بنطاقات عمر متعددة لنفس الطفل
     */
    public function up(): void
    {
        Schema::table('room_children_policies', function (Blueprint $table) {
            // حذف الـ foreign key أولاً
            $table->dropForeign(['room_id']);

            // حذف الـ unique constraint
            $table->dropUnique(['room_id', 'child_number']);

            // إعادة إضافة الـ foreign key
            $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();

            // إضافة unique constraint جديد: room_id + child_number + from_age
            $table->unique(['room_id', 'child_number', 'from_age'], 'room_child_age_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_children_policies', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropUnique('room_child_age_unique');

            $table->unique(['room_id', 'child_number']);
            $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
        });
    }
};
