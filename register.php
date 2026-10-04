<?php
/**
 * CipherUI - تسجيل مستخدم جديد
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // التحقق من المدخلات
    if (empty($username) || empty($password)) {
        $error = 'يرجى ملء جميع الحقول.';
    } elseif (strlen($username) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
    } elseif ($password !== $confirm) {
        $error = 'كلمتا المرور غير متطابقتين.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // التحقق من عدم تكرار اسم المستخدم
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'اسم المستخدم موجود مسبقاً.';
            } else {
                // إنشاء الحساب مع تشفير كلمة المرور
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())');
                $stmt->execute([$username, $hashedPassword]);
                
                $success = 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.';
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ: ' . $e->getMessage();  // للتشخيص - عدّلها بعد الحل
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التسجيل - CipherUI</title>
    <link rel="icon" type="image/png" href="assets/images/Logo2.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <img src="assets/images/Logo2.png" alt="CipherUI" class="logo-img">
            </a>
            <nav class="header-nav">
                <a href="about.php" class="nav-link">نبذة عن</a>
                <a href="login.php" class="nav-link">تسجيل الدخول</a>
            </nav>
        </div>
    </header>
    <div class="container">
        <main class="auth-box">
            <div class="auth-brand">
                <div class="logo-dark-bg">
                    <img src="assets/images/Logo2.png" alt="CipherUI" class="auth-logo">
                </div>
            </div>
            <p class="subtitle">إنشاء حساب جديد</p>
            
            <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php" id="registerForm">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required minlength="3">
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
            </form>
            
            <p class="auth-link">لديك حساب؟ <a href="login.php">تسجيل الدخول</a></p>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
