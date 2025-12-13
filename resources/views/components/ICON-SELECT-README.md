# Icon Select Component

## 📝 الوصف

Component قابل لإعادة الاستخدام لاختيار أيقونات Font Awesome مع إمكانية البحث والفلترة.

## ✨ المميزات

-   ✅ 200+ أيقونة من Font Awesome 6
-   ✅ بحث مباشر بالعربي والإنجليزي
-   ✅ عرض الأيقونات بجانب الأسماء
-   ✅ دعم Livewire wire:model
-   ✅ دعم validation errors
-   ✅ تصميم responsive مع RTL support

## 🚀 الاستخدام

### مثال بسيط

```blade
<x-icon-select
    label="Icon"
    wire:model="icon"
    :value="$icon"
/>
```

### مثال كامل مع جميع الخيارات

```blade
<x-icon-select
    label="{{ __('lang.icon') }}"
    wire:model="icon"
    :value="$icon"
    placeholder="{{ __('lang.select') }} {{ __('lang.icon') }}"
    hint="{{ __('lang.icon_hint') }}"
    :error="$errors->first('icon')"
    required
/>
```

## 📋 Parameters

| Parameter     | Type    | Required | Default       | Description                       |
| ------------- | ------- | -------- | ------------- | --------------------------------- |
| `label`       | string  | No       | 'Icon'        | النص الظاهر فوق الحقل             |
| `wire:model`  | string  | Yes      | -             | اسم المتغير في Livewire           |
| `value`       | string  | No       | ''            | القيمة الحالية للأيقونة           |
| `placeholder` | string  | No       | 'Select Icon' | النص الظاهر عند عدم اختيار أيقونة |
| `hint`        | string  | No       | null          | نص مساعد يظهر أسفل الحقل          |
| `error`       | string  | No       | null          | رسالة خطأ validation              |
| `required`    | boolean | No       | false         | إضافة علامة \* للحقول المطلوبة    |

## 💡 أمثلة الاستخدام

### في Livewire Component

```php
class MyComponent extends Component
{
    public $icon;

    public function rules()
    {
        return [
            'icon' => 'required|string',
        ];
    }
}
```

```blade
<x-icon-select
    label="اختر الأيقونة"
    wire:model="icon"
    :value="$icon"
    :error="$errors->first('icon')"
    required
/>
```

### في Form عادي

```blade
<form>
    <x-icon-select
        label="Service Icon"
        wire:model="serviceIcon"
        :value="$serviceIcon"
        hint="اختر أيقونة تمثل الخدمة"
    />
</form>
```

## 🎨 التخصيص

الـ component يستخدم DaisyUI classes، يمكنك تخصيص الألوان والأنماط من خلال:

-   تعديل ملف `/resources/views/components/icon-select.blade.php`
-   استخدام Tailwind classes مباشرة

## 📦 الأيقونات المتوفرة

الـ component يحتوي على أكثر من 200 أيقونة مقسمة إلى فئات:

-   🏠 Home & Living
-   💻 Technology & Electronics
-   🌡️ Comfort & Climate
-   🔒 Security & Safety
-   🏊 Outdoor & Recreation
-   🚗 Transportation & Parking
-   ♿ Accessibility & Services
-   🐾 Animals & Pets
-   🧹 Cleaning & Laundry
-   🛒 Shopping & Commerce
-   📱 Communication
-   💼 Office & Business
-   📚 Education & Learning
-   🎬 Media & Entertainment
-   ⚙️ UI & Controls
-   وأكثر...

## 🔄 التحديثات المستقبلية

لإضافة أيقونات جديدة، قم بتعديل array `$solidIcons` في ملف الـ component.
