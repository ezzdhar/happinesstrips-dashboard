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
        // إضافة عمود children_policy لجدول rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('children_policy')->nullable()->after('includes')
                ->comment('سياسة تسعير الأطفال: child_1, child_2, child_3, additional_child, adult_age');
        });

        // حذف أعمدة سياسة الأطفال من جدول hotels
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'first_child_price_percentage',
                'second_child_price_percentage',
                'third_child_price_percentage',
                'additional_child_price_percentage',
                'free_child_age',
                'adult_age',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة أعمدة سياسة الأطفال لجدول hotels
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('first_child_price_percentage', 5, 2)->default(50)->after('facilities');
            $table->decimal('second_child_price_percentage', 5, 2)->default(50)->after('first_child_price_percentage');
            $table->decimal('third_child_price_percentage', 5, 2)->default(50)->after('second_child_price_percentage');
            $table->decimal('additional_child_price_percentage', 5, 2)->default(100)->after('third_child_price_percentage');
            $table->unsignedTinyInteger('free_child_age')->default(4)->after('additional_child_price_percentage')
                ->comment('Age under which children are free');
            $table->unsignedTinyInteger('adult_age')->default(12)->after('free_child_age')
                ->comment('Age at which a child is considered an adult');
        });

        // حذف عمود children_policy من جدول rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('children_policy');
        });
    }
};
