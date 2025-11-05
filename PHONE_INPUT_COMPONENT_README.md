# Phone Input Component - دليل الاستخدام

## نظرة عامة
مكون `phone-input` هو مكون Blade محسّن يستخدم مكتبة intl-tel-input لتوفير حقل إدخال أرقام الهواتف الدولية مع اختيار رمز الدولة.

## الميزات
- ✅ عرض متطابق مع باقي الـ inputs في النظام
- ✅ دعم Label مع علامة الحقل المطلوب (*)
- ✅ عرض رسائل الأخطاء تلقائياً
- ✅ اختيار رمز الدولة تلقائياً بناءً على الموقع الجغرافي
- ✅ تنسيق تلقائي للأرقام
- ✅ دعم RTL/LTR
- ✅ تكامل كامل مع Livewire

## المتطلبات
تأكد من إضافة المكتبة في ملف `head.blade.php`:

```blade
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/intlTelInput.min.js"></script>
```

## طريقة الاستخدام

### 1. في Livewire Component (PHP)

```php
class CreateEmployee extends Component
{
    public $phone;      // لحفظ الرقم الوطني
    public $phone_key;  // لحفظ رمز الدولة (مثل: +20, +966)

    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:20|unique:users,phone',
            'phone_key' => 'required|string|max:5',
        ];
    }

    public function resetData(): void
    {
        $this->reset(['phone', 'phone_key']);
        $this->dispatch('tel-reset'); // لتصفير المكتبة
    }
}
```

### 2. في Blade View

```blade
<x-phone-input
    required
    label="{{__('lang.phone')}}"
    phoneProperty="phone"
    keyProperty="phone_key"
/>
```

## الخصائص (Props)

| الخاصية | النوع | المطلوب | الافتراضي | الوصف |
|---------|------|---------|-----------|-------|
| `phoneProperty` | string | نعم | - | اسم المتغير في Livewire للرقم الوطني |
| `keyProperty` | string | نعم | - | اسم المتغير في Livewire لرمز الدولة |
| `label` | string | لا | null | النص الذي يظهر فوق الحقل |
| `required` | boolean | لا | false | هل الحقل مطلوب؟ |

## أمثلة الاستخدام

### مثال 1: حقل بسيط بدون label
```blade
<x-phone-input
    phoneProperty="phone"
    keyProperty="phone_key"
/>
```

### مثال 2: حقل مطلوب مع label
```blade
<x-phone-input
    required
    label="رقم الهاتف"
    phoneProperty="phone"
    keyProperty="phone_key"
/>
```

### مثال 3: حقل مع ترجمة
```blade
<x-phone-input
    required
    label="{{__('lang.phone')}}"
    phoneProperty="phone"
    keyProperty="phone_key"
/>
```

### مثال 4: في Grid Layout
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input required label="{{__('lang.name')}}" wire:model="name"/>
    
    <x-phone-input
        required
        label="{{__('lang.phone')}}"
        phoneProperty="phone"
        keyProperty="phone_key"
    />
    
    <x-input label="{{__('lang.email')}}" wire:model="email"/>
</div>
```

## التخزين في قاعدة البيانات

عند حفظ البيانات في قاعدة البيانات، سيتم تخزين:
- `phone`: الرقم الوطني فقط (بدون رمز الدولة)
- `phone_key`: رمز الدولة (مثل: +20, +966)

مثال:
```php
User::create([
    'phone' => $this->phone,        // "1234567890"
    'phone_key' => $this->phone_key, // "+966"
]);
```

للعرض الكامل للرقم:
```blade
{{ $user->phone_key }}{{ $user->phone }}
// النتيجة: +9661234567890
```

## التصفير (Reset)

لتصفير الحقل عند فتح modal أو بعد الحفظ:

```php
public function resetData(): void
{
    $this->reset(['phone', 'phone_key']);
    $this->dispatch('tel-reset'); // مهم لتصفير المكتبة
}
```

## التحقق من الصحة (Validation)

```php
public function rules(): array
{
    return [
        'phone' => 'required|string|max:20|unique:users,phone',
        'phone_key' => 'required|string|max:5',
    ];
}
```

## رسائل الأخطاء

يتم عرض رسائل الأخطاء تلقائياً أسفل الحقل:
- `@error('phone')` - للرقم
- `@error('phone_key')` - لرمز الدولة

لا حاجة لإضافة أي كود إضافي في الـ view.

## التخصيص

يمكنك تخصيص الأنماط من خلال ملف `public/dashboard-asset/css/css.css`:

```css
/* تخصيص مظهر الحقل */
.iti__tel-input {
    /* أضف أنماطك المخصصة هنا */
}

/* تخصيص قائمة الدول */
.iti__country-list {
    /* أضف أنماطك المخصصة هنا */
}
```

## ملاحظات مهمة

1. ⚠️ لا تنسى إضافة `wire:ignore` للـ container الخارجي إذا قمت بتعديل المكون
2. ⚠️ استخدم دائماً `$wire.set()` بدلاً من `@entangle` لتجنب مشاكل التزامن
3. ⚠️ عند استخدام `reset()` في Livewire، تذكر أن ترسل حدث `tel-reset` لتصفير المكتبة
4. ✅ المكتبة تدعم RTL تلقائياً
5. ✅ يتم اكتشاف الدولة تلقائياً بناءً على IP المستخدم

## استكشاف الأخطاء

### المشكلة: الحقل لا يعمل
**الحل**: تأكد من إضافة مكتبة intl-tel-input في ملف head.blade.php

### المشكلة: لا يتم تصفير الحقل عند فتح modal
**الحل**: أضف `$this->dispatch('tel-reset')` في دالة resetData

### المشكلة: رسائل الأخطاء لا تظهر
**الحل**: تأكد من أن أسماء المتغيرات في rules() تطابق phoneProperty و keyProperty

## الدعم
للمزيد من المساعدة، راجع:
- [وثائق intl-tel-input](https://github.com/jackocnr/intl-tel-input)
- [وثائق Livewire](https://livewire.laravel.com/)

