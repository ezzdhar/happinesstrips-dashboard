<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Room\UpdateRoom;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomChildPolicy;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('prevents duplicate children policy entries on update form resubmission', function () {
    $hotel = Hotel::factory()->create();
    $room = Room::factory()->create([
        'hotel_id' => $hotel->id,
        'name' => ['ar' => 'غرفة موجودة', 'en' => 'Existing Room'],
        'adults_count' => 2,
        'children_count' => 1,
        'adult_age' => 12,
    ]);

    // إضافة سياسة طفل موجودة
    RoomChildPolicy::create([
        'room_id' => $room->id,
        'child_number' => 1,
        'from_age' => 0,
        'to_age' => 11,
        'price_percentage' => 25,
    ]);

    $component = Livewire::test(UpdateRoom::class, ['room' => $room])
        ->set('name_ar', 'غرفة محدثة')
        ->set('name_en', 'Updated Room')
        ->set('children_count', 1)
        ->set('price_periods_egp', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 150],
        ])
        ->set('price_periods_usd', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 15],
        ])
        ->set('children_policy', [
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 11, 'price_percentage' => 75],
                ],
            ],
        ]);

    // محاولة الحفظ مرتين (double-click simulation)
    $component->call('saveUpdate');

    expect($component->isProcessing)->toBeFalse();

    // تأكد من تحديث الغرفة
    $room->refresh();
    expect($room->getTranslation('name', 'ar'))->toBe('غرفة محدثة');

    // تأكد من وجود سياسة طفل واحدة فقط بالقيم الجديدة
    expect($room->childrenPolicies)->toHaveCount(1);
    expect($room->childrenPolicies->first())
        ->child_number->toBe(1)
        ->from_age->toBe(0)
        ->to_age->toBe(11)
        ->price_percentage->toBe(75.0);

    // محاولة الحفظ مرة أخرى يجب أن يتم منعها
    $component->call('saveUpdate');
    expect($component->isProcessing)->toBeFalse();

    // تأكد من عدم إنشاء سجلات مكررة
    expect($room->childrenPolicies)->toHaveCount(1);
});

it('correctly updates children policies with multiple ranges', function () {
    $hotel = Hotel::factory()->create();
    $room = Room::factory()->create([
        'hotel_id' => $hotel->id,
        'name' => ['ar' => 'غرفة للتحديث', 'en' => 'Room to Update'],
        'adults_count' => 2,
        'children_count' => 2,
        'adult_age' => 12,
    ]);

    // إضافة سياسات موجودة
    RoomChildPolicy::create([
        'room_id' => $room->id,
        'child_number' => 1,
        'from_age' => 0,
        'to_age' => 11,
        'price_percentage' => 50,
    ]);

    Livewire::test(UpdateRoom::class, ['room' => $room])
        ->set('children_count', 2)
        ->set('price_periods_egp', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 250],
        ])
        ->set('price_periods_usd', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 25],
        ])
        ->set('children_policy', [
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 5, 'price_percentage' => 0],
                    ['from_age' => 6, 'to_age' => 11, 'price_percentage' => 100],
                ],
            ],
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 11, 'price_percentage' => 50],
                ],
            ],
        ])
        ->call('saveUpdate');

    $room->refresh();

    // تأكد من وجود 3 سياسات جديدة (2 للطفل الأول + 1 للطفل الثاني)
    expect($room->childrenPolicies)->toHaveCount(3);

    // تأكد من الطفل الأول (فترتين عمريتين)
    $child1Policies = $room->childrenPolicies->where('child_number', 1);
    expect($child1Policies)->toHaveCount(2);

    // تأكد من الطفل الثاني (فترة عمرية واحدة)
    $child2Policies = $room->childrenPolicies->where('child_number', 2);
    expect($child2Policies)->toHaveCount(1);
});

it('handles empty children policy gracefully', function () {
    $hotel = Hotel::factory()->create();
    $room = Room::factory()->create([
        'hotel_id' => $hotel->id,
        'name' => ['ar' => 'غرفة بدون أطفال', 'en' => 'Room No Children'],
        'adults_count' => 2,
        'children_count' => 0,
        'adult_age' => 12,
    ]);

    Livewire::test(UpdateRoom::class, ['room' => $room])
        ->set('children_count', 0)
        ->set('price_periods_egp', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 100],
        ])
        ->set('price_periods_usd', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 10],
        ])
        ->set('children_policy', [])
        ->call('saveUpdate');

    $room->refresh();
    expect($room->childrenPolicies)->toHaveCount(0);
});
