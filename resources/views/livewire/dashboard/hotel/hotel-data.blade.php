@php
    use App\Enums\Status;
    use App\Services\FileService;
@endphp
<div>
    <x-card title="{{ __('lang.hotels') }}" shadow class="mb-3">
        <x-slot:menu>
            @can('create_hotel')
                <x-button noWireNavigate label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm"
                    link="{{ route('hotels.create') }}" />
            @endcan
        </x-slot:menu>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search_by_name') }}"
                icon="o-magnifying-glass" clearable />
            <x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}"
                icon="o-flag" clearable :options="[
                    ['id' => Status::Active, 'name' => __('lang.active')],
                    ['id' => Status::Inactive, 'name' => __('lang.inactive')],
                ]" />
            <x-choices-offline required label="{{ __('lang.hotel_type') }}" wire:model.live="hotel_type_filter"
                :options="$hotel_types" single clearable searchable option-value="id" option-label="name"
                placeholder="{{ __('lang.select') }}" />

        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="min-w-full divide-y bg-base-300 text-base-content">
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">{{ __('lang.name') }}</th>
                            <th class="text-center">{{ __('lang.city') }}</th>
                            <th class="text-center">{{ __('lang.hotel_type') }}</th>
                            <th class="text-center">{{ __('lang.rating') }}</th>
                            <th class="text-center">{{ __('lang.status') }}</th>
                            <th class="text-center">{{ __('lang.rooms') }}</th>
                            <th class="text-center">{{ __('lang.created_at') }}</th>
                            <th class="text-center">{{ __('lang.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotels as $hotel)
                            <tr class="bg-base-200">
                                <th class="text-center">{{ $hotels->firstItem() + $loop->index }}</th>
                                <th class="text-nowrap">
                                    {{ $hotel->name }}
                                </th>
                                <th class="text-center text-nowrap">{{ $hotel->city->name }}</th>
                                <th class="text-center">
                                    @foreach ($hotel->hotelTypes as $type)
                                        <x-badge :value="$type->name" class=" whitespace-nowrap text-xs bg-blue-500 mb-1" />
                                    @endforeach
                                </th>
                                <th class="text-center">
                                    <div class="flex justify-center gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <x-icon :name="$i <= $hotel->rating ? 'o-star' : 'o-star'"
                                                class="whitespace-nowrap w-4 h-4 {{ $i <= $hotel->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                        @endfor
                                    </div>
                                </th>
                                <th class="text-center text-nowrap">
                                    <x-badge :value="$hotel->status->title()" class="text-xs bg-{{ $hotel->status->color() }}" />
                                </th>
                                <th class="text-center text-nowrap">{{ $hotel->rooms_count }}</th>
                                <th class="text-center text-nowrap">{{ formatDate($hotel->created_at, true) }}</th>
                                <td>
                                    <div class="flex gap-2 justify-center">
                                        @can('update_hotel')
                                            <x-button noWireNavigate icon="o-pencil" class="btn-sm btn-ghost"
                                                link="{{ route('hotels.edit', $hotel->id) }}"
                                                tooltip="{{ __('lang.edit') }}" />
                                        @endcan
                                        @can('delete_hotel')
                                            <x-button icon="o-trash" class="btn-sm btn-ghost"
                                                wire:click="deleteSweetAlert({{ $hotel->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="deleteSweetAlert({{ $hotel->id }})"
                                                spinner="deleteSweetAlert({{ $hotel->id }})"
                                                tooltip="{{ __('lang.delete') }}" />
                                        @endcan
                                        @can('show_room')
                                            <x-button noWireNavigate icon="ionicon.bed-outline" class="btn-sm btn-ghost"
                                                link="{{ route('rooms', ['hotel_id_filter' => $hotel->id]) }}"
                                                tooltip="{{ __('lang.rooms') }}" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-base-200">
                                <th colspan="7" class="text-center">{{ __('lang.no_data') }}</th>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
                    <div class="flex w-full items-center justify-between">
                        <div class="w-full flex-none">
                            {{ $hotels->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>
