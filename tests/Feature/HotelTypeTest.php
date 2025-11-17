<?php

use App\Models\Hotel;
use App\Models\HotelType;

test('can create hotel type with translatable name', function () {
    $hotelType = HotelType::create([
        'name' => [
            'ar' => 'فندق فاخر',
            'en' => 'Luxury Hotel',
        ],
    ]);

    expect($hotelType)->toBeInstanceOf(HotelType::class)
        ->and($hotelType->name)->toBe(['ar' => 'فندق فاخر', 'en' => 'Luxury Hotel'])
        ->and($hotelType->getTranslation('name', 'ar'))->toBe('فندق فاخر')
        ->and($hotelType->getTranslation('name', 'en'))->toBe('Luxury Hotel');
});

test('hotel can have multiple types', function () {
    $hotel = Hotel::factory()->create();
    $type1 = HotelType::factory()->create();
    $type2 = HotelType::factory()->create();
    $type3 = HotelType::factory()->create();

    $hotel->hotelTypes()->attach([$type1->id, $type2->id, $type3->id]);

    expect($hotel->hotelTypes)->toHaveCount(3)
        ->and($hotel->hotelTypes->pluck('id')->toArray())->toContain($type1->id, $type2->id, $type3->id);
});

test('hotel type can belong to multiple hotels', function () {
    $type = HotelType::factory()->create();
    $hotel1 = Hotel::factory()->create();
    $hotel2 = Hotel::factory()->create();
    $hotel3 = Hotel::factory()->create();

    $type->hotels()->attach([$hotel1->id, $hotel2->id, $hotel3->id]);

    expect($type->hotels)->toHaveCount(3)
        ->and($type->hotels->pluck('id')->toArray())->toContain($hotel1->id, $hotel2->id, $hotel3->id);
});

test('can filter hotel types by search term', function () {
    HotelType::factory()->create(['name' => ['ar' => 'فندق فاخر', 'en' => 'Luxury Hotel']]);
    HotelType::factory()->create(['name' => ['ar' => 'فندق اقتصادي', 'en' => 'Budget Hotel']]);
    HotelType::factory()->create(['name' => ['ar' => 'منتجع', 'en' => 'Resort']]);

    $results = HotelType::filter('فاخر')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->getTranslation('name', 'ar'))->toContain('فاخر');
});

test('deleting hotel type removes pivot records', function () {
    $hotel = Hotel::factory()->create();
    $type = HotelType::factory()->create();

    $hotel->hotelTypes()->attach($type->id);

    expect($hotel->hotelTypes)->toHaveCount(1);

    $type->delete();

    $hotel->refresh();

    expect($hotel->hotelTypes)->toHaveCount(0);
});
