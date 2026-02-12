## الهدف
- رفع متطلبات المشروع رسميًا وتشغيليًا إلى PHP 8.0 مع إصلاح كل نقاط الكسر (Fatal/TypeError) الناتجة عن تغييرات PHP 8.

## ما يتغير في PHP 8.0 (المهم لهذا المشروع)
- بعض التحذيرات القديمة تتحول إلى أخطاء TypeError (أشهرها: `count()` على `null`/غير قابل للعد).
- إزالة/تشدد في سلوك بعض الدوال الداخلية، ما يكشف أخطاء كانت “تمر” في 7.x.

## نقاط كسر متوقعة داخل TravianZ (من فحص الكود)
- **count() على نتائج DB**: `mysqli_fetch_all()` في [Database.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php#L646-L656) قد تُرجع `null` عند فشل الاستعلام/نتيجة فارغة، وهناك مواضع تعمل `count($result)` مباشرة مثل:
  - [Database.php:L7396-L7425](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php#L7396-L7425)
  - [Database.php:L7441-L7455](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php#L7441-L7455)
  - [Database.php:L8138-L8146](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php#L8138-L8146)
- **بقايا magic_quotes**: لا تؤثر مباشرة على 8.0 إذا كانت ثابتة FALSE، لكن الأفضل تنظيفها نهائيًا لضمان وضوح السلوك. يوجد مسار `stripslashes` مرتبط بـ `$magic_quotes_gpc` في [Security.class.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Security/Security.class.php#L242-L260).

## خطة التنفيذ (على مراحل)
### 1) تثبيت المتطلبات والبوابات
- تحديث الحد الأدنى في [README.md](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/README.md) إلى PHP 8.0.
- تحديث حراس نسخة PHP (الموجودة في نقاط دخول مثل index / Session / install) لتصبح `PHP_VERSION_ID >= 80000`.

### 2) إصلاح نقاط الكسر المؤكدة في طبقة قاعدة البيانات
- تعديل `mysqli_fetch_all()` في [Database.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php#L646-L656) بحيث تُرجع دائمًا **مصفوفة** (مثل `[]`) بدل `null` عند عدم وجود نتائج/فشل، لأن هذا يمنع `count(null)` من رمي TypeError.
- بديل/تحسين إضافي: استبدال أنماط `if (count($result))` بـ `if (!empty($result))` عندما يكون المتغير مضمونًا كمصفوفة.

### 3) تنظيف/توحيد سلوك تنقية المدخلات
- جعل مسار magic_quotes في [Security.class.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Security/Security.class.php) غير موجود أو غير مستخدم (لأن PHP 8 لا يدعمه) مع الإبقاء على `xss_clean` وتنظيف مفاتيح/قيم الـ superglobals كما هو.

### 4) مسح إضافي لأنماط PHP 8 الشائعة (وقائي)
- تنفيذ مسح للكود عن:
  - `count()` على متغيرات قد تكون null/false
  - تمرير `null` لدوال string (`strlen`, `trim`, `strpos`…)
  - أي مقارنة/تحويل ضمني حساس (خصوصًا عند قراءة DB)
- إصلاح كل ما يظهر بنتيجة أخطاء تشغيل فعلية.

### 5) التحقق (Verification)
- تشغيل PHP lint على كل الملفات.
- تشغيل صفحات رئيسية: `index.php`, `install/`, `login.php`, `dorf1.php`, و`ajax.php` مع `error_reporting(E_ALL)` لملاحظة أي TypeError أو Warnings تتحول لأخطاء.
- اختبار مسارات DB الأساسية (قراءة/كتابة) عبر سيناريو تسجيل الدخول وتحميل القرية.

## ناتج التسليم
- تحديث متطلبات PHP إلى 8.0 في الوثائق + حراس التشغيل.
- إصلاح طبقة DB لمنع TypeError في PHP 8.0.
- تنظيف تنقية المدخلات لإزالة أي اعتماد على ميزات محذوفة.
- تحقق عملي عبر lint وتجربة تشغيل صفحات حرجة.
