@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.booking_details') }} - {{ $booking->booking_number }}" shadow class="mb-3">
        <x-slot:menu>
            <x-button noWireNavigate label="{{ __('lang.print') }}" icon="o-printer" class="btn-sm btn-success" link="{{ route('bookings.hotels.print', $booking->id) }}" target="_blank"/>
            @can('update_booking_hotel')
                <x-button noWireNavigate label="{{ __('lang.edit') }}" icon="o-pencil" class="btn-sm btn-primary" link="{{ route('bookings.hotels.edit', $booking->id) }}"/>
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
                        <p class="text-sm text-gray-500" >
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

            <!-- Hotel Information -->
            @if($booking->bookingHotel)
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">{{ __('lang.hotel_information') }}</h3>
                    <div class="bg-base-200 p-4 rounded-lg space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.hotel') }}</label>
                            <p class="text-base font-semibold">{{ $booking->bookingHotel->hotel->name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-600">{{ __('lang.room') }}</label>
                            <p class="text-base">{{ $booking->bookingHotel->room->name }}</p>
                        </div>

                            <div>
                                <label class="text-sm font-medium text-gray-600">{{ __('lang.room_capacity') }}</label>
                                <p class="text-sm">
                                    {{ $booking->bookingHotel->room->adults_count }} {{ __('lang.adults') }}
                                    @if($booking->bookingHotel->room->children_count > 0)
                                        + {{ $booking->bookingHotel->room->children_count }} {{ __('lang.children') }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">{{ __('lang.room_includes') }}</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <small class="">{!! $booking->bookingHotel->room_includes !!}</small>
                                </div>
                            </div>
                </div>
            @endif
        </div>
        </div>

        <!-- Pricing Details -->
        @if($booking->bookingHotel && $booking->bookingHotel->pricing_details)
            @php
                $pricing = $booking->bookingHotel->pricing_details;
            @endphp
            <div class="mt-6">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">{{ __('lang.pricing_details') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Adults Pricing -->
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold mb-3 text-primary">{{ __('lang.adults') }}</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('lang.adults_count') }}:</span>
                                <span class="font-medium">{{ $pricing['adults_count'] ?? 0 }}</span>
                            </div>
                            @if(isset($pricing['adult_price_per_person']))
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('lang.price_per_person') }}:</span>
                                    <span class="font-medium">{{ number_format($pricing['adult_price_per_person'], 2) }} {{ $pricing['currency'] ?? $booking->currency }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-semibold">{{ __('lang.adults_total') }}:</span>
                                <span class="font-bold text-primary">{{ number_format($booking->bookingHotel->adults_price, 2) }} {{ $pricing['currency'] ?? $booking->currency }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Children Pricing -->
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold mb-3 text-secondary">{{ __('lang.children') }}</h4>
                        @if(!empty($pricing['children_breakdown']) && count($pricing['children_breakdown']) > 0)
                            <div class="space-y-3">
                                @foreach($pricing['children_breakdown'] as $child)
                                    <div class="bg-base-100 p-3 rounded border">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <span class="text-sm font-medium">
                                                    {{ __('lang.child') }} {{ $child['child_number'] }}
                                                </span>
                                                <span class="text-xs text-gray-500 block">
                                                    {{ $child['age'] }} {{ __('lang.years') }}
                                                </span>
                                            </div>
                                            <span class="font-semibold text-secondary">
                                                {{ number_format($child['price'], 2) }} {{ $pricing['currency'] ?? $booking->currency }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            {{ $child['category_label'] }}
                                            @if($child['percentage'] > 0)
                                                <span class="badge badge-sm badge-outline">{{ $child['percentage'] }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <div class="flex justify-between pt-2 border-t">
                                    <span class="font-semibold">{{ __('lang.children_total') }}:</span>
                                    <span class="font-bold text-secondary">{{ number_format($booking->bookingHotel->children_price, 2) }} {{ $pricing['currency'] ?? $booking->currency }}</span>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('lang.no_children') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Grand Total -->
                <div class="bg-success/10 p-4 rounded-lg mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold">{{ __('lang.grand_total') }}:</span>
                        <span class="text-2xl font-bold text-success">{{ number_format($pricing['grand_total'], 2) }} {{ $pricing['currency'] ?? $booking->currency }}</span>
                    </div>
                </div>
            </div>
        @endif

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

