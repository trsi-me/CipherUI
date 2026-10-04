<?php
/**
 * CipherUI - الواجهة الرئيسية
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/crypto.php';

requireLogin();

$message = '';
$messageType = '';

// جلب البيانات المشفرة للمستخدم
$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT id, data, iv, created_at FROM encrypted_data WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([getCurrentUserId()]);
$encryptedItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - CipherUI</title>
    <link rel="icon" type="image/png" href="assets/images/Logo2.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="dashboard.php" class="logo">
                <img src="assets/images/Logo2.png" alt="CipherUI" class="logo-img">
            </a>
            <nav class="header-nav">
                <a href="dashboard.php" class="nav-link active">لوحة التحكم</a>
                <a href="about.php" class="nav-link">نبذة عن</a>
                <span class="user-name">مرحباً، <?= htmlspecialchars(getCurrentUsername()) ?></span>
                <a href="logout.php" class="btn btn-outline btn-small">خروج</a>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <section class="dashboard-section">
            <h2>تشفير البيانات النصية</h2>
            <form id="textEncryptForm" class="encrypt-form">
                <div class="form-group">
                    <label for="plainText">النص للتشفير</label>
                    <textarea id="plainText" name="plain_text" rows="5" placeholder="أدخل النص الذي تريد تشفيره..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">تشفير وحفظ</button>
            </form>
        </section>

        <section class="dashboard-section">
            <h2>رفع ملف وتشفيره</h2>
            <form id="uploadForm" class="encrypt-form">
                <div class="form-group">
                    <label for="fileInput">اختر ملفاً</label>
                    <input type="file" id="fileInput" name="file" accept="*/*">
                </div>
                <button type="submit" class="btn btn-primary">رفع وتشفير</button>
            </form>
        </section>

        <section class="dashboard-section">
            <h2>بياناتك المشفرة</h2>
            <div id="messageArea"></div>
            
            <?php if (empty($encryptedItems)): ?>
            <p class="empty-state">لا توجد بيانات مشفرة حتى الآن.</p>
            <?php else: ?>
            <div class="data-list">
                <?php foreach ($encryptedItems as $item): ?>
                <div class="data-item" data-id="<?= (int)$item['id'] ?>">
                    <div class="data-preview">
                        <code><?= htmlspecialchars(substr($item['data'], 0, 50)) ?>...</code>
                    </div>
                    <span class="data-date"><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></span>
                    <div class="data-actions">
                        <button type="button" class="btn btn-small btn-decrypt" data-id="<?= (int)$item['id'] ?>">فك التشفير</button>
                        <button type="button" class="btn btn-small btn-delete" data-id="<?= (int)$item['id'] ?>">حذف</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <div id="decryptModal" class="modal">
        <div class="modal-content">
            <h3>البيانات الأصلية</h3>
            <div id="decryptedContent" class="decrypted-content"></div>
            <button type="button" class="btn btn-primary" id="closeModalBtn">إغلاق</button>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
