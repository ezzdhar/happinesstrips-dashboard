@php use App\Enums\Status; @endphp
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
				<div class="grid grid-cols-1  md:grid-cols-4 gap-4">
					<x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id" :options="$users" option-value="id" option-label="name" option-sub-label="phone"
					                   single clearable searchable icon="o-user"/>
					<x-select required label="{{ __('lang.currency') }}" wire:model.live="currency" icon="o-currency-dollar" :options="[
                            ['id' => null, 'name' => __('lang.select')],
                            ['id' => 'egp', 'name' => 'EGP'],
                            ['id' => 'usd', 'name' => 'USD'],
                        ]"/>
					<x-select label="{{ __('lang.status') }}" wire:model.live="status" placeholder="{{ __('lang.select') }}" icon="o-flag" clearable :options="[
			                ['id' => Status::Pending, 'name' => __('lang.pending')],
			                ['id' => Status::UnderPayment, 'name' => __('lang.under_payment')],
			                ['id' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
			                ['id' => Status::Cancelled, 'name' => __('lang.cancelled')],
			                ['id' => Status::Completed, 'name' => __('lang.completed')],
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

			{{-- Rooms --}}
			<div class="border-b mt-2 pb-4">
				<h3 class="text-lg font-semibold mb-4">
					<x-icon name="o-building-office-2" class="w-5 h-5 inline"/> {{ __('lang.rooms') }}
				</h3>

				@if($hotel_id && count($rooms) > 0)
					<div class="mt-4">
						<h4 class="font-semibold mb-3">{{ __('lang.select_room') }}</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
							@foreach($rooms as $room)
								<div wire:key="room-{{ $room->id }}"
								     class="border rounded-lg p-4 cursor-pointer transition-all {{ $room_id == $room->id ? 'border-primary border-2' : 'border-base-300 hover:border-primary/50' }}"
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
										</div>

									</label>
								</div>
							@endforeach
						</div>
					</div>
				@elseif($hotel_id)
					<div class="alert alert-warning">
						<x-icon name="o-exclamation-triangle" class="w-5 h-5"/>
						<span>{{ __('lang.no_rooms_available') }}</span>
					</div>
				@else
					<div class="alert alert-info">
						<x-icon name="o-information-circle" class="w-5 h-5"/>
						<span>{{ __('lang.select_hotel_and_dates_to_see_available_rooms') }}</span>
					</div>
				@endif
			</div>

			{{-- People Count --}}
			@if($room_id)
				<div class="border-b mt-2 pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-users" class="w-5 h-5 inline"/> {{ __('lang.people_count') }}
					</h3>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						{{-- Adults --}}
						<div>
							<x-input required label="{{ __('lang.adults_count') }}" wire:model.live="adults_count" type="number" min="1" :max="$selected_room->adults_count" icon="o-user"/>
						</div>

						{{-- Children --}}
						<div>
							<div class="flex justify-between items-center mb-2">
								<label class="label">
									<span class="label-text">{{ __('lang.children_count') }}</span>
								</label>
								<x-button wire:click="addChild" icon="o-plus" class="btn-sm btn-primary" spinner="addChild" >
									{{ __('lang.add_child') }}
								</x-button>
							</div>

							@if(count($children_ages) > 0)
								<div class="space-y-2 max-h-64 overflow-y-auto">
									@foreach($children_ages as $index => $age)
										<div class="flex gap-2 items-center" wire:key="child-{{ $index }}">
											<x-input label="{{ __('lang.child') }} {{ $index + 1 }}" wire:model.live="children_ages.{{ $index }}" type="number" min="0" max="18" hint="{{ __('lang.age') }}" icon="o-cake"/>
											<x-button wire:click="removeChild({{ $index }})" icon="o-trash" class="btn-sm btn-error text-white btn-circle mt-6" spinner="removeChild({{ $index }})"/>
										</div>
									@endforeach
								</div>
							@else
								<div class="text-sm text-gray-500">{{ __('lang.no_children') }}</div>
							@endif
						</div>
					</div>

					{{-- Calculate Price Button --}}
					<div class="mt-4">
						<x-button wire:click="calculatePrice" class="btn-primary" spinner="calculatePrice">
							<x-icon name="o-calculator" class="w-5 h-5"/>
							{{ __('lang.calculate_price') }}
						</x-button>
					</div>
				</div>
			@endif

			{{-- Pricing Summary --}}
			@if($pricing_result && $pricing_result['success'])
				<div class="border-b mt-2 pb-4">
					<h3 class="text-lg font-semibold mb-4">
						<x-icon name="o-currency-dollar" class="w-5 h-5 inline"/> {{ __('lang.pricing_summary') }}
					</h3>

					<div class="card bg-base-200">
						<div class="card-body p-3">
							{{-- Room Info --}}
							<div class="flex justify-between items-center mb-4">
								<div>
									<h4 class="font-semibold">{{ $pricing_result['room_name'] }}</h4>
									<p class="text-sm text-gray-600">{{ $pricing_result['hotel_name'] }}</p>
								</div>
								<div class="text-right">
									<p class="text-sm">{{ $pricing_result['nights_count'] }} {{ __('lang.nights') }}</p>
									<p class="text-xs text-gray-500">{{ $pricing_result['check_in'] }} - {{ $pricing_result['check_out'] }}</p>
								</div>
							</div>

							<div class="divider"></div>

							{{-- Adults --}}
							<div class="flex justify-between items-center">
								<span>{{ $pricing_result['adults_count'] }} {{ __('lang.adults') }}</span>
								<span class="font-semibold">{{ number_format($pricing_result['adults_total'], 2) }} {{ $pricing_result['currency'] }}</span>
							</div>

							{{-- Children --}}
							@if($pricing_result['children_count'] > 0)
								<div class="mt-3">
									<p class="font-semibold mb-2">{{ __('lang.children') }}:</p>
									<div class="space-y-2 bg-base-100 p-3 rounded">
										@foreach($pricing_result['children_breakdown'] as $child)
											<div class="flex justify-between items-center text-sm">
												<span>
													{{ __('lang.child') }} {{ $child['child_number'] }}
													<span class="text-xs text-gray-500">({{ $child['age'] }} {{ __('lang.years') }})</span>
													- {{ $child['category_label'] }}
												</span>
												<span class="font-medium">{{ number_format($child['price'], 2) }} {{ $pricing_result['currency'] }}</span>
											</div>
										@endforeach
										<div class="divider my-1"></div>
										<div class="flex justify-between font-semibold">
											<span>{{ __('lang.children_total') }}</span>
											<span>{{ number_format($pricing_result['children_total'], 2) }} {{ $pricing_result['currency'] }}</span>
										</div>
									</div>
								</div>
							@endif

							<div class="divider"></div>

							{{-- Grand Total --}}
							<div class="flex justify-between items-center text-xl font-bold">
								<span>{{ __('lang.grand_total') }}</span>
								<span class="text-primary">{{ number_format($pricing_result['total_price'], 2) }} {{ $pricing_result['currency'] }}</span>
							</div>
						</div>
					</div>
				</div>
			@elseif($pricing_result && !$pricing_result['success'])
				<div class="alert alert-error mt-4">
					<x-icon name="o-exclamation-triangle" class="w-5 h-5"/>
					<span>{{ $pricing_result['error'] }}</span>
					@if(isset($pricing_result['uncovered_dates']))
						<div class="mt-2 text-sm">
							{{ __('lang.uncovered_dates') }}: {{ implode(', ', $pricing_result['uncovered_dates']) }}
						</div>
					@endif
				</div>
			@endif


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
										['id' => null, 'name' => __('lang.select')],
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

			<div class="mt-6 flex justify-end gap-2 px-4 pb-4">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('bookings.hotels')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="save" spinner="save"/>
			</div>
		</form>
	</x-card>
</div>

