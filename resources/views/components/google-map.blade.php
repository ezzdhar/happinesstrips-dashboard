@props([
    'latitude' => null,
    'longitude' => null,
    'addressAr' => null,
    'addressEn' => null,
    'latitudeProperty' => 'latitude',
    'longitudeProperty' => 'longitude',
    'addressArProperty' => 'address_ar',
    'addressEnProperty' => 'address_en',
    'defaultLat' => 27.9158,
    'defaultLng' => 34.3299,
    'height' => '500px',
    'zoom' => 8,
    'mapId' => 'map',
    'searchInputId' => 'pac-input',
])

@once
<script>
    // تهيئة متغيرات Google Maps العامة
    window.googleMapsCallbacks = window.googleMapsCallbacks || [];
    window.googleMapsApiLoaded = false;

    window.initGoogleMapsApi = function() {
        window.googleMapsApiLoaded = true;
        window.googleMapsCallbacks.forEach(callback => {
            try {
                callback();
            } catch(e) {
                console.error('Google Maps callback error:', e);
            }
        });
        window.googleMapsCallbacks = [];
    };

    // تحميل Google Maps API إذا لم يكن محملاً
    if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places,geocoding&callback=initGoogleMapsApi';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    } else if (typeof google !== 'undefined' && google.maps) {
        window.googleMapsApiLoaded = true;
    }
</script>
@endonce

<div wire:ignore x-data="{
        map: null,
        marker: null,
        geocoder: null,
        autocomplete: null,
        infoWindow: null,
        mapId: '{{ $mapId }}',
        searchInputId: '{{ $searchInputId }}',
        lat: $wire.get('{{ $latitudeProperty }}') || {{ $latitude ?? $defaultLat }},
        lng: $wire.get('{{ $longitudeProperty }}') || {{ $longitude ?? $defaultLng }},
        initRetries: 0,
        maxRetries: 50,
        isLocating: false,

        // الدالة الرئيسية لتهيئة الخريطة
        initGoogleMap() {
            // التحقق من تحميل Google Maps API
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                this.initRetries++;

                if (this.initRetries >= this.maxRetries) {
                    console.error('Failed to load Google Maps API after ' + this.maxRetries + ' retries');
                    return;
                }

                // إضافة callback لتنفيذه عند تحميل API
                window.googleMapsCallbacks = window.googleMapsCallbacks || [];
                window.googleMapsCallbacks.push(() => {
                    this.initMap();
                });

                // محاولة مرة أخرى بعد 100ms
                setTimeout(() => this.initGoogleMap(), 100);
                return;
            }

            this.initMap();
        },

        // دالة تهيئة الخريطة الفعلية
        initMap() {
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
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                    position: google.maps.ControlPosition.TOP_RIGHT,
                    mapTypeIds: ['roadmap', 'satellite', 'hybrid']
                },
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                zoomControl: true,
                zoomControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                streetViewControl: true,
                streetViewControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                fullscreenControl: true,
                scrollwheel: true,
                gestureHandling: 'greedy'
            });

            // إنشاء الدبوس (Marker) بالشكل الافتراضي
            this.marker = new google.maps.Marker({
                position: initialPosition,
                map: this.map,
                draggable: true,
                title: '{{ __("lang.drag_to_move") }}',
                animation: google.maps.Animation.DROP
            });

            // تهيئة Geocoder للحصول على العناوين
            this.geocoder = new google.maps.Geocoder();

            // --- تهيئة حقل البحث ---
            const searchInput = document.getElementById(this.searchInputId);
            if (searchInput) {
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
            this.map.addListener('click', (e) => this.updateLocation(e.latLng));
            this.marker.addListener('dragend', (e) => this.updateLocation(e.latLng));

            // إنشاء InfoWindow للعرض
            this.infoWindow = new google.maps.InfoWindow();

            // إنشاء زر الموقع الحالي
            this.createLocationButton();
        },

        // دالة إنشاء زر تحديد الموقع الحالي
        createLocationButton() {
            const locationButton = document.createElement('button');
            locationButton.type = 'button';

            // إنشاء SVG باستخدام createElementNS
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('stroke-width', '2');
            svg.setAttribute('stroke', 'currentColor');
            svg.classList.add('w-5', 'h-5');

            const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path1.setAttribute('stroke-linecap', 'round');
            path1.setAttribute('stroke-linejoin', 'round');
            path1.setAttribute('d', 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z');

            const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path2.setAttribute('stroke-linecap', 'round');
            path2.setAttribute('stroke-linejoin', 'round');
            path2.setAttribute('d', 'M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z');

            svg.appendChild(path1);
            svg.appendChild(path2);
            locationButton.appendChild(svg);

            locationButton.classList.add('bg-white', 'rounded-lg', 'shadow-lg', 'px-3', 'py-2', 'hover:bg-gray-100', 'transition', 'cursor-pointer', 'flex', 'items-center', 'justify-center');
            locationButton.style.margin = '10px';
            locationButton.title = '{{ __("lang.get_current_location") }}';

            this.map.controls[google.maps.ControlPosition.RIGHT_CENTER].push(locationButton);

            locationButton.addEventListener('click', () => {
                this.getCurrentLocation();
            });
        },

        // دالة الحصول على الموقع الحالي
        getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('{{ __("lang.geolocation_not_supported") }}');
                return;
            }

            this.isLocating = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };

                    this.map.setCenter(pos);
                    this.map.setZoom(17);
                    this.updateLocation(new google.maps.LatLng(pos.lat, pos.lng));
                    this.isLocating = false;
                },
                (error) => {
                    console.error('Error getting location:', error);
                    alert('{{ __("lang.location_error") }}');
                    this.isLocating = false;
                }
            );
        },

        // دالة موحدة لتحديث كل شيء
        updateLocation(latLng) {
            this.marker.setPosition(latLng);
            this.marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => this.marker.setAnimation(null), 1400);

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
        <div class="">
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

