<?php
/**
 * CipherUI - نبذة عن التطبيق
 */
require_once __DIR__ . '/includes/auth.php';

$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نبذة عن CipherUI</title>
    <link rel="icon" type="image/png" href="assets/images/Logo2.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="<?= $loggedIn ? 'dashboard.php' : 'index.php' ?>" class="logo">
                <img src="assets/images/Logo2.png" alt="CipherUI" class="logo-img">
            </a>
            <nav class="header-nav">
                <?php if ($loggedIn): ?>
                <a href="dashboard.php" class="nav-link">لوحة التحكم</a>
                <?php endif; ?>
                <a href="about.php" class="nav-link active">نبذة عن</a>
                <?php if ($loggedIn): ?>
                <span class="user-name">مرحباً، <?= htmlspecialchars(getCurrentUsername()) ?></span>
                <a href="logout.php" class="btn btn-outline btn-small">خروج</a>
                <?php else: ?>
                <a href="login.php" class="nav-link">تسجيل الدخول</a>
                <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container main-content page-about">
        <article class="about-article">
            <div class="about-header">
                <div class="logo-dark-bg about-logo-wrap">
                    <img src="assets/images/Logo2.png" alt="CipherUI" class="about-logo">
                </div>
                <h1 class="about-title">ما هو CipherUI؟</h1>
            </div>
            <p class="about-lead">
                CipherUI أداة ويب متخصصة لتشفير وحماية بياناتك النصية وملفاتك باستخدام معيار AES-256، مع واجهة عربية بسيطة وآمنة مصممة لتلبية احتياجاتك الأمنية اليومية.
            </p>

            <section class="about-section">
                <h2>لماذا التشفير مهم؟</h2>
                <p>
                    في عصر الرقمنة المتسارع، تصبح بياناتك الشخصية والمهنية والمالية معرّضة للوصول غير المصرح به من المخترقين، البرمجيات الخبيثة، أو حتى مقدمي الخدمات. التشفير يحوّل معلوماتك الحساسة إلى شكل مشفر غير قابل للقراءة إلا لمن يملك المفتاح الصحيح، مما يضمن خصوصيتك المطلقة وأمان بياناتك ضد التسرب أو الاستغلال.
                </p>
                <p>
                    سواء كنت تحفظ ملاحظات خاصة، وثائق عمل، أو معلومات حساسة — التشفير يشكّل خط الدفاع الأول والأهم لحمايتها. بدون تشفير، أي شخص يحصل على نسخة من بياناتك يمكنه قراءتها بالكامل. مع التشفير، حتى إن نُقلت البيانات إلى يد غير موثوقة تبقى محمية تماماً.
                </p>
            </section>

            <section class="about-section">
                <h2>كيف يعمل التشفير في CipherUI؟</h2>
                <p>
                    يستخدم CipherUI خوارزمية <strong>AES-256</strong> (Advanced Encryption Standard) — وهي نفس الخوارزمية المعتمدة من الحكومات والمؤسسات المالية حول العالم لحماية البيانات المصنّفة. إليك كيف تتم العملية خطوة بخطوة:
                </p>
                <ul class="about-features">
                    <li>
                        <strong>التشفير (AES-256):</strong> نستخدم معيار AES بمفتاح طوله 256 بت، وهو المعيار الذهبي للتشفير المتماثل. هذه الخوارزمية تُعتبر غير قابلة للكسر بالحوسبة الحالية، حيث يتطلب فكّها زمناً هائلاً حتى بأقوى الحواسيب.
                    </li>
                    <li>
                        <strong>مولد المفاتيح العشوائية:</strong> يُولَّد مفتاح تشفير فريد وعشوائي لكل عملية تشفير باستخدام دوال آمنة، مما يضمن عدم تكرار الأنماط أو قابلية التخمين.
                    </li>
                    <li>
                        <strong>متجه التهيئة (IV):</strong> يُستخدم متجه تهيئة مختلف لكل تشفير لضمان أن النصوص المتطابقة تُشفّر بشكل مختلف، مما يزيد الأمان ويُصعّب هجمات التحليل.
                    </li>
                    <li>
                        <strong>عزل البيانات بين المستخدمين:</strong> كل مستخدم يرى ويصل إلى بياناته المشفرة فقط. يتم ربط كل سجل بمُعرّف المستخدم، ولا يمكن لأي مستخدم آخر — حتى إداري النظام — الوصول إلى محتويات بياناتك دون مفتاحك.
                    </li>
                    <li>
                        <strong>تخزين آمن:</strong> يتم تخزين البيانات المشفرة ومتجه التهيئة في قاعدة البيانات بشكل آمن، بينما يبقى المفتاح ضمن سياق الجلسة عند الحاجة لفك التشفير.
                    </li>
                </ul>
            </section>

            <section class="about-section">
                <h2>المميزات والوظائف</h2>
                <ul class="about-list">
                    <li><strong>تشفير النصوص:</strong> أدخل أي نص في واجهة التطبيق، اضغط تشفير، وتُحفظ البيانات المشفرة في حسابك فوراً.</li>
                    <li><strong>تشفير الملفات:</strong> ارفع ملفات من أي نوع (مستندات، صور، أرشيفات) ليشفّرها التطبيق ويخزّنها بشكل آمن.</li>
                    <li><strong>فك التشفير عند الطلب:</strong> عند الحاجة لقراءة المحتوى الأصلي، اضغط زر «فك التشفير» لعرض النص أو تحميل الملف كما كان.</li>
                    <li><strong>واجهة عربية كاملة:</strong> التطبيق مصمّم بالكامل للغة العربية مع دعم اتجاه RTL وتجربة استخدام مريحة.</li>
                    <li><strong>حماية الحسابات:</strong> كلمات المرور تُخزّن بشكل مشفر باستخدام bcrypt، ولا يتم تخزينها أبداً كنص واضح.</li>
                    <li><strong>سهولة الاستخدام:</strong> لا تحتاج معرفة تقنية للتشفير — الواجهة واضحة وخطوات الاستخدام بسيطة.</li>
                </ul>
            </section>

            <section class="about-section">
                <h2>متى تستخدم CipherUI؟</h2>
                <p>
                    CipherUI مناسب لحماية ملاحظاتك الشخصية، مسودات المستندات الحساسة، نسخ احتياطية مشفرة من ملفاتك المهمة، أو أي بيانات تريد التأكد من عدم وصول الآخرين إليها حتى لو تسربت النسخة. التطبيق لا يحتفظ بنسخة من مفاتيحك — أمانك وسيطرتك على بياناتك تبدأ وتنتهي عندك.
                </p>
            </section>

            <section class="about-section">
                <h2>البدء</h2>
                <p>
                    إنشاء حساب مجاني للبدء في تشفير وحماية بياناتك. العملية سريعة ولا تتطلب سوى اسم مستخدم وكلمة مرور آمنة.
                </p>
                <?php if (!$loggedIn): ?>
                <div class="about-cta">
                    <a href="register.php" class="btn btn-primary btn-large">إنشاء حساب الآن</a>
                    <a href="login.php" class="btn btn-outline btn-large">لديك حساب؟ تسجيل الدخول</a>
                </div>
                <?php endif; ?>
            </section>
        </article>
    </main>
</body>
</html>
