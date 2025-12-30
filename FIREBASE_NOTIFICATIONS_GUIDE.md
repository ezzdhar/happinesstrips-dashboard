# دليل تتبع إشعارات Firebase (FCM)

## كيف تعرف أن الإشعار وصل بنجاح؟

### 1. فحص ملفات الـ Logs

#### في Laravel Log (storage/logs/laravel.log)

سيظهر لك أحد هذه الرسائل:

**✅ عند النجاح:**
```
[YYYY-MM-DD HH:MM:SS] local.INFO: FCM notification sent successfully
{
    "token": "fMYt3W8XSJqT...",
    "message_id": "projects/happiness-597ed/messages/0:1234567890"
}
```

**✅ عند تحضير الإشعار بنجاح:**
```
[YYYY-MM-DD HH:MM:SS] local.INFO: FCM Notification prepared successfully
{
    "user_id": 123,
    "title": "عنوان الإشعار",
    "body": "محتوى الإشعار",
    "data": {"id": 456, "type": "booking_trip"}
}
```

**❌ عند الفشل:**
```
[YYYY-MM-DD HH:MM:SS] local.ERROR: Failed to send FCM notification
{
    "token": "fMYt3W8XSJqT...",
    "error": "Requested entity was not found"
}
```

**⚠️ عند عدم وجود FCM Token:**
```
[YYYY-MM-DD HH:MM:SS] local.WARNING: No FCM token found for notifiable
{
    "notifiable_id": 123,
    "notifiable_type": "App\\Models\\User"
}
```

### 2. فحص قاعدة البيانات

#### جدول notifications
```sql
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\User' 
AND notifiable_id = [USER_ID]
ORDER BY created_at DESC 
LIMIT 10;
```

الإشعار يتم حفظه في قاعدة البيانات حتى لو فشل إرسال FCM.

### 3. اختبار الإشعارات يدوياً

#### عن طريق Tinker:
```php
php artisan tinker

$user = \App\Models\User::find(1);

// تأكد من وجود FCM token
$user->fcm_token;

// إرسال إشعار تجريبي
$title = ['en' => 'Test', 'ar' => 'اختبار'];
$body = ['en' => 'Test message', 'ar' => 'رسالة اختبار'];
$data = ['type' => 'test'];

$user->notify(new \App\Notifications\UserNotification($title, $body, $data));
```

### 4. فحص Firebase Console

1. افتح [Firebase Console](https://console.firebase.google.com/)
2. اختر مشروع `happiness-597ed`
3. اذهب إلى **Cloud Messaging** > **Campaign analytics**
4. ستجد إحصائيات الإشعارات المرسلة

### 5. نصائح لحل المشاكل

#### إذا لم تصل الإشعارات:

1. **تأكد من FCM Token:**
```php
$user = User::find($userId);
dd($user->fcm_token); // يجب أن يكون موجود وليس null
```

2. **تأكد من Firebase Credentials:**
```php
// في config/fcm.php
'credentials_path' => public_path('firebase.json'),
'project_id' => 'happiness-597ed',
```

3. **تحقق من أن Firebase.json صحيح:**
```bash
# يجب أن يحتوي على:
- project_id
- private_key
- client_email
```

4. **تأكد من أن الـ Queue يعمل (إذا كنت تستخدم queues):**
```bash
php artisan queue:work
```

### 6. الأخطاء الشائعة وحلولها

| الخطأ | السبب | الحل |
|------|------|-----|
| `No FCM token found` | المستخدم لم يسجل FCM token | تأكد من تسجيل Token من التطبيق |
| `Requested entity was not found` | Token غير صحيح أو انتهت صلاحيته | احذف Token القديم واطلب واحد جديد |
| `Firebase project ID is not set` | مشكلة في الإعدادات | تأكد من FIREBASE_PROJECT_ID في .env |
| `Failed to send FCM notification` | مشكلة في الاتصال أو Credentials | راجع firebase.json وتأكد من صحة البيانات |

### 7. مراقبة الإشعارات في الوقت الفعلي

```bash
# شاهد الـ logs مباشرة
tail -f storage/logs/laravel.log | grep -i "FCM"
```

أو في PowerShell:
```powershell
Get-Content storage\logs\laravel.log -Wait -Tail 50 | Select-String "FCM"
```

### 8. API Endpoint لاختبار الإشعارات

يمكنك استخدام endpoint الموجود:
```bash
POST /api/test-notification
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "title": {
        "en": "Test Notification",
        "ar": "إشعار تجريبي"
    },
    "body": {
        "en": "This is a test",
        "ar": "هذا اختبار"
    }
}
```

### 9. المؤشرات على نجاح الإرسال

✅ **الإشعار نجح 100% إذا:**
- وجدت رسالة `FCM notification sent successfully` في الـ logs
- يحتوي على `message_id` من Firebase
- المستخدم استلم الإشعار على جهازه

⚠️ **الإشعار تم تحضيره لكن لم يصل:**
- وجدت `FCM Notification prepared successfully`
- لكن لا يوجد `FCM notification sent successfully` بعدها
- تحقق من الـ logs بعدها مباشرة للخطأ

❌ **الإشعار فشل تماماً:**
- وجدت `Failed to send FCM notification` أو
- وجدت `FCM Notification preparation failed`

