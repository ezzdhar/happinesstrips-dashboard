@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.trip_bookings') }}" shadow class="mb-3">
        <x-slot:menu>
            <x-button noWireNavigate label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" link="{{route('bookings.trips.create')}}"/>
        </x-slot:menu>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search') }}" icon="o-magnifying-glass" clearable/>
            <x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}" icon="o-flag" clearable :options="[
                ['id' => Status::Pending, 'name' => __('lang.pending')],
                ['id' => Status::UnderPayment, 'name' => __('lang.under_payment')],
                ['id' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
                ['id' => Status::Cancelled, 'name' => __('lang.cancelled')],
                ['id' => Status::Completed, 'name' => __('lang.completed')],
            ]"/>
            <x-select label="{{ __('lang.user') }}" wire:model.live="user_filter" placeholder="{{ __('lang.all') }}" icon="o-user" clearable :options="$users" option-value="id" option-label="name"/>
        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="min-w-full divide-y bg-base-300 text-base-content">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">{{__('lang.booking_number')}}</th>
                        <th class="text-center">{{__('lang.user')}}</th>
                        <th class="text-center">{{__('lang.trip')}}</th>
                        <th class="text-center">{{__('lang.travelers')}}</th>
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
                            <th class="text-nowrap">{{$booking->user->name}}</th>
                            <th class="text-nowrap">{{$booking->trip->name}}</th>
                            <th class="text-center">{{$booking->travelers->count()}}</th>
                            <th class="text-center text-nowrap">{{formatDate($booking->check_in)}}</th>
                            <th class="text-center text-nowrap">{{formatDate($booking->check_out)}}</th>
                            <th class="text-center text-nowrap">{{$booking->total_price}} {{strtoupper($booking->currency)}}</th>
                            <th class="text-center text-nowrap">
                                <x-badge :value="$booking->status->title()" class="bg-{{$booking->status->color()}}"/>
                            </th>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    <x-button noWireNavigate icon="o-eye" class="btn-sm btn-ghost" link="{{route('bookings.trips.show', $booking->id)}}" tooltip="{{__('lang.view')}}"/>
                                    <x-button noWireNavigate icon="o-pencil" class="btn-sm btn-ghost" link="{{route('bookings.trips.edit', $booking->id)}}" tooltip="{{__('lang.edit')}}"/>
                                    <x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$booking->id}})" wire:loading.attr="disabled"
                                              wire:target="deleteSweetAlert({{$booking->id}})" spinner="deleteSweetAlert({{$booking->id}})" tooltip="{{__('lang.delete')}}"/>
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

