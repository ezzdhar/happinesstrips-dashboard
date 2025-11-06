{{-- Example 1: Basic Usage with Default Values --}}
<x-google-map />

{{-- Example 2: With Initial Values from Livewire Properties --}}
<x-google-map
    :latitude="$latitude"
    :longitude="$longitude"
    :address-ar="$address_ar"
    :address-en="$address_en"
/>

{{-- Example 3: With Custom Property Names --}}
<x-google-map
    :latitude="$hotel_lat"
    :longitude="$hotel_lng"
    latitude-property="hotel_lat"
    longitude-property="hotel_lng"
    address-ar-property="hotel_address_ar"
    address-en-property="hotel_address_en"
/>

{{-- Example 4: Custom Default Location (e.g., Tripoli, Libya) --}}
<x-google-map
    :default-lat="32.8872"
    :default-lng="13.1913"
    height="600px"
    :zoom="15"
/>

{{-- Example 5: Multiple Maps on Same Page --}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <h3>Hotel Location</h3>
        <x-google-map
            :latitude="$hotel_latitude"
            :longitude="$hotel_longitude"
            latitude-property="hotel_latitude"
            longitude-property="hotel_longitude"
            address-ar-property="hotel_address_ar"
            address-en-property="hotel_address_en"
            map-id="hotel-map"
            search-input-id="hotel-search"
            height="400px"
        />
    </div>

    <div>
        <h3>Branch Location</h3>
        <x-google-map
            :latitude="$branch_latitude"
            :longitude="$branch_longitude"
            latitude-property="branch_latitude"
            longitude-property="branch_longitude"
            address-ar-property="branch_address_ar"
            address-en-property="branch_address_en"
            map-id="branch-map"
            search-input-id="branch-search"
            height="400px"
        />
    </div>
</div>

{{-- Example 6: Edit Mode with Pre-filled Values --}}
<x-google-map
    :latitude="$property->latitude"
    :longitude="$property->longitude"
    :address-ar="$property->address['ar']"
    :address-en="$property->address['en']"
    latitude-property="latitude"
    longitude-property="longitude"
    address-ar-property="address_ar"
    address-en-property="address_en"
/>

{{-- Example 7: Different Default Location (Cairo, Egypt) --}}
<x-google-map
    :default-lat="30.0444"
    :default-lng="31.2357"
    :zoom="12"
/>

