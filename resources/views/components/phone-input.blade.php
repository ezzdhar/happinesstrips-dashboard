{{--
  هذا الكومبوننت سيقبل اسم المتغير الخاص بالرقم والمفتاح
  'phoneProperty' => اسم متغير الرقم في Livewire (مثل: 'phone')
  'keyProperty'   => اسم متغير المفتاح في Livewire (مثل: 'phone_key')
  'label'         => النص الذي سيظهر فوق الحقل (اختياري)
  'required'      => هل الحقل مطلوب؟ (اختياري)
--}}
@props([
    'phoneProperty', // الاسم كـ String
    'keyProperty',   // الاسم كـ String
    'label' => null, // النص الذي سيظهر فوق الحقل
    'required' => false, // هل الحقل مطلوب؟
])

@php
    $uuid = "phone-input-" . Str::random(8);
@endphp


@assets()
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/intlTelInput.min.js"></script>
<style>
    /* تحسين مظهر intl-tel-input */
    .iti {
        display: block;
        width: 100%;
    }

    .iti__input,
    .iti__tel-input {
        width: 100% !important;
    }

    /* تطبيق نفس أنماط DaisyUI للـ input */
    .iti__tel-input {
        @apply input input-bordered w-full;
    }

    .iti__tel-input:focus {
        border: 2px solid #3b25c1 !important;
        outline: 0 !important;
    }

    /* تحسين مظهر قائمة الدول */
    .iti__country-list {
        max-height: 200px;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .iti__country {
        padding: 8px 12px;
    }

    .iti__selected-country {
        padding: 0 8px;
    }

    /* تحسين عرض علم الدولة ورمز الاتصال */
    .iti__selected-dial-code {
        margin-left: 6px;
    }

    /* دعم RTL */
    [dir="rtl"] .iti__selected-dial-code {
        margin-left: 0;
        margin-right: 6px;
    }

    /* تحسين المظهر في حالة الـ disabled */
    .iti__tel-input:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* تحسين الحدود عند التركيز */
    .iti--container:focus-within {
        outline: none;
    }

    /* تحسين مظهر السهم */
    .iti__arrow {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid #6b7280;
    }

    /* تحسين الـ placeholder */
    .iti__tel-input::placeholder {
        color: #9ca3af;
        opacity: 0.7;
    }

    /* توافق أفضل مع الـ grid */
    .form-control .iti {
        width: 100%;
    }

    /* تحسين المظهر على الشاشات الصغيرة */
    @media (max-width: 640px) {
        .iti__country-list {
            max-height: 150px;
        }

        .iti__selected-country {
            padding: 0 6px;
        }
    }

</style>
@endassets

<div class="form-control w-full">
    {{-- Label --}}
    @if($label)
        <label for="{{ $uuid }}" class="label">
            <span class="label-text fieldset-legend mb-0.5">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif

    {{-- Phone Input Container --}}
    <div
        wire:ignore
        x-data="{ iti: null }"
        x-init="
            const input = $refs.phoneInput;

            // 1. تهيئة المكتبة
            iti = window.intlTelInput(input, {
                loadUtils: () => import('https://cdn.jsdelivr.net/npm/intl-tel-input@25.12.4/build/js/utils.js'),
                initialCountry: 'auto',
                geoIpLookup: (success, failure) => {
                    fetch('https://ipapi.co/json')
                        .then(res => res.json())
                        .then(data => success(data.country_code))
                        .catch(() => failure());
                },
                nationalMode: true,
                separateDialCode: true,
            });

            // 2. تحميل القيمة الأولية
            const initialPhone = $wire.get('{{ $phoneProperty }}');
            const initialKey = $wire.get('{{ $keyProperty }}');
            if (initialPhone && initialKey) {
                iti.setNumber(initialKey + initialPhone);
            }

            // 3. إرسال البيانات إلى Livewire عند الكتابة
            input.addEventListener('input', () => {
                const nationalNumber = input.value;
                const dialCode = '+' + iti.getSelectedCountryData().dialCode;

                $wire.set('{{ $phoneProperty }}', nationalNumber);
                $wire.set('{{ $keyProperty }}', dialCode);
            });

            // 4. إرسال المفتاح عند تغيير الدولة
            input.addEventListener('countrychange', () => {
                const dialCode = '+' + iti.getSelectedCountryData().dialCode;
                $wire.set('{{ $keyProperty }}', dialCode);
            });
        "
        @tel-reset.window="
            if(iti) {
                iti.setNumber('');
                $refs.phoneInput.value = '';
            }
        "
    >
        <input x-ref="phoneInput" type="tel" id="{{ $uuid }}" {{ $attributes->merge(['class' => 'input input-bordered w-full ']) }}@if($required) required @endif/>
    </div>

    {{-- Error Messages --}}
    @error($phoneProperty)
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
    @error($keyProperty)
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

