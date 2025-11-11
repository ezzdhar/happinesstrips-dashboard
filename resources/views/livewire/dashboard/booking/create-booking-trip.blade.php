@php use App\Enums\Status; @endphp
<div>
    <x-card title="{{ __('lang.create_trip_booking') }}" shadow class="mb-3">
        <form wire:submit.prevent="save">
            <div class="max-h-[75vh] overflow-y-auto p-4 space-y-6">

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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-input required label="{{ __('lang.check_in') }}" wire:model.live="check_in" type="date" icon="o-calendar"/>
                        <x-input required label="{{ __('lang.check_out') }}" wire:model.live="check_out" type="date" icon="o-calendar"/>
                        <x-input required label="{{ __('lang.nights') }}" wire:model="nights_count" type="number" min="1" icon="o-moon"/>
                        <div class="grid grid-cols-2 gap-2">
                            <x-input required label="{{ __('lang.adults') }}" wire:model="adults_count" type="number" min="1" icon="o-user"/>
                            <x-input label="{{ __('lang.children') }}" wire:model="children_count" type="number" min="0" icon="o-user-group"/>
                        </div>
                    </div>
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
            </div>

            <x-slot:actions>
                <x-button noWireNavigate label="{{ __('lang.cancel') }}" icon="o-x-mark" link="{{ route('bookings.trips') }}"/>
                <x-button label="{{ __('lang.save') }}" icon="o-paper-airplane" class="btn-primary" type="submit" spinner="save"/>
            </x-slot:actions>
        </form>
    </x-card>
</div>

