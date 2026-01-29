@php use App\Enums\Status; @endphp
<div>
    <x-card shadow class="mb-3">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <x-icon name="o-pencil-square" class="w-8 h-8 text-primary" />
                {{ __('lang.update_trip_booking') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('lang.booking_number') }}: <span class="font-semibold">{{ $booking->booking_number }}</span>
            </p>
        </div>
        <form wire:submit.prevent="update">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">

                <!-- Left Column: Booking Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Section 1: Client & Trip -->
                    <div class="bg-base-100 rounded-lg p-6 border border-base-300">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <x-icon name="o-user-circle" class="w-6 h-6 text-primary" />
                            </div>
                            <span>{{ __('lang.client_and_trip') }}</span>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id"
                                :options="$users" option-value="id" option-label="name" single searchable
                                icon="o-user" />

                            <x-choices-offline required label="{{ __('lang.trip') }}" wire:model.live="trip_id"
                                :options="$trips" option-value="id" option-label="name" single searchable
                                icon="o-map" />
                        </div>

                        @if ($selectedTrip)
                            <div class="mt-4 p-4 bg-info/10 rounded-lg border border-info/30">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-600">{{ __('lang.trip_type') }}:</span>
                                        <span
                                            class="ml-2 badge badge-{{ $selectedTrip['type'] === 'fixed' ? 'success' : 'warning' }}">
                                            {{ __('lang.' . $selectedTrip['type']) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('lang.base_price') }}:</span>
                                        <span class="ml-2 font-semibold">
                                            {{ number_format($selectedTrip['price']['egp'] ?? 0) }} EGP /
                                            {{ number_format($selectedTrip['price']['usd'] ?? 0) }} USD
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Section 2: Dates & People -->
                    <div class="bg-base-100 rounded-lg p-6 border border-base-300">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <x-icon name="o-calendar" class="w-6 h-6 text-primary" />
                            </div>
                            <span>{{ __('lang.dates_and_people') }}</span>
                        </h2>

                        <!-- Dates -->
                        <div class="mb-4">
                            <h3 class="font-semibold mb-3 text-lg">📅 {{ __('lang.travel_dates') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input label="{{ __('lang.check_in') }}" wire:model.live="check_in" type="date"
                                    required icon="o-calendar" />
                                <x-input label="{{ __('lang.check_out') }}" wire:model.live="check_out" type="date"
                                    required icon="o-calendar" />
                                <x-input label="{{ __('lang.nights') }}" wire:model="nights_count" type="number"
                                    min="1" icon="o-moon" readonly class="bg-base-200" />
                            </div>
                        </div>

                        <!-- People Count -->
                        <div class="mb-4">
                            <h3 class="font-semibold mb-3 text-lg">👥 {{ __('lang.number_of_travelers') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 bg-primary/5 rounded-lg border border-primary/20">
                                    <x-input label="{{ __('lang.adults') }}" wire:model.live="adults_count"
                                        type="number" min="1" icon="o-user" required />
                                </div>
                                <div class="p-4 bg-warning/5 rounded-lg border border-warning/20">
                                    <x-input
                                        label="{{ __('lang.children') }} {{ config('booking.child_age_threshold') }}+"
                                        wire:model.live="children_count" type="number" min="0"
                                        icon="o-user-group" />
                                    <p class="text-xs text-gray-500 mt-1">{{ __('lang.charged_as_adults') }}</p>
                                </div>
                                <div class="p-4 bg-success/5 rounded-lg border border-success/20">
                                    <x-input
                                        label="{{ __('lang.children') }} <{{ config('booking.child_age_threshold') }}"
                                        wire:model.live="free_children_count" type="number" min="0"
                                        icon="o-user-group" />
                                    <p class="text-xs text-success font-semibold mt-1">✓ {{ __('lang.free') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="mb-4">
                            <h3 class="font-semibold mb-3 text-lg">💰 {{ __('lang.currency') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="currency" value="egp"
                                        class="radio radio-primary" />
                                    <span class="ml-3 text-lg">🇪🇬 EGP (جنيه مصري)</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="currency" value="usd"
                                        class="radio radio-primary" />
                                    <span class="ml-3 text-lg">🇺🇸 USD (دولار أمريكي)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <h3 class="font-semibold mb-3 text-lg">📊 {{ __('lang.status') }}</h3>
                            <x-select wire:model="status" option-label="name" option-value="value" icon="o-flag"
                                required :options="[
                                    ['id' => Status::Pending, 'name' => __('lang.pending')],
                                    ['id' => Status::UnderPayment, 'name' => __('lang.under_payment')],
                                    ['id' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
                                    ['id' => Status::Cancelled, 'name' => __('lang.cancelled')],
                                    ['id' => Status::Completed, 'name' => __('lang.completed')],
                                ]" />
                        </div>
                    </div>

                    <!-- Section 3: Travelers -->
                    @if (count($travelers) > 0)
                        <div class="bg-base-100 rounded-lg p-6 border border-base-300">
                            <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                    <x-icon name="o-users" class="w-6 h-6 text-primary" />
                                </div>
                                <span>{{ __('lang.travelers_information') }}</span>
                                <span class="badge badge-primary badge-lg ml-auto">{{ count($travelers) }}</span>
                            </h2>

                            <div class="space-y-4">
                                @foreach ($travelers as $index => $traveler)
                                    <div
                                        class="border-2 border-base-300 rounded-lg p-5 hover:border-primary/50 transition-all">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="font-bold text-lg flex items-center gap-2">
                                                <x-icon name="o-user" class="w-5 h-5 text-primary" />
                                                <x-badge value="{{ __('lang.traveler') }} {{ $index + 1 }}"
                                                    class="badge-primary badge-lg" />
                                            </h3>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <x-input label="{{ __('lang.full_name') }}"
                                                wire:model="travelers.{{ $index }}.full_name" required
                                                icon="o-user" />

                                            <div>
                                                <label
                                                    class="block text-sm font-medium mb-1">{{ __('lang.phone') }}</label>
                                                <div class="flex gap-2">
                                                    <x-input wire:model="travelers.{{ $index }}.phone_key"
                                                        placeholder="+20" class="w-24" />
                                                    <x-input wire:model="travelers.{{ $index }}.phone"
                                                        placeholder="1234567890" class="flex-1" required />
                                                </div>
                                            </div>

                                            <x-input label="{{ __('lang.nationality') }}"
                                                wire:model="travelers.{{ $index }}.nationality" required
                                                icon="o-flag" />

                                            <x-input label="{{ __('lang.age') }}"
                                                wire:model="travelers.{{ $index }}.age" type="number"
                                                min="1" required icon="o-calendar" />

                                            <x-select label="{{ __('lang.id_type') }}"
                                                wire:model="travelers.{{ $index }}.id_type" :options="[
                                                    ['id' => 'passport', 'name' => __('lang.passport')],
                                                    ['id' => 'national_id', 'name' => __('lang.national_id')],
                                                ]"
                                                required />

                                            <x-input label="{{ __('lang.id_number') }}"
                                                wire:model="travelers.{{ $index }}.id_number" required
                                                icon="o-identification" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    <div class="bg-base-100 rounded-lg p-6 border border-base-300">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <x-icon name="o-document-text" class="w-5 h-5 text-primary" />
                            {{ __('lang.additional_notes') }}
                        </h2>
                        <x-textarea wire:model="notes" placeholder="{{ __('lang.add_notes_here') }}"
                            rows="3" />
                    </div>
                </div>

                <!-- Right Column: Price Summary (Sticky) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6">
                        <div
                            class="bg-gradient-to-br from-primary/5 to-secondary/5 rounded-lg p-5 border-2 border-primary/30">
                            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                                <x-icon name="o-calculator" class="w-6 h-6 text-primary" />
                                {{ __('lang.price_summary') }}
                            </h3>

                            <!-- Summary Details -->
                            <div class="space-y-3 mb-4">
                                @if ($check_in && $check_out)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.dates') }}:</span>
                                        <span
                                            class="font-semibold">{{ \Carbon\Carbon::parse($check_in)->format('d/m/Y') }}
                                            → {{ \Carbon\Carbon::parse($check_out)->format('d/m/Y') }}</span>
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

                                @if ($children_count > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.children') }}
                                            ({{ config('booking.child_age_threshold') }}+):</span>
                                        <span class="font-semibold">{{ $children_count }}</span>
                                    </div>
                                @endif

                                @if ($free_children_count > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('lang.children') }} (
                                            <{{ config('booking.child_age_threshold') }}):< /span>
                                                <span class="font-semibold text-success">{{ $free_children_count }}
                                                    ({{ __('lang.free') }})</span>
                                    </div>
                                @endif

                                <div class="flex justify-between text-sm pt-2 border-t border-base-300">
                                    <span
                                        class="text-gray-600 dark:text-gray-400">{{ __('lang.total_travelers') }}:</span>
                                    <span
                                        class="font-bold">{{ $adults_count + $children_count + $free_children_count }}</span>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="bg-base-200 rounded-lg p-4 space-y-2 mb-4">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.base_price') }}:</span>
                                    <span class="font-semibold">{{ number_format($calculated_price, 2) }}
                                        {{ strtoupper($currency) }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.paying') }}:</span>
                                    <span class="font-semibold">{{ $adults_count + $children_count }}</span>
                                </div>

                                @if ($selectedTrip && $selectedTrip['type'] === 'flexible')
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.calculation') }}:</span>
                                        <span class="text-xs">{{ $adults_count + $children_count }} ×
                                            {{ $calculated_price }} × {{ $nights_count }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.calculation') }}:</span>
                                        <span class="text-xs">{{ $adults_count + $children_count }} ×
                                            {{ $calculated_price }}</span>
                                    </div>
                                @endif

                                <div class="pt-3 border-t-2 border-primary">
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-lg font-bold text-primary">{{ __('lang.total_price') }}:</span>
                                        <span
                                            class="text-2xl font-bold text-primary">{{ number_format($total_price, 2) }}
                                            {{ strtoupper($currency) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2">
                                <x-button type="submit" label="{{ __('lang.update_booking') }}" icon="o-check"
                                    class="btn-primary w-full" spinner="update" />
                                <x-button label="{{ __('lang.cancel') }}" icon="o-x-mark"
                                    link="{{ route('bookings.trips') }}" class="btn-outline w-full" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>
