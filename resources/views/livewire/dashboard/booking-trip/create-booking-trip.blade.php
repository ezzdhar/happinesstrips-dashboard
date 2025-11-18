@php use App\Enums\Status;use App\Enums\TripType;use Illuminate\Support\Carbon; @endphp

@assets
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/intlTelInput.min.js"></script>
<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
</style>
@endassets
<div>
	<x-card shadow class="mb-3">
		<div class="mx-auto">
			<!-- Progress Steps Indicator -->
			<div class="mb-8">
				<div class="flex items-center justify-between">
					<!-- Step 1 -->
					<div class="flex-1 text-center">
						<div class="relative">
							<div class="flex items-center justify-center">
								<div class="w-12 h-12 rounded-full flex items-center justify-center {{ $currentStep >= 1 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-lg">
									@if($currentStep > 1)
										<x-icon name="o-check" class="w-6 h-6"/>
									@else
										1
									@endif
								</div>
							</div>
							<div class="mt-2 text-sm font-medium {{ $currentStep >= 1 ? 'text-primary' : 'text-gray-500' }}">
								{{ __('lang.client_and_trip') }}
							</div>
						</div>
					</div>

					<!-- Connector Line -->
					<div class="flex-1 h-1 {{ $currentStep >= 2 ? 'bg-primary' : 'bg-gray-200' }} mx-4"></div>

					<!-- Step 2 -->
					<div class="flex-1 text-center">
						<div class="relative">
							<div class="flex items-center justify-center">
								<div class="w-12 h-12 rounded-full flex items-center justify-center {{ $currentStep >= 2 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-lg">
									@if($currentStep > 2)
										<x-icon name="o-check" class="w-6 h-6"/>
									@else
										2
									@endif
								</div>
							</div>
							<div class="mt-2 text-sm font-medium {{ $currentStep >= 2 ? 'text-primary' : 'text-gray-500' }}">
								{{ __('lang.dates_and_people') }}
							</div>
						</div>
					</div>

					<!-- Connector Line -->
					<div class="flex-1 h-1 {{ $currentStep >= 3 ? 'bg-primary' : 'bg-gray-200' }} mx-4"></div>

					<!-- Step 3 -->
					<div class="flex-1 text-center">
						<div class="relative">
							<div class="flex items-center justify-center">
								<div class="w-12 h-12 rounded-full flex items-center justify-center {{ $currentStep >= 3 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-lg">
									@if($showReview)
										<x-icon name="o-check" class="w-6 h-6"/>
									@else
										3
									@endif
								</div>
							</div>
							<div class="mt-2 text-sm font-medium {{ $currentStep >= 3 ? 'text-primary' : 'text-gray-500' }}">
								{{ __('lang.travelers_information') }}
							</div>
						</div>
					</div>

					<!-- Connector Line -->
					<div class="flex-1 h-1 {{ $showReview ? 'bg-primary' : 'bg-gray-200' }} mx-4"></div>

					<!-- Step 4 - Review -->
					<div class="flex-1 text-center">
						<div class="relative">
							<div class="flex items-center justify-center">
								<div class="w-12 h-12 rounded-full flex items-center justify-center {{ $showReview ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-lg">
									<x-icon name="o-document-check" class="w-6 h-6"/>
								</div>
							</div>
							<div class="mt-2 text-sm font-medium {{ $showReview ? 'text-primary' : 'text-gray-500' }}">
								{{ __('lang.review_and_confirm') }}
							</div>
						</div>
					</div>
				</div>
			</div>

			<form wire:submit.prevent="save">
				@if(!$showReview)
					<!-- Step 1: Client & Trip Selection -->
					@if($currentStep == 1)
						<div class="animate-fade-in">
							<div class="bg-base-100 rounded-lg p-6 border border-base-300">
								<h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
									<div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
										<x-icon name="o-user-circle" class="w-6 h-6 text-primary"/>
									</div>
									<span>{{ __('lang.select_client_and_trip') }}</span>
								</h2>

								<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
									<div>
										<x-choices-offline required label="{{ __('lang.user') }}" wire:model="user_id" :options="$users" option-value="id" option-label="name"
										                   option-sub-label="phone" single clearable searchable icon="o-user"/>
									</div>

									<div>
										<x-choices-offline required label="{{ __('lang.trip') }}" wire:model.live="trip_id" :options="$trips" option-value="id" option-label="name" single clearable searchable icon="o-map"/>
									</div>
								</div>

								@if($selectedTrip)
									<div class="mt-6 p-4 bg-info/10 rounded-lg border border-info/30">
										<h3 class="font-semibold mb-3 flex items-center gap-2">
											<x-icon name="o-information-circle" class="w-5 h-5 text-info"/>
											{{ __('lang.trip_details') }}
										</h3>
										<div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
											<div>
												<span class="text-gray-600 dark:text-gray-400">{{ __('lang.trip_type') }}:</span>
												<span class="ml-2 badge badge-{{ $selectedTrip['type'] === 'fixed' ? 'success' : 'warning' }}">
                                                    {{ __('lang.' . $selectedTrip['type']) }}
                                                </span>
											</div>
											<div>
												<span class="text-gray-600 dark:text-gray-400">
													{{ __('lang.base_price') }}:
												</span>
												<span class="ml-2 font-semibold" dir="ltr">
                                                    {{ number_format($selectedTrip['price']['egp'] ?? 0) }} EGP
                                                </span>
												<span class="mx-2">|</span>
												<span class="ml-2 font-semibold" dir="ltr">
                                                    {{ number_format($selectedTrip['price']['usd'] ?? 0) }} USD
                                                </span>
												<div>
													<small>({{ $selectedTrip['type'] === TripType::Flexible ? __('lang.for_one_person_per_night') : __('lang.for_one_person_per_trip') }})</small>
												</div>
											</div>
											@if($selectedTrip['type'] === 'fixed')
												<div>
													<span class="text-gray-600 dark:text-gray-400">{{ __('lang.duration') }}:</span>
													<span class="ml-2 font-semibold">{{ $selectedTrip['duration_from'] }} / {{ $selectedTrip['duration_to'] }}</span>
													<div>
														<span class="text-gray-600 dark:text-gray-400">{{ __('lang.nights_count') }}: {{$selectedTrip['nights_count']}}</span>
													</div>
												</div>
											@endif
										</div>
									</div>
								@endif
							</div>
						</div>
					@endif

					<!-- Step 2: Dates & People -->
					@if($currentStep == 2 && $selectedTrip)
						<div class="animate-fade-in">
							<div class="bg-base-100 rounded-lg p-6 border border-base-300">
								<h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
									<div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
										<x-icon name="o-calendar" class="w-6 h-6 text-primary"/>
									</div>
									<span>{{ __('lang.select_dates_and_travelers') }}</span>
								</h2>

								<!-- Dates Section -->
								<div class="mb-6">
									<h3 class="font-semibold mb-4 text-lg">📅 {{ __('lang.travel_dates') }}</h3>
									<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
										<x-input label="{{ __('lang.check_in') }}" wire:model.live="check_in" type="date" required icon="o-calendar"
										         :min="Carbon::parse($this->selectedTrip['duration_from'])->format('Y-m-d')"
										         :disabled="$this->selectedTrip['type'] == TripType::Fixed"
										         :readonly="$this->selectedTrip['type'] == TripType::Fixed"
										/>
										<x-input label="{{ __('lang.check_out') }}" wire:model.live="check_out" type="date"
										         required :readonly="$this->selectedTrip['type'] == TripType::Fixed"
										         :disabled="$this->selectedTrip['type'] == TripType::Fixed" icon="o-calendar"/>

										<x-input label="{{ __('lang.nights') }}" wire:model="nights_count" type="number"
										         min="1" icon="o-moon" readonly disabled class="bg-base-200"/>
									</div>
								</div>

                                <!-- People Count Section -->
                                <div class="mb-6">
                                    <h3 class="font-semibold mb-4 text-lg">👥 {{ __('lang.number_of_travelers') }}</h3>

                                    <!-- Adults Count -->
                                    <div class="mb-4">
                                        <div class="p-4 bg-primary/5 rounded-lg border border-primary/20">
                                            <x-input
                                                    label="{{ __('lang.adults') }}"
                                                    wire:model.live="adults_count"
                                                    type="number"
                                                    min="1"
                                                    icon="o-user"
                                                    required
                                            />
                                            <p class="text-xs text-gray-500 mt-1">{{ __('lang.adults_description') }}</p>
                                        </div>
                                    </div>

                                    <!-- Children Ages Section -->
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="font-semibold text-sm">{{ __('lang.children') }}</label>
                                            <x-button wire:click="addChild" icon="o-plus" class="btn-sm btn-primary" label="{{ __('lang.add_child') }}" spinner="addChild"/>
                                        </div>

                                        @if(count($children_ages) > 0)
                                            <div class="space-y-2">
                                                @foreach($children_ages as $index => $age)
                                                    <div class="flex gap-2 items-center p-3 bg-info/5 rounded-lg border border-info/20">
                                                        <div class="flex-1">
                                                            <x-input label="{{ __('lang.child') }} {{ $index + 1 }} - {{ __('lang.age') }}" wire:model.live="children_ages.{{ $index }}" type="number"
                                                                     min="0" max="18" icon="o-cake" required hint="{{__('lang.child_age')}}"/>
                                                        </div>
                                                        <x-button wire:click="removeChild({{ $index }})" icon="o-trash" class="btn-sm btn-error btn-outline mt-6" spinner="removeChild({{$index }})"/>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="p-4 bg-base-100 rounded-lg border-2 border-dashed border-base-300 text-center">
                                                <p class="text-sm text-gray-500">{{ __('lang.no_children_added') }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ __('lang.click_add_child_button') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    @if($selectedTrip)
                                        <div class="mt-3 p-3 bg-info/10 rounded-lg border border-info/30">
                                            <p class="text-xs text-info flex items-center gap-2">
                                                <x-icon name="o-information-circle" class="w-4 h-4"/>
                                                <span>{{ __('lang.children_pricing_note', [
                                                    'free_age' => $selectedTrip['free_child_age'] ?? 5,
                                                    'adult_age' => $selectedTrip['adult_age'] ?? 12
                                                ]) }}</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>

								<!-- Currency Section -->
								<div>
									<h3 class="font-semibold mb-4 text-lg">💰 {{ __('lang.currency') }}</h3>
									<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
										<label class="cursor-pointer">
											<input type="radio" wire:model.live="currency" value="egp" class="radio radio-primary"/>
											<span class="ml-3 text-lg">🇪🇬 EGP (جنيه مصري)</span>
										</label>
										<label class="cursor-pointer">
											<input type="radio" wire:model.live="currency" value="usd" class="radio radio-primary"/>
											<span class="ml-3 text-lg">🇺🇸 USD (دولار أمريكي)</span>
										</label>
									</div>
								</div>

                                <!-- Price Preview -->
                                @if($total_price > 0)
                                    <div class="mt-6 p-4 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-lg border-2 border-primary/30">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('lang.estimated_total') }}:</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $adults_count }} {{ __('lang.adults') }}
                                                    @if(count($children_ages) > 0)
                                                        + {{ count($children_ages) }} {{ __('lang.children') }}
                                                    @endif
                                                    @if($selectedTrip['type'] === 'flexible')
                                                        × {{ $nights_count }} {{ __('lang.nights') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-3xl font-bold text-primary">{{ number_format($total_price, 2) }}</p>
                                                <p class="text-sm text-gray-600">{{ strtoupper($currency) }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-primary/20">
                                            <p class="text-xs text-gray-500">
                                                <x-icon name="o-information-circle" class="w-3 h-3 inline"/>
                                                {{ __('lang.final_price_based_on_ages') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
							</div>
						</div>
					@endif

					<!-- Step 3: Travelers Information -->
					@if($currentStep == 3 && count($travelers) > 0)
						<div class="animate-fade-in">
							<div class="bg-base-100 rounded-lg p-6 border border-base-300">
								<h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
									<div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
										<x-icon name="o-users" class="w-6 h-6 text-primary"/>
									</div>
									<span>{{ __('lang.enter_travelers_details') }}</span>
									<span class="badge badge-primary badge-lg ml-auto">{{ count($travelers) }} {{ __('lang.travelers') }}</span>
								</h2>

								<div class="space-y-4">
									@foreach($travelers as $index => $traveler)
										<div class="border-2 border-base-300 rounded-lg p-5 hover:border-primary/50 transition-all">
											<div class="flex items-center justify-between mb-4">
												<h3 class="font-bold text-lg flex items-center gap-2">
													@if($traveler['type'] === 'adult')
														<x-icon name="o-user" class="w-5 h-5 text-primary"/>
														<x-badge value="{{ __('lang.adult') }} {{ $loop->iteration }}" class="badge-primary badge-lg"/>
													@else
														<x-icon name="o-user-group" class="w-5 h-5 text-info"/>
														<x-badge value="{{ __('lang.child') }} {{ $loop->iteration - $adults_count }}" class="badge-info badge-lg"/>
													@endif
												</h3>
											</div>

											<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
												<x-input
														label="{{ __('lang.full_name') }}"
														wire:model="travelers.{{ $index }}.full_name"
														required
														icon="o-user"
														placeholder="{{ __('lang.enter_full_name') }}"
												/>

												<x-phone-input required label="{{__('lang.phone')}}" phoneProperty="travelers.{{ $index }}.phone" keyProperty="travelers.{{ $index }}.phone_key"/>


												<x-input
														label="{{ __('lang.nationality') }}"
														wire:model="travelers.{{ $index }}.nationality"
														required
														icon="o-flag"
														placeholder="{{ __('lang.nationality') }}"
												/>

												<x-input
														label="{{ __('lang.age') }}"
														wire:model.live="travelers.{{ $index }}.age"
														type="number"
														min="1"
														required
														icon="o-calendar"
												/>

												<x-select
														label="{{ __('lang.id_type') }}"
														wire:model="travelers.{{ $index }}.id_type"
														:options="[
                                                        ['id' => 'passport', 'name' => __('lang.passport')],
                                                        ['id' => 'national_id', 'name' => __('lang.national_id')]
                                                    ]"
														required
												/>

												<x-input
														label="{{ __('lang.id_number') }}"
														wire:model="travelers.{{ $index }}.id_number"
														required
														icon="o-identification"
														placeholder="{{ __('lang.id_number') }}"
												/>
											</div>
										</div>
									@endforeach
								</div>

								<!-- Notes Section -->
								<div class="mt-6 p-4 bg-base-200 rounded-lg">
									<h3 class="font-semibold mb-3 flex items-center gap-2">
										<x-icon name="o-document-text" class="w-5 h-5"/>
										{{ __('lang.additional_notes') }}
									</h3>
									<x-textarea
											wire:model="notes"
											placeholder="{{ __('lang.add_notes_here') }}"
											rows="3"
									/>
								</div>
							</div>
						</div>
					@endif

				@else
					<!-- Review Page -->
					<div class="animate-fade-in">
						<div class="bg-gradient-to-br from-primary/5 to-secondary/5 rounded-lg p-6 border-2 border-primary/30">
							<h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
								<div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center">
									<x-icon name="o-document-check" class="w-7 h-7 text-white"/>
								</div>
								<span>{{ __('lang.review_booking_details') }}</span>
							</h2>

							<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
								<!-- Left Column -->
								<div class="space-y-4">
									<!-- Client & Trip Info -->
									<div class="bg-white dark:bg-base-100 rounded-lg p-5 border border-base-300">
										<div class="flex justify-between items-start mb-4">
											<h3 class="font-bold text-lg flex items-center gap-2">
												<x-icon name="o-map" class="w-5 h-5 text-primary"/>
												{{ __('lang.trip_information') }}
											</h3>
											<x-button icon="o-pencil" wire:click="editStep(1)" class="btn-ghost btn-sm"/>
										</div>
										<div class="space-y-3">
											<div>
												<p class="text-sm text-gray-500">{{ __('lang.client') }}</p>
												<p class="font-semibold">{{ collect($users)->firstWhere('id', $user_id)['name'] ?? '-' }}</p>
											</div>
											<div>
												<p class="text-sm text-gray-500">{{ __('lang.trip') }}</p>
												<p class="font-semibold">{{ $selectedTrip['name'] ?? '-' }}</p>
												<x-badge :value="__('lang.' . ($selectedTrip['type'] ?? 'fixed'))" :class="($selectedTrip['type'] ?? 'fixed') === 'fixed' ? 'badge-success' : 'badge-warning'"/>
											</div>
										</div>
									</div>

									<!-- Dates & People Info -->
									<div class="bg-white dark:bg-base-100 rounded-lg p-5 border border-base-300">
										<div class="flex justify-between items-start mb-4">
											<h3 class="font-bold text-lg flex items-center gap-2">
												<x-icon name="o-calendar" class="w-5 h-5 text-primary"/>
												{{ __('lang.dates_and_travelers') }}
											</h3>
											<x-button icon="o-pencil" wire:click="editStep(2)" class="btn-ghost btn-sm"/>
										</div>
										<div class="space-y-3">
											<div class="grid grid-cols-2 gap-3">
												<div>
													<p class="text-sm text-gray-500">{{ __('lang.check_in') }}</p>
													<p class="font-semibold">{{ \Carbon\Carbon::parse($check_in)->format('d M Y') }}</p>
												</div>
												<div>
													<p class="text-sm text-gray-500">{{ __('lang.check_out') }}</p>
													<p class="font-semibold">{{ \Carbon\Carbon::parse($check_out)->format('d M Y') }}</p>
												</div>
											</div>
											<div>
												<p class="text-sm text-gray-500">{{ __('lang.duration') }}</p>
												<p class="font-semibold">{{ $nights_count }} {{ __('lang.nights') }}</p>
											</div>
                                            <div class="pt-3 border-t">
                                                <div class="grid grid-cols-2 gap-2 text-center">
                                                    <div class="p-2 bg-primary/10 rounded">
                                                        <p class="text-2xl font-bold text-primary">{{ $adults_count }}</p>
                                                        <p class="text-xs text-gray-600">{{ __('lang.adults') }}</p>
                                                    </div>
                                                    @if(count($children_ages) > 0)
                                                        <div class="p-2 bg-info/10 rounded">
                                                            <p class="text-2xl font-bold text-info">{{ count($children_ages) }}</p>
                                                            <p class="text-xs text-gray-600">{{ __('lang.children') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
										</div>
									</div>

									<!-- Travelers List -->
									<div class="bg-white dark:bg-base-100 rounded-lg p-5 border border-base-300">
										<div class="flex justify-between items-start mb-4">
											<h3 class="font-bold text-lg flex items-center gap-2">
												<x-icon name="o-users" class="w-5 h-5 text-primary"/>
												{{ __('lang.travelers_list') }}
												<span class="badge badge-primary">{{ count($travelers) }}</span>
											</h3>
											<x-button icon="o-pencil" wire:click="editStep(3)" class="btn-ghost btn-sm"/>
										</div>
										<div class="space-y-2 max-h-96 overflow-y-auto">
											@foreach($travelers as $index => $traveler)
												<div class="p-3 bg-base-50 rounded border border-base-200 hover:border-primary/30 transition-all">
													<div class="flex items-center gap-3">
														<div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary">
															{{ $index + 1 }}
														</div>
														<div class="flex-1">
															<p class="font-semibold">{{ $traveler['full_name'] }}</p>
															<p class="text-xs text-gray-500">
																{{ $traveler['age'] }} {{ __('lang.years') }} • {{ $traveler['nationality'] }} • {{ $traveler['phone_key'] }}{{ $traveler['phone'] }}
															</p>
														</div>
														@if($index < $adults_count)
															<x-badge value="{{ __('lang.adult') }}" class="badge-primary badge-sm"/>
														@else
															<x-badge value="{{ __('lang.child') }}" class="badge-warning badge-sm"/>
														@endif
													</div>
												</div>
											@endforeach
										</div>
									</div>
								</div>

								<!-- Right Column - Price Summary -->
								<div class="space-y-4">
									<div class="bg-white dark:bg-base-100 rounded-lg p-5 border-2 border-primary/50 sticky top-6">
										<h3 class="font-bold text-xl mb-4 flex items-center gap-2">
											<x-icon name="o-calculator" class="w-6 h-6 text-primary"/>
											{{ __('lang.price_breakdown') }}
										</h3>

										<div class="space-y-3 mb-4">
                                            <div class="flex justify-between items-center pb-2 border-b">
                                                <span class="text-gray-600">{{ __('lang.base_price') }}</span>
                                                <span class="font-semibold">{{ number_format($sub_total, 2) }} {{ strtoupper($currency) }}</span>
                                            </div>

                                            <div class="flex justify-between items-center pb-2 border-b">
                                                <span class="text-gray-600">{{ __('lang.total_travelers') }}</span>
                                                <span class="font-semibold">{{ $adults_count + count($children_ages) }}</span>
                                            </div>

                                            @if($selectedTrip['type'] === 'flexible')
                                                <div class="flex justify-between items-center pb-2 border-b">
                                                    <span class="text-gray-600">{{ __('lang.nights') }}</span>
                                                    <span class="font-semibold">{{ $nights_count }}</span>
                                                </div>
                                            @endif

                                            <div class="p-3 bg-info/10 rounded-lg border border-info/20">
                                                <p class="text-xs text-info mb-1 flex items-center gap-1">
                                                    <x-icon name="o-information-circle" class="w-3 h-3"/>
                                                    {{ __('lang.pricing_details') }}:
                                                </p>
                                                <p class="text-sm">
                                                    {{ __('lang.price_calculated_by_ages') }}
                                                </p>
                                            </div>
										</div>

										<div class="pt-4 border-t-2 border-primary">
											<div class="flex justify-between items-center mb-2">
												<span class="text-xl font-bold">{{ __('lang.total_amount') }}</span>
												<div class="text-right">
													<p class="text-3xl font-bold text-primary">{{ number_format($total_price, 2) }}</p>
													<p class="text-sm text-gray-500">{{ strtoupper($currency) }}</p>
												</div>
											</div>
										</div>

										@if($notes)
											<div class="mt-4 p-3 bg-warning/10 rounded-lg border border-warning/30">
												<p class="text-xs font-semibold text-warning mb-1">{{ __('lang.notes') }}:</p>
												<p class="text-sm">{{ $notes }}</p>
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				@endif

				<!-- Navigation Buttons -->
				<div class="mt-8 flex justify-between items-center">
					<div>
						@if($currentStep > 1 || $showReview)
							<x-button type="button" wire:click="previousStep" label="{{ __('lang.previous') }}" icon="o-arrow-left" class="btn-outline"/>
						@else
							<x-button label="{{ __('lang.cancel') }}" icon="o-x-mark" link="{{ route('bookings.trips') }}" class="btn-ghost"/>
						@endif
					</div>

					<div class="flex gap-2">
						@if(!$showReview)
							@if($currentStep < 3 || ($currentStep == 3 && count($travelers) == 0))
								<x-button type="button" spinner wire:click="nextStep" label="{{ __('lang.next') }}" icon="o-arrow-right" class="btn-primary" :disabled="!$selectedTrip && $currentStep == 1"/>
							@else
								<x-button type="button" spinner wire:click="nextStep" label="{{ __('lang.review_booking') }}" icon="o-document-check" class="btn-primary"/>
							@endif
						@else
							<x-button type="submit" label="{{ __('lang.confirm_and_save') }}" icon="o-check-circle" class="btn-success btn-lg" spinner="save"/>
						@endif
					</div>
				</div>
			</form>
		</div>
	</x-card>
</div>


