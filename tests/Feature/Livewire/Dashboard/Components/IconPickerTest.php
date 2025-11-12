<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Components\IconPicker;
use App\Services\IconService;
use Livewire\Volt\Volt;

test('icon picker component can be rendered', function () {
    Livewire::test(IconPicker::class)
        ->assertStatus(200);
});

test('icon picker can select an icon', function () {
    Livewire::test(IconPicker::class)
        ->call('selectIcon', 'o-home')
        ->assertSet('selectedIcon', 'o-home')
        ->assertSet('showPicker', false);
});

test('icon picker can clear selected icon', function () {
    Livewire::test(IconPicker::class)
        ->set('selectedIcon', 'o-home')
        ->call('clearIcon')
        ->assertSet('selectedIcon', '');
});

test('icon picker can toggle picker visibility', function () {
    Livewire::test(IconPicker::class)
        ->assertSet('showPicker', false)
        ->call('togglePicker')
        ->assertSet('showPicker', true)
        ->call('togglePicker')
        ->assertSet('showPicker', false);
});

test('icon picker can filter icons by search', function () {
    $component = Livewire::test(IconPicker::class)
        ->set('search', 'home');

    $filteredIcons = $component->get('filteredIcons');

    expect($filteredIcons)->toBeArray()
        ->and($filteredIcons)->toContain('o-home');
});

test('icon picker can filter icons by category', function () {
    $component = Livewire::test(IconPicker::class)
        ->call('setCategory', 'common');

    $filteredIcons = $component->get('filteredIcons');

    expect($filteredIcons)->toBeArray()
        ->and(count($filteredIcons))->toBeGreaterThan(0);
});

test('icon picker resets search when category is set', function () {
    Livewire::test(IconPicker::class)
        ->set('search', 'test')
        ->call('setCategory', 'common')
        ->assertSet('search', '');
});

test('icon service returns icons array', function () {
    $icons = IconService::getIcons();

    expect($icons)->toBeArray()
        ->and(count($icons))->toBeGreaterThan(0)
        ->and($icons)->toContain('o-home')
        ->and($icons)->toContain('o-user')
        ->and($icons)->toContain('s-heart');
});

test('icon service returns categories array', function () {
    $categories = IconService::getCategories();

    expect($categories)->toBeArray()
        ->and($categories)->toHaveKeys(['common', 'actions', 'navigation'])
        ->and($categories['common'])->toHaveKeys(['label', 'label_ar', 'icons'])
        ->and($categories['common']['icons'])->toBeArray();
});

test('icon picker accepts model binding', function () {
    Livewire::test(IconPicker::class, ['selectedIcon' => 'o-star'])
        ->assertSet('selectedIcon', 'o-star');
});

test('icon picker accepts label and placeholder', function () {
    Livewire::test(IconPicker::class, [
        'label' => 'Select Icon',
        'placeholder' => 'Choose an icon',
    ])
        ->assertSet('label', 'Select Icon')
        ->assertSet('placeholder', 'Choose an icon');
});

