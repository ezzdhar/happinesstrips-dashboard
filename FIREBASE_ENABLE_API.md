# تفعيل Firebase Cloud Messaging API

## المشكلة
عند إرسال إشعار FCM، يظهر الخطأ التالي:
```
HTTP request returned status code 404
Error: Requested entity was not found
Status: NOT_FOUND
```

هذا يعني أن **Firebase Cloud Messaging API (V1) غير مفعل** في مشروع Firebase الخاص بك.

---

## الحل: تفعيل FCM API

### الخطوة 1: الذهاب إلى Google Cloud Console

1. افتح [Google Cloud Console](https://console.cloud.google.com/)
2. اختر مشروع `happiness-597ed` من القائمة العلوية
3. إذا لم تجد المشروع، تأكد أنك مسجل دخول بنفس الحساب المستخدم في Firebase

### الخطوة 2: تفعيل Firebase Cloud Messaging API

**الطريقة الأولى: عبر الرابط المباشر**

اذهب مباشرة إلى:
```
https://console.cloud.google.com/apis/library/fcm.googleapis.com?project=happiness-597ed
```

ثم اضغط **Enable** (تفعيل)

**الطريقة الثانية: عبر البحث في Cloud Console**

1. في Google Cloud Console، اذهب إلى **APIs & Services** → **Library**
2. ابحث عن `Firebase Cloud Messaging API`
3. اضغط على النتيجة الأولى
4. اضغط **Enable** (تفعيل)
5. انتظر بضع ثوانٍ حتى يكتمل التفعيل

### الخطوة 3: تفعيل FCM API (Legacy) اختياري

إذا كنت تستخدم الإصدار القديم من FCM أيضاً:

1. ابحث عن `Firebase Cloud Messaging API (Legacy)`
2. اضغط **Enable**

---

## التحقق من التفعيل

### 1. التحقق من Google Cloud Console

اذهب إلى:
```
https://console.cloud.google.com/apis/dashboard?project=happiness-597ed
```

تأكد من وجود `Firebase Cloud Messaging API` في قائمة الـ APIs المفعلة.

### 2. التحقق من Firebase Console

1. اذهب إلى [Firebase Console](https://console.firebase.google.com/)
2. اختر مشروع `happiness-597ed`
3. اذهب إلى **Project Settings** → **Cloud Messaging**
4. تأكد من وجود **Cloud Messaging API (V1)** مفعل

---

## بعد التفعيل

### جرب إرسال إشعار اختباري

بعد تفعيل API، انتظر دقيقة واحدة ثم جرب إرسال إشعار من التطبيق أو API.

### إذا استمر الخطأ

تحقق من:

1. **Project ID صحيح**
   ```bash
   php artisan firebase:check
   ```
   تأكد أن Project ID في firebase.json يطابق المشروع في Firebase Console

2. **Service Account لديه الصلاحيات المطلوبة**
   
   في Firebase Console:
   - اذهب إلى **Project Settings** → **Service Accounts**
   - تأكد من أن Service Account موجود ونشط
   - الصلاحيات المطلوبة: `Firebase Cloud Messaging API Admin`

3. **تحديث Service Account Key**
   
   إذا كان Service Account قديم، قد تحتاج لإنشاء Key جديد:
   - Firebase Console → **Project Settings** → **Service Accounts**
   - اضغط **Generate New Private Key**
   - حمّل الملف الجديد وارفعه إلى السيرفر:
     ```bash
     php artisan firebase:update
     ```

---

## أخطاء شائعة أخرى

### 1. الخطأ: 403 Permission Denied

**السبب:** Service Account لا يملك الصلاحيات المطلوبة

**الحل:**
1. اذهب إلى [Google Cloud Console IAM](https://console.cloud.google.com/iam-admin/iam?project=happiness-597ed)
2. ابحث عن Service Account email: `firebase-adminsdk-fbsvc@happiness-597ed.iam.gserviceaccount.com`
3. تأكد من وجود الدور (Role): `Firebase Cloud Messaging API Admin` أو `Editor`

### 2. الخطأ: 401 Unauthorized

**السبب:** Service Account Key غير صحيح أو منتهي الصلاحية

**الحل:**
1. أنشئ Service Account Key جديد من Firebase Console
2. حدّث الملف على السيرفر:
   ```bash
   php artisan firebase:update
   ```

### 3. الخطأ: Invalid Token

**السبب:** FCM Token منتهي الصلاحية أو غير صحيح

**الحل:**
- المستخدم يحتاج لتسجيل دخول من جديد للحصول على FCM Token جديد
- أو امسح FCM Token من قاعدة البيانات وسيتم توليد واحد جديد

---

## الأوامر المفيدة

### فحص Firebase Configuration
```bash
php artisan firebase:check
```

### تحديث Firebase Credentials
```bash
php artisan firebase:update
```

### مراقبة Logs في الوقت الفعلي
```bash
tail -f storage/logs/laravel.log | grep -i fcm
```

---

## روابط مفيدة

- [Firebase Console](https://console.firebase.google.com/project/happiness-597ed)
- [Google Cloud Console - APIs](https://console.cloud.google.com/apis/dashboard?project=happiness-597ed)
- [FCM API Documentation](https://firebase.google.com/docs/cloud-messaging/migrate-v1)
- [Service Accounts IAM](https://console.cloud.google.com/iam-admin/iam?project=happiness-597ed)

---

## ملاحظة مهمة

تأكد من:
- ✅ استخدام Firebase Cloud Messaging API (V1) - الإصدار الجديد
- ✅ تفعيل API في **كل من** Firebase Console و Google Cloud Console
- ✅ انتظار 1-2 دقيقة بعد التفعيل قبل المحاولة مرة أخرى
- ✅ Service Account Key حديث ولم يتم حذفه أو تعطيله

