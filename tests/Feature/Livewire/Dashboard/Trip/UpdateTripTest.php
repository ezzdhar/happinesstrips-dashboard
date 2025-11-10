<?php

use App\Livewire\Dashboard\Trip\UpdateTrip;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UpdateTrip::class)
        ->assertStatus(200);
});
