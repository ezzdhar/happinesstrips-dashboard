<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(public_path('files_json/Egypt Cities.json'));
        $dataArray = json_decode($json, true);
        foreach ($dataArray as $index => $data) {
            City::create([
                'name' => [
                    'ar' => $data['arabic'],
                    'en' => $data['english'],
                    //                    'zh' => $data['chinese'],
                ],
                'code' => 'eg',
            ]);
        }
    }
}
