@php use App\Enums\Status; @endphp
@assets()
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/intlTelInput.min.js"></script>
@endassets
<div>
    <x-card title="{{ __('lang.create_trip_booking') }}" shadow class="mb-3">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="lg:col-span-2 space-y-6">

                    {{-- Step 1: Client & Trip Selection --}}
                    <div class="border rounded-lg p-5 bg-base-100">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <x-icon name="o-user-circle" class="w-6 h-6 text-primary"/>
                            <span>{{ __('lang.step') }} 1: {{ __('lang.client_and_trip') }}</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id" :options="$users"
                                               option-value="id" option-label="name" single clearable searchable icon="o-user"/>

                            <x-choices-offline required label="{{ __('lang.trip') }}" wire:model.live="trip_id" :options="$trips"
                                               option-value="id" option-label="name" single clearable searchable icon="o-map"/>
                        </div>
                    </div>

                    @if($selectedTrip)
                        {{-- Step 2: Dates & People --}}
                        <div class="border rounded-lg p-5 bg-base-100">
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <x-icon name="o-calendar" class="w-6 h-6 text-primary"/>
                                <span>{{ __('lang.step') }} 2: {{ __('lang.dates_and_people') }}</span>
                            </h3>

                            {{-- Trip Type Badge --}}
                            <div class="mb-4 p-3 bg-info/10 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ __('lang.trip_type') }}:</span>
                                    <span class="badge badge-{{ $selectedTrip['type'] === 'fixed' ? 'success' : 'warning' }}">
                                        {{ __('lang.' . $selectedTrip['type']) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Dates --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <x-input label="{{ __('lang.check_in') }}" wire:model.live="check_in" type="date" required icon="o-calendar"/>
                                <x-input label="{{ __('lang.check_out') }}" wire:model.live="check_out" type="date" required icon="o-calendar"/>
                                <x-input label="{{ __('lang.nights') }}" wire:model="nights_count" type="number" min="1" icon="o-moon" readonly/>
                            </div>

                            {{-- People Count --}}
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-3 text-gray-700 dark:text-gray-300">
                                    {{ __('lang.number_of_travelers') }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <x-input label="{{ __('lang.adults') }}" wire:model.live="adults_count" type="number" min="1" icon="o-user" required/>
                                    <x-input label="{{ __('lang.children') }} {{ config('booking.child_age_threshold') }}+" wire:model.live="children_count" type="number" min="0"
                                             icon="o-user-group" hint="{{ __('lang.charged_as_adults') }}"/>
                                    <x-input label="{{ __('lang.children') }} <{{ config('booking.child_age_threshold') }}" wire:model.live="free_children_count" type="number"
                                             min="0" icon="o-user-group" hint="{{ __('lang.free') }}"/>
                                </div>
                            </div>

                            {{-- Currency --}}
                            <div class="mt-4">
                                <x-select required label="{{ __('lang.currency') }}" wire:model.live="currency" icon="o-currency-dollar" :options="[
                                        ['id' => 'egp', 'name' => 'EGP (جنيه مصري)'],
                                        ['id' => 'usd', 'name' => 'USD (دولار أمريكي)'],
                                    ]"
                                />
                            </div>
                        </div>

                        {{-- Step 3: Travelers Information --}}
                        @if(count($travelers) > 0)
                            <div class="border rounded-lg p-5 bg-base-100">
                                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                    <x-icon name="o-users" class="w-6 h-6 text-primary"/>
                                    <span>{{ __('lang.step') }} 3: {{ __('lang.travelers_information') }}</span>
                                    <span class="badge badge-primary ml-auto">{{ count($travelers) }} {{ __('lang.travelers') }}</span>
                                </h3>

                                <div class="space-y-4">
                                    @foreach($travelers as $index => $traveler)
                                        <div class="border border-base-300 rounded-lg p-4 bg-base-50">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                    @if($index < $adults_count)
                                                        <x-badge value="{{ __('lang.adult') }} {{ $index + 1 }}" class="badge-primary"/>
                                                    @elseif($index < $adults_count + $children_count)
                                                        <x-badge value="{{ __('lang.child') }} {{ $index - $adults_count + 1 }} ({{ config('booking.child_age_threshold') }}+)" class="badge-warning"/>
                                                    @else
                                                        <x-badge value="{{ __('lang.child') }} {{ $index - $adults_count - $children_count + 1 }} (<{{ config('booking.child_age_threshold') }})" class="badge-success"/>
                                                    @endif
                                                </h4>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                <x-input label="{{ __('lang.full_name') }}" wire:model="travelers.{{ $index }}.full_name" required icon="o-user"
                                                         placeholder="{{ __('lang.enter_full_name') }}"/>

                                                <x-phone-input required label="{{__('lang.phone')}}" phoneProperty="travelers.{{ $index }}.phone" keyProperty="travelers.{{ $index }}.phone_key"/>

                                                <x-input label="{{ __('lang.nationality') }}" wire:model="travelers.{{ $index }}.nationality" required icon="o-flag"
                                                         placeholder="{{ __('lang.nationality') }}"/>

                                                <x-input label="{{ __('lang.age') }}" wire:model="travelers.{{ $index }}.age" type="number" min="1" required icon="o-calendar"/>

                                                <x-select required label="{{ __('lang.id_type') }}" wire:model="travelers.{{ $index }}.id_type" :options="[
                                                        ['id' => 'passport', 'name' => __('lang.passport')],
                                                        ['id' => 'national_id', 'name' => __('lang.national_id')]
                                                    ]"
                                                />

                                                <x-input label="{{ __('lang.id_number') }}" wire:model="travelers.{{ $index }}.id_number" required
                                                         icon="o-identification" placeholder="{{ __('lang.id_number') }}"/>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Notes --}}
                        <div class="border rounded-lg p-5 bg-base-100">
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <x-icon name="o-document-text" class="w-6 h-6 text-primary"/>
                                <span>{{ __('lang.additional_notes') }}</span>
                            </h3>
                            <x-textarea wire:model="notes" placeholder="{{ __('lang.add_notes_here') }}" rows="3"/>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Price Summary (Sticky) --}}
                @if($selectedTrip)
                    <div class="lg:col-span-1">
                        <div class="sticky top-6">
                            <div class="border rounded-lg p-5 bg-gradient-to-br from-primary/5 to-secondary/5">
                                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                                    <x-icon name="o-calculator" class="w-6 h-6 text-primary"/>
                                    <span>{{ __('lang.booking_summary') }}</span>
                                </h3>

                                {{-- Trip Info --}}
                                <div class="mb-4 pb-4 border-b border-base-300">
                                    <h4 class="font-bold text-lg mb-2">{{ $selectedTrip['name'] }}</h4>
                                    <x-badge :value="$selectedTrip['type'] === 'fixed' ? __('lang.fixed_trip') : __('lang.flexible_trip')"
                                             :class="$selectedTrip['type'] === 'fixed' ? 'badge-info' : 'badge-success'"/>
                                </div>

                                {{-- Summary Details --}}
                                <div class="space-y-3 mb-4">
                                    @if($check_in && $check_out)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('lang.dates') }}:</span>
                                            <span class="font-semibold">{{ \Carbon\Carbon::parse($check_in)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($check_out)->format('d/m/Y') }}</span>
                                        </div>
                                    @endif

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.nights') }}:</span>
                                        <span class="font-semibold">{{ $nights_count }}</span>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.adults') }}:</span>
                                        <span class="font-semibold">{{ $adults_count }}</span>
                                    </div>

                                    @if($children_count > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('lang.children') }} ({{ config('booking.child_age_threshold') }}+):</span>
                                            <span class="font-semibold">{{ $children_count }}</span>
                                        </div>
                                    @endif

                                    @if($free_children_count > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('lang.children') }} (<{{ config('booking.child_age_threshold') }}) - {{ __('lang.free') }}:</span>
                                            <span class="font-semibold text-success">{{ $free_children_count }}</span>
                                        </div>
                                    @endif

                                    <div class="flex justify-between text-sm pt-2 border-t border-base-300">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.total_travelers') }}:</span>
                                        <span class="font-bold">{{ $adults_count + $children_count + $free_children_count }}</span>
                                    </div>
                                </div>

                                {{-- Pricing --}}
                                <div class="bg-base-200 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.base_price') }}:</span>
                                        <span class="font-semibold">{{ number_format($calculated_price, 2) }} {{ strtoupper($currency) }}</span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.paying') }}:</span>
                                        <span class="font-semibold">{{ $adults_count + $children_count }} {{ __('lang.persons') }}</span>
                                    </div>

                                    @if($selectedTrip['type'] === 'flexible')
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.calculation') }}:</span>
                                            <span class="text-xs">{{ $adults_count + $children_count }} × {{ $calculated_price }} × {{ $nights_count }}</span>
                                        </div>
                                    @else
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.calculation') }}:</span>
                                            <span class="text-xs">{{ $adults_count + $children_count }} × {{ $calculated_price }}</span>
                                        </div>
                                    @endif

                                    <div class="pt-3 border-t-2 border-primary">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-primary">{{ __('lang.total_price') }}:</span>
                                            <span class="text-2xl font-bold text-primary">{{ number_format($total_price, 2) }} {{ strtoupper($currency) }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-6 space-y-2">
                                    <x-button type="submit" label="{{ __('lang.create_booking') }}" icon="o-check" class="btn-primary w-full" spinner="save"/>
                                    <x-button label="{{ __('lang.back') }}" icon="o-arrow-left" link="{{ route('bookings.trips') }}" class="btn-outline w-full"/>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </x-card>
</div>

