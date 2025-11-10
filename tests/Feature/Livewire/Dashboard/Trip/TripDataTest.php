<?php

use App\Livewire\Dashboard\Trip\TripData;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(TripData::class)
        ->assertStatus(200);
});
