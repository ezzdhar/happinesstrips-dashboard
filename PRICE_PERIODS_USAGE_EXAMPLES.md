# أمثلة استخدام نظام الفترات السعرية

## 1. التحقق من إمكانية الحجز قبل إنشاء الحجز

```php
// في BookingHotel Component أو API
public function checkRoomAvailability($roomId, $checkIn, $checkOut)
{
    $room = Room::find($roomId);
    
    // التحقق من أن الفترة مغطاة بالكامل
    if (!$room->isDateRangeCovered($checkIn, $checkOut)) {
        // الحصول على التواريخ غير المتاحة
        $uncoveredDates = $room->getUncoveredDates($checkIn, $checkOut);
        
        return [
            'available' => false,
            'message' => __('lang.date_range_not_covered'),
            'uncovered_dates' => $uncoveredDates,
        ];
    }
    
    return [
        'available' => true,
        'message' => __('lang.room_available'),
    ];
}
```

---

## 2. حساب السعر الإجمالي للحجز

```php
public function calculateBookingPrice($roomId, $checkIn, $checkOut, $currency = 'egp')
{
    $room = Room::find($roomId);
    
    // التحقق أولاً من التوفر
    if (!$room->isDateRangeCovered($checkIn, $checkOut)) {
        return null; // أو رمي استثناء
    }
    
    // حساب السعر الأساسي (للشخص البالغ)
    $adultPrice = $room->totalPriceForPeriod($checkIn, $checkOut, $currency);
    
    // الحصول على سياسة الأطفال من الفندق
    $hotel = $room->hotel;
    
    // حساب سعر الأطفال بناءً على سياسة الفندق
    // (هذا مثال - التطبيق الفعلي يعتمد على عدد الأطفال وأعمارهم)
    
    return [
        'adult_price_per_person' => $adultPrice,
        'nights_count' => Carbon::parse($checkIn)->diffInDays($checkOut),
        'currency' => strtoupper($currency),
    ];
}
```

---

## 3. عرض تفاصيل الأسعار للعميل

```php
public function getRoomPriceBreakdown($roomId, $checkIn, $checkOut, $currency = 'egp')
{
    $room = Room::find($roomId);
    
    $breakdown = $room->priceBreakdownForPeriod($checkIn, $checkOut, $currency);
    
    if (!$breakdown['is_covered']) {
        return [
            'error' => __('lang.date_range_not_covered'),
            'breakdown' => $breakdown,
        ];
    }
    
    return [
        'room_name' => $room->name,
        'hotel_name' => $room->hotel->name,
        'breakdown' => $breakdown,
        'nights' => $breakdown['nights_count'],
        'total' => $breakdown['total'],
        'currency' => $breakdown['currency'],
        'daily_prices' => $breakdown['days'],
    ];
}
```

---

## 4. البحث عن الغرف المتاحة في فترة معينة

```php
public function getAvailableRooms($hotelId, $checkIn, $checkOut)
{
    $rooms = Room::where('hotel_id', $hotelId)
        ->where('status', Status::Active)
        ->get();
    
    $availableRooms = [];
    
    foreach ($rooms as $room) {
        if ($room->isDateRangeCovered($checkIn, $checkOut)) {
            $availableRooms[] = [
                'id' => $room->id,
                'name' => $room->name,
                'adults_count' => $room->adults_count,
                'children_count' => $room->children_count,
                'price_egp' => $room->totalPriceForPeriod($checkIn, $checkOut, 'egp'),
                'price_usd' => $room->totalPriceForPeriod($checkIn, $checkOut, 'usd'),
            ];
        }
    }
    
    return $availableRooms;
}
```

---

## 5. التحقق من صحة التواريخ في Validation

```php
// في BookingHotel Form Request أو Livewire Component
public function rules()
{
    return [
        'room_id' => 'required|exists:rooms,id',
        'check_in' => 'required|date|after:today',
        'check_out' => 'required|date|after:check_in',
        // ... باقي الحقول
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $room = Room::find($this->room_id);
        
        if ($room && !$room->isDateRangeCovered($this->check_in, $this->check_out)) {
            $validator->errors()->add(
                'check_in',
                __('lang.date_range_not_covered')
            );
        }
    });
}
```

---

## 6. عرض الفترات السعرية للغرفة في واجهة العميل

```blade
{{-- في صفحة تفاصيل الغرفة --}}
<div class="pricing-periods">
    <h3>{{ __('lang.price_periods') }}</h3>
    
    @if($room->price_periods)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($room->price_periods as $period)
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h4 class="card-title text-sm">
                            {{ Carbon::parse($period['start_date'])->format('d M Y') }}
                            -
                            {{ Carbon::parse($period['end_date'])->format('d M Y') }}
                        </h4>
                        <div class="flex justify-between">
                            <span>{{ $period['adult_price_egp'] }} {{ __('lang.egp') }}</span>
                            <span>{{ $period['adult_price_usd'] }} {{ __('lang.usd') }}</span>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ __('lang.price_per_person_per_night') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-warning">
            {{ __('lang.no_price_periods_added') }}
        </div>
    @endif
</div>
```

---

## 7. API Endpoint للحصول على السعر

```php
// في API Controller
public function getRoomPrice(Request $request, $roomId)
{
    $request->validate([
        'check_in' => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'currency' => 'required|in:egp,usd',
    ]);
    
    $room = Room::findOrFail($roomId);
    
    $breakdown = $room->priceBreakdownForPeriod(
        $request->check_in,
        $request->check_out,
        $request->currency
    );
    
    if (!$breakdown['is_covered']) {
        return response()->json([
            'success' => false,
            'message' => __('lang.date_range_not_covered'),
            'uncovered_dates' => $room->getUncoveredDates(
                $request->check_in,
                $request->check_out
            ),
        ], 422);
    }
    
    return response()->json([
        'success' => true,
        'data' => [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'nights' => $breakdown['nights_count'],
            'total' => $breakdown['total'],
            'currency' => $breakdown['currency'],
            'breakdown' => $breakdown['days'],
        ],
    ]);
}
```

---

## 8. Livewire Component للبحث والحجز

```php
class BookingSearch extends Component
{
    public $hotel_id;
    public $check_in;
    public $check_out;
    public $currency = 'egp';
    public $available_rooms = [];
    
    public function searchRooms()
    {
        $this->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'currency' => 'required|in:egp,usd',
        ]);
        
        $rooms = Room::where('hotel_id', $this->hotel_id)
            ->where('status', Status::Active)
            ->get();
        
        $this->available_rooms = [];
        
        foreach ($rooms as $room) {
            if ($room->isDateRangeCovered($this->check_in, $this->check_out)) {
                $breakdown = $room->priceBreakdownForPeriod(
                    $this->check_in,
                    $this->check_out,
                    $this->currency
                );
                
                $this->available_rooms[] = [
                    'id' => $room->id,
                    'name' => $room->name,
                    'adults_count' => $room->adults_count,
                    'children_count' => $room->children_count,
                    'total_price' => $breakdown['total'],
                    'nights' => $breakdown['nights_count'],
                    'price_per_night_avg' => $breakdown['total'] / $breakdown['nights_count'],
                    'currency' => $breakdown['currency'],
                    'images' => $room->files->take(3),
                ];
            }
        }
        
        if (empty($this->available_rooms)) {
            session()->flash('warning', __('lang.no_rooms_available_for_dates'));
        }
    }
}
```

---

## ملاحظات مهمة

1. **دائماً تحقق من `isDateRangeCovered()`** قبل إنشاء أي حجز
2. **استخدم `getUncoveredDates()`** لعرض التواريخ غير المتاحة للعميل
3. **`totalPriceForPeriod()` يُرجع 0** إذا كانت أي يوم غير مغطى
4. **`priceBreakdownForPeriod()` يُعطي تفاصيل كاملة** مفيدة للعرض
5. **العملة يمكن أن تكون:** `egp`, `usd`, `EGP`, `USD`, `جنيه`, `دولار`, إلخ

---

## التكامل مع سياسة الأطفال في الفندق

```php
public function calculateTotalWithChildren($room, $checkIn, $checkOut, $adults, $children)
{
    $hotel = $room->hotel;
    
    // سعر البالغين
    $adultPricePerNight = $room->totalPriceForPeriod($checkIn, $checkOut, 'egp');
    $totalAdults = $adultPricePerNight * $adults;
    
    // حساب سعر الأطفال حسب سياسة الفندق
    $totalChildren = 0;
    
    foreach ($children as $index => $childAge) {
        if ($childAge < $hotel->free_child_age) {
            // الطفل مجاني
            continue;
        }
        
        if ($childAge >= $hotel->adult_age) {
            // يُحسب كبالغ
            $totalChildren += $adultPricePerNight;
            continue;
        }
        
        // تطبيق نسبة الطفل حسب ترتيبه
        $childNumber = $index + 1;
        $percentage = 0;
        
        if ($childNumber == 1) {
            $percentage = $hotel->first_child_price_percentage;
        } elseif ($childNumber == 2) {
            $percentage = $hotel->second_child_price_percentage;
        } elseif ($childNumber == 3) {
            $percentage = $hotel->third_child_price_percentage;
        } else {
            $percentage = $hotel->additional_child_price_percentage;
        }
        
        $totalChildren += ($adultPricePerNight * $percentage / 100);
    }
    
    return [
        'adults_total' => $totalAdults,
        'children_total' => $totalChildren,
        'grand_total' => $totalAdults + $totalChildren,
    ];
}
```

