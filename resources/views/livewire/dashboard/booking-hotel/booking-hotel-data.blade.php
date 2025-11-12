@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.hotel_bookings') }}" shadow class="mb-3">
        <x-slot:menu>
            <x-button noWireNavigate label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" link="{{route('bookings.hotels.create')}}"/>
        </x-slot:menu>
        <div class="grid grid-cols-2 sm-only:grid-cols-3 md:grid-cols-3 gap-4 mb-6">
            <x-stat title="{{ __('lang.total') }}" value="{{ $bookings_count }}" icon="fas.list" class="border text-primary"
                    wire:click="changeStatusFilter(null)" style="cursor: pointer;"/>
            <x-stat title="{{ __('lang.pending') }}" value="{{ $bookings_pending_count }}" icon="fas.hourglass-half" class="border text-{{Status::fromValue(Status::Pending)->color()}}"
                    wire:click="changeStatusFilter('{{ Status::Pending }}')" style="cursor: pointer;"/>
            <x-stat title="{{ __('lang.under_payment') }}" value="{{ $bookings_under_payment_count }}" icon="o-credit-card" class="border text-{{Status::fromValue(Status::UnderPayment)->color()}}"
                    wire:click="changeStatusFilter('{{ Status::UnderPayment }}')" style="cursor: pointer;"/>
            <x-stat title="{{ __('lang.under_cancellation') }}" value="{{ $bookings_under_cancellation_count }}" icon="o-x-circle" class="border text-{{Status::fromValue(Status::UnderCancellation)->color()}}"
                    wire:click="changeStatusFilter('{{ Status::UnderCancellation }}')" style="cursor:pointer;"/>
            <x-stat title="{{ __('lang.cancelled') }}" value="{{ $bookings_cancelled_count }}" icon="o-trash" class="border text-{{Status::fromValue(Status::Cancelled)->color()}}"
                    wire:click="changeStatusFilter('{{ Status::Cancelled }}')" style="cursor: pointer;"/>
            <x-stat title="{{ __('lang.completed') }}" value="{{ $bookings_completed_count }}" icon="o-check-circle" class="border text-{{Status::fromValue(Status::Completed)->color()}}"
                    wire:click="changeStatusFilter('{{ Status::Completed }}')" style="cursor: pointer;"/>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
            <x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search_booking_number') }}" icon="o-magnifying-glass" clearable/>
            <x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}" icon="o-flag" clearable :options="[
                ['id' => Status::Pending, 'name' => __('lang.pending')],
                ['id' => Status::UnderPayment, 'name' => __('lang.under_payment')],
                ['id' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
                ['id' => Status::Cancelled, 'name' => __('lang.cancelled')],
                ['id' => Status::Completed, 'name' => __('lang.completed')],
            ]"/>
            <x-ui.choices-advanced-search label="{{ __('lang.user') }}" wire:model.live="user_filter" :options="$users" single searchable
                               option-value="id" option-label="name" option-sub-label="phone" placeholder="{{ __('lang.select') }}" icon="o-user"/>
            <x-ui.choices-advanced-search label="{{ __('lang.select_hotels') }}" wire:model.live="hotel_filter" :options="$hotels" single
                                          searchable option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="min-w-full divide-y bg-base-300 text-base-content">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">{{__('lang.booking_number')}}</th>
                        <th class="text-center">{{__('lang.user')}}</th>
                        <th class="text-center">{{__('lang.room')}}</th>
                        <th class="text-center">{{__('lang.hotel')}}</th>
                        <th class="text-center">{{__('lang.check_in')}}</th>
                        <th class="text-center">{{__('lang.check_out')}}</th>
                        <th class="text-center">{{__('lang.total_price')}}</th>
                        <th class="text-center">{{__('lang.status')}}</th>
                        <th class="text-center">{{__('lang.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bookings as $booking)
                        <tr class="bg-base-200">
                            <th class="text-center">{{$bookings->firstItem() + $loop->index}}</th>
                            <th class="text-nowrap">{{$booking->booking_number}}</th>
                            <th class="text-nowrap">
                                {{$booking->user->name}}
                                <span class="block text-sm text-gray-500" dir="ltr">{{$booking->user->full_phone}}</span>
                            </th>
                            <th class="text-nowrap">{{ $booking->bookingHotel?->room?->name ?? '-' }}</th>
                            <th class="text-center">{{ $booking->bookingHotel?->hotel?->name ?? '-' }}</th>
                            <th class="text-center text-nowrap">{{formatDate($booking->check_in)}}</th>
                            <th class="text-center text-nowrap">{{formatDate($booking->check_out)}}</th>
                            <th class="text-center text-nowrap">{{$booking->total_price}} {{strtoupper($booking->currency)}}</th>
                            <th class="text-center text-nowrap">
                                <x-badge :value="$booking->status->title()" class="bg-{{$booking->status->color()}}"/>
                            </th>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    <x-button noWireNavigate icon="o-eye" class="btn-sm btn-ghost" link="{{route('bookings.hotels.show', $booking->id)}}" tooltip="{{__('lang.view')}}"/>
                                    @can('update_booking_hotel')
                                        <x-button noWireNavigate icon="o-pencil" class="btn-sm btn-ghost" link="{{route('bookings.hotels.edit', $booking->id)}}" tooltip="{{__('lang.edit')}}"/>
                                    @endcan
                                    @can('delete_booking_hotel')
                                    <x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$booking->id}})" wire:loading.attr="disabled"
                                              wire:target="deleteSweetAlert({{$booking->id}})" spinner="deleteSweetAlert({{$booking->id}})" tooltip="{{__('lang.delete')}}"/>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-base-200">
                            <th colspan="10" class="text-center">{{__('lang.no_data')}}</th>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6">
                    <div class="flex w-full items-center justify-between">
                        <div class="w-full flex-none">
                            {{ $bookings->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>

