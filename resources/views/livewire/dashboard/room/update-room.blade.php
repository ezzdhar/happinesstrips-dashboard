@php
    use App\Enums\Status;
    use App\Services\FileService;
@endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
    <x-card title="{{ __('lang.update_room') }}" shadow class="mb-3">
        <form wire:submit.prevent="saveUpdate">

            <div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-4 gap-3">
                <x-input label="{{ __('lang.name') . ' (' . __('lang.ar') . ')' }}" wire:model="name_ar"
                    placeholder="{{ __('lang.name') . ' (' . __('lang.ar') . ')' }}" icon="o-language" />
                <x-input label="{{ __('lang.name') . ' (' . __('lang.en') . ')' }}" wire:model="name_en"
                    placeholder="{{ __('lang.name') . ' (' . __('lang.en') . ')' }}" icon="o-language" />

                <x-choices-offline label="{{ __('lang.hotel') }}" wire:model="hotel_id" :options="$hotels" single
                    searchable clearable option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"
                    icon="o-building-office-2" />

                <x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}"
                    icon="o-flag" :options="[
                        ['id' => Status::Active, 'name' => __('lang.active')],
                        ['id' => Status::Inactive, 'name' => __('lang.inactive')],
                    ]" />

                <x-input label="{{ __('lang.adults_count') }}" wire:model="adults_count" type="number" min="1"
                    placeholder="{{ __('lang.adults_count') }}" icon="o-users" />
                <x-input label="{{ __('lang.children_count') }}" wire:model.live="children_count" type="number"
                    min="0" placeholder="{{ __('lang.children_count') }}" icon="o-user-group" />
                <x-checkbox label="{{ __('lang.is_featured') }}" wire:model.live="is_featured" :checked="$is_featured ? true : false" />
                @if ($is_featured)
                    <x-input type="number" step="0.01" min="0" max="100"
                        label="{{ __('lang.discount_percentage') }}" wire:model="discount_percentage"
                        placeholder="0.00" suffix="%" icon="o-tag" />
                @endif
            </div>

            <div class="mt-3">
                <x-choices-offline label="{{ __('lang.amenities') }}" wire:model="selected_amenities" :options="$amenities"
                    searchable option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"
                    icon="o-sparkles" hint="{{ __('lang.select') . ' ' . __('lang.amenities') }}" />
            </div>

            {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                <x-trix required wire:model="includes_ar"
                    label="{{ __('lang.includes') . ' (' . __('lang.ar') . ')' }}"
                    key="{{ \Illuminate\Support\Str::random(20) }}"></x-trix>
                <x-trix dir="ltr" required wire:model="includes_en"
                    label="{{ __('lang.includes') . ' (' . __('lang.en') . ')' }}"
                    key="{{ \Illuminate\Support\Str::random(20) }}"></x-trix>
            </div> --}}

            {{-- EGP Price Periods Section --}}
            <div class="mt-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg"><x-icon name="o-currency-dollar" class="w-5 h-5 inline" />
                        {{ __('lang.price_periods_egp') }}</h3>
                    <x-button wire:click="addPricePeriodEgp" icon="o-plus" class="btn-sm btn-primary"
                        spinner="addPricePeriodEgp">
                        {{ __('lang.add_price_period') }}
                    </x-button>
                </div>

                @if ($egp_gaps_warning)
                    <div class="alert alert-warning mb-3">
                        <x-icon name="o-exclamation-triangle" class="w-5 h-5" />
                        <span>{{ $egp_gaps_warning }}</span>
                    </div>
                @endif

                @if (empty($price_periods_egp))
                    <div class="alert alert-info">
                        <x-icon name="o-information-circle" class="w-5 h-5" />
                        <span>{{ __('lang.no_price_periods_added') }}</span>
                    </div>
                @endif

                @foreach ($price_periods_egp as $index => $period)
                    <div class="card bg-base-200 p-4 mb-3">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold">{{ __('lang.price_period') }} #{{ $index + 1 }}</h4>
                            <x-button wire:click="removePricePeriodEgp({{ $index }})" icon="o-trash"
                                class="btn-sm btn-error btn-circle" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <x-input required label="{{ __('lang.start_date') }}"
                                wire:model="price_periods_egp.{{ $index }}.start_date" type="date"
                                icon="o-calendar" />
                            <x-input required label="{{ __('lang.end_date') }}"
                                wire:model="price_periods_egp.{{ $index }}.end_date" type="date"
                                icon="o-calendar" />
                            <x-input required label="{{ __('lang.price') }}"
                                wire:model="price_periods_egp.{{ $index }}.price" type="number" step="0.01"
                                min="0" placeholder="0" icon="o-currency-dollar" suffix="EGP"
                                hint="{{ __('lang.price_per_person_per_night') }}" />
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- USD Price Periods Section --}}
            <div class="mt-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg"><x-icon name="o-currency-dollar" class="w-5 h-5 inline" />
                        {{ __('lang.price_periods_usd') }}</h3>
                    <x-button wire:click="addPricePeriodUsd" icon="o-plus" class="btn-sm btn-success"
                        spinner="addPricePeriodUsd">
                        {{ __('lang.add_price_period') }}
                    </x-button>
                </div>

                @if ($usd_gaps_warning)
                    <div class="alert alert-warning mb-3">
                        <x-icon name="o-exclamation-triangle" class="w-5 h-5" />
                        <span>{{ $usd_gaps_warning }}</span>
                    </div>
                @endif

                @if (empty($price_periods_usd))
                    <div class="alert alert-info">
                        <x-icon name="o-information-circle" class="w-5 h-5" />
                        <span>{{ __('lang.no_price_periods_added') }}</span>
                    </div>
                @endif

                @foreach ($price_periods_usd as $index => $period)
                    <div class="card bg-success/10 p-4 mb-3">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold">{{ __('lang.price_period') }} #{{ $index + 1 }}</h4>
                            <x-button wire:click="removePricePeriodUsd({{ $index }})" icon="o-trash"
                                class="btn-sm btn-error btn-circle" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <x-input required label="{{ __('lang.start_date') }}"
                                wire:model="price_periods_usd.{{ $index }}.start_date" type="date"
                                icon="o-calendar" />
                            <x-input required label="{{ __('lang.end_date') }}"
                                wire:model="price_periods_usd.{{ $index }}.end_date" type="date"
                                icon="o-calendar" />
                            <x-input required label="{{ __('lang.price') }}"
                                wire:model="price_periods_usd.{{ $index }}.price" type="number"
                                step="0.01" min="0" placeholder="0" icon="o-currency-dollar"
                                suffix="USD" hint="{{ __('lang.price_per_person_per_night') }}" />
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Children Policy Section --}}
            @if ($children_count > 0)
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-lg"><x-icon name="o-user-group" class="w-5 h-5 inline" />
                            {{ __('lang.children_policy') }}</h3>
                    </div>

                    <div class="mb-4">
                        <x-input required label="{{ __('lang.adult_age') }}" wire:model="adult_age" type="number"
                            min="1" max="25" placeholder="12" icon="o-user-circle"
                            hint="{{ __('lang.age_considered_adult') }}" />
                    </div>

                    @foreach ($children_policy as $childIndex => $child)
                        <div class="card bg-base-200 p-4 mb-3">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold">{{ __('lang.child') }} #{{ $childIndex + 1 }}</h4>
                                <x-button wire:click="addAgeRange({{ $childIndex }})" icon="o-plus"
                                    class="btn-sm btn-secondary" spinner="addAgeRange">
                                    {{ __('lang.add_age_range') }}
                                </x-button>
                            </div>

                            @foreach ($child['ranges'] as $rangeIndex => $range)
                                <div class="bg-base-100 p-3 rounded-lg mb-2">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium">{{ __('lang.age_range') }}
                                            #{{ $rangeIndex + 1 }}</span>
                                        @if (count($child['ranges']) > 1)
                                            <x-button
                                                wire:click="removeAgeRange({{ $childIndex }}, {{ $rangeIndex }})"
                                                icon="o-trash" class="btn-xs btn-error btn-circle" />
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <x-input required label="{{ __('lang.from_age') }}"
                                            wire:model="children_policy.{{ $childIndex }}.ranges.{{ $rangeIndex }}.from_age"
                                            type="number" min="0" max="18" placeholder="0"
                                            icon="o-user" suffix="{{ __('lang.years') }}" />
                                        <x-input required label="{{ __('lang.to_age') }}"
                                            wire:model="children_policy.{{ $childIndex }}.ranges.{{ $rangeIndex }}.to_age"
                                            type="number" min="0" max="18" placeholder="11"
                                            icon="o-user" suffix="{{ __('lang.years') }}" />
                                        <x-input required label="{{ __('lang.price_percentage') }}"
                                            wire:model="children_policy.{{ $childIndex }}.ranges.{{ $rangeIndex }}.price_percentage"
                                            type="number" step="0.01" min="0" max="100"
                                            placeholder="0" icon="o-percent-badge" suffix="%"
                                            hint="{{ __('lang.percentage_of_adult_price') }}" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($room->files->isNotEmpty())
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">{{ __('lang.current_images') }}</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach ($room->files as $file)
                            <div class="relative mb-4">
                                <img src="{{ $file->path ? FileService::get($file->path) : null }}" alt=""
                                    class="w-24 h-24 object-cover rounded">
                                <x-button icon="o-trash" size="w-16 h-16"
                                    class="object-cover rounded btn-sm absolute top-1 right-1 bg-red-500 text-white hover:bg-red-600 mt-2"
                                    wire:click="delete({{ $file->id }})" wire:loading.attr="disabled"
                                    wire:target="delete({{ $file->id }})" spinner="delete({{ $file->id }})"
                                    wire:confirm="{{ __('lang.confirm_delete', ['attribute' => __('lang.image')]) }}" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <x-dropzone-images wire:model="images" label="{{ __('lang.images') }}" :max-files="10"
                    :max-file-size="5" />
            </div>

            <div class="mt-6 flex justify-end gap-2 px-4 pb-4">
                <x-button label="{{ __('lang.cancel') }}" @click="window.location='{{ route('rooms') }}'"
                    wire:loading.attr="disabled" />
                <x-button label="{{ __('lang.save') }}" class="btn-primary" type="submit"
                    wire:loading.attr="disabled" wire:target="saveUpdate,images" spinner="saveUpdate" />
            </div>
        </form>
    </x-card>
</div>
