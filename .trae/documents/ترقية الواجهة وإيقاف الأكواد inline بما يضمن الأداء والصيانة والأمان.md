## الأهداف

* إزالة الأكواد inline تدريجياً واستبدالها بملفات JS/CSS مركزية قابلة للصيانة.

* تشديد سياسة Content Security Policy بدون كسر الواجهة، ثم الانتقال للحالة الصارمة.

* تحسين الأداء (تحميل مؤجّل/تقسيم شفرات/كاش) واستقرار السلوك عبر تهيئة موحّدة.

## الوضع الحالي (مرجعية)

* وجود سكربتات inline في صفحات مثل [index.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/index.php#L86-L91) و[notification/index.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/notification/index.php#L36-L40)، إضافة إلى كثرة الـ inline handlers داخل مجلد [Templates](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Templates).

* التحميل يتم عبر ملفات JS قديمة (new\.js/new2.js/notification.js)، ولا توجد بنية وحدات مركزية.

## الاستراتيجية العامة

* استخراج كل سكربت inline إلى وحدات منفصلة داخل مجلد أصول موحّد، مع تهيئة صفحة بنمط "Controllers" مبني على سمات/كلاسات DOM.

* إلغاء event attributes مثل onclick/onmouseover لصالح Event Delegation.

* اعتماد CSP تدريجي: Report-Only صارم أولاً مع nonces، ثم إزالة "unsafe-inline" و"unsafe-eval" تدريجياً.

* الحفاظ على تغييرات قليلة التبعيات: بدءًا بملفات JS عادية مع تنظيم معياري، وإتاحة التحزيم لاحقاً (ESBuild/Vite) عند الحاجة.

## هيكلة الملفات المقترحة

* assets/js/core/

  * dom.js (مرافق DOM)

  * events.js (تفويض الأحداث/التسجيل)

  * i18n.js (الترجمة)

* assets/js/controllers/

  * indexPage.js (تهيئة عناصر صفحة البداية)

  * notificationPage.js (تهيئة صفحة الإشعارات)

  * admin/\*.js (لوحة الإدارة عند الحاجة)

* assets/css/ (لنقل أي أنماط inline مستقبلًا)

## خطوات التنفيذ المرحلية

1. إنشاء طبقة Core موحّدة

* إضافة دوال مساعدة للـ DOM وEvent Delegation.

* نقطة دخول عامة: `window.TravianZ.init()` تستدعي Controllers حسب `body.className` أو `data-controller`.

1. استخراج السكربتات inline

* نقل من [index.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/index.php#L86-L91) مناداة `show_flags` إلى `controllers/indexPage.js`، مع تحميل الملف عبر `<script src="assets/js/controllers/indexPage.js" defer>`.

* نقل إضافة الترجمة في [notification/index.php](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/notification/index.php#L36-L40) إلى `controllers/notificationPage.js`.

1. إلغاء inline handlers

* استبدال `onclick="..."` و`onmouseover` بسمات `data-action`/`data-target`، وتسجيل المستمعين عبر Delegation من عنصر جذر.

* معالجة القوالب الأكثر استخدامًا أولاً داخل [Templates](file:///c:/Users/batah/OneDrive/%D8%A7%D9%84%D9%85%D8%B3%D8%AA%D9%86%D8%AF%D8%A7%D8%AA/GitHub/TravianZ/Templates).

1. سياسة CSP تطورية

* المرحلة 1: تفعيل Report-Only صارم مع `script-src-attr 'none'` وإدراج nonce للسكربتات الضرورية أثناء الانتقال.

* المرحلة 2: إزالة `unsafe-inline` مع إبقاء nonces للسكربتات المحقونة ديناميكياً، ومنع `eval`.

* المرحلة 3: عند الانتقال لأداة تحزيم حديثة، إمكان اعتماد `strict-dynamic` مع nonces.

* توحيد الاستثناءات الخارجية (jQuery/Google) أو تفضيل الاستضافة الذاتية لتبسيط CSP.

1. تحسين الأداء

* استخدام `defer` لجميع سكربتات الصفحة، وتقسيم الشفرات حسب الصفحة.

* كاش ملفات ثابتة (Cache-Control) ونسخ مصغرة (minification).

* Lazy-load لما هو اختياري؛ إزالة السكربتات غير المستخدمة.

1. حوكمة الصيانة

* إضافة ESLint+Prettier، واتباع Naming موحّد.

* إمكانية إدخال TypeScript تدريجيًا (controllers أولاً) دون كسر البناء.

* وثائق مطوّرة لكل Controller: مسؤوليات/اعتمادات/نقاط ربط DOM.

1. التحقق والاختبارات

* كتابة Smoke Tests لصفحات: الرئيسية، الإشعارات، وبعض قوالب Templates الشائعة.

* مراقبة تقارير CSP (log) حتى تقل الانتهاكات للحد الأدنى قبل الانتقال للـ Enforced.

1. خطة تراجع ومخاطر

* Feature Flag للتشدّد التدريجي (مثلاً ENABLE\_STRICT\_CSP)، والقدرة على العودة لـ Report-Only بسرعة.

* متابعة لوج الأخطاء ووضع Rollback سريع لأي صفحة تسبب كسر وظائف.

## المخرجات المتوقعة

* واجهة أكثر أمانًا وقابلة للصيانة، بأداء أفضل وترويسات CSP صارمة بلا اعتماد على inline.

* كود منظم بوحدات واضحة، يسهل توسيعه ودمجه لاح

