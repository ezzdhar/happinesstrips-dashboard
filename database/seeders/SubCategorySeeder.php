<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Services\FileService;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
	public function run(): void
	{
		$mainCategories = MainCategory::all();

		if ($mainCategories->isEmpty()) {
			$this->command->error('No main categories found. Please run MainCategorySeeder first.');
			return;
		}

		$subCategories = [
			[
				'name' => [
					'ar' => 'رحلات داخلية',
					'en' => 'Domestic Trips',
				],
				'image' => FileService::fakeImage(name: 'image', folder:'sub_category'),
				'status' => Status::Active,
			],
			[
				'name' => [
					'ar' => 'رحلات خارجية',
					'en' => 'International Trips',
				],
				'image' => FileService::fakeImage(name: 'image', folder:'sub_category'),
				'status' => Status::Active,
			],
			[
				'name' => [
					'ar' => 'رحلات قصيرة',
					'en' => 'Short Trips',
				],
				'image' => FileService::fakeImage(name: 'image', folder:'sub_category'),
				'status' => Status::Active,
			],
			[
				'name' => [
					'ar' => 'رحلات طويلة',
					'en' => 'Long Trips',
				],
				'image' => FileService::fakeImage(name: 'image', folder:'sub_category'),
				'status' => Status::Active,
			],
		];

		foreach ($mainCategories->take(5) as $mainCategory) {
			foreach ($subCategories as $subCategory) {
				SubCategory::create([
					'main_category_id' => $mainCategory->id,
					'name' => $subCategory['name'],
					'status' => $subCategory['status'],
				]);
			}
		}

		// Create additional random sub categories
		SubCategory::factory()->count(2)->create();
	}
}

