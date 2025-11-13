@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.create_trip_booking') }}" shadow class="mb-3">
        <form wire:submit.prevent="save">
                {{-- Basic Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-4">
                        <x-icon name="o-information-circle" class="w-5 h-5 inline"/> {{ __('lang.basic_information') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id" :options="$users"
                                         option-value="id" option-label="name" single clearable searchable icon="o-user"/>
                        <x-choices-offline required label="{{ __('lang.trip') }}" wire:model.live="trip_id" :options="$trips"
                                         option-value="id" option-label="name" single clearable searchable icon="o-map"/>
                        <x-select required label="{{ __('lang.currency') }}" wire:model="currency" icon="o-currency-dollar" :options="[
                            ['id' => 'egp', 'name' => 'EGP'],
                            ['id' => 'usd', 'name' => 'USD'],
                        ]"/>
                    </div>
                </div>

                {{-- Dates Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-4">
                        <x-icon name="o-calendar" class="w-5 h-5 inline"/> {{ __('lang.dates') }}
                    </h3>

                    @if($selectedTrip)
                        {{-- Trip Details --}}
                        <div class="bg-info/10 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-semibold">{{ __('lang.trip_type') }}:</span>
                                    <span class="badge badge-{{ $selectedTrip['type'] === 'fixed' ? 'success' : 'warning' }}">
                                        {{ __('lang.' . $selectedTrip['type']) }}
                                    </span>
                                </div>

                                @if($selectedTrip['type'] === 'fixed')
                                    <div>
                                        <span class="font-semibold">{{ __('lang.duration') }}:</span>
                                        {{ $selectedTrip['duration_from'] }} → {{ $selectedTrip['duration_to'] }}
                                    </div>
                                @else
                                    <div>
                                        <span class="font-semibold">{{ __('lang.available_from') }}:</span>
                                        {{ $selectedTrip['duration_from'] ?? __('lang.any_time') }}
                                    </div>
                                @endif

                                <div>
                                    <span class="font-semibold">{{ __('lang.base_people_count') }}:</span>
                                    {{ $selectedTrip['adults_count'] }} {{ __('lang.adults') }}
                                    @if($selectedTrip['children_count'] > 0)
                                        + {{ $selectedTrip['children_count'] }} {{ __('lang.children') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-input
                                required
                                label="{{ __('lang.check_in') }}"
                                wire:model.live="check_in"
                                type="date"
                                icon="o-calendar"
                                :readonly="$selectedTrip && $selectedTrip['type'] === 'fixed'"
                                :min="$selectedTrip && $selectedTrip['type'] === 'flexible' ? $selectedTrip['duration_from'] : null"
                        />
                        <x-input
                                required
                                label="{{ __('lang.check_out') }}"
                                wire:model.live="check_out"
                                type="date"
                                icon="o-calendar"
                                :readonly="$selectedTrip && $selectedTrip['type'] === 'fixed'"
                                :min="$check_in"
                        />
                        <x-input
                                required
                                label="{{ __('lang.nights') }}"
                                wire:model="nights_count"
                                type="number"
                                min="1"
                                icon="o-moon"
                                readonly
                        />
                        <div class="grid grid-cols-3 gap-2">
                            <x-input
                                    required
                                    label="{{ __('lang.adults') }}"
                                    wire:model.live="adults_count"
                                    type="number"
                                    min="1"
                                    icon="o-user"
                            />
                            <x-input
                                    label="{{ __('lang.children') }} {{ config('booking.child_age_threshold') }}+"
                                    wire:model.live="children_count"
                                    type="number"
                                    min="0"
                                    icon="o-user-group"
                                    hint="{{ __('lang.charged_as_adults') }}"
                            />
                            <x-input
                                    label="{{ __('lang.children') }} <{{ config('booking.child_age_threshold') }}"
                                    wire:model.live="free_children_count"
                                    type="number"
                                    min="0"
                                    icon="o-user-group"
                                    hint="{{ __('lang.free') }}"
                            />
                        </div>
                    </div>

                    {{-- Pricing Details --}}
                    @if($selectedTrip && $total_price > 0)
                        <div class="bg-success/10 p-4 rounded-lg mt-4">
                            <h4 class="font-semibold mb-3 text-success">
                                <x-icon name="o-currency-dollar" class="w-5 h-5 inline"/> {{ __('lang.pricing_details') }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                @if($selectedTrip['type'] === 'fixed')
                                    <div>
                                        <span class="text-gray-600">{{ __('lang.base_price') }}:</span>
                                        <span class="font-bold">{{ number_format($calculated_price, 2) }} {{ strtoupper($currency) }}</span>
                                    </div>
                                @else
                                    <div>
                                        <span class="text-gray-600">{{ __('lang.price_per_night') }}:</span>
                                        <span class="font-bold">{{ number_format($calculated_price, 2) }} {{ strtoupper($currency) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('lang.total_nights') }}:</span>
                                        <span class="font-bold">{{ $nights_count }} {{ __('lang.nights') }}</span>
                                    </div>
                                @endif

                                <div>
                                    <span class="text-gray-600">{{ __('lang.total_people') }}:</span>
                                    <span class="font-bold">
                                        {{ (int)$adults_count + (int)$children_count }} {{ __('lang.paying') }}
                                        @if($free_children_count > 0)
                                            + {{ $free_children_count }} {{ __('lang.free') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="md:col-span-3 pt-2 border-t border-success/30">
                                    <span class="text-gray-600">{{ __('lang.total_price') }}:</span>
                                    <span class="font-bold text-lg text-success">{{ number_format($total_price, 2) }} {{ strtoupper($currency) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Travelers --}}
                <div class="border-b pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            <x-icon name="o-users" class="w-5 h-5 inline"/> {{ __('lang.travelers') }}
                        </h3>
                        <x-button label="{{ __('lang.add_traveler') }}" icon="o-plus" wire:click="addTraveler" class="btn-sm btn-primary"/>
                    </div>

                    @foreach($travelers as $index => $traveler)
                        <div class="bg-base-200 p-4 rounded-lg mb-3">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-semibold">{{ __('lang.traveler') }} #{{ $index + 1 }}</h4>
                                <x-button icon="o-trash" wire:click="removeTraveler({{ $index }})" class="btn-sm btn-ghost btn-error"/>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <x-input required label="{{ __('lang.full_name') }}" wire:model="travelers.{{ $index }}.full_name" icon="o-user"/>
                                <x-phone-input required label="{{__('lang.phone')}}" phoneProperty="travelers.{{ $index }}.phone" keyProperty="travelers.{{ $index }}.phone_key"/>
                                <x-input required label="{{ __('lang.nationality') }}" wire:model="travelers.{{ $index }}.nationality" icon="o-flag"/>
                                <x-input required label="{{ __('lang.age') }}" wire:model="travelers.{{ $index }}.age" type="number" min="1" icon="o-hashtag"/>
                                <x-select required label="{{ __('lang.id_type') }}" wire:model="travelers.{{ $index }}.id_type" icon="o-identification" :options="[
                                    ['id' => 'passport', 'name' => __('lang.passport')],
                                    ['id' => 'national_id', 'name' => __('lang.national_id')],
                                ]"/>
                                <x-input required label="{{ __('lang.id_number') }}" wire:model="travelers.{{ $index }}.id_number" icon="o-hashtag"/>
                                <x-select required label="{{ __('lang.type') }}" wire:model="travelers.{{ $index }}.type" icon="o-user" :options="[
                                    ['id' => 'adult', 'name' => __('lang.adult')],
                                    ['id' => 'child', 'name' => __('lang.child')],
                                ]"/>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Notes --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        <x-icon name="o-document-text" class="w-5 h-5 inline"/> {{ __('lang.notes') }}
                    </h3>
                    <x-textarea wire:model="notes" placeholder="{{ __('lang.notes') }}" rows="3"/>
                </div>

            <x-slot:actions>
                <x-button noWireNavigate label="{{ __('lang.cancel') }}" icon="o-x-mark" link="{{ route('bookings.trips') }}"/>
                <x-button label="{{ __('lang.save') }}" icon="o-paper-airplane" class="btn-primary" type="submit" spinner="save"/>
            </x-slot:actions>
        </form>
    </x-card>
</div>

