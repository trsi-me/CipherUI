<?php
/**
 * CipherUI - تسجيل الدخول
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireGuest();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى ملء جميع الحقول.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ. حاول مرة أخرى لاحقاً.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - CipherUI</title>
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
                <a href="login.php" class="nav-link active">تسجيل الدخول</a>
                <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
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
            <p class="subtitle">تسجيل الدخول</p>
            
            <?php if (isset($_GET['registered'])): ?>
            <div class="message success">تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.</div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">دخول</button>
            </form>
            
            <p class="auth-link">لا تملك حساباً؟ <a href="register.php">إنشاء حساب</a></p>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
