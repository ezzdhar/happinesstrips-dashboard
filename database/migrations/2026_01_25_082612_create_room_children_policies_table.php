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
        // إنشاء جدول سياسات الأطفال للغرف
        Schema::create('room_children_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedTinyInteger('child_number')->comment('ترتيب الطفل: 1, 2, 3, إلخ');
            $table->unsignedTinyInteger('from_age')->default(0);
            $table->unsignedTinyInteger('to_age')->default(11);
            $table->decimal('price_percentage', 5, 2)->default(0)->comment('نسبة من سعر البالغ');
            $table->timestamps();

            // يضمن عدم تكرار نفس ترتيب الطفل لنفس الغرفة
            $table->unique(['room_id', 'child_number']);
        });

        // حذف عمود children_policy من جدول rooms إذا كان موجوداً
        if (Schema::hasColumn('rooms', 'children_policy')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('children_policy');
            });
        }

        // إضافة عمود adult_age للغرف
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedTinyInteger('adult_age')->default(12)->after('children_count')
                ->comment('العمر الذي يُعتبر بعده بالغاً');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف عمود adult_age من الغرف
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('adult_age');
        });

        // إعادة عمود children_policy
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('children_policy')->nullable()->after('includes');
        });

        // حذف جدول سياسات الأطفال
        Schema::dropIfExists('room_children_policies');
    }
};
