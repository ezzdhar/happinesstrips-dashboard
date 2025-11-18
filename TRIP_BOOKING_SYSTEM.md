# ملخص التعديلات على نظام حساب أسعار الرحلات

## التعديلات المنفذة

### 1. إنشاء Trait جديد: `CalculatesTripBookingPrice`
**المسار:** `app/Traits/CalculatesTripBookingPrice.php`

هذا الـ Trait مشابه لـ `CalculatesHotelBookingPrice` ولكن مخصص للرحلات. يحتوي على:

#### الوظائف الرئيسية:

- **`calculateTripPriceInternal()`**: الدالة الرئيسية لحساب سعر الرحلة
   - تدعم الرحلات الثابتة (Fixed) والمرنة (Flexible)
   - تحسب أسعار البالغين والأطفال بناءً على أعمارهم الفعلية
   - تطبق النسب المختلفة للأطفال:
      - الطفل الأول: `first_child_price_percentage`
      - الطفل الثاني: `second_child_price_percentage`
      - الطفل الثالث: `third_child_price_percentage`
      - الأطفال الإضافيين: `additional_child_price_percentage`

- **`getTripBasePrice()`**: تحصل على السعر الأساسي حسب نوع الرحلة
   - **رحلة ثابتة**: السعر هو ثمن الشخص البالغ للرحلة الكاملة
   - **رحلة مرنة**: السعر هو ثمن الشخص البالغ لليلة الواحدة

- **`calculateAdultsPrice()`**: تحسب إجمالي سعر البالغين

- **`calculateChildrenPrice()`**: تحسب أسعار الأطفال مع تفصيل كامل:
   - الأطفال المجانيين (أقل من `free_child_age`)
   - الطفل الأول، الثاني، الثالث (بنسب مختلفة)
   - الأطفال الإضافيين
   - الأطفال في سن البلوغ (>= `adult_age`)

- **`getChildPrice()`**: تحسب سعر طفل واحد حسب ترتيبه

- **`convertCurrency()`**: تحول العملة من EGP إلى USD إذا لزم الأمر

- **`getTripPricingSummary()`**: تعطي ملخص مبسط للأسعار للعرض في الواجهة

---

### 2. تحديث `TripPricingService`
**المسار:** `app/Services/TripPricingService.php`

#### التعديلات:

- إضافة `use CalculatesTripBookingPrice;` للاستفادة من الـ Trait
- إضافة دالة جديدة: **`calculateTripPriceWithAges()`**
   - تستقبل أعمار الأطفال الفعلية كـ array
   - تستخدم `calculateTripPriceInternal()` من الـ Trait

- تحديث الدالة القديمة **`calculateTripPrice()`** للتوافق العكسي:
   - تحول عدد الأطفال إلى أعمار تقريبية
   - تستدعي الدالة الجديدة `calculateTripPriceInternal()`

---

### 3. تحديث `CreateBookingTrip` Component
**المسار:** `app/Livewire/Dashboard/BookingTrip/CreateBookingTrip.php`

#### التعديلات:

1. **إزالة `$free_children_count`**: لم تعد هناك حاجة لهذا المتغير لأن النظام يحدد الأطفال المجانيين تلقائياً بناءً على أعمارهم

2. **تحديث `calculatePrice()`**:
   ```php
   // استخراج أعمار الأطفال من المسافرين
   $childrenAges = [];
   foreach ($this->travelers as $traveler) {
       if (isset($traveler['type']) && $traveler['type'] === 'child' && !empty($traveler['age'])) {
           $childrenAges[] = (int) $traveler['age'];
       }
   }

   // استخدام الدالة الجديدة مع الأعمار الفعلية
   $result = TripPricingService::calculateTripPriceWithAges(
       trip: $trip,
       checkIn: $this->check_in,
       checkOut: $this->check_out,
       adultsCount: (int) $this->adults_count,
       childrenAges: $childrenAges,
       currency: $this->currency
   );
   ```

3. **إضافة `updatedTravelers()`**: إعادة حساب السعر عند تغيير أعمار المسافرين

4. **تحديث `syncTravelers()`**: إزالة المنطق الخاص بـ `free_children_count`

5. **تحديث قواعد التحقق**: إزالة `free_children_count` من القواعد

---

### 4. إنشاء اختبارات شاملة
**المسار:** `tests/Feature/Services/TripPricingServiceTest.php`

تم إنشاء 9 اختبارات تغطي:
- حساب سعر رحلة ثابتة للبالغين فقط
- حساب سعر رحلة ثابتة مع طفل واحد
- حساب سعر رحلة ثابتة مع عدة أطفال
- حساب سعر رحلة ثابتة مع أطفال مجانيين
- حساب سعر رحلة مرنة للبالغين فقط
- حساب سعر رحلة مرنة مع أطفال
- تحويل العملة إلى USD
- التعامل مع أطفال إضافيين بعد الطفل الثالث
- التعامل مع الأطفال في سن البلوغ

---

## كيفية عمل النظام الجديد

### للرحلات الثابتة (Fixed):
- **سعر البالغ**: `price` × عدد البالغين
- **سعر الطفل الأول**: `price` × `first_child_price_percentage` / 100
- **سعر الطفل الثاني**: `price` × `second_child_price_percentage` / 100
- **سعر الطفل الثالث**: `price` × `third_child_price_percentage` / 100
- **الأطفال الإضافيين**: `price` × `additional_child_price_percentage` / 100
- **الأطفال المجانيين**: 0 (أعمار < `free_child_age`)

### للرحلات المرنة (Flexible):
نفس الحسابات أعلاه ولكن **مضروبة في عدد الليالي**

### مثال عملي:

رحلة ثابتة:
- السعر: 5000 جنيه
- مدة الرحلة: 7 ليالي
- عدد البالغين: 2
- أعمار الأطفال: [8, 6, 3]
- `free_child_age`: 5
- `first_child_price_percentage`: 50%
- `second_child_price_percentage`: 40%

**الحساب:**
- البالغين: 2 × 5000 = 10,000 جنيه
- الطفل الأول (عمر 8): 5000 × 50% = 2,500 جنيه
- الطفل الثاني (عمر 6): 5000 × 40% = 2,000 جنيه
- الطفل الثالث (عمر 3): مجاني (< 5 سنوات)
- **الإجمالي: 14,500 جنيه**

---

## الملفات المعدلة

1. ✅ `app/Traits/CalculatesTripBookingPrice.php` (جديد)
2. ✅ `app/Services/TripPricingService.php` (محدث)
3. ✅ `app/Livewire/Dashboard/BookingTrip/CreateBookingTrip.php` (محدث)
4. ✅ `tests/Feature/Services/TripPricingServiceTest.php` (جديد)

---

## الخطوات التالية المقترحة

1. تشغيل الاختبارات للتأكد من عمل النظام بشكل صحيح
2. تحديث واجهة المستخدم إذا لزم الأمر لإزالة حقل `free_children_count`
3. اختبار النظام يدوياً من خلال إنشاء حجز رحلة جديد

---

## ملاحظات مهمة

- النظام الآن يعتمد بالكامل على **أعمار الأطفال الفعلية** بدلاً من عدد الأطفال المجانيين
- يتم تحديد ما إذا كان الطفل مجاني أو مدفوع **تلقائياً** بناءً على عمره
- النظام يدعم عدد غير محدود من الأطفال
- التوافق العكسي محفوظ من خلال الدالة القديمة `calculateTripPrice()`
