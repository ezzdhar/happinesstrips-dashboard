<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Room\CreateRoom;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('prevents duplicate children policy entries on form resubmission', function () {
    $hotel = Hotel::factory()->create();

    $component = Livewire::test(CreateRoom::class)
        ->set('name_ar', 'غرفة تجريبية')
        ->set('name_en', 'Test Room')
        ->set('hotel_id', $hotel->id)
        ->set('adults_count', 2)
        ->set('children_count', 1)
        ->set('adult_age', 12)
        ->set('status', 'active')
        ->set('price_periods_egp', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 100],
        ])
        ->set('price_periods_usd', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 10],
        ])
        ->set('children_policy', [
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 11, 'price_percentage' => 50],
                ],
            ],
        ]);

    // محاولة الحفظ مرتين (double-click simulation)
    $component->call('saveAdd');

    expect($component->isProcessing)->toBeFalse();

    // تأكد من إنشاء الغرفة
    $room = Room::where('name->ar', 'غرفة تجريبية')->first();
    expect($room)->not->toBeNull();

    // تأكد من وجود سياسة طفل واحدة فقط
    expect($room->childrenPolicies)->toHaveCount(1);
    expect($room->childrenPolicies->first())
        ->child_number->toBe(1)
        ->from_age->toBe(0)
        ->to_age->toBe(11)
        ->price_percentage->toBe(50.0);

    // محاولة الحفظ مرة أخرى يجب أن يتم منعها
    $component->call('saveAdd');
    expect($component->isProcessing)->toBeFalse();

    // تأكد من عدم إنشاء سجلات مكررة
    $rooms = Room::where('name->ar', 'غرفة تجريبية')->get();
    expect($rooms)->toHaveCount(1);
});

it('handles multiple children policies correctly', function () {
    $hotel = Hotel::factory()->create();

    Livewire::test(CreateRoom::class)
        ->set('name_ar', 'غرفة عائلية')
        ->set('name_en', 'Family Room')
        ->set('hotel_id', $hotel->id)
        ->set('adults_count', 2)
        ->set('children_count', 2)
        ->set('adult_age', 12)
        ->set('status', 'active')
        ->set('price_periods_egp', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 200],
        ])
        ->set('price_periods_usd', [
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'price' => 20],
        ])
        ->set('children_policy', [
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 5, 'price_percentage' => 0],
                    ['from_age' => 6, 'to_age' => 11, 'price_percentage' => 50],
                ],
            ],
            [
                'ranges' => [
                    ['from_age' => 0, 'to_age' => 11, 'price_percentage' => 25],
                ],
            ],
        ])
        ->call('saveAdd');

    $room = Room::where('name->ar', 'غرفة عائلية')->first();
    expect($room)->not->toBeNull();

    // تأكد من وجود 3 سياسات (طفلين بمجموع 3 فترات عمرية)
    expect($room->childrenPolicies)->toHaveCount(3);

    // تأكد من الطفل الأول (فترتين عمريتين)
    $child1Policies = $room->childrenPolicies->where('child_number', 1);
    expect($child1Policies)->toHaveCount(2);

    // تأكد من الطفل الثاني (فترة عمرية واحدة)
    $child2Policies = $room->childrenPolicies->where('child_number', 2);
    expect($child2Policies)->toHaveCount(1);
});
