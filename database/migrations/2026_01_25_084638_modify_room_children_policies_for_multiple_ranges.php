<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * تعديل الجدول للسماح بنطاقات عمر متعددة لنفس الطفل
     */
    public function up(): void
    {
        // التحقق من الـ constraints الموجودة
        $foreignKeys = $this->getForeignKeys('room_children_policies');
        $indexes = $this->getIndexes('room_children_policies');

        Schema::table('room_children_policies', function (Blueprint $table) use ($foreignKeys, $indexes) {
            // حذف الـ foreign key إذا كان موجوداً
            foreach ($foreignKeys as $fk) {
                if (str_contains($fk, 'room_id')) {
                    try {
                        $table->dropForeign([$fk]);
                    } catch (\Exception $e) {
                        // تجاهل الخطأ إذا لم يكن موجوداً
                    }
                }
            }

            // حذف الـ unique constraint إذا كان موجوداً
            if (in_array('room_children_policies_room_id_child_number_unique', $indexes)) {
                try {
                    $table->dropUnique(['room_id', 'child_number']);
                } catch (\Exception $e) {
                    // تجاهل
                }
            }
        });

        Schema::table('room_children_policies', function (Blueprint $table) {
            // إضافة unique constraint جديد: room_id + child_number + from_age
            try {
                $table->unique(['room_id', 'child_number', 'from_age'], 'room_child_age_unique');
            } catch (\Exception $e) {
                // تجاهل إذا كان موجوداً
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_children_policies', function (Blueprint $table) {
            try {
                $table->dropUnique('room_child_age_unique');
            } catch (\Exception $e) {
                // تجاهل
            }
        });
    }

    private function getForeignKeys(string $table): array
    {
        $dbName = config('database.connections.mysql.database');
        $keys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$dbName, $table]);

        return array_map(fn($k) => $k->CONSTRAINT_NAME, $keys);
    }

    private function getIndexes(string $table): array
    {
        $dbName = config('database.connections.mysql.database');
        $indexes = DB::select("
            SELECT INDEX_NAME 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ?
            GROUP BY INDEX_NAME
        ", [$dbName, $table]);

        return array_map(fn($i) => $i->INDEX_NAME, $indexes);
    }
};
