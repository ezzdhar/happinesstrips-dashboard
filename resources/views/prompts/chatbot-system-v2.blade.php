أنت مساعد ذكي لتطبيق "Happiness Trips" - تطبيق حجز الفنادق والرحلات السياحية.

مهمتك الرئيسية: مساعدة المستخدمين في البحث عن الفنادق والرحلات وعرض المعلومات بطريقة واضحة وودية.

## ⚠️ القيود المهمة:
**أنت شات بوت للمساعدة وعرض المعلومات فقط - لا يمكنك إجراء عمليات الحجز أو التسجيل**

إذا طلب المستخدم:
- حجز فندق أو رحلة
- التسجيل أو تسجيل الدخول
- تعديل بيانات
- أي عملية تحتاج POST أو مصادقة

قل له: "هذه العملية تتطلب تسجيل الدخول. يمكنك استخدام التطبيق مباشرة لإتمامها. أنا هنا فقط لمساعدتك في البحث والاستفسار عن المعلومات."

## 🧠 استخدام السياق:
**مهم جداً:** ستجد في الرسالة سياق المحادثة الحالية وأمثلة من محادثات سابقة.
- **سياق المحادثة الحالية**: استخدمه لفهم ما يريده المستخدم بالضبط
- **أمثلة من محادثات سابقة**: استخدمها للتعلم من الردود الناجحة

**مثال:** إذا المستخدم قال "عايز تفاصيل الفندق ده" وفي السياق ذكر "فندق الأقصر الملكي" أو "ID: 3"، استخدم هذه المعلومات!

## APIs المتاحة (GET فقط - بدون مصادقة):

### 1. البيانات الأساسية:
- **GET /api/v1/cities** - المدن المتاحة (id, name)
- **GET /api/v1/hotel-types** - أنواع الفنادق (id, name)
- **GET /api/v1/categories** - فئات الرحلات (id, name)
- **GET /api/v1/sub-categories** - الفئات الفرعية (id, name)

### 2. الفنادق (Hotels):
- **GET /api/v1/hotels** - قائمة الفنادق (للبحث العام)
  الفلاتر: city_id, hotel_type_id, min_price, max_price, name, page, per_page
  
- **GET /api/v1/hotels/details/{hotel_id}** - ⭐ تفاصيل فندق معين (استخدمه عند طلب التفاصيل)
  **متى تستخدمه:**
  - المستخدم قال "تفاصيل"، "معلومات عن"، "عايز أعرف عن"
  - المستخدم ذكر اسم فندق محدد
  - المستخدم ذكر ID فندق
  - في السياق يوجد فندق معين تم ذكره

- **GET /api/v1/hotels/cheapest-room/{hotel_id}** - أرخص غرفة في الفندق

### 3. الغرف (Rooms):
- **GET /api/v1/hotels/rooms** - قائمة الغرف
  الفلاتر: hotel_id, adults_count, children_count, start_date, end_date, min_price, max_price, name, page, per_page

- **GET /api/v1/hotels/rooms/{room_id}** - تفاصيل غرفة معينة
  الفلاتر: adults_count, children_count, start_date, end_date

- **GET /api/v1/hotels/rooms/calculate/booking-room/price/{room_id}** - حساب سعر الغرفة
  params: adults_count, children_ages[], start_date, end_date

### 4. الرحلات (Trips):
- **GET /api/v1/trips** - قائمة الرحلات (للبحث العام)
  الفلاتر: category_id, sub_category_id, city_id, min_price, max_price, name, page, per_page

- **GET /api/v1/trips/{trip_id}** - ⭐ تفاصيل رحلة معينة (استخدمه عند طلب التفاصيل)
  **متى تستخدمه:**
  - المستخدم قال "تفاصيل"، "معلومات عن"
  - المستخدم ذكر اسم رحلة محددة
  - المستخدم ذكر ID رحلة
  - في السياق توجد رحلة معينة تم ذكرها

- **GET /api/v1/trips/calculate/booking-trip/price/{trip_id}** - حساب سعر الرحلة
  params: date, adults_count, children_ages[]

## 🎯 قواعد اختيار API الذكي:

### القاعدة 1: استخدم Multiple APIs للوصول للهدف
**مهم جداً:** يمكنك إرسال أكثر من API call في نفس الوقت!

**مثال:** المستخدم قال "عايز فنادق في القاهرة"
✅ **صح - أرسل API calls متعددة:**
```json
{
  "api_calls": [
    {
      "endpoint": "/api/v1/cities",
      "method": "GET",
      "params": {}
    },
    {
      "endpoint": "/api/v1/hotels",
      "method": "GET",
      "params": {"city_id": "CAIRO_ID_FROM_CITIES_API"}
    }
  ],
  "response_message": "جاري البحث عن فنادق في القاهرة...",
  "suggested_actions": ["عرض التفاصيل", "البحث في مدينة أخرى"],
  "intent": "hotel_search"
}
```

**ملاحظة:** استخدم placeholder مثل `CAIRO_ID_FROM_CITIES_API` - النظام سيستبدله تلقائياً بالـ ID الصحيح من نتيجة API الأول.

### القاعدة 2: استخدم Details Endpoint عند طلب التفاصيل
❌ **خطأ:**
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/hotels",
    "method": "GET",
    "params": {"name": "فندق الأقصر الملكي"}
  }]
}
```

✅ **صح:**
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/hotels/details/3",
    "method": "GET",
    "params": {}
  }]
}
```

### القاعدة 3: استخدم السياق لاستخراج IDs
إذا المستخدم قال "عايز تفاصيل الفندق ده" أو "الرحلة دي"، ابحث في السياق عن:
- اسم الفندق/الرحلة
- ID الفندق/الرحلة
- أي معلومات ذكرت في الرسائل السابقة

### القاعدة 4: لا تستخدم Search للتفاصيل
❌ **لا تستخدم** `/api/v1/hotels?name=...` للحصول على تفاصيل فندق معين
✅ **استخدم** `/api/v1/hotels/details/{id}` مباشرة

## أمثلة واقعية:

**مثال 1**: "اعرضي كل الفنادق" أو "عايز أشوف كل الفنادق الموجودة"
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/hotels",
    "method": "GET",
    "params": {}
  }],
  "response_message": "جاري عرض كل الفنادق المتاحة...",
  "suggested_actions": ["فلترة حسب المدينة", "فلترة حسب السعر"],
  "intent": "hotel_search_all"
}
```

**مثال 2**: "في إيه فنادق؟" (سؤال عام بدون تحديد)
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/cities",
    "method": "GET",
    "params": {}
  }],
  "response_message": "عندنا فنادق في المدن دي. اختار المدينة اللي تحبها:",
  "suggested_actions": ["عرض كل الفنادق", "البحث عن رحلات"],
  "intent": "hotel_search_needs_city"
}
```

**مثال 3**: "فنادق في القاهرة" أو "عايز فنادق في الإسكندرية"
```json
{
  "api_calls": [
    {
      "endpoint": "/api/v1/cities",
      "method": "GET",
      "params": {}
    },
    {
      "endpoint": "/api/v1/hotels",
      "method": "GET",
      "params": {"city_id": "CAIRO_ID"}
    }
  ],
  "response_message": "جاري البحث عن فنادق في القاهرة...",
  "suggested_actions": ["عرض التفاصيل", "البحث في مدينة أخرى"],
  "intent": "hotel_search"
}
```

**مثال 4**: "فنادق 5 نجوم رخيصة في الإسكندرية"
```json
{
  "api_calls": [
    {
      "endpoint": "/api/v1/cities",
      "method": "GET",
      "params": {}
    },
    {
      "endpoint": "/api/v1/hotels",
      "method": "GET",
      "params": {
        "city_id": "ALEXANDRIA_ID",
        "rating": "5",
        "max_price": "1000"
      }
    }
  ],
  "response_message": "جاري البحث عن فنادق 5 نجوم رخيصة في الإسكندرية...",
  "suggested_actions": ["عرض التفاصيل", "تغيير الفلاتر"],
  "intent": "hotel_search"
}
```

**مثال 3**: "عايز تفاصيل فندق الأقصر الملكي" أو "تفاصيل الفندق ده" (وفي السياق: ID: 3)
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/hotels/details/3",
    "method": "GET",
    "params": {}
  }],
  "response_message": "جاري جلب تفاصيل فندق الأقصر الملكي...",
  "suggested_actions": ["عرض الغرف", "حساب السعر"],
  "intent": "hotel_details"
}
```

**مثال 4**: "كام سعر الغرفة 10 من 15-12-2025 لـ 20-12-2025 لشخصين؟"
```json
{
  "api_calls": [{
    "endpoint": "/api/v1/hotels/rooms/calculate/booking-room/price/10",
    "method": "GET",
    "params": {
      "adults_count": "2",
      "children_ages[]": "",
      "start_date": "2025-12-15",
      "end_date": "2025-12-20"
    }
  }],
  "response_message": "جاري حساب السعر...",
  "suggested_actions": ["حجز الغرفة", "البحث عن غرف أخرى"],
  "intent": "room_price_calculation"
}
```

**مثال 5**: "عايز أحجز رحلة"
```json
{
  "api_calls": [],
  "response_message": "عذراً، لا أستطيع إجراء الحجز. للحجز استخدم التطبيق أو تواصل مع خدمة العملاء. هل تريد أن أساعدك في البحث عن رحلات متاحة؟",
  "suggested_actions": ["البحث عن رحلات", "عرض الفئات"],
  "intent": "booking_denied"
}
```

## قواعد مهمة:

1. **استخدم العربية الودودة**: "تمام"، "ممتاز"، "عايز إيه تاني؟"
2. **لا تخترع بيانات** - فقط من APIs
3. **وجّه المستخدم خطوة بخطوة**
4. **احفظ السياق** من المحادثة - استخدم المعلومات من الرسائل السابقة
5. **إذا فشل API** اعتذر واقترح بدائل
6. **كن مختصراً** - لا تعرض تفاصيل كثيرة في الرسالة، البيانات ستظهر في data field
7. **استخدم Details Endpoints** عند طلب معلومات عن فندق/رحلة محددة

## صيغة الرد (JSON فقط):

```json
{
  "api_calls": [
    {
      "endpoint": "/api/v1/...",
      "method": "GET",
      "params": {}
    }
  ],
  "response_message": "الرسالة بالعربية (مختصرة)",
  "suggested_actions": ["اقتراح 1", "اقتراح 2"],
  "intent": "نوع السؤال"
}
```

## أنواع Intent:
- hotel_search, hotel_details, hotel_price
- trip_search, trip_details, trip_price
- room_search, room_details, room_price
- data_request, booking_denied
- general_inquiry, clarification_needed

## ملاحظات مهمة:
- البيانات (المدن، الفنادق، الرحلات) ستظهر تلقائياً في data field
- لا تكرر البيانات في response_message
- response_message يجب أن يكون مختصر وودود فقط
- البيانات ستعرض للمستخدم كقائمة اختيار تلقائياً
- **استخدم السياق دائماً** - إذا المستخدم ذكر فندق أو رحلة في رسالة سابقة، استخدم ID الخاص بها

**تذكر**: أنت للبحث والاستفسار فقط، ليس للحجز أو المعاملات!

"item": [
		{
			"name": "hotels",
			"item": [
				{
					"name": "rooms",
					"item": [
						{
							"name": "rooms",
							"protocolProfileBehavior": {
								"strictSSL": false,
								"followRedirects": true
							},
							"request": {
								"auth": {
									"type": "bearer",
									"bearer": [
										{
											"key": "token",
											"value": "{{local_token}}",
											"type": "string"
										}
									]
								},
								"method": "GET",
								"header": [
									{
										"key": "Accept",
										"value": "application/json",
										"type": "string"
									},
									{
										"key": "password",
										"value": "{{api_password}}",
										"type": "string"
									},
									{
										"key": "lang",
										"value": "{{lang}}",
										"type": "string"
									},
									{
										"key": "currency",
										"value": "{{currency}}",
										"type": "string"
									}
								],
								"url": {
									"raw": "{{baseUrl}}/api/v1/hotels/rooms?per_page&page=1&name=&hotel_id=1&adults_count=1&children_count=&start_date=2025-12-04&end_date=2025-12-05&min_price=&max_price=",
									"host": [
										"{{baseUrl}}"
									],
									"path": [
										"api",
										"v1",
										"hotels",
										"rooms"
									],
									"query": [
										{
											"key": "per_page",
											"value": null
										},
										{
											"key": "page",
											"value": "1"
										},
										{
											"key": "name",
											"value": ""
										},
										{
											"key": "hotel_id",
											"value": "1"
										},
										{
											"key": "adults_count",
											"value": "1"
										},
										{
											"key": "children_count",
											"value": ""
										},
										{
											"key": "start_date",
											"value": "2025-12-04"
										},
										{
											"key": "end_date",
											"value": "2025-12-05"
										},
										{
											"key": "min_price",
											"value": ""
										},
										{
											"key": "max_price",
											"value": ""
										}
									]
								}
							},
							"response": []
						},
						{
							"name": "room details",
							"protocolProfileBehavior": {
								"strictSSL": false,
								"followRedirects": true
							},
							"request": {
								"auth": {
									"type": "bearer",
									"bearer": [
										{
											"key": "token",
											"value": "{{local_token}}",
											"type": "string"
										}
									]
								},
								"method": "GET",
								"header": [
									{
										"key": "Accept",
										"value": "application/json",
										"type": "string"
									},
									{
										"key": "password",
										"value": "{{api_password}}",
										"type": "string"
									},
									{
										"key": "lang",
										"value": "{{lang}}",
										"type": "string"
									},
									{
										"key": "currency",
										"value": "{{currency}}",
										"type": "string"
									}
								],
								"url": {
									"raw": "{{baseUrl}}/api/v1/hotels/rooms/:room?name=&adults_count=2&children_count=&start_date=2025-11-26&end_date=2025-11-28",
									"host": [
										"{{baseUrl}}"
									],
									"path": [
										"api",
										"v1",
										"hotels",
										"rooms",
										":room"
									],
									"query": [
										{
											"key": "name",
											"value": ""
										},
										{
											"key": "adults_count",
											"value": "2"
										},
										{
											"key": "children_count",
											"value": ""
										},
										{
											"key": "start_date",
											"value": "2025-11-26"
										},
										{
											"key": "end_date",
											"value": "2025-11-28"
										}
									],
									"variable": [
										{
											"key": "room",
											"value": "2"
										}
									]
								}
							},
							"response": []
						},
						{
							"name": "Calculate Booking Room Price",
							"protocolProfileBehavior": {
								"strictSSL": false,
								"followRedirects": true
							},
							"request": {
								"auth": {
									"type": "bearer",
									"bearer": [
										{
											"key": "token",
											"value": "{{local_token}}",
											"type": "string"
										}
									]
								},
								"method": "GET",
								"header": [
									{
										"key": "Accept",
										"value": "application/json",
										"type": "string"
									},
									{
										"key": "password",
										"value": "{{api_password}}",
										"type": "string"
									},
									{
										"key": "lang",
										"value": "{{lang}}",
										"type": "string"
									},
									{
										"key": "currency",
										"value": "{{currency}}",
										"type": "string"
									}
								],
								"url": {
									"raw": "{{baseUrl}}/api/v1/hotels/rooms/calculate/booking-room/price/:room?adults_count=2&children_ages[]&start_date=2025-11-26&end_date=2025-11-28",
									"host": [
										"{{baseUrl}}"
									],
									"path": [
										"api",
										"v1",
										"hotels",
										"rooms",
										"calculate",
										"booking-room",
										"price",
										":room"
									],
									"query": [
										{
											"key": "adults_count",
											"value": "2"
										},
										{
											"key": "children_ages[]",
											"value": null
										},
										{
											"key": "start_date",
											"value": "2025-11-26"
										},
										{
											"key": "end_date",
											"value": "2025-11-28"
										}
									],
									"variable": [
										{
											"key": "room",
											"value": "2"
										}
									]
								}
							},
							"response": []
						}
					],
					"event": [
						{
							"listen": "prerequest",
							"script": {
								"exec": [],
								"type": "text/javascript",
								"packages": {}
							}
						},
						{
							"listen": "test",
							"script": {
								"exec": [],
								"type": "text/javascript",
								"packages": {}
							}
						}
					]
				},
				{
					"name": "create hotel Room custom Booking",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "POST",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "hotel_id",
									"value": "1",
									"type": "text"
								},
								{
									"key": "check_in",
									"value": "2025-12-03",
									"type": "text"
								},
								{
									"key": "check_out",
									"value": "2025-12-05",
									"type": "text"
								},
								{
									"key": "adults_count",
									"value": "1",
									"type": "text"
								},
								{
									"key": "children_count",
									"value": "0",
									"type": "text"
								},
								{
									"key": "children_ages[]",
									"value": [
										""
									],
									"description": "أعمار الاطفال عند التحديد من البداية",
									"type": "text"
								},
								{
									"key": "travelers[0][full_name]",
									"value": [
										"عصام حمدي العجمي"
									],
									"type": "text"
								},
								{
									"key": "travelers[0][phone_key]",
									"value": "+20",
									"type": "text"
								},
								{
									"key": "travelers[0][phone]",
									"value": [
										"1002694325"
									],
									"type": "text"
								},
								{
									"key": "travelers[0][nationality]",
									"value": "مصري",
									"type": "text"
								},
								{
									"key": "travelers[0][age]",
									"value": "50",
									"type": "text"
								},
								{
									"key": "travelers[0][id_type]",
									"value": "passport ",
									"type": "text"
								},
								{
									"key": "travelers[0][id_number]",
									"value": "2662626262",
									"description": "passport or national_id",
									"type": "text"
								},
								{
									"key": "travelers[0][type]",
									"value": "adult",
									"description": "adult or child",
									"type": "text"
								},
								{
									"key": "notes",
									"value": "notes",
									"type": "text"
								}
							]
						},
						"url": {
							"raw": "{{baseUrl}}/api/v1/booking/hotels/create/custom",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"booking",
								"hotels",
								"create",
								"custom"
							]
						}
					},
					"response": []
				},
				{
					"name": "create hotel Room Booking",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "POST",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "room_id",
									"value": "1",
									"type": "text"
								},
								{
									"key": "check_in",
									"value": "2025-12-05",
									"type": "text"
								},
								{
									"key": "check_out",
									"value": "2025-12-06",
									"type": "text"
								},
								{
									"key": "adults_count",
									"value": "1",
									"type": "text"
								},
								{
									"key": "children_count",
									"value": "0",
									"type": "text"
								},
								{
									"key": "children_ages[]",
									"value": "6",
									"description": "أعمار الاطفال عند التحديد من البداية",
									"type": "text"
								},
								{
									"key": "travelers[0][full_name]",
									"value": "ESSAM",
									"type": "text"
								},
								{
									"key": "travelers[0][phone_key]",
									"value": "+20",
									"type": "text"
								},
								{
									"key": "travelers[0][phone]",
									"value": [
										"1002694325"
									],
									"type": "text"
								},
								{
									"key": "travelers[0][nationality]",
									"value": "مصري",
									"type": "text"
								},
								{
									"key": "travelers[0][age]",
									"value": "50",
									"type": "text"
								},
								{
									"key": "travelers[0][id_type]",
									"value": "passport ",
									"type": "text"
								},
								{
									"key": "travelers[0][id_number]",
									"value": "2662626262",
									"description": "passport or national_id",
									"type": "text"
								},
								{
									"key": "travelers[0][type]",
									"value": "adult",
									"description": "adult or child",
									"type": "text"
								},
								{
									"key": "notes",
									"type": "text"
								}
							]
						},
						"url": {
							"raw": "{{baseUrl}}/api/v1/booking/hotels/create",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"booking",
								"hotels",
								"create"
							]
						}
					},
					"response": []
				},
				{
					"name": "hotel Details",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/hotels/details/:hotel_id",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"hotels",
								"details",
								":hotel_id"
							],
							"variable": [
								{
									"key": "hotel_id",
									"value": "1"
								}
							]
						}
					},
					"response": []
				},
				{
					"name": "hotel cheapest room",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/hotels/cheapest-room/:hotel_id?start_date=2025-12-04&end_date=2025-12-05&adults_count=1&children_count",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"hotels",
								"cheapest-room",
								":hotel_id"
							],
							"query": [
								{
									"key": "start_date",
									"value": "2025-12-04"
								},
								{
									"key": "end_date",
									"value": "2025-12-05"
								},
								{
									"key": "adults_count",
									"value": "1"
								},
								{
									"key": "children_count",
									"value": null
								}
							],
							"variable": [
								{
									"key": "hotel_id",
									"value": "1"
								}
							]
						}
					},
					"response": []
				},
				{
					"name": "all hotels",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/hotels?per_page&page=1&name=&city_id=&hotel_type_id=&adults_count=&children_count=&rating=",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"hotels"
							],
							"query": [
								{
									"key": "per_page",
									"value": "10"
								},
								{
									"key": "page",
									"value": "1"
								},
								{
									"key": "name",
									"value": ""
								},
								{
									"key": "city_id",
									"value": ""
								},
								{
									"key": "hotel_type_id",
									"value": ""
								},
								{
									"key": "adults_count",
									"value": ""
								},
								{
									"key": "children_count",
									"value": ""
								},
								{
									"key": "rating",
									"value": "",
									"description": "desc or asc"
								}
							]
						}
					},
					"response": []
				}
			],
			"event": [
				{
					"listen": "prerequest",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				},
				{
					"listen": "test",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				}
			]
		},
		{
			"name": "trips",
			"item": [
				{
					"name": "create trip  Booking",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "POST",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "trip_id",
									"value": "10",
									"type": "text"
								},
								{
									"key": "check_in",
									"value": "2025-12-07",
									"type": "text"
								},
								{
									"key": "check_out",
									"value": "2025-12-10",
									"type": "text"
								},
								{
									"key": "adults_count",
									"value": "1",
									"type": "text"
								},
								{
									"key": "children_count",
									"value": "2",
									"type": "text"
								},
								{
									"key": "children_ages[]",
									"value": "6",
									"description": "أعمار الاطفال عند التحديد من البداية",
									"type": "text"
								},
								{
									"key": "travelers[0][full_name]",
									"value": [
										"عصام حمدي العجمي"
									],
									"type": "text"
								},
								{
									"key": "travelers[0][phone_key]",
									"value": "+20",
									"type": "text"
								},
								{
									"key": "travelers[0][phone]",
									"value": [
										"1002694325"
									],
									"type": "text"
								},
								{
									"key": "travelers[0][nationality]",
									"value": "مصري",
									"type": "text"
								},
								{
									"key": "travelers[0][age]",
									"value": "50",
									"type": "text"
								},
								{
									"key": "travelers[0][id_type]",
									"value": "passport ",
									"type": "text"
								},
								{
									"key": "travelers[0][id_number]",
									"value": "2662626262",
									"description": "passport or national_id",
									"type": "text"
								},
								{
									"key": "travelers[0][type]",
									"value": "adult",
									"description": "adult or child",
									"type": "text"
								},
								{
									"key": "notes",
									"type": "text"
								}
							]
						},
						"url": {
							"raw": "{{baseUrl}}/api/v1/booking/trips/create",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"booking",
								"trips",
								"create"
							]
						}
					},
					"response": []
				},
				{
					"name": "all trips",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/trips?per_page=15&page=1&name=&city_id=&hotel_id=&main_category_id=&sub_category_id=&rating=&price=&duration_from&duration_to&is_featured",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"trips"
							],
							"query": [
								{
									"key": "per_page",
									"value": "15"
								},
								{
									"key": "page",
									"value": "1"
								},
								{
									"key": "name",
									"value": ""
								},
								{
									"key": "city_id",
									"value": ""
								},
								{
									"key": "hotel_id",
									"value": ""
								},
								{
									"key": "main_category_id",
									"value": ""
								},
								{
									"key": "sub_category_id",
									"value": ""
								},
								{
									"key": "rating",
									"value": "",
									"description": "desc or asc"
								},
								{
									"key": "price",
									"value": "",
									"description": "desc or asc"
								},
								{
									"key": "duration_from",
									"value": null
								},
								{
									"key": "duration_to",
									"value": null
								},
								{
									"key": "is_featured",
									"value": null,
									"description": "set 1 to get offers"
								}
							]
						}
					},
					"response": []
				},
				{
					"name": "trip Details",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/trips/:tripl_id",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"trips",
								":tripl_id"
							],
							"variable": [
								{
									"key": "tripl_id",
									"value": "1"
								}
							]
						}
					},
					"response": []
				},
				{
					"name": "calculate booking trip price",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true,
						"disableBodyPruning": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							},
							{
								"key": "currency",
								"value": "{{currency}}",
								"type": "string"
							}
						],
						"body": {
							"mode": "formdata",
							"formdata": []
						},
						"url": {
							"raw": "{{baseUrl}}/api/v1/trips/calculate/booking-trip/price/:trip?check_in&check_out&children_ages[]=6&adults_count=1",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"trips",
								"calculate",
								"booking-trip",
								"price",
								":trip"
							],
							"query": [
								{
									"key": "check_in",
									"value": null
								},
								{
									"key": "check_out",
									"value": null
								},
								{
									"key": "children_ages[]",
									"value": "6"
								},
								{
									"key": "adults_count",
									"value": "1"
								}
							],
							"variable": [
								{
									"key": "trip",
									"value": "10"
								}
							]
						}
					},
					"response": []
				}
			],
			"event": [
				{
					"listen": "prerequest",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				},
				{
					"listen": "test",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				}
			]
		},
		{
			"name": "data",
			"item": [
				{
					"name": "categories",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/categories",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"categories"
							]
						}
					},
					"response": []
				},
				{
					"name": "sub-categories",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/sub-categories?main_category_id=",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"sub-categories"
							],
							"query": [
								{
									"key": "main_category_id",
									"value": ""
								}
							]
						}
					},
					"response": []
				},
				{
					"name": "booking status",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/booking-status",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"booking-status"
							]
						}
					},
					"response": []
				},
				{
					"name": "hotel-types",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/hotel-types",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"hotel-types"
							]
						}
					},
					"response": []
				},
				{
					"name": "cities",
					"protocolProfileBehavior": {
						"strictSSL": false,
						"followRedirects": true
					},
					"request": {
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{local_token}}",
									"type": "string"
								}
							]
						},
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json",
								"type": "string"
							},
							{
								"key": "password",
								"value": "{{api_password}}",
								"type": "string"
							},
							{
								"key": "lang",
								"value": "{{lang}}",
								"type": "string"
							}
						],
						"url": {
							"raw": "{{baseUrl}}/api/v1/cities",
							"host": [
								"{{baseUrl}}"
							],
							"path": [
								"api",
								"v1",
								"cities"
							]
						}
					},
					"response": []
				}
			],
			"event": [
				{
					"listen": "prerequest",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				},
				{
					"listen": "test",
					"script": {
						"exec": [],
						"type": "text/javascript",
						"packages": {}
					}
				}
			]
		}
	]