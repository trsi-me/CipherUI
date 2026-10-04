# CipherUI

الاسم في الملفات: CipherUI. قاعدة `includes/db.php` و`database.sql`: `cipherui`. المجلد: `CipherUI`.

## 1. ما هو المشروع

تطبيق PHP يتيح إنشاء حساب، ثم تشفير نص أو محتوى ملف بخوارزمية `aes-256-cbc` عبر `includes/crypto.php`، وحفظ الناتج و`iv` في جدول `encrypted_data`، ثم فك التشفير أو الحذف لصاحب السجل فقط.

## 2. لماذا يوجد هذا المشروع

`about.php` يصف الأداة كتشفير لنص وملفات بمعيار AES-256 وبواجهة عربية. الغرض التشغيلي في الكود: حفظ ناتج `openssl_encrypt` لكل مستخدم في MySQL.

## 3. من يستخدمه

| الطرف | السلوك |
| --- | --- |
| زائر | `index.php` يحوّل إلى `login.php`. `requireGuest()` على الدخول والتسجيل |
| مستخدم مسجل | `$_SESSION['user_id']` عبر `includes/auth.php`. لوحة `dashboard.php` وسجلات `encrypted_data` الخاصة به |

دور مدير أو عمود `role`: غير موجود في `database.sql`.

## 4. ماذا يستطيع النظام أن يفعل

- تسجيل في `register.php` بكلمة مرور مجزأة بـ `password_hash`.
- دخول في `login.php` بـ `password_verify`.
- تشفير نص من `encrypt.php` (JSON).
- رفع ملف إلى `upload.php` ثم تشفير محتواه.
- عرض القائمة في `dashboard.php`.
- فك التشفير من `decrypt.php` بشرط `id` و`user_id`.
- حذف من `delete.php` بنفس الشرط.
- صفحة `about.php`.
- خروج `logout.php`.
- واجهة `assets/js/main.js` ترسل النماذج إلى هذه الملفات.

مشاركة سجل مع مستخدم آخر: غير موجودة. الاستعلام يقيّد `user_id` الحالي.

## 5. كيف يعمل النظام

```
المتصفح main.js
    |  POST JSON أو multipart
    v
encrypt.php / upload.php / decrypt.php / delete.php
    |  requireLogin()
    v
encryptData() / decryptData()   includes/crypto.php
    |  CIPHER_METHOD aes-256-cbc
    |  مفتاح CIPHER_KEY مشتق داخل الملف
    v
جدول encrypted_data (data base64, iv base64)
```

`encryptData()` ينشئ `iv` عشوائياً بطول `openssl_cipher_iv_length`، ويشفّر بـ `OPENSSL_RAW_DATA`، ويرجع `encrypted` و`iv` بعد `base64_encode`.

## 6. أمثلة واقعية

1. مستخدم يكتب نصاً في `#plainText` داخل `dashboard.php`. `main.js` يرسل POST إلى `encrypt.php` بالحقل `plain_text` أو `data`. الاستجابة JSON فيها `success` و`encrypted` و`id`.
2. رفع ملف UTF-8 يخزن النص كما هو قبل التشفير. الملف الذي لا يمر `mb_check_encoding` كـ UTF-8 يُحوَّل إلى سلسلة تبدأ بـ `FILE:` ثم النوع ثم base64 ثم اسم الملف، كما في `upload.php`.
3. زر «فك التشفير» يرسل `id` إلى `decrypt.php`. إن لم يكن الصف لنفس `user_id` ترجع الرسالة «البيانات غير موجودة أو غير مصرح لك بالوصول».

## 7. رحلة المستخدم

1. فتح `index.php` فيصل إلى `login.php` إن لم توجد جلسة، أو إلى `dashboard.php` إن وُجدت.
2. `register.php` ثم رسالة نجاح في `login.php` عندما ينجح المسار (الصفحة تعرض نص نجاح إنشاء الحساب في الحالة المقابلة).
3. الدخول إلى `dashboard.php`.
4. تشفير نص أو رفع ملف.
5. فك التشفير في النافذة `#decryptModal` أو حذف البطاقة.
6. `about.php` للشرح.
7. `logout.php`.

## 8. الوحدات والأقسام

| الوحدة | الملفات |
| --- | --- |
| توجيه | `index.php` |
| حساب | `register.php`, `login.php`, `logout.php`, `includes/auth.php` |
| تشفير | `includes/crypto.php`, `encrypt.php`, `decrypt.php`, `upload.php`, `delete.php` |
| واجهة | `dashboard.php`, `about.php`, `assets/js/main.js`, `assets/css/style.css` |
| بيانات | `includes/db.php`, `database.sql` |

## 9. الشركات والكيانات

شركة أو مؤسسة في الملفات: غير موثق. الاسم الظاهر CipherUI.

## 10. الصلاحيات

| الدالة | الأثر |
| --- | --- |
| `isLoggedIn()` | وجود `$_SESSION['user_id']` غير فارغ |
| `requireLogin()` | تحويل إلى `login.php` |
| `requireGuest()` | تحويل المسجّل إلى `dashboard.php` |
| `getCurrentUserId()` | معرف الجلسة المستخدم في كل استعلام بيانات |

صلاحيات أدق (قراءة فقط، مدير): غير موجودة.

## 11. الأتمتة وسير العمل

مجدول: غير موجود.

سير التشفير:

1. رفض غير POST برمز HTTP `405` وجسم `success: false`.
2. رفض النص الفارغ في `encrypt.php`.
3. `encryptData()`.
4. `INSERT INTO encrypted_data (user_id, data, iv, created_at)`.
5. JSON فيه `id` من `lastInsertId()`.

سير الرفع يتحقق من `$_FILES['file']['error']` ويربط رموز `UPLOAD_ERR_*` برسائل عربية قبل التشفير.

## 12. التكامل بين الوحدات

`dashboard.php` يرسم النماذج، و`main.js` يستدعي `encrypt.php` و`upload.php` و`decrypt.php` و`delete.php`. الكل يستخدم `getDBConnection()` و`requireLogin()` ما عدا صفحات الضيف. شكل الملف الثنائي (`FILE:mime:base64:filename`) موثق في تعليق `upload.php` حتى يمكن تمييزه بعد `decryptData()`.

## 13. المصطلحات

| المصطلح | المعنى في المشروع |
| --- | --- |
| `CIPHER_METHOD` | `aes-256-cbc` |
| `CIPHER_KEY` | ناتج `hash('sha256', ..., true)` داخل `includes/crypto.php`. السلسلة الأصلية غير مكررة هنا |
| `iv` | متجه تهيئة عشوائي مخزن base64 في العمود `iv` |
| `data` | النص المشفر base64 في العمود `data` |
| `encrypted_data` | جدول السجلات |
| جلسة | `user_id` و`username` |

## 14. الأسئلة الشائعة

**هل لكل مستخدم مفتاحه؟** الدالة `encryptData()` تستخدم ثابتاً واحداً `CIPHER_KEY` لكل العمليات. مفتاح لكل مستخدم: غير موجود.

**هل يُحفظ الملف الأصلي على القرص؟** `upload.php` يقرأ الملف المؤقت ويشفّر المحتوى في القاعدة. مجلد تخزين دائم للملف الأصلي: غير موجود.

**من يفك التشفير؟** أي جلسة تعرف `id` الخاص بها. القيد في SQL هو `user_id`. المفتاح نفسه مشترك في الكود، لذلك القدرة على فك الصف مرتبطة أيضاً بامتلاك المفتاح المعرّف في `crypto.php`.

**ماذا يرجع طلب GET على `encrypt.php`؟** رمز `405` وJSON «طريقة غير مسموحة».

## 15. المعمارية

```
+------------------+     +---------------------------+     +------------------+
| المتصفح          |     | PHP                       |     | MySQL cipherui   |
| dashboard about  | --> | auth.php requireLogin     | --> | users            |
| main.js fetch    | <-- | crypto.php openssl        | <-- | encrypted_data   |
| style.css        |     | encrypt decrypt upload    |     |                  |
+------------------+     | delete  db.php PDO        |     +------------------+
                         +---------------------------+
```

## 16. التقنيات المستخدمة

| التقنية | أين |
| --- | --- |
| PHP | الصفحات |
| PDO MySQL `utf8mb4` و`EMULATE_PREPARES` معطّل | `includes/db.php` |
| OpenSSL `openssl_encrypt` / `openssl_decrypt` | `includes/crypto.php` |
| جلسات PHP | `includes/auth.php` يستدعي `session_start()` |
| HTML و CSS و JavaScript | `assets/` و`dashboard.php` |
| JSON | `encrypt.php`, `decrypt.php`, `upload.php`, `delete.php` |

README السابق يطلب PHP 7.4 أو أحدث مع PDO وOpenSSL، وMySQL 5.7 أو أحدث. إطار عمل: غير موجود.

## 17. هيكل المشروع

```
CipherUI/
├── index.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── about.php
├── encrypt.php
├── decrypt.php
├── upload.php
├── delete.php
├── database.sql
├── includes/auth.php
├── includes/db.php
├── includes/crypto.php
└── assets/css/style.css
    assets/js/main.js
    assets/fonts/OFL.txt
    assets/images/Logo2.png   (مشار إليه كأيقونة)
```

## 18. واجهة المستخدم

صفحات `login.php` و`register.php` و`dashboard.php` و`about.php` عربية وتضم `<link rel="icon" type="image/png" href="assets/images/Logo2.png">`. `index.php` تحويل بلا HTML. شعار داخل `logo-dark-bg` في صفحات الحساب و`about.php`. النافذة المنبثقة `#decryptModal`. favicon في `index.php`: غير موجود لأنه لا يطبع `head`.

## 19. الخادم

README السابق يوثق التشغيل من المسار `d:\VSCode\Projects\CipherUI` بالأمر `php -S localhost:8000`، أو النسخ إلى `htdocs` والفتح حسب إعداد الخادم مثل `http://localhost/Projects/CipherUI`. منفذ ثابت في الكود: غير موجود.

## 20. مسار الطلب

1. `index.php` يحمّل `auth.php` ويحوّل.
2. `dashboard.php` يحمّل الجلسة ويجلب `encrypted_data` للمستخدم.
3. `main.js` يرسل `fetch` إلى سكربت العملية.
4. السكربت يضبط `Content-Type: application/json` ويرد كائناً فيه `success`.
5. فشل القاعدة في `getDBConnection()` يوقف التنفيذ بنص «فشل الاتصال بقاعدة البيانات» مع رسالة PDO، وهذا المسار ليس JSON.

## 21. قاعدة البيانات

`cipherui` بترميز `utf8mb4_unicode_ci`.

### users

| العمود | القيد |
| --- | --- |
| `id` | PK AUTO_INCREMENT |
| `username` | VARCHAR(100) NOT NULL UNIQUE |
| `password` | VARCHAR(255) NOT NULL |
| `created_at` | DATETIME افتراضي `CURRENT_TIMESTAMP` |

### encrypted_data

| العمود | القيد |
| --- | --- |
| `id` | PK |
| `user_id` | NOT NULL، FK إلى `users(id)` ON DELETE CASCADE |
| `data` | MEDIUMTEXT NOT NULL |
| `iv` | VARCHAR(255) NOT NULL |
| `created_at` | DATETIME |

حسابات بذرة في `database.sql`: غير موجودة (`INSERT` للمستخدمين غير موجود في الملف).

## 22. واجهات البرمجة

| الطريقة | المسار | الغرض | المعاملات | الجلسة | الاستجابة |
| --- | --- | --- | --- | --- | --- |
| GET | `index.php` | توجيه | | | تحويل `302` إلى `dashboard.php` أو `login.php` |
| POST | `register.php` | إنشاء مستخدم | `username`, `password`, تأكيد في النموذج | ضيف | HTML |
| POST | `login.php` | دخول | `username`, `password` | ضيف | HTML أو تحويل |
| GET | `logout.php` | خروج | | | تحويل |
| GET | `dashboard.php` | لوحة وقائمة | | `requireLogin` | HTML |
| GET | `about.php` | شرح | | حسب الصفحة | HTML |
| POST | `encrypt.php` | تشفير نص | JSON أو POST: `plain_text` أو `data` | دخول | JSON `success`, `message`, `encrypted`, `id` |
| POST | `upload.php` | تشفير ملف | حقل ملف `file` | دخول | JSON `success`, `message`, `id` |
| POST | `decrypt.php` | فك تشفير | `id` | دخول وملكية الصف | JSON `success`, `data` أو `message` |
| POST | `delete.php` | حذف | `id` | دخول وملكية الصف | JSON `success`, `message` |
| غير POST | `encrypt.php` ومثيلاتها | رفض | | دخول | HTTP `405` وJSON |

## 23. تسجيل الدخول والصلاحيات

`login.php` يبحث عن المستخدم باسم المستخدم ثم `password_verify`. عند النجاح تُضبط `user_id` و`username`. `register.php` يدرج `username` و`password` و`NOW()`.

`includes/auth.php` يستدعي `session_start()` عند التحميل. خيارات الكعكة: غير موثقة. CSRF على `encrypt.php`: غير موجود. المصادقة هي الجلسة فقط.

## 24. الحماية

- عبارات محضرة في الإدراج والحذف والفك.
- كلمة مرور المستخدم مجزأة بكلمة `password_hash` وليست مفتاح التشفير.
- فك التشفير والحذف مقيدان بـ `user_id`.
- المفتاح `CIPHER_KEY` ثابت ومشتق من سلسلة مكتوبة داخل `includes/crypto.php`. تعليق الملف يقول إن المفتاح يجب تغييره في الإنتاج. وضع المفتاح في `.env`: غير موجود.
- `openssl_random_pseudo_bytes` لـ `iv` لكل عملية.
- رسائل أخطاء JSON للمستخدم عامة في مسارات القاعدة (`فشل حفظ البيانات`, `حدث خطأ`).
- فشل الاتصال الأولي يطبع رسالة PDO كنص HTML.
- حد حجم الرفع يعتمد إعدادات PHP `upload_max_filesize` لأن الكود يترجم `UPLOAD_ERR_INI_SIZE`. حد خاص داخل المشروع: غير موثق.
- فحص نوع الملف بغير امتداده: الكود يقرأ المحتوى ويختبر UTF-8، وقائمة امتدادات مسموحة: غير موجودة.

## 25. الإعدادات

| الرمز | الملف | القيمة الحالية |
| --- | --- | --- |
| `DB_HOST` | `includes/db.php` | `localhost` |
| `DB_NAME` | `includes/db.php` | `cipherui` |
| `DB_USER` | `includes/db.php` | `root` |
| `DB_PASS` | `includes/db.php` | فارغ في الملف |
| `CIPHER_METHOD` | `includes/crypto.php` | `aes-256-cbc` |
| `CIPHER_KEY` | `includes/crypto.php` | مشتق داخل الملف، القيمة السرية غير مكررة هنا |

## 26. التكاملات الخارجية

بوابة طرف ثالث أو بريد أو تخزين كائنات: غير موجودة. التشفير مكتبة OpenSSL المحلية في PHP.

## 27. المهام المجدولة

Cron: غير موجود في الملفات الحالية.

## 28. تخزين الملفات

المحتوى السري بعد التشفير في عمود `encrypted_data.data`. الملف المرفوع يُقرأ من المسار المؤقت لـ PHP ثم لا يُنسخ إلى مجلد مشروع في الكود المقروء. صور الواجهة مشار إليها بـ `assets/images/Logo2.png`.

## 29. السجلات والمتابعة

ملفات log: غير موجودة. العمليات ترد JSON إلى `#messageArea` في اللوحة. أخطاء OpenSSL ترجع `false` من `encryptData` وتتحول إلى «فشل التشفير» أو «فشل فك التشفير».

## 30. التثبيت

من README السابق والملفات:

1. PHP 7.4 أو أحدث مع PDO وOpenSSL، وMySQL 5.7 أو أحدث (حسب README السابق).
2. من مجلد المشروع: `php -S localhost:8000` ثم فتح `http://localhost:8000`، أو وضع المجلد تحت خادم ويب.
3. ضبط `DB_HOST` و`DB_NAME` و`DB_USER` و`DB_PASS` في `includes/db.php`.
4. تنفيذ `database.sql`:

```
mysql -u root -p < d:\VSCode\Projects\CipherUI\database.sql
```

أو استيراد الملف من phpMyAdmin. الأمر يطلب كلمة مرور عميل MySQL تفاعلياً بسبب `-p`. قيمة كلمة مرور التطبيق في الملف فارغة.

5. إنشاء حساب من `register.php` لأن البذرة لا تدرج مستخدمين.
6. روابط التحميل المذكورة في README السابق: https://www.php.net/downloads و https://dev.mysql.com/downloads/mysql/

## 31. دليل التطوير

- أي عملية بيانات جديدة تستدعي `requireLogin()` وتقيد `user_id`.
- الحفاظ على شكل `FILE:` في `upload.php` إذا أضفت عرضاً للملفات بعد الفك.
- تغيير مادة اشتقاق `CIPHER_KEY` يجعل السجلات القديمة غير قابلة للفك بالمفتاح الجديد.
- `main.js` يتحقق من تطابق كلمتي المرور في `#registerForm` قبل الإرسال.
- اختبارات آلية: غير موجودة.

## 32. النشر

ملف نشر: غير موجود. README السابق يصف خادماً محلياً أو نسخ المجلد إلى جذر الويب. على خادم عام يلزم تغيير مصدر المفتاح المذكور في تعليق `crypto.php` وبيانات `db.php` خارج المستودع العام. آلية ذلك في المشروع: غير موجودة (لا `.env`).

## 33. النسخ الاحتياطي والاستعادة

سكربت نسخ: غير موجود. نسخ قاعدة `cipherui` خارجياً يحفظ `users` و`encrypted_data`. استعادة القراءة تتطلب نفس `CIPHER_KEY` المستخدم عند التشفير. إعادة استيراد `database.sql` تنشئ الجداول بـ `CREATE TABLE IF NOT EXISTS` ولا تحذف البيانات القائمة.

## 34. تشخيص المشكلات

| العرض | المطابق |
| --- | --- |
| «فشل الاتصال بقاعدة البيانات» | MySQL أو ثوابت `DB_*` أو عدم استيراد `database.sql` |
| HTTP 405 | استدعاء `encrypt.php` أو أخواتها بغير POST |
| «النص فارغ» | `plain_text` فارغ |
| «فشل فك التشفير» | `iv` أو المفتاح أو تلف base64، و`decryptData()` يتحقق من طول `iv` |
| «غير مصرح» | `id` لا يخص `user_id` |
| «الملف كبير جداً» | `UPLOAD_ERR_INI_SIZE` أو `UPLOAD_ERR_FORM_SIZE` |
| لوحة فارغة | لا صفوف لهذا المستخدم |

## 35. الاعتماديات

`composer.json`: غير موجود. امتدادات PHP: PDO MySQL وOpenSSL (`openssl_encrypt`). MySQL يقبل `MEDIUMTEXT` و`utf8mb4`.

## 36. القيود المعروفة

- مفتاح تشفير واحد مكتوب في المصدر لكل المستخدمين.
- CSRF على واجهات JSON: غير موجود.
- فحص امتداد أو حجم مخصص في الكود: غير موجود.
- حساب مدير: غير موجود.
- مستخدمون افتراضيون في SQL: غير موجودون.
- فشل الاتصال ليس استجابة JSON.
- مشاركة الملفات بين الحسابات: غير موجودة.

## 37. حالة النظام الحالية

الصفحات و`database.sql` و`crypto.php` موجودة. جداول منشأة على جهاز معين: غير موثقة داخل الملفات. رقم إصدار منتج: غير موجود. تعليق المفتاح يذكر تغييراً مطلوباً للإنتاج ولم يُنفَّذ كملف إعداد منفصل.

## 38. قرارات المعمارية

| القرار | الأثر |
| --- | --- |
| AES-256-CBC مع IV عشوائي لكل سجل | العمود `iv` إلزامي للفك |
| مفتاح تطبيق واحد | كل السجلات تُفك بنفس الثابت |
| حفظ الناتج في MySQL لا الملف الخام | القرص الدائم للملفات غير مستخدم |
| ملكية بالجلسة و`user_id` | عزل منطقي بين الحسابات على مستوى الاستعلام |
| `index.php` تحويل فقط | الصفحة العامة للزائر هي `login.php` |

## 39. سجل التغييرات

سجل إصدارات: غير موجود. README السابق وثق المتطلبات وأمر `php -S` واستيراد SQL. هذا الملف يوثق المسارات الأربعة JSON وشكل `FILE:` وغياب بذرة المستخدمين.

## System Overview

CipherUI is a PHP and MySQL app. Registered users encrypt text or uploaded file contents with AES-256-CBC and store ciphertext plus IV in `encrypted_data`. Decrypt and delete require the same session user id. There is one application key in `includes/crypto.php`.

## Quick Reference

| البند | القيمة |
| --- | --- |
| القاعدة | `cipherui` |
| الاتصال | `includes/db.php` |
| SQL | `database.sql` |
| الجداول | `users`, `encrypted_data` |
| الخوارزمية | `aes-256-cbc` |
| تشفير نص | POST `encrypt.php` |
| رفع | POST `upload.php` حقل `file` |
| فك | POST `decrypt.php` حقل `id` |
| حذف | POST `delete.php` حقل `id` |
| أيقونة | `assets/images/Logo2.png` في صفحات HTML المذكورة |
| خادم موثق سابقاً | `php -S localhost:8000` |

## Quick Start

1. استورد `database.sql`.
2. راجع `includes/db.php`.
3. من مجلد المشروع شغّل `php -S localhost:8000`.
4. افتح `http://localhost:8000` ثم أنشئ حساباً من `register.php`.

## For Non-Technical Users

تنشئ حساباً ثم تلصق نصاً أو ترفع ملفاً من لوحة التحكم. الموقع يحفظ نسخة مشفرة. زر فك التشفير يظهر المحتوى لك وأنت داخل الحساب. حساب شخص آخر لا يظهر في قائمتك. خدمة مشاركة عامة: غير موجودة.

## For Developers

اقرأ `includes/crypto.php` و`upload.php` قبل تغيير الصيغة. قيد كل استعلام بـ `getCurrentUserId()`. لا تكتب سلسلة المفتاح في التوثيق أو في المستودع العام. الواجهات الأربع تتوقع POST وترد JSON، بينما فشل `getDBConnection()` يرد نصاً ويوقف التنفيذ.
