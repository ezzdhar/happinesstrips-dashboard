<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
	    $this->call(RolePermissionSeeder::class);
	    $this->call(UserSeeder::class);
		$this->call(CitySeeder::class);

		// Travel System Seeders
		$this->call(MainCategorySeeder::class);
		$this->call(SubCategorySeeder::class);
		$this->call(HotelSeeder::class);
		$this->call(RoomSeeder::class);
		$this->call(TripSeeder::class);
    }
}
