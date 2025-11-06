@props([
    'latitude' => null,
    'longitude' => null,
    'addressAr' => null,
    'addressEn' => null,
    'latitudeProperty' => 'latitude',
    'longitudeProperty' => 'longitude',
    'addressArProperty' => 'address_ar',
    'addressEnProperty' => 'address_en',
    'defaultLat' => 30.0444,
    'defaultLng' => 31.2357,
    'height' => '500px',
    'zoom' => 8,
    'mapId' => 'map',
    'searchInputId' => 'pac-input',
])
@assets()
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places,geocoding" async defer></script>
@endassets
<div wire:ignore x-data="{
        map: null,
        marker: null,
        geocoder: null,
        autocomplete: null,
        mapId: '{{ $mapId }}',
        searchInputId: '{{ $searchInputId }}',
        // ربط المتغيرات مع Livewire
        lat: $wire.get('{{ $latitudeProperty }}') || {{ $latitude ?? $defaultLat }},
        lng: $wire.get('{{ $longitudeProperty }}') || {{ $longitude ?? $defaultLng }},

        // الدالة الرئيسية لتهيئة الخريطة
        initGoogleMap() {
            // التأكد من تحميل مكتبة جوجل
            if (typeof google === 'undefined') {
                console.error('Google Maps script not loaded.');
                return;
            }

            // تعيين موقع افتراضي إذا لم تكن هناك قيم
            if (!this.lat || !this.lng) {
                this.lat = {{ $defaultLat }};
                this.lng = {{ $defaultLng }};
            }
            const initialPosition = { lat: parseFloat(this.lat), lng: parseFloat(this.lng) };

            // إنشاء الخريطة
            this.map = new google.maps.Map(document.getElementById(this.mapId), {
                center: initialPosition,
                zoom: {{ $zoom }},
                mapTypeControl: true,
                mapTypeId: google.maps.MapTypeId.HYBRID,
                scrollwheel: true,
                gestureHandling: 'auto'
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
            const searchInput = document.getElementById(this.searchInputId);
            if (searchInput) {
                // إزالة class hidden لإظهار الحقل
                searchInput.classList.remove('hidden');

                this.autocomplete = new google.maps.places.Autocomplete(searchInput);
                this.autocomplete.bindTo('bounds', this.map);

                // إضافة حقل البحث للخريطة
                this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(searchInput);

                // حدث عند اختيار مكان من قائمة البحث
                this.autocomplete.addListener('place_changed', () => {
                    const place = this.autocomplete.getPlace();
                    if (place.geometry && place.geometry.location) {
                        this.map.setCenter(place.geometry.location);
                        this.map.setZoom(17);
                        this.updateLocation(place.geometry.location);
                    }
                });
            }

            // --- إضافة الأحداث (Listeners) ---

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
            $wire.set('{{ $latitudeProperty }}', newLat);
            $wire.set('{{ $longitudeProperty }}', newLng);

            // الحصول على العنوان بالإنجليزية
            this.geocoder.geocode({ 'location': latLng, 'language': 'en' }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    $wire.set('{{ $addressEnProperty }}', results[0].formatted_address);
                }
            });

            // الحصول على العنوان بالعربية
            this.geocoder.geocode({ 'location': latLng, 'language': 'ar' }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    $wire.set('{{ $addressArProperty }}', results[0].formatted_address);
                }
            });
        }
    }"
     x-init="initGoogleMap()"
>
    {{-- حقل البحث (سيتم إضافته للخريطة تلقائياً عبر JavaScript) --}}
    <input
        id="{{ $searchInputId }}"
        type="text"
        placeholder="{{__('lang.search_for_a_location')}}"
        class="hidden pac-input"
        style="margin-top: 10px; margin-left: 10px; width: 300px; height: 40px; padding: 0 12px; border-radius: 6px; border: 1px solid #ddd; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-size: 14px; background-color: #fff;"
    />

    {{-- حاوية الخريطة --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <div id="{{ $mapId }}" style="height: {{ $height }}; width: 100%; border-radius: 8px;"></div>
        </div>

        {{-- حقول العرض --}}
        <div class="space-y-4">
            <x-input readonly label="{{__('lang.latitude')}}" wire:model.live="{{ $latitudeProperty }}"/>
            <x-input readonly label="{{__('lang.longitude')}}" wire:model.live="{{ $longitudeProperty }}"/>
            <x-textarea label="{{ __('lang.address').' ('.__('lang.ar').')' }}" wire:model="{{ $addressArProperty }}" placeholder="{{ __('lang.address').' ('.__('lang.ar').')' }}" rows="3"/>
            <x-textarea label="{{ __('lang.address').' ('.__('lang.en').')' }}" wire:model="{{ $addressEnProperty }}" placeholder="{{ __('lang.address').' ('.__('lang.en').')' }}" rows="3"/>
        </div>
    </div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places,geocoding" async defer></script>

