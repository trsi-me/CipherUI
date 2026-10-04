<?php
/**
 * CipherUI - Authentication Module
 * إدارة المصادقة والجلسات
 */

session_start();

/**
 * التحقق من تسجيل الدخول
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * الحصول على معرف المستخدم
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * الحصول على اسم المستخدم
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * إعادة التوجيه للمسجلين فقط
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * إعادة التوجيه لغير المسجلين (صفحات الدخول والتسجيل)
 */
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
