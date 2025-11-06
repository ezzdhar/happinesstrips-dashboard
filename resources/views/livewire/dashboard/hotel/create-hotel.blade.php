@php use App\Enums\Status; @endphp
@assets
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets
<div>
	<x-card title="{{ __('lang.add_hotel') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveAdd">
			<div class="max-h-[75vh] overflow-y-auto p-4 space-y-6">

				{{-- Basic Information Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-information-circle" class="w-5 h-5 inline"/> {{ __('lang.basic_information') }}
					</h3>
					<div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
						<x-select label="{{ __('lang.user') }}" wire:model="user_id" placeholder="{{ __('lang.select') }}" icon="o-user" :options="$users" option-label="name"/>
						<x-choices-offline label="{{ __('lang.city') }}" wire:model="city_id" :options="$cities" single clearable searchable
						                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
						<x-input label="{{ __('lang.email') }}" wire:model="email" placeholder="{{ __('lang.email') }}" icon="o-envelope"/>
						<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
						<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
						<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
							['id' => Status::Active, 'name' => __('lang.active')],
							['id' => Status::Inactive, 'name' => __('lang.inactive')],
						]"/>
						<x-select label="{{ __('lang.rating') }}" wire:model="rating" placeholder="{{ __('lang.select') }}" icon="o-star" :options="[
							['id' => 1, 'name' => '1'],
							['id' => 2, 'name' => '2'],
							['id' => 3, 'name' => '3'],
							['id' => 4, 'name' => '4'],
							['id' => 5, 'name' => '5'],
						]"/>
						<x-phone-input
							required
							label="{{__('lang.phone')}}"
							phoneProperty="phone"
							keyProperty="phone_key"
						/>
					</div>
				</div>

				{{-- Location & Map Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-map-pin" class="w-5 h-5 inline"/> {{ __('lang.location_information') }}
					</h3>

					{{-- Start of the new map section --}}
					<div wire:ignore x-data="{
			        map: null,
			        marker: null,
			        geocoder: null,
			        autocomplete: null,
			        // ربط المتغيرات مع Livewire باستخدام $wire
			        lat: $wire.get('latitude'),
			        lng: $wire.get('longitude'),

			        // الدالة الرئيسية لتهيئة الخريطة
			        initGoogleMap() {
			            // التأكد من تحميل مكتبة جوجل
			            if (typeof google === 'undefined') {
			                console.error('Google Maps script not loaded.');
			                return;
			            }

			            // تعيين موقع افتراضي (القاهرة) إذا لم تكن هناك قيم
			           if (!this.lat || !this.lng) {
							    this.lat = 32.8872;
							    this.lng = 13.1913;
							}
			            const initialPosition = { lat: this.lat, lng: this.lng };

			            // إنشاء الخريطة
			             this.map = new google.maps.Map(document.getElementById('map'), {
						    center: initialPosition,
						    zoom: 14,
						    mapTypeControl: true,
						    mapTypeId: google.maps.MapTypeId.HYBRID,
						    scrollwheel: true,      // تفعيل الزووم بالموس
						    gestureHandling: 'auto' // يخلي السحب والزووم بالموس شغال طبيعي
						});

			            // إنشاء الدبوس (Marker)
			            this.marker = new google.maps.Marker({
			                position: initialPosition,
			                map: this.map,
			                draggable: true,
			            });

			            // تهيئة Geocoder للحصول على العناوين
			            this.geocoder = new google.maps.Geocoder();

			            // --- تهيئة حقل البحث ---
			            const searchInput = document.getElementById('pac-input');
			            this.autocomplete = new google.maps.places.Autocomplete(searchInput);
			            this.autocomplete.bindTo('bounds', this.map);

			            // --- إضافة الأحداث (Listeners) ---

			            // حدث عند اختيار مكان من قائمة البحث
			            this.autocomplete.addListener('place_changed', () => {
			                const place = this.autocomplete.getPlace();
			                if (place.geometry && place.geometry.location) {
			                    this.map.setCenter(place.geometry.location);
			                    this.map.setZoom(17);
			                    this.updateLocation(place.geometry.location);
			                }
			            });

			            // حدث عند النقر على الخريطة
			            this.map.addListener('click', (e) => this.updateLocation(e.latLng));

			            // حدث عند الانتهاء من سحب الدبوس
			            this.marker.addListener('dragend', (e) => this.updateLocation(e.latLng));
			        },

			        // دالة موحدة لتحديث كل شيء
			        updateLocation(latLng) {
			            this.marker.setPosition(latLng);
			            const newLat = latLng.lat();
			            const newLng = latLng.lng();

			            // تحديث قيم Livewire
			            $wire.set('latitude', newLat);
			            $wire.set('longitude', newLng);

			            // الحصول على العنوان من الإحداثيات وتحديث Livewire
			            this.geocoder.geocode({ 'location': latLng }, (results, status) => {
			                if (status === 'OK' && results[0]) {
			                    $wire.set('address', results[0].formatted_address);
			                }
			            });
			        }
			    }"
					     x-init="initGoogleMap()"
					>
						{{-- حقل البحث --}}
						{{--				<div class="md:col-span-2">--}}
						{{--					<x-input id="pac-input" label="{{__('lang.search_address')}}" placeholder="{{__('lang.search_for_a_location')}}" />--}}
						{{--				</div>--}}

						{{-- حاوية الخريطة --}}


						{{-- حقول العرض (للقراءة فقط) --}}
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:col-span-2">
							<div class="md:col-span-2">
								<div id="map" style="height: 500px; width: 100%; border-radius: 8px;"></div>
							</div>
							<div>
								<x-input readonly label="{{__('lang.latitude')}}" wire:model.live="latitude"/>
								<x-input readonly label="{{__('lang.longitude')}}" wire:model.live="longitude"/>
								<x-textarea label="{{ __('lang.address').' ('.__('lang.ar').')' }}" wire:model="address_ar" placeholder="{{ __('lang.address').' ('.__('lang.ar').')' }}" rows="3"/>
								<x-textarea label="{{ __('lang.address').' ('.__('lang.en').')' }}" wire:model="address_en" placeholder="{{ __('lang.address').' ('.__('lang.en').')' }}" rows="3"/>
							</div>
						</div>
					</div>
					{{-- End of the new map section --}}
				</div>

				{{-- Facilities Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-building-office" class="w-5 h-5 inline"/> {{ __('lang.facilities') }}
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-textarea label="{{ __('lang.facilities').' ('.__('lang.ar').')' }}" wire:model="facilities_ar" placeholder="{{ __('lang.facilities').' ('.__('lang.ar').')' }}" rows="3"/>
						<x-textarea label="{{ __('lang.facilities').' ('.__('lang.en').')' }}" wire:model="facilities_en" placeholder="{{ __('lang.facilities').' ('.__('lang.en').')' }}" rows="3"/>
					</div>
				</div>

				{{-- Services Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-sparkles" class="w-5 h-5 inline"/> {{ __('lang.include_services') }}
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-textarea label="{{ __('lang.include_services').' ('.__('lang.ar').')' }}" wire:model="include_services_ar" placeholder="{{ __('lang.include_services').' ('.__('lang.ar').')' }}" rows="3"/>
						<x-textarea label="{{ __('lang.include_services').' ('.__('lang.en').')' }}" wire:model="include_services_en" placeholder="{{ __('lang.include_services').' ('.__('lang.en').')' }}" rows="3"/>
					</div>
				</div>

				{{-- Description Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-document-text" class="w-5 h-5 inline"/> {{ __('lang.description') }}
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-textarea label="{{ __('lang.description').' ('.__('lang.ar').')' }}" wire:model="description_ar" placeholder="{{ __('lang.description').' ('.__('lang.ar').')' }}" rows="4"/>
						<x-textarea label="{{ __('lang.description').' ('.__('lang.en').')' }}" wire:model="description_en" placeholder="{{ __('lang.description').' ('.__('lang.en').')' }}" rows="4"/>
					</div>
				</div>

				{{-- Images Section --}}
				<div>
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-photo" class="w-5 h-5 inline"/> {{ __('lang.images') }}
					</h3>
					<x-image-library wire:model="images" wire:library="library" :preview="$library" label="{{__('lang.project_images')}}"/>
				</div>

			</div>



			<div class="mt-6 flex justify-end gap-2 px-4 pb-4">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('hotels')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
			</div>
		</form>

	</x-card>
</div>

@section('script')
	<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places,geocoding&async" async defer></script>
@endsection

