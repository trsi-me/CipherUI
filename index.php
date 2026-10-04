<?php
/**
 * CipherUI - الصفحة الرئيسية
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
?>
