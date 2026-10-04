<?php
/**
 * CipherUI - Encryption/Decryption Module
 * تشفير وفك التشفير باستخدام AES-256-CBC
 */

require_once __DIR__ . '/db.php';

// المفتاح السري (يجب تغييره في بيئة الإنتاج)
// يُخزّن بشكل آمن ولا يُعرض للمستخدم - 32 بايت مطلوب لـ AES-256
define('CIPHER_KEY', hash('sha256', 'CipherUI_SecureKey_v1', true));
define('CIPHER_METHOD', 'aes-256-cbc');

/**
 * تشفير النص
 * @param string $data النص للتشفير
 * @return array|false ['encrypted' => base64, 'iv' => base64] أو false عند الفشل
 */
function encryptData($data) {
    $ivLength = openssl_cipher_iv_length(CIPHER_METHOD);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    $encrypted = openssl_encrypt(
        $data,
        CIPHER_METHOD,
        CIPHER_KEY,
        OPENSSL_RAW_DATA,
        $iv
    );
    
    if ($encrypted === false) {
        return false;
    }
    
    return [
        'encrypted' => base64_encode($encrypted),
        'iv' => base64_encode($iv)
    ];
}

/**
 * فك تشفير النص
 * @param string $encryptedData البيانات المشفرة (base64)
 * @param string $iv متجه التهيئة (base64)
 * @return string|false النص الأصلي أو false عند الفشل
 */
function decryptData($encryptedData, $iv) {
    $encryptedData = trim($encryptedData);
    $iv = trim($iv);
    
    $encrypted = base64_decode($encryptedData, true);
    $ivDecoded = base64_decode($iv, true);
    
    if ($encrypted === false || $ivDecoded === false) {
        return false;
    }
    
    $ivLen = openssl_cipher_iv_length(CIPHER_METHOD);
    if (strlen($ivDecoded) !== $ivLen) {
        return false;
    }
    
    $decrypted = openssl_decrypt(
        $encrypted,
        CIPHER_METHOD,
        CIPHER_KEY,
        OPENSSL_RAW_DATA,
        $ivDecoded
    );
    
    return $decrypted;
}
?>
