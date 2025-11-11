@assets()
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/intlTelInput.min.js"></script>
<style>
    /* تحسين مظهر intl-tel-input */
    .iti {
        display: block;
        width: 100%;
    }

    .iti__input,
    .iti__tel-input {
        width: 100% !important;
    }

    /* تطبيق نفس أنماط DaisyUI للـ input */
    .iti__tel-input {
        @apply input input-bordered w-full;
    }

    .iti__tel-input:focus {
        border: 2px solid #3b25c1 !important;
        outline: 0 !important;
    }

    /* تحسين مظهر قائمة الدول */
    .iti__country-list {
        max-height: 200px;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .iti__country {
        padding: 8px 12px;
    }

    .iti__selected-country {
        padding: 0 8px;
    }

    /* تحسين عرض علم الدولة ورمز الاتصال */
    .iti__selected-dial-code {
        margin-left: 6px;
    }

    /* دعم RTL */
    [dir="rtl"] .iti__selected-dial-code {
        margin-left: 0;
        margin-right: 6px;
    }

    /* تحسين المظهر في حالة الـ disabled */
    .iti__tel-input:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* تحسين الحدود عند التركيز */
    .iti--container:focus-within {
        outline: none;
    }

    /* تحسين مظهر السهم */
    .iti__arrow {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid #6b7280;
    }

    /* تحسين الـ placeholder */
    .iti__tel-input::placeholder {
        color: #9ca3af;
        opacity: 0.7;
    }

    /* توافق أفضل مع الـ grid */
    .form-control .iti {
        width: 100%;
    }

    /* تحسين المظهر على الشاشات الصغيرة */
    @media (max-width: 640px) {
        .iti__country-list {
            max-height: 150px;
        }

        .iti__selected-country {
            padding: 0 6px;
        }
    }

</style>
@endassets
<div>
	<x-card title="{{ __('lang.create_hotel_booking') }}" shadow class="mb-3">
		<form wire:submit.prevent="save">

				{{-- Basic Information --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-information-circle" class="w-5 h-5 inline"/> {{ __('lang.basic_information') }}
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id" :options="$users" option-value="id" option-label="name" option-sub-label="phone"
						                   single clearable searchable icon="o-user"/>
						<x-select required label="{{ __('lang.currency') }}" wire:model.live="currency" icon="o-currency-dollar" :options="[
                            ['id' => 'egp', 'name' => 'EGP'],
                            ['id' => 'usd', 'name' => 'USD'],
                        ]"/>
						<x-choices-offline required label="{{ __('lang.hotel') }}" wire:model.live="hotel_id" :options="$hotels" option-value="id" option-label="name"
						                   single clearable searchable icon="o-building-office-2" placeholder="{{ __('lang.select_hotel') }}"
						/>
					</div>
				</div>

				{{-- Dates Information --}}
				<div class="border-b mt-2 pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-calendar" class="w-5 h-5 inline"/> {{ __('lang.dates') }}
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<x-input required label="{{ __('lang.check_in') }}" wire:model.live="check_in" type="date" icon="o-calendar"/>
						<x-input required label="{{ __('lang.check_out') }}" wire:model.live="check_out" type="date" icon="o-calendar"/>
						<x-input required label="{{ __('lang.nights') }}" wire:model="nights_count" type="number" min="1" icon="o-moon" readonly/>
					</div>
				</div>

				{{-- Hotels & Rooms --}}
				<div class="border-b mt-2 pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-building-office-2" class="w-5 h-5 inline"/> {{ __('lang.hotel_and_room') }}
					</h3>

					@if($hotel_id && count($rooms) > 0)
						<div class="mt-4">
							<h4 class="font-semibold mb-3">{{ __('lang.rooms') }}</h4>
							<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
								@foreach($rooms as $room)
									<div wire:key="room-{{ $room->id }}"
											class="border rounded-lg p-4 cursor-pointer transition-all {{ $room_id == $room->id ? 'border-2 border-solid' : 'border-base-300 hover:border-primary/50' }}"
									>
										<label class="cursor-pointer">
											<div class="flex items-start justify-between mb-2">
												<h5 class="font-semibold text-lg">{{ $room->name }}</h5>
												<input type="radio" name="room_selection" value="{{ $room->id }}" wire:model.live="room_id" class="radio radio-primary"/>
											</div>

											<div class="space-y-2 text-sm">
												<div class="flex items-center gap-2">
													<x-icon name="o-user" class="w-4 h-4"/>
													<span>{{ __('lang.adults') }}: {{ $room->adults_count }}</span>
												</div>

												<div class="flex items-center gap-2">
													<x-icon name="o-user-group" class="w-4 h-4"/>
													<span>{{ __('lang.children') }}: {{ $room->children_count }}</span>
												</div>

												@php
													$breakdown = $room->priceBreakdownForPeriod($check_in, $check_out, $currency);
												@endphp

												<div class="mt-3 pt-3 border-t border-base-300">
													{{-- Price Details --}}
													<div class="lg:col-span-4">
														<details class="text-sm">
															<summary class="cursor-pointer flex items-center gap-2 text-primary hover:text-primary-focus">
																<x-icon name="o-calendar-days" class="w-4 h-4"/>
																<span class="font-medium">{{ __('lang.show_price_details') }}</span>
															</summary>
															<div class="mt-2 space-y-1 max-h-32 overflow-y-auto bg-base-200/50 rounded p-2">
																@foreach($breakdown['days'] as $day)
																	<div class="flex justify-between items-center text-xs">
																	<span class="text-base-content/70">
																		{{ $day['day_name'] }}
																		<span class="text-base-content/50">({{ \Carbon\Carbon::parse($day['date'])->format('d/m') }})</span>
																	</span>
																		<span class="font-medium">{{ number_format($day['price'], 2) }}</span>
																	</div>
																@endforeach
															</div>
														</details>
													</div>

													<div class="flex items-center justify-between mt-2 pt-2 ">
														<div class="flex items-center gap-1">
															<x-icon name="o-currency-dollar" class="w-4 h-4 text-primary"/>
															<span class="font-bold text-sm">{{ __('lang.total') }}</span>
															<span class="text-xs text-base-content/60">({{ $breakdown['nights_count'] }} {{ __('lang.nights') }})</span>
														</div>
														<span class="font-bold text-primary">{{ number_format($breakdown['total'], 2) }} {{ $breakdown['currency'] }}</span>
													</div>
												</div>




												{{-- Includes (Collapsible) --}}
												<div class="lg:col-span-12">
													<details class="text-sm">
														<summary class="cursor-pointer text-xs font-semibold text-base-content/70 hover:text-base-content">
															{{ __('lang.includes') }}
														</summary>
														<div class="mt-2 text-xs bg-base-200/30 rounded p-2">
															{!! $room->includes !!}
														</div>
													</details>
												</div>
											</div>
										</label>
									</div>
								@endforeach
							</div>
						</div>
					@else
						<div class="flex flex-col items-center justify-center py-10">
							<p class="text-sm text-base-content/70">{{ __('lang.select_hotel_and_dates_to_see_available_rooms') }}</p>
						</div>
					@endif
				</div>

				{{-- Travelers --}}
				@if(count($travelers) > 0)
					<div class="border-b mt-2 pb-4">
						<h3 class="text-lg font-semibold mb-4">
							<x-icon name="o-users" class="w-5 h-5 inline"/> {{ __('lang.travelers') }}
							<span class="text-sm font-normal text-base-content/60">
								({{ __('lang.total') }}: {{ count($travelers) }})
							</span>
						</h3>

						@foreach($travelers as $index => $traveler)
							<div class="bg-base-200 p-4 rounded-lg mb-3" wire:key="traveler-{{ $index }}">
								<h4 class="font-semibold mb-3">
									{{ $traveler['type'] == 'adult' ? __('lang.adult') : __('lang.child') }} #{{ $index + 1 }}
								</h4>
								<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
									<x-input required label="{{ __('lang.full_name') }}" wire:model="travelers.{{ $index }}.full_name" icon="o-user"/>
									<x-phone-input required label="{{__('lang.phone')}}" phoneProperty="travelers.{{ $index }}.phone" keyProperty="travelers.{{ $index }}.phone_key"/>
									<x-input required label="{{ __('lang.nationality') }}" wire:model="travelers.{{ $index }}.nationality" icon="o-flag"/>
									<x-input required label="{{ __('lang.age') }}" wire:model="travelers.{{ $index }}.age" type="number" min="1" icon="o-hashtag"/>
									<x-select required label="{{ __('lang.id_type') }}" wire:model="travelers.{{ $index }}.id_type" icon="o-identification" :options="[
										['id' => 'passport', 'name' => __('lang.passport')],
										['id' => 'national_id', 'name' => __('lang.national_id')],
									]"/>
									<x-input required label="{{ __('lang.id_number') }}" wire:model="travelers.{{ $index }}.id_number" icon="o-hashtag"/>
								</div>
							</div>
						@endforeach
					</div>
				@endif

				{{-- Notes --}}
				<div class="border-b mt-2 pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-document-text" class="w-5 h-5 inline"/> {{ __('lang.notes') }}
					</h3>
					<x-textarea wire:model="notes" placeholder="{{ __('lang.notes') }}" rows="3"/>
				</div>

			<x-slot:actions>
				<x-button noWireNavigate label="{{ __('lang.cancel') }}" icon="o-x-mark" link="{{ route('bookings.hotels') }}"/>
				<x-button label="{{ __('lang.save') }}" icon="o-paper-airplane" class="btn-primary" type="submit" spinner="save"/>
			</x-slot:actions>
		</form>
	</x-card>
</div>

