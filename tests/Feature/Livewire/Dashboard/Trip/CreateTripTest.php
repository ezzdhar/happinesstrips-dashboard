<?php

use App\Livewire\Dashboard\Trip\CreateTrip;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateTrip::class)
        ->assertStatus(200);
});
