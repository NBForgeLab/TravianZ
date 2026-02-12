## مبادئ ومعايير معتمدة
- اعتماد معايير PSR: PSR-4 (autoload)، PSR-12 (تنسيق)، PSR-3 (تسجيل)، مع Composer.
- استهداف PHP ≥ 8.2 لتفعيل خصائص النوعية الصارمة والـ enums وattributes.
- الحفاظ على سرية الإعدادات عبر .env وعدم تضمين كلمات المرور في الشيفرة.

## هيكلة المشروع (طبقات نظيفة)
- Domain: نماذج المجال وقواعده.
- Application: الخدمات (Service) ومنسّقات الأعمال.
- Infrastructure: قاعدة البيانات (PDO/mysqli)، التسجيل، البريد.
- Presentation: Controllers + ViewRenderer.
- Templates: Twig/legacy.
- إزالة الاعتماد على المتغيرات العمومية ($database) واستبدالها بحقن اعتماديات عبر Container بسيط.

## تحويل قاعدة البيانات إلى PDO (أولوية أولى)
- إضافة تنفيذ PDO خلف [IDbConnection.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/src/Database/IDbConnection.php): src/Database/PdoDb.php.
- مصنع اتصال يختار التنفيذ حسب DB_TYPE وتعديل الإنشاء الحالي في [Database.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Database.php).
- معاملات وإدارة الأخطاء: Exceptions موحّدة، retry محدود للـ deadlocks، توحيد isolation level.
- نتائج موحّدة: إرجاع مصفوفات associative فقط لمنع اختلافات بين mysqli/PDO.
- أداء وSQL: منع SELECT *، اعتماد pagination وفهارس مناسبة، إضافة سجل الاستعلامات البطيئة.

## إدخال Twig (بعد تثبيت PDO)
- استخدام ViewRenderer كجسر ثنائي (legacy + twig) cmccoremem id="01KH9VHB4ZKQA07YHR4A8112PP" /.
- تهيئة بيئة Twig مع: autoescape=html، strict_variables=true، cache للمخرجات.
- أنماط القوالب: layout عبر extends/blocks، فصل المنطق عن العرض، استخدام فلاتر آمنة.
- بدء التحويل بقوالب غير حرجة: [install/templates/config.tpl](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/install/templates/config.tpl)، [Admin/Templates/config.tpl](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Admin/Templates/config.tpl)، ثم قوالب اللعبة مثل [mapview.tpl](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Templates/Map/mapview.tpl).

## الأمن وأفضل الممارسات
- استعلامات مُحضّرة في كل التفاعلات مع DB، ضبط الترميز utf8mb4.
- CSRF tokens في النماذج، وOutput Encoding في القوالب.
- تقوية الجلسات: SameSite، HttpOnly، Secure، تدوير المعرّف بعد الدخول.
- إدارة الأسرار عبر .env ورفض طباعتها أو تسجيلها.

## الأداء والقياس
- Caching: طبقة ذاكرة للنتائج الثقيلة (ترتيبات، خريطة) مع سياسة انتهاء صلاحية واضحة.
- Persistent connections (PDO ATTR_PERSISTENT) بشكل انتقائي بعد القياس.
- قياس p95/p99 زمن الاستجابة، وإضافة telemetry بسيط (عدادات واستثناءات).

## الجودة والاختبارات
- PHPUnit لاختبارات الوحدة والتكامل لمسارات رئيسية.
- PHPStan (مستوى 6+) لفحص ثابت، وCode Style عبر PHP-CS-Fixer (PSR-12).
- بوابة CI (GitHub Actions) لتشغيل الفحوصات تلقائيًا عند كل دفع.

## خطة تنفيذ مرحلية واضحة
- المرحلة 1: إدخال PdoDb خلف الواجهة، إبقاء DB_TYPE=1 افتراضيًا حتى اكتمال فحوصات التكافؤ.
- المرحلة 2: مصنع اتصال + توحيد النتائج + تحويل استخدامات mysqli المباشرة في [Admin/database.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/GameEngine/Admin/database.php).
- المرحلة 3: تفعيل DB_TYPE=2 افتراضيًا بعد اجتياز المعايير، والبدء في إدخال Twig عبر ViewRenderer.
- المرحلة 4: تحويل القوالب تدريجيًا (غير حرجة → حرجة) مع الحفاظ على الوضع الثنائي حتى الاكتمال.

## معايير القبول
- تكافؤ النتائج والإخراج بين mysqli وPDO في مسارات حساسة.
- معدل خطأ منخفض (<0.5%) وزمن استجابة p95 ضمن الميزانية المتفق عليها.
- اجتياز جميع اختبارات PHPUnit وPHPStan وPSR-12 في CI بدون إخفاقات.

## طلب التأكيد
- هل توافق على هذه الخطة الاحترافية المدمجة (PDO أولًا ثم Twig) مع تبنّي معايير PSR، اختبارات، وقياس الأداء؟ عند الموافقة أبدأ بتنفيذ المرحلة 1 مباشرة.