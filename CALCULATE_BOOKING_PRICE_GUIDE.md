# 🧮 دالة حساب إجمالي سعر الحجز

## 📋 نظرة عامة

تم إضافة دالة شاملة `calculateBookingPrice()` في الـ Room Model تحسب إجمالي سعر الحجز مع تطبيق **جميع السياسات**:
- ✅ سياسة الفترات السعرية للغرفة
- ✅ سياسة أعمار الأطفال بالفندق
- ✅ نسب أسعار الأطفال بالفندق

---

## 🎯 الاستخدام الأساسي

```php
$room = Room::find(1);

$result = $room->calculateBookingPrice(
    checkIn: '2025-01-01',      // تاريخ الدخول
    checkOut: '2025-01-05',     // تاريخ الخروج
    adultsCount: 2,             // عدد البالغين
    childrenAges: [3, 6, 8, 13], // أعمار الأطفال
    currency: 'egp'             // العملة (egp أو usd)
);

if ($result['success']) {
    echo "الإجمالي: {$result['grand_total']} {$result['currency']}\n";
    echo "البالغون: {$result['adults_total']}\n";
    echo "الأطفال: {$result['children_total']}\n";
} else {
    echo "خطأ: {$result['error']}\n";
}
```

---

## 📤 المخرجات الكاملة

### مثال على النتيجة:

```php
[
    'success' => true,
    'room_id' => 1,
    'room_name' => 'غرفة مزدوجة',
    'hotel_id' => 5,
    'hotel_name' => 'فندق النيل',
    'check_in' => '2025-01-01',
    'check_out' => '2025-01-05',
    'nights_count' => 4,
    'currency' => 'EGP',
    
    // البالغون
    'adults_count' => 2,
    'adult_price_per_person' => 4000,  // السعر للشخص الواحد للفترة الكاملة
    'adults_total' => 8000,            // 2 بالغ × 4000
    
    // الأطفال
    'children_count' => 4,
    'children_breakdown' => [
        [
            'child_number' => 1,
            'age' => 3,
            'category' => 'free',
            'category_label' => 'مجاناً (3 سنوات، < 4)',
            'percentage' => 0,
            'price' => 0,
        ],
        [
            'child_number' => 2,
            'age' => 6,
            'category' => 'child',
            'category_label' => 'سعر الطفل (6 سنوات)',
            'percentage' => 50,  // نسبة الطفل الأول
            'price' => 2000,     // 4000 × 50%
        ],
        [
            'child_number' => 3,
            'age' => 8,
            'category' => 'child',
            'category_label' => 'سعر الطفل (8 سنوات)',
            'percentage' => 30,  // نسبة الطفل الثاني
            'price' => 1200,     // 4000 × 30%
        ],
        [
            'child_number' => 4,
            'age' => 13,
            'category' => 'adult',
            'category_label' => 'يُحسب كبالغ (13 سنوات، ≥ 12)',
            'percentage' => 100,
            'price' => 4000,     // سعر كامل
        ],
    ],
    'children_total' => 7200,  // 0 + 2000 + 1200 + 4000
    
    // الإجماليات
    'subtotal' => 15200,       // 8000 + 7200
    'grand_total' => 15200,
    
    // التفاصيل اليومية
    'daily_breakdown' => [
        [
            'date' => '2025-01-01',
            'day_name' => 'الأربعاء',
            'price' => 1000,
            'currency' => 'EGP',
            'is_covered' => true,
        ],
        // ... باقي الأيام
    ],
    'price_per_night_average' => 1000,  // 4000 ÷ 4
    
    // سياسات الفندق
    'hotel_policies' => [
        'free_child_age' => 4,
        'adult_age' => 12,
        'first_child_percentage' => 50,
        'second_child_percentage' => 30,
        'third_child_percentage' => 20,
        'additional_child_percentage' => 10,
    ],
]
```

---

## 🔍 شرح الحساب

### إعدادات المثال:
```
الفندق:
- عمر الطفل المجاني: 4 سنوات
- عمر البلوغ: 12 سنة
- نسبة الطفل الأول: 50%
- نسبة الطفل الثاني: 30%
- نسبة الطفل الثالث: 20%
- نسبة الأطفال الإضافيين: 10%

الغرفة:
- سعر البالغ: 1000 جنيه/ليلة
- الفترة: 4 ليالي = 4000 جنيه للشخص

الحجز:
- بالغون: 2
- أطفال: [3, 6, 8, 13]
```

### الحساب:

#### 1. البالغون:
```
2 بالغ × 4000 جنيه = 8000 جنيه
```

#### 2. الأطفال:
```php
// طفل 1 (عمره 3 سنوات) - مجاناً
3 < 4 (عمر الطفل المجاني)
السعر = 0 جنيه

// طفل 2 (عمره 6 سنوات) - الطفل الأول
6 >= 4 && 6 < 12
نسبة الطفل الأول = 50%
السعر = 4000 × 50% = 2000 جنيه

// طفل 3 (عمره 8 سنوات) - الطفل الثاني
8 >= 4 && 8 < 12
نسبة الطفل الثاني = 30%
السعر = 4000 × 30% = 1200 جنيه

// طفل 4 (عمره 13 سنة) - بالغ
13 >= 12 (عمر البلوغ)
السعر = 4000 جنيه (سعر كامل)

إجمالي الأطفال = 0 + 2000 + 1200 + 4000 = 7200 جنيه
```

#### 3. الإجمالي النهائي:
```
البالغون: 8000 جنيه
الأطفال: 7200 جنيه
───────────────────
الإجمالي: 15200 جنيه
```

---

## 💡 أمثلة استخدام متقدمة

### مثال 1: في Livewire Component

```php
class CreateBooking extends Component
{
    public $room_id;
    public $check_in;
    public $check_out;
    public $adults_count = 2;
    public $children_ages = [];
    public $currency = 'egp';
    
    public $pricing_result = null;
    
    public function calculatePrice()
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'adults_count' => 'required|integer|min:1',
            'children_ages' => 'nullable|array',
            'children_ages.*' => 'integer|min:0|max:18',
        ]);
        
        $room = Room::find($this->room_id);
        
        $this->pricing_result = $room->calculateBookingPrice(
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: $this->adults_count,
            childrenAges: $this->children_ages,
            currency: $this->currency
        );
        
        if (!$this->pricing_result['success']) {
            session()->flash('error', $this->pricing_result['error']);
        }
    }
}
```

### عرض النتيجة في Blade:

```blade
@if($pricing_result && $pricing_result['success'])
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">{{ __('lang.booking_summary') }}</h2>
            
            {{-- معلومات أساسية --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ __('lang.room') }}</p>
                    <p class="font-semibold">{{ $pricing_result['room_name'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('lang.nights') }}</p>
                    <p class="font-semibold">{{ $pricing_result['nights_count'] }}</p>
                </div>
            </div>
            
            {{-- البالغون --}}
            <div class="divider"></div>
            <div class="flex justify-between">
                <span>{{ $pricing_result['adults_count'] }} {{ __('lang.adults') }}</span>
                <span class="font-semibold">
                    {{ number_format($pricing_result['adults_total'], 2) }} 
                    {{ $pricing_result['currency'] }}
                </span>
            </div>
            
            {{-- الأطفال --}}
            @if($pricing_result['children_count'] > 0)
                <div class="mt-2">
                    <p class="font-semibold">{{ __('lang.children') }}:</p>
                    @foreach($pricing_result['children_breakdown'] as $child)
                        <div class="flex justify-between text-sm mt-1">
                            <span>{{ $child['category_label'] }}</span>
                            <span>
                                {{ number_format($child['price'], 2) }} 
                                {{ $pricing_result['currency'] }}
                            </span>
                        </div>
                    @endforeach
                    <div class="flex justify-between font-semibold mt-2">
                        <span>{{ __('lang.children_total') }}</span>
                        <span>
                            {{ number_format($pricing_result['children_total'], 2) }} 
                            {{ $pricing_result['currency'] }}
                        </span>
                    </div>
                </div>
            @endif
            
            {{-- الإجمالي --}}
            <div class="divider"></div>
            <div class="flex justify-between text-xl font-bold">
                <span>{{ __('lang.grand_total') }}</span>
                <span class="text-primary">
                    {{ number_format($pricing_result['grand_total'], 2) }} 
                    {{ $pricing_result['currency'] }}
                </span>
            </div>
        </div>
    </div>
@endif
```

---

### مثال 2: في API

```php
// في Controller
public function calculateRoomPrice(Request $request, $roomId)
{
    $validated = $request->validate([
        'check_in' => 'required|date|after:today',
        'check_out' => 'required|date|after:check_in',
        'adults_count' => 'required|integer|min:1',
        'children_ages' => 'nullable|array',
        'children_ages.*' => 'integer|min:0|max:18',
        'currency' => 'required|in:egp,usd',
    ]);
    
    $room = Room::findOrFail($roomId);
    
    $result = $room->calculateBookingPrice(
        checkIn: $validated['check_in'],
        checkOut: $validated['check_out'],
        adultsCount: $validated['adults_count'],
        childrenAges: $validated['children_ages'] ?? [],
        currency: $validated['currency']
    );
    
    if (!$result['success']) {
        return response()->json($result, 422);
    }
    
    return response()->json($result);
}
```

---

### مثال 3: حساب بسيط بدون تفاصيل الأطفال

```php
$room = Room::find(1);

// إذا كنت تريد حساب بسيط بدون تفاصيل أعمار الأطفال
$result = $room->calculateSimpleBookingPrice(
    checkIn: '2025-01-01',
    checkOut: '2025-01-05',
    adultsCount: 2,
    childrenCount: 2,  // فقط العدد بدون الأعمار
    currency: 'egp'
);

// النتيجة:
[
    'success' => true,
    'nights_count' => 4,
    'adults_count' => 2,
    'children_count' => 2,
    'adult_price_per_person' => 4000,
    'adults_total' => 8000,
    'price_per_night' => 1000,
    'currency' => 'EGP',
]
```

---

## ⚠️ معالجة الأخطاء

### أخطاء محتملة:

```php
// 1. التواريخ غير مغطاة بفترات الأسعار
[
    'success' => false,
    'error' => 'التواريخ المحددة غير مغطاة بفترات الأسعار',
    'uncovered_dates' => ['2025-06-01', '2025-06-02'],
]

// 2. عملة غير صحيحة
[
    'success' => false,
    'error' => 'Invalid currency',
]

// 3. فترة تواريخ غير صحيحة
[
    'success' => false,
    'error' => 'Invalid date range',
]
```

### مثال معالجة الأخطاء:

```php
$result = $room->calculateBookingPrice(
    checkIn: $checkIn,
    checkOut: $checkOut,
    adultsCount: $adultsCount,
    childrenAges: $childrenAges,
    currency: $currency
);

if (!$result['success']) {
    // معالجة الخطأ
    if (isset($result['uncovered_dates'])) {
        // عرض التواريخ غير المتاحة
        $dates = implode(', ', $result['uncovered_dates']);
        echo "التواريخ غير المتاحة: {$dates}";
    } else {
        echo "خطأ: {$result['error']}";
    }
    return;
}

// المتابعة مع النتيجة الناجحة
$grandTotal = $result['grand_total'];
```

---

## 📊 ملخص المميزات

✅ **حساب شامل ودقيق** للأسعار  
✅ **تطبيق تلقائي** لجميع سياسات الفندق  
✅ **تفاصيل كاملة** لكل طفل مع فئته ونسبته  
✅ **معالجة أخطاء واضحة** ومفيدة  
✅ **دعم العملتين** (جنيه ودولار)  
✅ **تفاصيل يومية** لتوضيح السعر  
✅ **سهولة الاستخدام** في أي سياق

---

## 🎯 الملخص

الدالة `calculateBookingPrice()` هي الحل الشامل لحساب أسعار الحجوزات مع:
- ✅ سياسة الفترات السعرية
- ✅ سياسة أعمار الأطفال
- ✅ نسب أسعار الأطفال
- ✅ تفاصيل كاملة وواضحة

**استخدمها دائماً قبل إنشاء أي حجز!**

