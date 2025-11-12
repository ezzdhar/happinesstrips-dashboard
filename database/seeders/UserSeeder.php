<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'super admin',
            'email' => 'superadmin@admin.com',
            'phone_key' => '+20',
            'phone' => '01000000000',
            'password' => 12345678,
            'image' => FileService::fakeImage(name: 'users', folder: 'users'),
            'email_verified_at' => now(),
        ])->assignRole('admin')->givePermissionTo(Permission::all()->pluck('name')->toArray());

        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('hotel')->givePermissionTo(Permission::role('hotel')->get()->pluck('name')->toArray());
        });

        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

    }
}
