<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => ['ar' => 'واي فاي', 'en' => 'WiFi'], 'icon' => 'o-wifi'],
            ['name' => ['ar' => 'تكييف', 'en' => 'Air Conditioning'], 'icon' => 'o-bolt'],
            ['name' => ['ar' => 'تلفاز', 'en' => 'TV'], 'icon' => 'o-tv'],
            ['name' => ['ar' => 'ثلاجة', 'en' => 'Refrigerator'], 'icon' => 'o-cube'],
            ['name' => ['ar' => 'حمام خاص', 'en' => 'Private Bathroom'], 'icon' => 'o-home'],
            ['name' => ['ar' => 'شرفة', 'en' => 'Balcony'], 'icon' => 'o-building-office'],
            ['name' => ['ar' => 'خدمة الغرف', 'en' => 'Room Service'], 'icon' => 'o-bell-alert'],
            ['name' => ['ar' => 'مجفف شعر', 'en' => 'Hair Dryer'], 'icon' => 'o-fire'],
            ['name' => ['ar' => 'خزنة', 'en' => 'Safe'], 'icon' => 'o-lock-closed'],
            ['name' => ['ar' => 'مكواة', 'en' => 'Iron'], 'icon' => 'o-wrench'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
