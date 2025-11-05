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

<div class="form-control w-full">
    {{-- Label --}}
    @if($label)
        <label for="{{ $uuid }}" class="label">
            <span class="label-text">
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
        <input
            x-ref="phoneInput"
            type="tel"
            id="{{ $uuid }}"
            {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
            @if($required) required @endif
        />
    </div>

    {{-- Error Messages --}}
    @error($phoneProperty)
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
    @error($keyProperty)
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

