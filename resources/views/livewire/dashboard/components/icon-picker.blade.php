<div x-data="{ open: @entangle('showPicker') }" class="relative">
    <!-- Label -->
    @if($label)
        <label class="block text-sm font-medium mb-1">
            {{ $label }}
        </label>
    @endif

    <!-- Selected Icon Display / Input Trigger -->
    <div class="relative">
        <div
            @click="open = !open"
            class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:border-primary transition-colors bg-base-100"
            :class="{ 'border-primary': open }"
        >
            @if($selectedIcon)
                <x-icon :name="$selectedIcon" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                <span class="flex-1 text-sm">{{ $selectedIcon }}</span>
                <button
                    type="button"
                    wire:click.stop="clearIcon"
                    class="p-1 hover:bg-base-200 rounded transition-colors"
                >
                    <x-icon name="o-x-mark" class="w-4 h-4" />
                </button>
            @else
                <x-icon name="o-sparkles" class="w-5 h-5 text-gray-400" />
                <span class="flex-1 text-sm text-gray-400">{{ $placeholder ?: __('lang.select_icon', ['icon' => 'Select icon']) }}</span>
            @endif
            <x-icon name="o-chevron-down" class="w-4 h-4 text-gray-400 transition-transform" x-bind:class="{ 'rotate-180': open }" />
        </div>

        <!-- Hint -->
        @if($hint)
            <div class="mt-1 text-xs text-gray-500">
                {{ $hint }}
            </div>
        @endif

        <!-- Error Message -->
        @error('selectedIcon')
            <div class="mt-1 text-xs text-error">{{ $message }}</div>
        @enderror
    </div>

    <!-- Icon Picker Modal/Dropdown -->
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-2 bg-base-100 border rounded-lg shadow-xl max-h-96 overflow-hidden"
        style="display: none;"
    >
        <!-- Search Bar -->
        <div class="p-3 border-b sticky top-0 bg-base-100">
            <div class="relative">
                <x-icon name="o-magnifying-glass" class="absolute {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('lang.search', ['search' => 'Search icons...']) }}"
                    class="w-full {{ app()->getLocale() === 'ar' ? 'pr-10 pl-3' : 'pl-10 pr-3' }} py-2 text-sm border rounded-lg focus:outline-none focus:border-primary"
                >
            </div>
        </div>

        <!-- Categories -->
        <div class="p-2 border-b bg-base-50 overflow-x-auto">
            <div class="flex gap-2 flex-nowrap min-w-max">
                <button
                    type="button"
                    wire:click="setCategory(null)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors whitespace-nowrap"
                    x-bind:class="@js($activeCategory === null) ? 'bg-primary text-white' : 'bg-base-200 hover:bg-base-300'"
                >
                    {{ __('lang.all', ['all' => 'All']) }}
                </button>
                @foreach($this->categories as $key => $category)
                    <button
                        type="button"
                        wire:click="setCategory('{{ $key }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors whitespace-nowrap"
                        x-bind:class="@js($activeCategory === $key) ? 'bg-primary text-white' : 'bg-base-200 hover:bg-base-300'"
                    >
                        {{ app()->getLocale() === 'ar' ? $category['label_ar'] : $category['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Icons Grid -->
        <div class="p-3 overflow-y-auto max-h-64">
            @if(count($this->filteredIcons) > 0)
                <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2">
                    @foreach($this->filteredIcons as $icon)
                        <button
                            type="button"
                            wire:click="selectIcon('{{ $icon }}')"
                            class="flex flex-col items-center justify-center p-2 rounded-lg transition-all hover:bg-base-200 group relative {{ $selectedIcon === $icon ? 'bg-primary/10 ring-2 ring-primary' : '' }}"
                            title="{{ $icon }}"
                        >
                            <x-icon :name="$icon" class="w-6 h-6" />
                            <span class="text-xs mt-1 truncate w-full text-center opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ substr($icon, 2) }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-400">
                    <x-icon name="o-magnifying-glass" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">{{ __('lang.no_icons_found', ['no_icons' => 'No icons found']) }}</p>
                </div>
            @endif
        </div>

        <!-- Footer with count -->
        <div class="p-2 border-t bg-base-50 text-xs text-gray-500 text-center">
            {{ count($this->filteredIcons) }} {{ __('lang.icons', ['icons' => 'icons']) }}
        </div>
    </div>
</div>
