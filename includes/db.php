<?php
/**
 * CipherUI - Database Connection
 * اتصال قاعدة البيانات MySQL
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'cipherui');
define('DB_USER', 'root');
define('DB_PASS', '');

// إنشاء الاتصال
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>
