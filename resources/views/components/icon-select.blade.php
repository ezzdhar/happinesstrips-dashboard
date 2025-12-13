@props([
    'label' => 'Icon',
    'wire:model' => null,
    'wireModel' => null,
    'value' => '',
    'placeholder' => 'Select Icon',
    'hint' => null,
    'required' => false,
    'error' => null,
])

@php
    $wireModelValue = $attributes->wire('model')->value() ?? $wireModel;
    $currentValue = $value;
    
    // Get all available icons
    $icons = [];
    $solidIcons = [
        'house' => 'منزل - House',
        'home' => 'بيت - Home',
        'user' => 'مستخدم - User',
        'users' => 'مستخدمين - Users',
        'heart' => 'قلب - Heart',
        'star' => 'نجمة - Star',
        'check' => 'صح - Check',
        'xmark' => 'خطأ - X Mark',
        'circle' => 'دائرة - Circle',
        'square' => 'مربع - Square',
        'bed' => 'سرير - Bed',
        'couch' => 'أريكة - Couch',
        'bath' => 'حمام - Bath',
        'shower' => 'دش - Shower',
        'toilet' => 'مرحاض - Toilet',
        'door-open' => 'باب مفتوح - Door Open',
        'door-closed' => 'باب مغلق - Door Closed',
        'sink' => 'حوض - Sink',
        'kitchen-set' => 'مطبخ - Kitchen',
        'chair' => 'كرسي - Chair',
        'table' => 'طاولة - Table',
        'lamp' => 'مصباح - Lamp',
        'lightbulb' => 'لمبة - Lightbulb',
        'plug' => 'قابس - Plug',
        'utensils' => 'أدوات مائدة - Utensils',
        'fork-knife' => 'شوكة وسكين - Fork Knife',
        'plate-wheat' => 'طبق - Plate',
        'bowl-food' => 'وعاء طعام - Bowl',
        'mug-hot' => 'كوب ساخن - Hot Mug',
        'coffee' => 'قهوة - Coffee',
        'wine-glass' => 'كأس - Wine Glass',
        'wine-bottle' => 'زجاجة نبيذ - Wine Bottle',
        'martini-glass' => 'كأس مارتيني - Martini',
        'blender' => 'خلاط - Blender',
        'ice-cream' => 'آيس كريم - Ice Cream',
        'pizza-slice' => 'بيتزا - Pizza',
        'burger' => 'برجر - Burger',
        'cake-candles' => 'كيك - Cake',
        'wifi' => 'واي فاي - WiFi',
        'tv' => 'تلفاز - TV',
        'laptop' => 'لابتوب - Laptop',
        'desktop' => 'كمبيوتر - Desktop',
        'mobile' => 'موبايل - Mobile',
        'mobile-screen' => 'شاشة موبايل - Mobile Screen',
        'tablet' => 'تابلت - Tablet',
        'keyboard' => 'كيبورد - Keyboard',
        'mouse' => 'ماوس - Mouse',
        'headphones' => 'سماعات - Headphones',
        'microphone' => 'ميكروفون - Microphone',
        'camera' => 'كاميرا - Camera',
        'video' => 'فيديو - Video',
        'gamepad' => 'ألعاب - Gamepad',
        'music' => 'موسيقى - Music',
        'volume-high' => 'صوت عالي - Volume High',
        'snowflake' => 'تكييف - AC/Snowflake',
        'fan' => 'مروحة - Fan',
        'fire' => 'نار - Fire',
        'fire-flame-curved' => 'لهب - Flame',
        'temperature-high' => 'حرارة عالية - Hot',
        'temperature-low' => 'حرارة منخفضة - Cold',
        'temperature-half' => 'حرارة متوسطة - Temperature',
        'sun' => 'شمس - Sun',
        'moon' => 'قمر - Moon',
        'cloud' => 'سحابة - Cloud',
        'wind' => 'رياح - Wind',
        'water' => 'ماء - Water',
        'lock' => 'قفل - Lock',
        'unlock' => 'مفتوح - Unlock',
        'key' => 'مفتاح - Key',
        'shield' => 'حماية - Shield',
        'shield-halved' => 'درع - Shield Half',
        'user-shield' => 'حماية المستخدم - User Shield',
        'bell' => 'جرس - Bell',
        'bell-slash' => 'جرس مكتوم - Bell Off',
        'eye' => 'عين - Eye',
        'eye-slash' => 'عين مخفية - Eye Hidden',
        'fingerprint' => 'بصمة - Fingerprint',
        'swimming-pool' => 'مسبح - Pool',
        'umbrella-beach' => 'شاطئ - Beach',
        'tree' => 'شجرة - Tree',
        'mountain' => 'جبل - Mountain',
        'tent' => 'خيمة - Tent',
        'campground' => 'مخيم - Campground',
        'dumbbell' => 'جيم - Gym',
        'person-running' => 'جري - Running',
        'person-swimming' => 'سباحة - Swimming',
        'person-biking' => 'دراجة - Biking',
        'person-hiking' => 'تسلق - Hiking',
        'spa' => 'سبا - Spa',
        'volleyball' => 'كرة طائرة - Volleyball',
        'football' => 'كرة قدم - Football',
        'basketball' => 'كرة سلة - Basketball',
        'car' => 'سيارة - Car',
        'car-side' => 'سيارة جانبية - Car Side',
        'taxi' => 'تاكسي - Taxi',
        'bus' => 'باص - Bus',
        'truck' => 'شاحنة - Truck',
        'van-shuttle' => 'فان - Van',
        'motorcycle' => 'دراجة نارية - Motorcycle',
        'bicycle' => 'دراجة - Bicycle',
        'parking' => 'موقف سيارات - Parking',
        'square-parking' => 'موقف - Parking Square',
        'road' => 'طريق - Road',
        'traffic-light' => 'إشارة مرور - Traffic Light',
        'elevator' => 'مصعد - Elevator',
        'wheelchair' => 'كرسي متحرك - Wheelchair',
        'wheelchair-move' => 'كرسي متحرك متحرك - Wheelchair Moving',
        'person-walking-with-cane' => 'عصا - Walking Cane',
        'universal-access' => 'وصول شامل - Universal Access',
        'baby' => 'طفل - Baby',
        'baby-carriage' => 'عربة أطفال - Baby Carriage',
        'child' => 'طفل - Child',
        'paw' => 'حيوانات أليفة - Paw',
        'dog' => 'كلب - Dog',
        'cat' => 'قطة - Cat',
        'fish' => 'سمك - Fish',
        'horse' => 'حصان - Horse',
        'dove' => 'حمامة - Dove',
        'broom' => 'تنظيف - Broom',
        'soap' => 'صابون - Soap',
        'pump-soap' => 'صابون سائل - Liquid Soap',
        'spray-can' => 'بخاخ - Spray',
        'sponge' => 'إسفنجة - Sponge',
        'shirt' => 'قميص - Shirt',
        'cart-shopping' => 'عربة تسوق - Shopping Cart',
        'bag-shopping' => 'حقيبة تسوق - Shopping Bag',
        'basket-shopping' => 'سلة تسوق - Shopping Basket',
        'store' => 'متجر - Store',
        'credit-card' => 'بطاقة ائتمان - Credit Card',
        'money-bill' => 'نقود - Money',
        'coins' => 'عملات - Coins',
        'wallet' => 'محفظة - Wallet',
        'receipt' => 'فاتورة - Receipt',
        'tag' => 'علامة - Tag',
        'tags' => 'علامات - Tags',
        'gift' => 'هدية - Gift',
        'percent' => 'نسبة مئوية - Percent',
        'phone' => 'هاتف - Phone',
        'envelope' => 'بريد - Envelope',
        'comment' => 'تعليق - Comment',
        'message' => 'رسالة - Message',
        'briefcase' => 'حقيبة عمل - Briefcase',
        'building' => 'مبنى - Building',
        'file' => 'ملف - File',
        'folder' => 'مجلد - Folder',
        'calendar' => 'تقويم - Calendar',
        'book' => 'كتاب - Book',
        'graduation-cap' => 'قبعة تخرج - Graduation Cap',
        'school' => 'مدرسة - School',
        'image' => 'صورة - Image',
        'play' => 'تشغيل - Play',
        'pause' => 'إيقاف مؤقت - Pause',
        'clock' => 'ساعة - Clock',
        'location-dot' => 'موقع - Location',
        'map' => 'خريطة - Map',
        'compass' => 'بوصلة - Compass',
        'globe' => 'كرة أرضية - Globe',
        'crown' => 'تاج - Crown',
        'gem' => 'جوهرة - Gem',
        'award' => 'جائزة - Award',
        'trophy' => 'كأس - Trophy',
        'medal' => 'ميدالية - Medal',
        'flag' => 'علم - Flag',
        'magnifying-glass' => 'بحث - Search',
        'download' => 'تحميل - Download',
        'upload' => 'رفع - Upload',
        'share' => 'مشاركة - Share',
        'print' => 'طباعة - Print',
        'trash' => 'حذف - Trash',
        'rocket' => 'صاروخ - Rocket',
    ];
    
    foreach ($solidIcons as $icon => $name) {
        $icons[] = ['id' => "fas fa-{$icon}", 'name' => $name];
    }
    
    $selectedIcon = collect($icons)->firstWhere('id', $currentValue);
@endphp

<div class="form-control w-full" x-data="{ open: false, search: '' }">
    @if($label)
        <label class="label">
            <span class="label-text">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif
    
    <!-- Custom Select Button -->
    <div class="relative" @click.away="open = false">
        <button 
            type="button"
            @click="open = !open"
            class="select select-bordered w-full text-right flex items-center justify-between gap-2 {{ $error ? 'select-error' : '' }}"
        >
            <span class="flex items-center gap-2 flex-1">
                @if($currentValue && $selectedIcon)
                    <i class="{{ $currentValue }}" style="font-size: 1.2rem;"></i>
                    <span>{{ $selectedIcon['name'] }}</span>
                @else
                    <span class="text-base-content/50">{{ $placeholder }}</span>
                @endif
            </span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <!-- Dropdown Menu -->
        <div 
            x-show="open" 
            x-transition
            class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-80 overflow-hidden"
        >
            <!-- Search Input -->
            <div class="p-2 border-b border-base-300 sticky top-0 bg-base-100">
                <input 
                    type="text" 
                    x-model="search"
                    placeholder="بحث... Search..."
                    class="input input-sm input-bordered w-full"
                >
            </div>
            
            <!-- Options List -->
            <div class="overflow-y-auto max-h-64">
                @foreach($icons as $iconOption)
                    <button
                        type="button"
                        @if($wireModelValue)
                            wire:click="$set('{{ $wireModelValue }}', '{{ $iconOption['id'] }}')"
                        @endif
                        @click="open = false"
                        x-show="search === '' || '{{ strtolower($iconOption['name']) }}'.includes(search.toLowerCase())"
                        class="w-full px-3 py-2 text-right hover:bg-base-200 flex items-center gap-3 transition-colors {{ $currentValue === $iconOption['id'] ? 'bg-primary/10' : '' }}"
                    >
                        <i class="{{ $iconOption['id'] }}" style="font-size: 1.2rem;"></i>
                        <span class="flex-1">{{ $iconOption['name'] }}</span>
                        @if($currentValue === $iconOption['id'])
                            <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    
    @if($hint || $error)
        <label class="label">
            @if($error)
                <span class="label-text-alt text-error">{{ $error }}</span>
            @elseif($hint)
                <span class="label-text-alt text-base-content/70">{{ $hint }}</span>
            @endif
        </label>
    @endif
</div>
