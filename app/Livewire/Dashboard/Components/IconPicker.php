<?php

namespace App\Livewire\Dashboard\Components;

use App\Services\IconService;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class IconPicker extends Component
{
    #[Modelable]
    public $selectedIcon = '';

    public string $search = '';

    public bool $showPicker = false;

    public ?string $activeCategory = null;

    public string $label = '';

    public string $placeholder = '';

    public string $hint = '';

    public function mount(
        ?string $selectedIcon = null,
        string $label = '',
        string $placeholder = '',
        string $hint = ''
    ): void {
        $this->selectedIcon = $selectedIcon ?? '';
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->hint = $hint;
    }

    public function selectIcon(string $icon): void
    {
        $this->selectedIcon = $icon;
        $this->showPicker = false;
        $this->search = '';
        $this->activeCategory = null;
    }

    public function clearIcon(): void
    {
        $this->selectedIcon = '';
    }

    public function togglePicker(): void
    {
        $this->showPicker = ! $this->showPicker;
        if (! $this->showPicker) {
            $this->search = '';
            $this->activeCategory = null;
        }
    }

    public function setCategory(?string $category): void
    {
        $this->activeCategory = $category;
        $this->search = '';
    }

    public function getFilteredIconsProperty(): array
    {
        $allIcons = IconService::getIcons();

        // Filter by category
        if ($this->activeCategory) {
            $categories = IconService::getCategories();
            if (isset($categories[$this->activeCategory])) {
                $allIcons = $categories[$this->activeCategory]['icons'];
            }
        }

        // Filter by search
        if ($this->search) {
            $allIcons = array_filter($allIcons, function ($icon) {
                return str_contains(strtolower($icon), strtolower($this->search));
            });
        }

        return array_values($allIcons);
    }

    public function getCategoriesProperty(): array
    {
        return IconService::getCategories();
    }

    public function render()
    {
        return view('livewire.dashboard.components.icon-picker');
    }
}
