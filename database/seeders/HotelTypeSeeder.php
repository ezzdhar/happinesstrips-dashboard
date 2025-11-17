<?php

namespace Database\Seeders;

use App\Models\HotelType;
use Illuminate\Database\Seeder;

class HotelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotelTypes = [
            ['name' => ['ar' => 'فندق فاخر', 'en' => 'Luxury Hotel']],
            ['name' => ['ar' => 'فندق بوتيك', 'en' => 'Boutique Hotel']],
            ['name' => ['ar' => 'منتجع', 'en' => 'Resort']],
            ['name' => ['ar' => 'فندق اقتصادي', 'en' => 'Budget Hotel']],
            ['name' => ['ar' => 'فندق أعمال', 'en' => 'Business Hotel']],
            ['name' => ['ar' => 'فندق شقق', 'en' => 'Aparthotel']],
            ['name' => ['ar' => 'نزل', 'en' => 'Inn']],
            ['name' => ['ar' => 'موتيل', 'en' => 'Motel']],
        ];

        foreach ($hotelTypes as $type) {
            HotelType::create($type);
        }
    }
}
