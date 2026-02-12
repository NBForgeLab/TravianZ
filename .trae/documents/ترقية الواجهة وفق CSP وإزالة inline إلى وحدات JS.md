## الهدف (بلا Node وبلا Build)
- رفع الأمان/الجودة/الأداء مع إبقاء التشغيل على استضافة مشتركة (PHP + ملفات ثابتة).

## المبادئ
- إزالة inline scripts/handlers تدريجيًا.
- Controllers حسب الصفحة + Core مشتركة + Bridge للقديم.
- CSP تدريجي: Report-Only ثم Enforced.
- تحسين الأداء عبر تحميل حسب الصفحة + كاش.

## الخطوات
### 1) Bootstrap + هيكلة أصول
- `assets/js/app/bootstrap.js` كنقطة دخول واحدة.
- `body[data-controller]` لتحديد Controller الصفحة.
- مجلدات: `assets/js/core/`, `assets/js/controllers/`, `assets/js/bridge/`.

### 2) Core
- dom/events/i18n كوحدات صغيرة قابلة لإعادة الاستخدام.

### 3) Bridge للتراث
- تغليف الدوال العالمية القديمة مؤقتًا ثم الاستبدال التدريجي.

### 4) استخراج inline (صفحات أولًا)
- نقل inline من الصفحات الأعلى أثرًا إلى Controllers.
- استبدال on* بــ data-action + delegation.

### 5) Templates الأكثر تكرارًا
- تحويل Popup/hover وغيرها إلى data-* وربط مركزي.

### 6) CSP تدريجي
- Report-Only + nonces + تقارير، ثم Enforced بعد استقرار التقارير.

### 7) أداء وكاش
- `?v=filemtime(...)` للـ cache busting.
- ترويسات كاش للأصول حسب المتاح.

### 8) جودة بدون Node
- JSDoc للواجهات العامة + ممارسات DOM آمنة.
- استمرار QA الحالي في PHP (phpstan/php-cs-fixer/rector).

## معيار النجاح
- انخفاض inline.
- تقارير CSP تقل حتى الإنفاذ.
- JS حسب الصفحة + صيانة أسهل دون كسر.