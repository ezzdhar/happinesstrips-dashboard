@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.booking_details') }} - {{ $booking->booking_number }}" shadow class="mb-3">
        <x-slot:menu>
            <x-button noWireNavigate label="{{ __('lang.print') }}" icon="o-printer" class="btn-sm btn-success" link="{{ route('bookings.trips.print', $booking->id) }}" target="_blank"/>
            @can('update_booking_trip')
                <x-button noWireNavigate label="{{ __('lang.edit') }}" icon="o-pencil" class="btn-sm btn-primary" link="{{ route('bookings.trips.edit', $booking->id) }}"/>
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
                        <p><x-badge :value="$booking->status->title()" class="bg-{{$booking->status->color()}}"/></p>
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
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.children') }} {{ $child_age_threshold }}+</label>
                        <p class="text-base">{{ $booking->children_count }}</p>
                    </div>
                </div>

                @if(($booking->free_children_count ?? 0) > 0)
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.children') }} <{{ $child_age_threshold }}</label>
                            <p class="text-base badge badge-success">{{ $booking->free_children_count }} ({{ __('lang.free') }})</p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.price') }}</label>
                        <p class="text-base font-semibold">{{ $booking->price }} {{ strtoupper($booking->currency) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.total_price') }}</label>
                        <p class="text-lg font-bold text-primary">{{ $booking->total_price }} {{ strtoupper($booking->currency) }}</p>
                    </div>
                </div>

                @if($booking->notes)
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ __('lang.notes') }}</label>
                        <p class="text-base">{{ $booking->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Trip Information -->
            @if($booking->trip)
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
                                <x-badge
                                    :value="__('lang.' . $booking->trip->type->value)"
                                    class="{{ $booking->trip->type->value === 'fixed' ? 'badge-success' : 'badge-warning' }}"
                                />
                            </p>
                        </div>

                        @if($booking->trip->description)
                            <div>
                                <label class="text-sm font-medium text-gray-600">{{ __('lang.description') }}</label>
                                <p class="text-sm">{{ $booking->trip->description }}</p>
                            </div>
                        @endif

                        @if($booking->trip->type->value === 'flexible')
                            <div>
                                <label class="text-sm font-medium text-gray-600">{{ __('lang.price_per_night') }}</label>
                                <p class="text-sm">
                                    {{ number_format($calculated_price, 2) }} {{ strtoupper($booking->currency) }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.calculation') }}</label>
                            <p class="text-sm font-mono">
                                {{ $booking->adults_count + $booking->children_count }} × {{ number_format($calculated_price, 2) }}
                                @if($booking->trip->type->value === 'flexible')
                                    × {{ $booking->nights_count }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Travelers Information -->
        @if($booking->travelers->count() > 0)
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
                                <th class="text-center">{{ __('lang.type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->travelers as $traveler)
                                <tr class="bg-base-200">
                                    <th class="text-center">{{ $loop->iteration }}</th>
                                    <td>{{ $traveler->full_name }}</td>
                                    <td dir="ltr">{{ $traveler->phone_key }} {{ $traveler->phone }}</td>
                                    <td>{{ $traveler->nationality }}</td>
                                    <td class="text-center">{{ $traveler->age }}</td>
                                    <td>{{ __('lang.' . $traveler->id_type) }}</td>
                                    <td>{{ $traveler->id_number }}</td>
                                    <td class="text-center">
                                        <x-badge :value="__('lang.' . $traveler->type)" class="{{ $traveler->type == 'adult' ? 'badge-primary' : 'badge-secondary' }}"/>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </x-card>
</div>

