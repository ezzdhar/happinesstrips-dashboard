# تحليل مشروع نظام حجز الرحلات والفنادق (Happiness Trips)

## معلومات المشروع

- **اسم المشروع**: Happiness Trips - نظام حجز الرحلات والفنادق
- **Framework**: Laravel 12.36.1
- **PHP Version**: 8.4.14
- **Branch**: main
- **Last Commit**: 357a232 - Enhance dashboard charts with improved y-axis tick settings
- **تاريخ التحليل**: 16 نوفمبر 2025

## ملخص تنفيذي

نظام متكامل لإدارة وحجز الرحلات والفنادق مبني على Laravel 12 مع Livewire 3. النظام يدعم:
- **حجوزات الفنادق**: مع نظام تسعير أسبوعي ديناميكي
- **حجوزات الرحلات**: رحلات ثابتة ومرنة
- **إدارة المسافرين**: تتبع بيانات كل مسافر
- **نظام أسعار متقدم**: دعم عملتين (EGP/USD) مع حساب تلقائي
- **Multilingual**: دعم العربية والإنجليزية

---

## جدول المحتويات

1. [User Model](#1-user-model)
2. [Booking Model](#2-booking-model)
3. [BookingHotel Model](#3-bookinghotel-model)
4. [BookingTraveler Model](#4-bookingtraveler-model)
5. [Hotel Model](#5-hotel-model)
6. [Room Model](#6-room-model)
7. [Trip Model](#7-trip-model)
8. [City Model](#8-city-model)
9. [MainCategory Model](#9-maincategory-model)
10. [SubCategory Model](#10-subcategory-model)
11. [Amenity Model](#11-amenity-model)
12. [File Model](#12-file-model)
13. [خلاصة المشاكل والتوصيات](#خلاصة-المشاكل-والتوصيات)

---

## 1. User Model

**الجدول**: `users`

### Schema (من Migration)

```php
- id (PK, bigint, auto_increment)
- username (string, nullable, unique)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string, hashed)
- image (string, nullable)
- status (enum: active/inactive, default: active)
- phone_key (string, nullable)
- phone (string, nullable)
- verification_code (string, nullable)
- remember_token
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$hidden: ['password', 'remember_token']

Casts:
- email_verified_at => datetime
- password => hashed
- status => Status::class (Enum)

Traits:
- HasApiTokens (Sanctum)
- HasFactory
- HasRoles (Spatie Permissions)
- MustVerifyEmail
- Notifiable
```

### Relations

- `bookings()` → hasMany(Booking::class)

### Observers

- **UserObserver**: يتم تفعيله عند `created`

### Create Flow

#### 1. Registration (API)
**Endpoint**: `POST /api/v1/register`  
**Controller**: `App\Http\Controllers\Api\GuestController@register`

**سير العمل**:
```
1. Request → GuestController@register
2. Validation (API Request Validation)
3. User::create([...])
4. Observer triggered → UserObserver@created
   - يُنشئ image وهمية باستخدام FileService::fakeImage()
5. Response: User + Token
```

#### 2. Dashboard Creation (Livewire)
**Component**: `App\Livewire\Dashboard\Employee\*` or `App\Livewire\Dashboard\User\*`

**سير العمل**:
```
1. Livewire Component (CreateEmployee/CreateUser)
2. Validation في Component
3. User::create([...])
4. Observer: UserObserver@created
5. Assign Roles (Spatie)
6. Flash message + redirect
```

### Business Calculations

لا توجد عمليات حسابية مباشرة، لكن:
- **Accessor**: `getFullPhoneAttribute()` → يجمع `phone_key` + `phone`
- **Helper Method**: `initials()` → يستخرج الأحرف الأولى من الاسم

### Update Flow

**Routes**:
- `PATCH /api/v1/profile/update` (API)
- Livewire Components للـ Dashboard

**Logic**:
```
1. Validation
2. $user->update([...])
3. لا توجد Observers على updated
4. Flash/Response
```

### Delete Flow

**Routes**:
- `POST /api/v1/profile/delete` (API)
- Livewire Components للـ Dashboard

**Side Effects**:
- ⚠️ **CASCADE DELETE**: جميع الحجوزات المرتبطة ستُحذف (bookings → booking_hotels, booking_travelers)

### Performance Notes

✅ **جيد**: استخدام Scopes في Models ذات صلة  
⚠️ **انتبه**: لا توجد eager loading صريحة في بعض الاستعلامات

### Recommended Fixes (SHORT)

1. ✅ إضافة Index على `email` و `phone` (موجود على email)
2. ⚠️ إضافة soft deletes للـ Users بدلاً من الحذف النهائي
3. ✅ التحقق من الـ UserObserver - قد يكون غير ضروري في Production

### Code Snippet Fix (Observer)

```php
// app/Observers/UserObserver.php
public function created(User $user): void
{
    // تأكد من عدم وجود صورة مسبقاً
    if (!$user->image) {
        $user->update([
            'image' => FileService::fakeImage(
                name: $user->name, 
                shape: 'circle', 
                folder: 'users'
            ),
        ]);
    }
}
```

### Checklist

- [x] Observer موجود ويعمل
- [ ] إضافة Unit Tests للـ Observer
- [ ] إضافة Soft Deletes
- [ ] مراجعة cascade deletes
- [ ] إضافة Policy للـ authorization

---

## 2. Booking Model

**الجدول**: `bookings`

### Schema (من Migration)

```php
- id (PK)
- booking_number (string, unique) - يتم توليده تلقائياً
- user_id (FK → users.id, cascade on delete)
- trip_id (FK → trips.id, cascade on delete)
- type (string, default: 'hotel') // 'hotel' or 'trip'

// تفاصيل الإقامة
- check_in (date, nullable)
- check_out (date, nullable)
- nights_count (integer, default: 1)

// عدد الأشخاص
- adults_count (integer, default: 1)
- children_count (integer, default: 0)

// السعر
- price (decimal(8,2))
- total_price (decimal(8,2))
- currency (string, default: 'egp')

- notes (text, nullable)
- status (enum: pending/under_payment/under_cancellation/cancelled/completed)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']

Casts:
- check_in => date
- check_out => date
- status => Status::class
- adults_count => integer
- children_count => integer
- nights_count => integer
```

### Boot Method (Auto-generation)

```php
static::creating(function ($booking) {
    if (!$booking->booking_number) {
        $booking->booking_number = 'BK-' . strtoupper(uniqid());
    }
});
```

⚠️ **مشكلة محتملة**: `uniqid()` ليس آمناً تماماً في البيئات المتزامنة (concurrent requests)

### Relations

- `user()` → belongsTo(User::class)
- `trip()` → belongsTo(Trip::class)
- `bookingHotel()` → hasOne(BookingHotel::class)
- `travelers()` → hasMany(BookingTraveler::class)

### Create Flow - Hotel Booking

**Route**: `GET /bookings/hotels/create`  
**Component**: `App\Livewire\Dashboard\BookingHotel\CreateBookingHotel`

#### سير العمل التفصيلي:

```
1. User يختار Hotel → يتم تحميل Rooms
2. User يختار Room → يتم تحميل Room Details
3. يتم تهيئة Travelers تلقائياً حسب adults_count + children_count
4. User يملأ بيانات كل Traveler
5. عند Save:
   
   a. Validation (rules في Component)
   b. حساب السعر باستخدام:
      $room->priceBreakdownForPeriod($check_in, $check_out, $currency)
   
   c. DB::beginTransaction() ✅
   
   d. إنشاء Booking:
      Booking::create([
          'user_id' => ...,
          'check_in' => ...,
          'check_out' => ...,
          'nights_count' => $breakdown['nights_count'],
          'adults_count' => $room->adults_count,
          'children_count' => $room->children_count,
          'price' => $breakdown['total'],
          'total_price' => $breakdown['total'],
          'currency' => ...,
          'status' => ...,
      ])
   
   e. إنشاء BookingHotel:
      BookingHotel::create([
          'booking_id' => ...,
          'hotel_id' => ...,
          'room_id' => ...,
          'room_includes' => $room->includes,
      ])
   
   f. إنشاء BookingTravelers (loop):
      foreach ($travelers as $traveler) {
          BookingTraveler::create([...])
      }
   
   g. DB::commit() ✅
   
   h. Flash success message
   i. Redirect → bookings.hotels.show
   
6. في حالة Exception:
   - DB::rollBack() ✅
   - Log::error($e->getMessage())
   - Flash error message
```

### Create Flow - Trip Booking

**Route**: `GET /bookings/trips/create`  
**Component**: `App\Livewire\Dashboard\BookingTrip\CreateBookingTrip`

#### سير العمل التفصيلي:

```
1. Multi-step form (3 خطوات + مراجعة)
   
   Step 1: اختيار User + Trip
   Step 2: تحديد التواريخ + عدد الأشخاص
   Step 3: بيانات المسافرين
   Review: مراجعة نهائية
   
2. عند اختيار Trip:
   - إذا Fixed Trip → التواريخ تُحدد تلقائياً
   - إذا Flexible Trip → User يختار التواريخ
   
3. حساب السعر تلقائياً:
   TripPricingService::calculateTripPrice(
       trip: $trip,
       checkIn: $check_in,
       checkOut: $check_out,
       adultsCount: $adults_count,
       childrenCount: $children_count, // فوق سن الـ threshold
       freeChildrenCount: $free_children_count, // تحت سن الـ threshold
       currency: $currency
   )
   
4. عند Save:
   
   a. Validation
   b. Booking::create([...]) ⚠️ لا يوجد DB::transaction
   c. Loop: BookingTraveler::create([...])
   d. Flash + Redirect
```

⚠️ **CRITICAL ISSUE**: Trip Booking لا يستخدم `DB::transaction`!

### Business Calculations

#### 1. Hotel Booking Price Calculation

**Service**: `Room::priceBreakdownForPeriod()` (من Trait `HasWeeklyPrices`)

**المعادلة**:
```php
total = sum(price_for_each_day_in_period)

حيث:
- price_for_each_day يُحدد من weekly_prices JSON
- weekly_prices = {
    "sunday": {"price_egp": 500, "price_usd": 25},
    "monday": {...},
    ...
  }
```

**مثال**:
```
Check-in: 2025-01-01 (Wednesday)
Check-out: 2025-01-04 (Saturday)
Nights: 3

Day 1 (Wed): 600 EGP
Day 2 (Thu): 700 EGP
Day 3 (Fri): 1200 EGP
-----------------
Total: 2500 EGP
```

✅ **دقيق ومنطقي**

#### 2. Trip Booking Price Calculation

**Service**: `TripPricingService::calculateTripPrice()`

**المعادلات**:

**Fixed Trip** (رحلة بتاريخ محدد):
```php
calculated_price = base_price (per person)
total_price = (adults_count + children_count) * base_price

// free_children_count لا يُحسب
```

**Flexible Trip** (رحلة مرنة):
```php
calculated_price = base_price (per person per night)
total_price = (adults_count + children_count) * base_price * nights_count

// free_children_count لا يُحسب
```

**Child Age Threshold**: يتم تحديده من `config('booking.child_age_threshold', 12)`
- أطفال عمر < 12: مجاناً
- أطفال عمر >= 12: يُحسبون كـ adults

✅ **منطق جيد وواضح**

⚠️ **ملاحظة**: لا يوجد rounding في بعض الأماكن، والـ Service يستخدم `round($value, 2)`

### Update Flow

**Routes**:
- `GET /bookings/hotels/edit/{booking}`
- `GET /bookings/trips/edit/{booking}`

**Components**:
- `UpdateBookingHotel`
- `UpdateBookingTrip`

**Logic** (يجب التحقق من الكود الفعلي):
```
1. Load existing booking
2. Allow status change
3. Update booking details
4. ⚠️ تأكد من استخدام DB::transaction
```

### Delete Flow

⚠️ لا يوجد Delete صريح في Routes، لكن:
- **Cascade Delete** من User
- **Cascade Delete** من Trip

### Performance Issues

❌ **N+1 Problem محتمل**:
```php
// في BookingHotelData أو BookingTripData
$bookings = Booking::all(); // ❌
foreach ($bookings as $booking) {
    echo $booking->user->name; // N+1
    echo $booking->trip->name; // N+1
}
```

**الحل**:
```php
$bookings = Booking::with(['user', 'trip', 'bookingHotel.hotel', 'travelers'])->get();
```

### Recommended Fixes (SHORT-TERM)

1. **CRITICAL**: إضافة `DB::transaction` في CreateBookingTrip
2. **HIGH**: استبدال `uniqid()` بـ UUID أو Snowflake ID
3. **MEDIUM**: إضافة eager loading في Data Components
4. **MEDIUM**: إضافة Index على `booking_number`, `status`, `check_in`

### Code Snippet Fix (Transaction في Trip Booking)

```php
// app/Livewire/Dashboard/BookingTrip/CreateBookingTrip.php

public function save(): void
{
    $this->validate();

    try {
        DB::beginTransaction();
        
        // Create booking with calculated prices
        $booking = Booking::create([
            'user_id' => $this->user_id,
            'trip_id' => $this->trip_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights_count' => $this->nights_count,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'price' => $this->calculated_price,
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => Status::Pending,
            'type' => 'trip',
        ]);

        // Create travelers
        foreach ($this->travelers as $travelerData) {
            BookingTraveler::create([
                'booking_id' => $booking->id,
                'full_name' => $travelerData['full_name'],
                'phone_key' => $travelerData['phone_key'] ?? '+20',
                'phone' => $travelerData['phone'],
                'nationality' => $travelerData['nationality'],
                'age' => $travelerData['age'],
                'id_type' => $travelerData['id_type'],
                'id_number' => $travelerData['id_number'],
                'type' => $travelerData['type'],
            ]);
        }
        
        DB::commit();
        
        flash()->success(__('lang.created_successfully', ['attribute' => __('lang.booking')]));
        $this->redirectIntended(default: route('bookings.trips'));
        
    } catch (\Exception $e) {
        DB::rollBack();
        flash()->error(__('lang.error_occurred'));
        Log::error('Trip Booking Creation Failed: ' . $e->getMessage());
    }
}
```

### Checklist

- [x] Validation rules موجودة
- [x] Hotel Booking يستخدم Transaction
- [ ] **Trip Booking يستخدم Transaction** ← CRITICAL
- [ ] إضافة unique constraint على booking_number في DB
- [ ] إضافة Policy للتحكم بمن يمكنه إنشاء/تعديل الحجوزات
- [ ] إضافة Unit Tests لحساب الأسعار
- [ ] إضافة Feature Tests للـ Booking Flow
- [ ] إضافة Idempotency Key للحماية من التكرار
- [ ] مراجعة status transitions (state machine)

---

## 3. BookingHotel Model

**الجدول**: `booking_hotels`

### Schema (من Migration)

```php
- id (PK)
- booking_id (FK → bookings.id, cascade on delete)
- hotel_id (FK → hotels.id, cascade on delete)
- room_id (FK → rooms.id, nullable, cascade on delete)
- room_includes (longText, nullable) // نسخة من includes عند الحجز
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']

Casts:
- room_price => array (⚠️ لا يوجد في المايجريشن!)
- rooms_count => integer (⚠️ لا يوجد في المايجريشن!)
```

⚠️ **CRITICAL ISSUE**: Model يحتوي على Casts لحقول غير موجودة في الجدول!

### Relations

- `booking()` → belongsTo(Booking::class)
- `hotel()` → belongsTo(Hotel::class)->withDefault(['name' => __('lang.no_data')])
- `room()` → belongsTo(Room::class)->withDefault(['name' => __('lang.no_data')])

### Create Flow

يتم إنشاؤه كجزء من Booking Flow (انظر Booking Model)

### Business Logic

- **room_includes**: يتم نسخ بيانات `Room::includes` وقت الحجز لحفظ snapshot

### Recommended Fixes (CRITICAL)

1. **إزالة Casts غير المستخدمة** أو **إضافة الحقول للمايجريشن**
2. إذا كنت تحتاج `room_price` و `rooms_count`، أضفهم للمايجريشن

### Code Snippet Fix (Migration)

```php
// قم بإنشاء Migration جديدة
php artisan make:migration add_missing_fields_to_booking_hotels_table

// في ملف المايجريشن:
public function up(): void
{
    Schema::table('booking_hotels', function (Blueprint $table) {
        $table->json('room_price')->nullable()->after('room_id');
        $table->integer('rooms_count')->default(1)->after('room_price');
    });
}
```

**أو قم بإزالة Casts**:
```php
// app/Models/BookingHotel.php
protected function casts(): array
{
    return [
        // حذف room_price و rooms_count إذا لم تكن بحاجة لهم
    ];
}
```

### Checklist

- [ ] **حل مشكلة Casts vs Schema** ← CRITICAL
- [ ] التأكد من استخدام room_price إذا كان موجوداً
- [ ] إضافة tests

---

## 4. BookingTraveler Model

**الجدول**: `booking_travelers`

### Schema (من Migration)

```php
- id (PK)
- booking_id (FK → bookings.id, cascade on delete)
- full_name (string)
- phone_key (string, nullable)
- phone (string)
- nationality (string)
- age (integer)
- id_type (enum: 'passport', 'national_id', default: 'passport')
- id_number (string)
- type (enum: 'adult', 'child', default: 'adult')
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']

Casts:
- age => integer
```

### Relations

- `booking()` → belongsTo(Booking::class)

### Scopes

- `scopeType($query, $type)` → فلترة حسب adult/child

### Create Flow

يتم إنشاؤه كجزء من Booking Flow في loop

### Business Logic

- **type**: يحدد إذا كان الشخص بالغ أو طفل
- **age**: مهم لتحديد إذا كان يُحسب ضمن المدفوعين أم لا

### Recommended Fixes

1. إضافة validation على `age` vs `type` (مثلاً: إذا age < 18 → يجب أن يكون type = 'child')
2. إضافة Index على `booking_id`

### Checklist

- [x] Model بسيط وواضح
- [ ] إضافة validation rule مخصص
- [ ] إضافة tests

---

## 5. Hotel Model

**الجدول**: `hotels`

### Schema (من Migration)

```php
- id (PK)
- user_id (FK → users.id, cascade on delete)
- city_id (FK → cities.id, cascade on delete)
- email (string)
- name (json) // {"ar": "...", "en": "..."}
- status (enum: active/inactive, default: active)
- rating (enum: 1/2/3/4/5, default: 3)
- phone_key (string, nullable)
- phone (string, nullable)
- latitude (string, nullable)
- longitude (string, nullable)
- description (json, nullable)
- address (json)
- facilities (json)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at', 'deleted_at']
$translatable: ['name', 'address', 'description', 'facilities']

Traits:
- HasFactory
- HasTranslations (Spatie)

Casts:
- status => Status::class
- rating => integer
- latitude => decimal:7
- longitude => decimal:7
```

### Relations

- `user()` → belongsTo(User::class) - صاحب الفندق
- `city()` → belongsTo(City::class)
- `rooms()` → hasMany(Room::class)
- `trips()` → belongsToMany(Trip::class, 'hotel_trip')
- `bookingHotels()` → hasMany(BookingHotel::class)
- `files()` → morphMany(File::class, 'fileable')

### Scopes

- `scopeStatus($query, $status)` → فلترة حسب الحالة
- `scopeFilter($query, $search)` → بحث في الاسم (ar/en)

### Create Flow

**Route**: `GET /hotels/create-hotel`  
**Component**: `App\Livewire\Dashboard\Hotel\CreateHotel`

**سير العمل**:
```
1. User يملأ النموذج (اسم، مدينة، تقييم، إلخ)
2. Validation
3. Hotel::create([...])
4. رفع الصور → File::create([...]) لكل صورة
5. Flash + Redirect
```

⚠️ **يجب التحقق**: هل يتم استخدام Transaction عند رفع الملفات؟

### Update Flow

**Route**: `GET /hotels/edit/{hotel}`  
**Component**: `App\Livewire\Dashboard\Hotel\UpdateHotel`

### Delete Flow

- يمكن الحذف من الـ Data Component
- **Cascade**: يحذف Rooms, BookingHotels, Files

### Performance Notes

✅ استخدام Scopes جيد  
⚠️ تأكد من eager loading عند عرض قائمة الفنادق مع المدن والغرف

### Recommended Fixes

1. إضافة Index على `city_id`, `status`
2. إضافة validation على `latitude` و `longitude` (format)
3. التأكد من Transaction عند رفع الملفات

### Checklist

- [ ] إضافة Index على city_id, status
- [ ] إضافة tests للـ CRUD operations
- [ ] مراجعة Cascade Delete behavior

---

## 6. Room Model

**الجدول**: `rooms`

### Schema (من Migration)

```php
- id (PK)
- hotel_id (FK → hotels.id, cascade on delete)
- name (json)
- adults_count (integer, default: 1)
- children_count (integer, default: 0)
- weekly_prices (json) // {"sunday": {"price_egp": 500, "price_usd": 25}, ...}
- includes (json, nullable)
- status (enum: active/inactive, default: active)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name', 'includes']

Traits:
- HasFactory
- HasTranslations
- HasWeeklyPrices ← **هذا هو المهم!**

Casts:
- weekly_prices => array
- status => Status::class
- adults_count => integer
- children_count => integer
```

### Relations

- `hotel()` → belongsTo(Hotel::class)
- `bookingHotels()` → hasMany(BookingHotel::class)
- `files()` → morphMany(File::class, 'fileable')
- `amenities()` → belongsToMany(Amenity::class, 'room_amenity')

### Scopes

- `scopeStatus($query, $status)`
- `scopeHotelId($query, $hotel_id)`
- `scopeFilter($query, $search)`

### Create Flow

**Route**: `GET /rooms/create-room`  
**Component**: `App\Livewire\Dashboard\Room\CreateRoom`

**سير العمل**:
```
1. اختيار Hotel
2. تعبئة معلومات الغرفة
3. تحديد السعر لكل يوم في الأسبوع (EGP + USD)
4. Validation
5. Room::create([
     'weekly_prices' => [
         'sunday' => ['price_egp' => 500, 'price_usd' => 25],
         'monday' => [...],
         ...
     ]
   ])
6. رفع صور الغرفة
7. Flash + Redirect
```

### Business Calculations (من Trait HasWeeklyPrices)

#### 1. `priceForDay($day, $currency)`

يرجع سعر يوم معين بعملة محددة

**مثال**:
```php
$room->priceForDay('friday', 'egp'); // 1200
$room->priceForDay(now(), 'usd'); // 60
$room->priceForDay(5, 'egp'); // الجمعة
```

#### 2. `totalPriceForPeriod($startDate, $endDate, $currency)`

يحسب السعر الإجمالي لفترة معينة

**المعادلة**:
```php
total = 0
for each day from start_date to (end_date - 1 day):
    total += priceForDay(day, currency)
return total
```

✅ **دقيق**: يحسب كل يوم على حدة

#### 3. `priceBreakdownForPeriod($startDate, $endDate, $currency)`

يرجع تفاصيل كل يوم مع السعر + الإجمالي

**Output Example**:
```php
[
    'days' => [
        ['date' => '2025-01-01', 'day_name' => 'الأربعاء', 'price' => 600, 'currency' => 'EGP'],
        ['date' => '2025-01-02', 'day_name' => 'الخميس', 'price' => 700, 'currency' => 'EGP'],
        ['date' => '2025-01-03', 'day_name' => 'الجمعة', 'price' => 1200, 'currency' => 'EGP'],
    ],
    'total' => 2500,
    'currency' => 'EGP',
    'nights_count' => 3
]
```

✅ **ممتاز**: يوفر تفاصيل كاملة لعرضها للمستخدم

### Update Flow

**Route**: `GET /rooms/edit/{room}`

### Delete Flow

- Cascade من Hotel
- يحذف bookingHotels المرتبطة

### Performance Notes

✅ Trait منظم بشكل ممتاز  
✅ Caching للـ weeklyPricesMap داخل الطلب الواحد

### Recommended Fixes

1. إضافة Validation على `weekly_prices` (يجب أن تحتوي على جميع أيام الأسبوع)
2. إضافة Index على `hotel_id`, `status`
3. إضافة Unit Tests للـ HasWeeklyPrices Trait

### Unit Test Example

```php
// tests/Unit/HasWeeklyPricesTest.php
use App\Models\Room;
use Carbon\Carbon;

test('calculates correct total price for weekend period', function () {
    $room = Room::factory()->create([
        'weekly_prices' => [
            'thursday' => ['price_egp' => 700, 'price_usd' => 35],
            'friday' => ['price_egp' => 1200, 'price_usd' => 60],
            'saturday' => ['price_egp' => 1000, 'price_usd' => 50],
        ]
    ]);
    
    $total = $room->totalPriceForPeriod('2025-01-02', '2025-01-04', 'egp');
    
    expect($total)->toBe(1900.0); // Thu(700) + Fri(1200) = 1900
});

test('price breakdown returns correct structure', function () {
    $room = Room::factory()->create([...]);
    
    $breakdown = $room->priceBreakdownForPeriod('2025-01-01', '2025-01-03', 'egp');
    
    expect($breakdown)
        ->toHaveKey('days')
        ->toHaveKey('total')
        ->toHaveKey('currency')
        ->toHaveKey('nights_count')
        ->and($breakdown['nights_count'])->toBe(2)
        ->and($breakdown['currency'])->toBe('EGP');
});
```

### Checklist

- [ ] إضافة Validation للـ weekly_prices format
- [ ] إضافة Unit Tests للـ Trait
- [ ] إضافة Index على hotel_id, status
- [ ] مراجعة Cascade Delete

---

## 7. Trip Model

**الجدول**: `trips`

### Schema (من Migration)

```php
- id (PK)
- main_category_id (FK → main_categories.id, cascade on delete)
- sub_category_id (FK → sub_categories.id, cascade on delete)
- name (json)
- price (json) // {"egp": 5000, "usd": 250}
- duration_from (date, nullable)
- duration_to (date, nullable)
- nights_count (integer, nullable)
- people_count (integer, default: 1)
- notes (json, nullable)
- program (json, nullable)
- is_featured (boolean, default: false)
- type (enum: 'fixed', 'flexible', default: 'fixed')
- status (enum: active/inactive/end/start, default: active)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name', 'notes', 'program']

Traits:
- HasFactory
- HasTranslations

Casts:
- price => array
- duration_from => date
- duration_to => date
- is_featured => boolean
- status => Status::class
- type => TripType::class
- nights_count => integer
- adults_count => integer (⚠️ لا يوجد في المايجريشن!)
- children_count => integer (⚠️ لا يوجد في المايجريشن!)
```

⚠️ **ISSUE**: Casts لحقول `adults_count` و `children_count` غير موجودة في Schema!

### Relations

- `mainCategory()` → belongsTo(MainCategory::class)
- `subCategory()` → belongsTo(SubCategory::class)
- `hotels()` → belongsToMany(Hotel::class, 'hotel_trip')
- `bookings()` → hasMany(Booking::class)
- `files()` → morphMany(File::class, 'fileable')

### Scopes

- `scopeStatus($query, $status)`
- `scopeType($query, $type)`
- `scopeFeatured($query)` → is_featured = true
- `scopeFilter($query, $search)`

### Create Flow

**Route**: `GET /trips/create-trip`  
**Component**: `App\Livewire\Dashboard\Trip\CreateTrip`

**سير العمل**:
```
1. اختيار Category + SubCategory
2. تحديد نوع الرحلة (Fixed/Flexible)
3. إدخال التفاصيل:
   - اسم الرحلة
   - السعر (EGP + USD)
   - التواريخ (للـ Fixed)
   - عدد الليالي (للـ Flexible)
   - البرنامج
4. اختيار الفنادق (many-to-many)
5. Validation
6. Trip::create([...])
7. Sync hotels: $trip->hotels()->sync([...])
8. رفع الصور
9. Flash + Redirect
```

### Business Logic (TripPricingService)

**انظر Booking Model - Business Calculations** للتفاصيل الكاملة

**ملخص**:
- **Fixed Trip**: `price` هو السعر للشخص الواحد لكامل الرحلة
- **Flexible Trip**: `price` هو السعر للشخص الواحد لليلة الواحدة

### Update Flow

**Route**: `GET /trips/edit/{trip}`

### Delete Flow

- **Cascade Delete**: يحذف Bookings (⚠️ خطير!)
- يفك الربط مع Hotels (many-to-many)
- يحذف Files

### Recommended Fixes (HIGH PRIORITY)

1. **حذف Casts للحقول غير الموجودة** (adults_count, children_count)
2. **إضافة Soft Deletes** بدلاً من الحذف النهائي
3. إضافة Index على `type`, `status`, `is_featured`

### Code Snippet Fix

```php
// app/Models/Trip.php
protected function casts(): array
{
    return [
        'price' => 'array',
        'duration_from' => 'date',
        'duration_to' => 'date',
        'is_featured' => 'boolean',
        'status' => Status::class,
        'type' => TripType::class,
        'nights_count' => 'integer',
        // حذف adults_count و children_count
    ];
}
```

### Checklist

- [ ] **إصلاح Casts** ← HIGH
- [ ] إضافة Soft Deletes
- [ ] إضافة Indexes
- [ ] إضافة Validation على price format
- [ ] إضافة Tests

---

## 8. City Model

**الجدول**: `cities`

### Schema (من Migration)

```php
- id (PK)
- name (json) // {"ar": "القاهرة", "en": "Cairo"}
- code (string, default: 'eg')
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name']

Traits:
- HasFactory
- HasTranslations
```

### Relations

- `hotels()` → hasMany(Hotel::class)

### Scopes

- `scopeFilter($query, $search)` → بحث في name (ar/en) أو code

### Create/Update/Delete Flow

يتم من خلال Livewire Component بسيط

### Recommended Fixes

1. إضافة Index على `code`
2. إضافة unique constraint على `code`

### Checklist

- [x] Model بسيط
- [ ] إضافة Index/Unique على code
- [ ] إضافة seeder للمدن المصرية

---

## 9. MainCategory Model

**الجدول**: `main_categories`

### Schema (من Migration)

```php
- id (PK)
- name (json)
- image (string, nullable)
- status (enum: active/inactive, default: active)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name']

Casts:
- status => Status::class
```

### Relations

- `subCategories()` → hasMany(SubCategory::class)
- `trips()` → hasMany(Trip::class)

### Scopes

- `scopeStatus($query, $status)`
- `scopeFilter($query, $search)`

### Checklist

- [x] Model بسيط
- [ ] إضافة Index على status
- [ ] إضافة tests

---

## 10. SubCategory Model

**الجدول**: `sub_categories`

### Schema (من Migration)

```php
- id (PK)
- main_category_id (FK → main_categories.id)
- name (json)
- status (enum: active/inactive, default: active)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name']

Casts:
- status => Status::class
```

### Relations

- `mainCategory()` → belongsTo(MainCategory::class)
- `trips()` → hasMany(Trip::class)
- `files()` → morphMany(File::class, 'fileable')

### Scopes

- `scopeActive($query)`
- `scopeStatus($query, $status)`
- `scopeFilter($query, $search)`

### Checklist

- [x] Model بسيط
- [ ] إضافة Index على main_category_id, status
- [ ] إضافة tests

---

## 11. Amenity Model

**الجدول**: `amenities`

### Schema (من Migration)

```php
- id (PK)
- name (json)
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
$translatable: ['name']

Casts:
- name => array
```

### Relations

- `rooms()` → belongsToMany(Room::class, 'room_amenity')

### Pivot Table

`room_amenity`:
```php
- room_id (FK)
- amenity_id (FK)
```

### Checklist

- [x] Model بسيط
- [ ] إضافة seeder للـ Amenities الشائعة

---

## 12. File Model

**الجدول**: `files`

### Schema (من Migration)

```php
- id (PK)
- fileable_type (string) // polymorphic
- fileable_id (bigint) // polymorphic
- path (string) // مسار الملف
- created_at, updated_at
```

### Eloquent Configuration

```php
$guarded: ['id', 'created_at', 'updated_at']
```

### Relations

- `fileable()` → morphTo()

### Usage

يُستخدم لتخزين الصور والملفات لـ:
- Hotels
- Rooms
- Trips
- SubCategories

### Create Flow

```
1. رفع الملف عبر Livewire
2. تخزين الملف في storage
3. File::create([
     'fileable_type' => Hotel::class,
     'fileable_id' => $hotel->id,
     'path' => $path,
   ])
```

### Recommended Fixes

1. إضافة `type` field (image/document/video)
2. إضافة `size` field
3. إضافة `mime_type` field
4. إضافة Index على (fileable_type, fileable_id)

### Checklist

- [ ] إضافة حقول إضافية للـ metadata
- [ ] إضافة Index على polymorphic keys
- [ ] إضافة File cleanup عند حذف الـ fileable

---

## خلاصة المشاكل والتوصيات

### 🔴 CRITICAL (يجب إصلاحها فوراً)

1. **Booking (Trip) - No Transaction**
   - **المشكلة**: CreateBookingTrip لا يستخدم `DB::transaction`
   - **الخطورة**: إمكانية إنشاء Booking بدون Travelers في حالة فشل
   - **الحل**: إضافة try-catch مع transaction (كود موجود أعلاه)

2. **BookingHotel - Casts Mismatch**
   - **المشكلة**: Model يحتوي على casts لحقول غير موجودة في الجدول
   - **الخطورة**: Errors محتملة عند محاولة الوصول لهذه الحقول
   - **الحل**: إما إضافة الحقول للمايجريشن أو حذف الـ casts

3. **Trip Model - Invalid Casts**
   - **المشكلة**: casts لـ adults_count و children_count غير موجودة في schema
   - **الحل**: حذفها من Model

4. **Booking Number - uniqid() Race Condition**
   - **المشكلة**: `uniqid()` ليس آمناً في الطلبات المتزامنة
   - **الحل**: استخدام UUID أو Snowflake ID

### 🟠 HIGH Priority

5. **N+1 Query Problems**
   - **المشكلة**: عدم استخدام eager loading في Data Components
   - **الأماكن**: BookingHotelData, BookingTripData, HotelData
   - **الحل**:
   ```php
   Booking::with([
       'user:id,name,email',
       'trip:id,name',
       'bookingHotel.hotel:id,name',
       'bookingHotel.room:id,name',
       'travelers'
   ])->paginate();
   ```

6. **Missing Database Indexes**
   - `bookings`: booking_number, status, check_in, user_id, trip_id
   - `hotels`: city_id, status
   - `rooms`: hotel_id, status
   - `trips`: type, status, is_featured
   - `files`: (fileable_type, fileable_id)

7. **Cascade Deletes - Too Aggressive**
   - **المشكلة**: حذف User يحذف كل حجوزاته
   - **الحل**: استخدام Soft Deletes أو منع الحذف إذا كان لديه حجوزات

### 🟡 MEDIUM Priority

8. **Missing Unit Tests**
   - **TripPricingService**: اختبار Fixed vs Flexible
   - **HasWeeklyPrices Trait**: اختبار حساب الأسعار
   - **Booking**: اختبار booking_number generation

9. **Missing Validation**
   - `weekly_prices` format في Room
   - `price` format في Trip
   - `latitude/longitude` format في Hotel
   - Age vs Type في BookingTraveler

10. **Missing Authorization**
    - Policies للـ Models
    - Gate checks في Components

### 🟢 LOW Priority (تحسينات)

11. **File Model - Missing Metadata**
    - إضافة type, size, mime_type

12. **API Rate Limiting**
    - التأكد من وجود rate limiting على API endpoints

13. **Logging & Monitoring**
    - إضافة logging مفصل للعمليات الحرجة
    - استخدام Laravel Pulse للـ monitoring

---

## الأوامر المفيدة للتطوير

### إنشاء Migration للـ Indexes

```bash
php artisan make:migration add_indexes_to_bookings_table --no-interaction
php artisan make:migration add_indexes_to_hotels_table --no-interaction
php artisan make:migration add_indexes_to_rooms_table --no-interaction
```

### تشغيل Tests

```bash
# كل الـ Tests
php artisan test

# Test محدد
php artisan test --filter=BookingTest

# مع Coverage
php artisan test --coverage
```

### Laravel Pint (Code Formatting)

```bash
vendor/bin/pint --dirty
```

### Query Optimization

```bash
# تفعيل Query Logging
php artisan tinker
DB::enableQueryLog();
// قم بالعملية المطلوبة
dd(DB::getQueryLog());
```

---

## الخلاصة النهائية

**النظام بشكل عام**:
- ✅ بنية جيدة ومنظمة
- ✅ استخدام صحيح لـ Livewire 3
- ✅ منطق حساب الأسعار دقيق ومدروس
- ✅ دعم متعدد اللغات

**نقاط القوة**:
- Trait `HasWeeklyPrices` ممتاز ومرن
- `TripPricingService` واضح ومنطقي
- استخدام Transactions في Hotel Booking

**نقاط تحتاج تحسين فوري**:
- إضافة Transaction في Trip Booking
- حل مشاكل Casts vs Schema
- إضافة Indexes للأداء
- إضافة Tests

**التقييم العام**: 7.5/10
- يمكن رفعه إلى 9/10 بعد إصلاح المشاكل الحرجة والـ HIGH priority

---

**تم إنشاء هذا التقرير بواسطة**: GitHub Copilot AI Agent  
**التاريخ**: 16 نوفمبر 2025  
**الإصدار**: 1.0

