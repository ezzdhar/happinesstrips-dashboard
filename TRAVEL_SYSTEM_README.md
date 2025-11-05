# نظام إدارة الرحلات السياحية - Travel Management System

## الموديلات والجداول المنشأة

### 1. MainCategory (الأقسام الرئيسية)
- الحقول: id, name (translatable), image_id, is_active, timestamps
- العلاقات: hasMany(SubCategory), hasMany(Section), morphMany(File)

### 2. SubCategory (الأقسام الفرعية)
- الحقول: id, main_category_id, name (translatable), image_id, is_active, timestamps
- العلاقات: belongsTo(MainCategory), hasMany(Section), morphMany(File)

### 3. Hotel (الفنادق)
- الحقول: id, name (translatable), latitude, longitude, address (translatable), description (translatable), rating, facilities (translatable), is_active, timestamps
- العلاقات: hasMany(Room), belongsToMany(Section), morphMany(File)

### 4. Room (الغرف)
- الحقول: id, hotel_id, name (translatable), adults_count, children_count, price (json), includes (translatable), is_active, timestamps
- العلاقات: belongsTo(Hotel), morphMany(File)

### 5. Section (العروض/السكاشن)
- الحقول: id, main_category_id, sub_category_id, name (translatable), price (json), duration_from, duration_to, people_count, notes (translatable), program (translatable), is_featured, is_active, timestamps
- العلاقات: belongsTo(MainCategory), belongsTo(SubCategory), belongsToMany(Hotel), morphMany(File)

### 6. File (الملفات والصور)
- الحقول: id, path, type, fileable_id, fileable_type, timestamps
- العلاقات: morphTo(fileable)

## خطوات التشغيل

### 1. تشغيل المايجريشن
```bash
php artisan migrate
```

### 2. تشغيل السيدر لإضافة البيانات التجريبية
```bash
php artisan db:seed
```

أو لتشغيل سيدر معين:
```bash
php artisan db:seed --class=MainCategorySeeder
php artisan db:seed --class=SubCategorySeeder
php artisan db:seed --class=HotelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=SectionSeeder
```

### 3. إعادة تهيئة قاعدة البيانات (اختياري)
لحذف جميع الجداول وإعادة إنشائها مع البيانات:
```bash
php artisan migrate:fresh --seed
```

## استخدام الموديلات

### أمثلة على الاستعلامات

#### 1. الحصول على جميع الأقسام النشطة
```php
$categories = MainCategory::active()->get();
```

#### 2. البحث في الفنادق
```php
$hotels = Hotel::active()->filter($search)->get();
```

#### 3. الحصول على الغرف في فندق معين
```php
$rooms = Room::where('hotel_id', $hotel_id)->active()->get();
```

#### 4. الحصول على العروض المميزة
```php
$sections = Section::active()->featured()->get();
```

#### 5. الحصول على الصور الخاصة بفندق
```php
$hotel = Hotel::find($id);
$images = $hotel->files;
```

#### 6. الحصول على الفنادق المرتبطة بعرض معين
```php
$section = Section::find($id);
$hotels = $section->hotels;
```

### استخدام الترجمة

#### الحصول على القيمة المترجمة
```php
$hotel = Hotel::find(1);
echo $hotel->getTranslation('name', 'ar'); // الاسم بالعربية
echo $hotel->getTranslation('name', 'en'); // الاسم بالإنجليزية
```

#### أو استخدام app()->getLocale()
```php
app()->setLocale('ar');
echo $hotel->name; // سيعرض الاسم بالعربية تلقائياً
```

#### حفظ بيانات مترجمة
```php
$hotel = new Hotel();
$hotel->setTranslation('name', 'ar', 'فندق جديد');
$hotel->setTranslation('name', 'en', 'New Hotel');
$hotel->save();
```

أو:
```php
Hotel::create([
    'name' => [
        'ar' => 'فندق جديد',
        'en' => 'New Hotel',
    ],
    'is_active' => true,
]);
```

### استخدام الأسعار (JSON)
```php
$room = Room::find(1);
echo $room->price['egp']; // السعر بالجنيه المصري
echo $room->price['usd']; // السعر بالدولار

// تحديث السعر
$room->price = [
    'egp' => 1500,
    'usd' => 50,
];
$room->save();
```

## الملاحظات المهمة

1. **الترجمة**: جميع الحقول النصية (name, description, address, notes, program, facilities, includes) تدعم الترجمة للعربية والإنجليزية.

2. **حقل is_active**: موجود في جميع الجداول الرئيسية للتحكم في تفعيل أو تعطيل العناصر.

3. **العلاقات Polymorphic**: جدول files يستخدم علاقة polymorphic لتخزين الصور والملفات لجميع الموديلات.

4. **الأسعار**: تُخزن كـ JSON بمفاتيح egp و usd لسهولة التعامل مع عملات متعددة.

5. **Scopes المتاحة**:
   - `active()`: للحصول على العناصر النشطة فقط
   - `filter($search)`: للبحث في الحقول المترجمة
   - `featured()`: للحصول على العروض المميزة (Section فقط)

6. **البيانات التجريبية**: تحتوي السيدرز على بيانات عربية وإنجليزية جاهزة للاختبار.

## البيانات التجريبية المضافة

- 10 أقسام رئيسية (5 محددة + 5 عشوائية)
- 35 قسم فرعي (20 محدد + 15 عشوائي)
- 18 فندق (3 محددة + 15 عشوائي)
- كل فندق يحتوي على 4 أنواع غرف + 30 غرفة عشوائية إضافية
- 23 عرض سياحي (3 محددة + 20 عشوائي)
- صور متعددة لكل عنصر باستخدام العلاقة Polymorphic

## التطوير المستقبلي

يمكن إضافة:
- نظام الحجوزات
- نظام الدفع
- نظام التقييمات والمراجعات
- نظام الخصومات والعروض
- لوحة تحكم لإدارة الرحلات

