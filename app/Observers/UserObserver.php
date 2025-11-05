<?php

namespace App\Observers;

use App\Models\User;
use App\Services\FileService;

class UserObserver
{
    public function created(User $user): void
    {
        $user->update([
            //            'username' => rand(111111111, 9999999999),
            'image' => FileService::fakeImage(name: $user->name, shape: 'circle', folder: 'users'),
        ]);
    }
}
