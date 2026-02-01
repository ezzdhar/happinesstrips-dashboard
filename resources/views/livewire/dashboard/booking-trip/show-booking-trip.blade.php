@php
    use App\Enums\Status;
    use App\Enums\TripType;
@endphp
<div>
    <x-card title="{{ __('lang.booking_details') }} - {{ $booking->booking_number }}" shadow class="mb-3">
        <x-slot:menu>
            <x-button noWireNavigate label="{{ __('lang.print') }}" icon="o-printer" class="btn-sm btn-success"
                link="{{ route('bookings.trips.print', $booking->id) }}" target="_blank" />
            @can('update_booking_trip')
                <x-button noWireNavigate label="{{ __('lang.edit') }}" icon="o-pencil" class="btn-sm btn-primary"
                    link="{{ route('bookings.trips.edit', $booking->id) }}" />
            @endcan
        </x-slot:menu>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Booking Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold border-b pb-2">{{ __('lang.booking_information') }}</h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.booking_number') }}</label>
                        <p class="text-base font-semibold">{{ $booking->booking_number }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.status') }}</label>
                        <p>
                            <x-badge :value="$booking->status->title()" class="bg-{{ $booking->status->color() }}" />
                        </p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.user') }}</label>
                        <p class="text-base">{{ $booking->user->name }}</p>
                        <p class="text-sm text-gray-500">
                            <span dir="ltr">{{ $booking->user->full_phone }}</span>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.check_in') }}</label>
                        <p class="text-base">{{ formatDate($booking->check_in) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.check_out') }}</label>
                        <p class="text-base">{{ formatDate($booking->check_out) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.nights') }}</label>
                        <p class="text-base">{{ $booking->nights_count }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.adults') }}</label>
                        <p class="text-base">{{ $booking->adults_count }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.children') }}</label>
                        <p class="text-base">{{ $booking->children_count }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.currency') }}</label>
                        <p class="text-base">
                            <x-badge :value="strtoupper($booking->currency)" class="badge-info" />
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.total_travelers') }}</label>
                        <p class="text-base font-semibold">{{ $booking->adults_count + $booking->children_count }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-primary/5 to-secondary/5 rounded-lg p-4 border-2 border-primary/30">
                    <h4 class="font-bold text-lg mb-3 flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5 text-primary" />
                        {{ __('lang.pricing_summary') }}
                    </h4>

                    <!-- Base Prices -->
                    <div class="space-y-2 mb-3">
                        <div class="flex justify-between items-center pb-2 border-b border-primary/20">
                            <span class="text-sm text-gray-600">{{ __('lang.base_price') }}</span>
                            <span class="font-semibold">{{ number_format($booking->price, 2) }}
                                {{ strtoupper($booking->currency) }}</span>
                        </div>

                        @if ($adults_price > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm">{{ $booking->adults_count }} {{ __('lang.adults') }}</span>
                                <span class="font-semibold">{{ number_format($adults_price, 2) }}
                                    {{ strtoupper($booking->currency) }}</span>
                            </div>
                        @endif

                        @if ($children_breakdown && $booking->children_count > 0)
                            <div class="mt-3 bg-white dark:bg-base-100 p-3 rounded">
                                <p class="font-semibold mb-2 text-sm">{{ __('lang.children') }}
                                    ({{ $booking->children_count }}):</p>
                                <div class="space-y-2">
                                    {{-- Free Children --}}
                                    @if (!empty($children_breakdown['free_children']))
                                        @foreach ($children_breakdown['free_children'] as $child)
                                            <div class="flex justify-between items-center text-sm">
                                                <span>
                                                    {{ __('lang.child') }}
                                                    <span class="text-xs text-gray-500">({{ $child['age'] }}
                                                        {{ __('lang.years') }})</span>
                                                    <span
                                                        class="badge badge-success badge-xs">{{ __('lang.free') }}</span>
                                                </span>
                                                <span
                                                    class="font-medium text-success">{{ number_format($child['price'], 2) }}
                                                    {{ strtoupper($booking->currency) }}</span>
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- First Child --}}
                                    @if (isset($children_breakdown['first_child']))
                                        @php $child = $children_breakdown['first_child']; @endphp
                                        <div class="flex justify-between items-center text-sm">
                                            <span>
                                                {{ __('lang.child') }} 1
                                                <span class="text-xs text-gray-500">({{ $child['age'] }}
                                                    {{ __('lang.years') }})</span>
                                            </span>
                                            <span class="font-medium">{{ number_format($child['price'], 2) }}
                                                {{ strtoupper($booking->currency) }}</span>
                                        </div>
                                    @endif

                                    {{-- Second Child --}}
                                    @if (isset($children_breakdown['second_child']))
                                        @php $child = $children_breakdown['second_child']; @endphp
                                        <div class="flex justify-between items-center text-sm">
                                            <span>
                                                {{ __('lang.child') }} 2
                                                <span class="text-xs text-gray-500">({{ $child['age'] }}
                                                    {{ __('lang.years') }})</span>
                                            </span>
                                            <span class="font-medium">{{ number_format($child['price'], 2) }}
                                                {{ strtoupper($booking->currency) }}</span>
                                        </div>
                                    @endif

                                    {{-- Third Child --}}
                                    @if (isset($children_breakdown['third_child']))
                                        @php $child = $children_breakdown['third_child']; @endphp
                                        <div class="flex justify-between items-center text-sm">
                                            <span>
                                                {{ __('lang.child') }} 3
                                                <span class="text-xs text-gray-500">({{ $child['age'] }}
                                                    {{ __('lang.years') }})</span>
                                            </span>
                                            <span class="font-medium">{{ number_format($child['price'], 2) }}
                                                {{ strtoupper($booking->currency) }}</span>
                                        </div>
                                    @endif

                                    {{-- Additional Children --}}
                                    @if (!empty($children_breakdown['additional_children']))
                                        @foreach ($children_breakdown['additional_children'] as $index => $child)
                                            <div class="flex justify-between items-center text-sm">
                                                <span>
                                                    {{ __('lang.child') }} {{ 4 + $index }}
                                                    <span class="text-xs text-gray-500">({{ $child['age'] }}
                                                        {{ __('lang.years') }})</span>
                                                </span>
                                                <span class="font-medium">{{ number_format($child['price'], 2) }}
                                                    {{ strtoupper($booking->currency) }}</span>
                                            </div>
                                        @endforeach
                                    @endif

                                    <div class="divider my-1"></div>
                                    <div class="flex justify-between font-semibold text-sm">
                                        <span>{{ __('lang.children_total') }}</span>
                                        <span>{{ number_format($children_breakdown['total_children_price'] ?? $children_price, 2) }}
                                            {{ strtoupper($booking->currency) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Trip Type Calculation Info -->
                    @if ($booking->trip)
                        <div class="p-2 bg-info/10 rounded text-xs text-info mb-3">
                            <x-icon name="o-information-circle" class="w-3 h-3 inline" />
                            @if ($booking->trip->type->value === 'flexible')
                                {{ __('lang.flexible_trip_calculation') }}:
                                {{ $booking->adults_count + $booking->children_count }} ×
                                {{ number_format($booking->price, 2) }} × {{ $booking->nights_count }}
                                {{ __('lang.nights') }}
                            @else
                                {{ __('lang.fixed_trip_calculation') }}:
                                {{ $booking->adults_count + $booking->children_count }} ×
                                {{ number_format($booking->price, 2) }}
                            @endif
                        </div>
                    @endif

                    <!-- Total Price -->
                    <div class="pt-3 border-t-2 border-primary">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold">{{ __('lang.total_price') }}</span>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-primary">
                                    {{ number_format($booking->total_price, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ strtoupper($booking->currency) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($booking->notes)
                    <div class="bg-warning/10 rounded-lg p-3 border border-warning/30">
                        <label class="text-xs font-semibold text-warning mb-1 flex items-center gap-1">
                            <x-icon name="o-document-text" class="w-4 h-4" />
                            {{ __('lang.notes') }}:
                        </label>
                        <p class="text-sm">{{ $booking->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Trip Information -->
            @if ($booking->trip)
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">{{ __('lang.trip_information') }}</h3>
                    <div class="bg-base-200 p-4 rounded-lg space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.trip') }}</label>
                            <p class="text-base font-semibold">{{ $booking->trip->name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.trip_type') }}</label>
                            <p>
                                <x-badge :value="__('lang.' . $booking->trip->type->value)"
                                    class="{{ $booking->trip->type->value === 'fixed' ? 'badge-success' : 'badge-warning' }}" />
                            </p>
                        </div>

                        @if ($booking->trip->description)
                            <div>
                                <label class="text-sm font-medium text-gray-600">{{ __('lang.description') }}</label>
                                <p class="text-sm">{{ $booking->trip->description }}</p>
                            </div>
                        @endif

                        @if ($booking->trip->type->value === 'flexible')
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-600">{{ __('lang.price_per_night') }}</label>
                                <p class="text-sm">
                                    {{ number_format($calculated_price, 2) }} {{ strtoupper($booking->currency) }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.calculation') }}</label>
                            <p class="text-sm font-mono">
                                {{ $booking->adults_count + $booking->children_count }} ×
                                {{ number_format($calculated_price, 2) }}
                                @if ($booking->trip->type->value === TripType::Flexible)
                                    × {{ $booking->nights_count }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Travelers Information -->
        @if ($booking->travelers->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">{{ __('lang.travelers') }}</h3>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead class="bg-base-300">
                            <tr>
                                <th class="text-center">#</th>
                                <th>{{ __('lang.full_name') }}</th>
                                <th>{{ __('lang.phone') }}</th>
                                <th>{{ __('lang.nationality') }}</th>
                                <th class="text-center">{{ __('lang.age') }}</th>
                                <th>{{ __('lang.id_type') }}</th>
                                <th>{{ __('lang.id_number') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($booking->travelers as $traveler)
                                <tr class="bg-base-200">
                                    <th class="text-center">{{ $loop->iteration }}</th>
                                    <td>{{ $traveler->full_name }}</td>
                                    <td dir="ltr">{{ $traveler->phone_key }} {{ $traveler->phone }}</td>
                                    <td>{{ $traveler->nationality }}</td>
                                    <td class="text-center">{{ $traveler->age }}</td>
                                    <td>{{ __('lang.' . $traveler->id_type) }}</td>
                                    <td>{{ $traveler->id_number }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </x-card>
</div>
