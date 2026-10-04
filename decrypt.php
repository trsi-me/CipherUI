<?php
/**
 * CipherUI - فك تشفير البيانات (للمستخدم المصرح فقط)
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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT data, iv FROM encrypted_data WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, getCurrentUserId()]);
    $row = $stmt->fetch();
    
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'البيانات غير موجودة أو غير مصرح لك بالوصول']);
        exit;
    }
    
    $decrypted = decryptData($row['data'], $row['iv']);
    if ($decrypted === false) {
        echo json_encode(['success' => false, 'message' => 'فشل فك التشفير']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $decrypted
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
}
?>
