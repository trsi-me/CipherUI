<?php
/**
 * CipherUI - رفع ملفات وتشفير محتواها
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/crypto.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'الملف كبير جداً',
        UPLOAD_ERR_FORM_SIZE => 'الملف كبير جداً',
        UPLOAD_ERR_PARTIAL => 'تم رفع جزء فقط',
        UPLOAD_ERR_NO_FILE => 'لم يتم اختيار ملف',
        UPLOAD_ERR_NO_TMP_DIR => 'خطأ في الخادم',
        UPLOAD_ERR_CANT_WRITE => 'فشل الكتابة',
        UPLOAD_ERR_EXTENSION => 'تم إيقاف الرفع'
    ];
    $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'message' => $errors[$code] ?? 'فشل رفع الملف']);
    exit;
}

$file = $_FILES['file'];
$content = file_get_contents($file['tmp_name']);

if ($content === false) {
    echo json_encode(['success' => false, 'message' => 'فشل قراءة الملف']);
    exit;
}

// تحويل المحتوى إلى نص قابل للتشفير (base64 للملفات الثنائية)
// الصيغة: FILE:mime:base64:filename (للدعم المستقبلي) أو FILE:base64:filename (للتوافق مع الملفات القديمة)
$isBinary = !mb_check_encoding($content, 'UTF-8');
if ($isBinary) {
    $mime = $file['type'] ?: 'application/octet-stream';
    $filename = basename($file['name']);
    $b64 = base64_encode($content);
    $plainText = 'FILE:' . $mime . ':' . $b64 . ':' . $filename;
} else {
    $plainText = $content;
}

$result = encryptData($plainText);
if ($result === false) {
    echo json_encode(['success' => false, 'message' => 'فشل التشفير']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO encrypted_data (user_id, data, iv, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([getCurrentUserId(), $result['encrypted'], $result['iv']]);
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'تم رفع وتشفير الملف بنجاح',
        'id' => (int)$id
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'فشل حفظ البيانات']);
}
?>
