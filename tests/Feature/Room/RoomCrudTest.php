<?php

use App\Enums\Status;
use App\Livewire\Dashboard\Room\CreateRoom;
use App\Livewire\Dashboard\Room\RoomData;
use App\Livewire\Dashboard\Room\UpdateRoom;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create city for HotelFactory
    City::create([
        'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
        'status' => Status::Active,
    ]);

    // Create hotel role for HotelFactory
    Role::create(['name' => 'hotel']);

    // Create permissions
    Permission::create(['name' => 'show_room', 'type' => 'rooms_mng']);
    Permission::create(['name' => 'create_room', 'type' => 'rooms_mng']);
    Permission::create(['name' => 'update_room', 'type' => 'rooms_mng']);
    Permission::create(['name' => 'delete_room', 'type' => 'rooms_mng']);

    // Create role with all permissions
    $role = Role::create(['name' => 'admin']);
    $role->givePermissionTo(['show_room', 'create_room', 'update_room', 'delete_room']);

    $this->user->assignRole('admin');

    $this->hotel = Hotel::factory()->create(['status' => Status::Active]);
});

test('user can view rooms page', function () {
    actingAs($this->user)
        ->get(route('rooms'))
        ->assertSeeLivewire(RoomData::class)
        ->assertSuccessful();
});

test('user can view rooms list', function () {
    $rooms = Room::factory(3)->create(['hotel_id' => $this->hotel->id]);

    Livewire::actingAs($this->user)
        ->test(RoomData::class)
        ->assertSee($rooms->first()->name)
        ->assertSee($rooms->last()->name);
});

test('user can search rooms', function () {
    $room1 = Room::factory()->create([
        'name' => ['ar' => 'غرفة مزدوجة', 'en' => 'Double Room'],
        'hotel_id' => $this->hotel->id,
    ]);

    $room2 = Room::factory()->create([
        'name' => ['ar' => 'غرفة فردية', 'en' => 'Single Room'],
        'hotel_id' => $this->hotel->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(RoomData::class)
        ->set('search', 'Double')
        ->assertSee($room1->name)
        ->assertDontSee($room2->name);
});

test('user can filter rooms by status', function () {
    $activeRoom = Room::factory()->create([
        'status' => Status::Active,
        'hotel_id' => $this->hotel->id,
    ]);

    $inactiveRoom = Room::factory()->create([
        'status' => Status::Inactive,
        'hotel_id' => $this->hotel->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(RoomData::class)
        ->set('status_filter', Status::Active)
        ->assertSee($activeRoom->name)
        ->assertDontSee($inactiveRoom->name);
});

test('user can create a new room', function () {
    $weeklyPrices = [
        'sunday' => ['day_of_week' => 'sunday', 'price_egp' => 500, 'price_usd' => 10],
        'monday' => ['day_of_week' => 'monday', 'price_egp' => 500, 'price_usd' => 10],
        'tuesday' => ['day_of_week' => 'tuesday', 'price_egp' => 500, 'price_usd' => 10],
        'wednesday' => ['day_of_week' => 'wednesday', 'price_egp' => 500, 'price_usd' => 10],
        'thursday' => ['day_of_week' => 'thursday', 'price_egp' => 600, 'price_usd' => 12],
        'friday' => ['day_of_week' => 'friday', 'price_egp' => 700, 'price_usd' => 14],
        'saturday' => ['day_of_week' => 'saturday', 'price_egp' => 700, 'price_usd' => 14],
    ];

    Livewire::actingAs($this->user)
        ->test(CreateRoom::class)
        ->set('name_ar', 'غرفة ديلوكس')
        ->set('name_en', 'Deluxe Room')
        ->set('hotel_id', $this->hotel->id)
        ->set('adults_count', 2)
        ->set('children_count', 1)
        ->set('status', Status::Active->value)
        ->set('includes_ar', 'إفطار مجاني')
        ->set('includes_en', 'Free breakfast')
        ->set('weekly_prices', $weeklyPrices)
        ->call('saveAdd')
        ->assertHasNoErrors();

    expect(Room::where('hotel_id', $this->hotel->id)->count())->toBe(1);

    $room = Room::first();
    expect($room->name)->toBe('Deluxe Room');
    expect($room->adults_count)->toBe(2);
    expect($room->children_count)->toBe(1);
    expect($room->status)->toBe(Status::Active);
});

test('room creation requires name in both languages', function () {
    Livewire::actingAs($this->user)
        ->test(CreateRoom::class)
        ->set('name_ar', '')
        ->set('name_en', 'Deluxe Room')
        ->set('hotel_id', $this->hotel->id)
        ->call('saveAdd')
        ->assertHasErrors(['name_ar']);
});

test('room creation requires valid hotel', function () {
    Livewire::actingAs($this->user)
        ->test(CreateRoom::class)
        ->set('name_ar', 'غرفة ديلوكس')
        ->set('name_en', 'Deluxe Room')
        ->set('hotel_id', 999)
        ->call('saveAdd')
        ->assertHasErrors(['hotel_id']);
});

test('user can update a room', function () {
    $room = Room::factory()->create([
        'hotel_id' => $this->hotel->id,
        'name' => ['ar' => 'غرفة قديمة', 'en' => 'Old Room'],
        'adults_count' => 1,
    ]);

    $weeklyPrices = [
        'sunday' => ['day_of_week' => 'sunday', 'price_egp' => 500, 'price_usd' => 10],
        'monday' => ['day_of_week' => 'monday', 'price_egp' => 500, 'price_usd' => 10],
        'tuesday' => ['day_of_week' => 'tuesday', 'price_egp' => 500, 'price_usd' => 10],
        'wednesday' => ['day_of_week' => 'wednesday', 'price_egp' => 500, 'price_usd' => 10],
        'thursday' => ['day_of_week' => 'thursday', 'price_egp' => 600, 'price_usd' => 12],
        'friday' => ['day_of_week' => 'friday', 'price_egp' => 700, 'price_usd' => 14],
        'saturday' => ['day_of_week' => 'saturday', 'price_egp' => 700, 'price_usd' => 14],
    ];

    Livewire::actingAs($this->user)
        ->test(UpdateRoom::class, ['room' => $room])
        ->set('name_ar', 'غرفة جديدة')
        ->set('name_en', 'New Room')
        ->set('adults_count', 3)
        ->set('weekly_prices', $weeklyPrices)
        ->call('saveUpdate')
        ->assertHasNoErrors();

    $room->refresh();
    expect($room->name)->toBe('New Room');
    expect($room->adults_count)->toBe(3);
});

test('user can delete a room', function () {
    $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);

    Livewire::actingAs($this->user)
        ->test(RoomData::class)
        ->call('delete', ['envelope' => ['options' => ['id' => $room->id]]])
        ->assertHasNoErrors();

    expect(Room::find($room->id))->toBeNull();
});

test('unauthorized user cannot access rooms page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('rooms'))
        ->assertForbidden();
});
