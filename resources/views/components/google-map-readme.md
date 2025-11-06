# Google Map Component Documentation

## Overview
A reusable Blade component for integrating Google Maps with Livewire, supporting bilingual address geocoding (Arabic & English) and interactive location selection.

## Component Location
`resources/views/components/google-map.blade.php`

## Prerequisites
- Google Maps API key configured in `config/services.php`
- Include the Google Maps script in your layout or page:
```blade
@section('script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places,geocoding" async defer></script>
@endsection
```

## Basic Usage

```blade
<x-google-map
    :latitude="$latitude"
    :longitude="$longitude"
    :address-ar="$address_ar"
    :address-en="$address_en"
/>
```

## Available Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `latitude` | float/null | null | Initial latitude value |
| `longitude` | float/null | null | Initial longitude value |
| `addressAr` | string/null | null | Initial Arabic address |
| `addressEn` | string/null | null | Initial English address |
| `latitudeProperty` | string | 'latitude' | Livewire property name for latitude |
| `longitudeProperty` | string | 'longitude' | Livewire property name for longitude |
| `addressArProperty` | string | 'address_ar' | Livewire property name for Arabic address |
| `addressEnProperty` | string | 'address_en' | Livewire property name for English address |
| `defaultLat` | float | 32.8872 | Default latitude (Libya) |
| `defaultLng` | float | 13.1913 | Default longitude (Libya) |
| `height` | string | '500px' | Map container height |
| `zoom` | int | 14 | Initial map zoom level |
| `mapId` | string | 'map' | Unique ID for the map container |
| `searchInputId` | string | 'pac-input' | Unique ID for the search input |

## Full Example

```blade
{{-- In your Livewire component view --}}
<x-google-map
    :latitude="$latitude"
    :longitude="$longitude"
    :address-ar="$address_ar"
    :address-en="$address_en"
    latitude-property="latitude"
    longitude-property="longitude"
    address-ar-property="address_ar"
    address-en-property="address_en"
    :default-lat="32.8872"
    :default-lng="13.1913"
    height="600px"
    :zoom="15"
    map-id="hotel-map"
    search-input-id="hotel-search"
/>
```

## Multiple Maps on Same Page

When using multiple maps on the same page, ensure unique IDs:

```blade
{{-- First map --}}
<x-google-map
    :latitude="$hotel_latitude"
    :longitude="$hotel_longitude"
    latitude-property="hotel_latitude"
    longitude-property="hotel_longitude"
    map-id="hotel-map"
    search-input-id="hotel-search"
/>

{{-- Second map --}}
<x-google-map
    :latitude="$branch_latitude"
    :longitude="$branch_longitude"
    latitude-property="branch_latitude"
    longitude-property="branch_longitude"
    map-id="branch-map"
    search-input-id="branch-search"
/>
```

## Livewire Component Requirements

Your Livewire component should have these public properties:

```php
public $latitude;
public $longitude;
public $address_ar;
public $address_en;
```

Or custom property names matching what you pass to the component.

## Features

✅ Interactive map with draggable marker
✅ Click to place marker
✅ Search location with autocomplete
✅ Auto-geocoding to Arabic and English addresses
✅ Responsive design
✅ Read-only lat/lng display
✅ Editable address fields
✅ Dark mode support
✅ Reusable across multiple pages

## Example Use Cases

- Hotels location management
- Properties/Real estate listings
- Trip destinations
- Store/Branch locations
- Event venues
- User addresses

## Customization

You can customize the map appearance by modifying the component file or passing different props for:
- Map type (currently set to HYBRID)
- Zoom controls
- Map height
- Default coordinates
- Input styling

