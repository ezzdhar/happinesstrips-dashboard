<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Services\FileService;
use Illuminate\Database\Seeder;

class MainCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => [
                    'ar' => 'رحلات سياحية',
                    'en' => 'Tourism Trips',
                ],
                'image' => FileService::fakeImage(name: 'image', folder: 'main_category'),
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'رحلات دينية',
                    'en' => 'Religious Trips',
                ],
                'image' => FileService::fakeImage(name: 'image', folder: 'main_category'),
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'رحلات شاطئية',
                    'en' => 'Beach Trips',
                ],
                'image' => FileService::fakeImage(name: 'image', folder: 'main_category'),
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'رحلات مغامرات',
                    'en' => 'Adventure Trips',
                ],
                'image' => FileService::fakeImage(name: 'image', folder: 'main_category'),
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'رحلات عائلية',
                    'en' => 'Family Trips',
                ],
                'image' => FileService::fakeImage(name: 'image', folder: 'main_category'),
                'status' => Status::Active,
            ],
        ];

        foreach ($categories as $category) {
            MainCategory::create($category);
        }

        // Create additional random categories
        //        MainCategory::factory(2)->create();
    }
}
