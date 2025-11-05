<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\File;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Create additional random rooms
        Room::factory()
            ->count(30)
            ->create()
            ->each(function ($room) {
                File::factory()
                    ->count(rand(2, 5))
                    ->image()
                    ->create([
                        'fileable_id' => $room->id,
                        'fileable_type' => Room::class,
                    ]);
            });
    }
}

